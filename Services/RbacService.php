<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExecutiveDecreeModel;
use App\Models\ExecutiveUserModel;
use App\Models\RbacRoleModel;
use App\Models\ServerTelemetryModel;

/**
 * Domain Service for the National Information Portal and Role-Exclusive Executive Workspaces.
 * Queries all metrics, news, DPR composition, economy indicators, active laws, and political parties
 * dynamically from the MySQL database.
 */
final class RbacService
{
    /** @var RbacRoleModel Data access model for roles */
    private RbacRoleModel $roleModel;

    /** @var ServerTelemetryModel Data access model for national portal data */
    private ServerTelemetryModel $telemetryModel;

    /** @var ExecutiveUserModel Data access model for users */
    private ExecutiveUserModel $executiveUserModel;

    /** @var ExecutiveDecreeModel Data access model for executive decrees */
    private ExecutiveDecreeModel $decreeModel;

    /**
     * Initializes the service with required data models.
     *
     * @param RbacRoleModel|null $roleModel Optional injected role model.
     * @param ServerTelemetryModel|null $telemetryModel Optional injected telemetry model.
     * @param ExecutiveUserModel|null $executiveUserModel Optional injected user model.
     * @param ExecutiveDecreeModel|null $decreeModel Optional injected decree model.
     */
    public function __construct(
        ?RbacRoleModel $roleModel = null,
        ?ServerTelemetryModel $telemetryModel = null,
        ?ExecutiveUserModel $executiveUserModel = null,
        ?ExecutiveDecreeModel $decreeModel = null
    ) {
        $this->roleModel = $roleModel ?? new RbacRoleModel();
        $this->telemetryModel = $telemetryModel ?? new ServerTelemetryModel();
        $this->executiveUserModel = $executiveUserModel ?? new ExecutiveUserModel();
        $this->decreeModel = $decreeModel ?? new ExecutiveDecreeModel();
    }

    /**
     * Builds the Main Information Portal payload (`?page=beranda`).
     * Contains News, DPR Composition, Economy Indicators, Active Laws, and Political Parties.
     *
     * @return array<string, mixed> Complete structured data for the main portal view.
     */
    public function getLandingPagePayload(): array
    {
        $rawGazette = $this->telemetryModel->fetchRecentStateGazette();
        $rawParties = $this->telemetryModel->fetchPoliticalParties();
        $rawNews = $this->telemetryModel->fetchNationalNews();
        $rawLaws = $this->telemetryModel->fetchActiveLaws();

        return [
            'telemetry' => $this->telemetryModel->fetchNationalTelemetry(),
            'news_feed' => $this->formatNewsRecords($rawNews),
            'economy_metrics' => $this->telemetryModel->fetchNationalEconomyMetrics(),
            'political_parties' => $this->enrichPartiesWithPercentage($rawParties),
            'dpr_summary' => $this->computeDprCompositionSummary($rawParties),
            'active_laws' => $this->formatActiveLaws($rawLaws),
            'gazette_feed' => $this->formatGazetteRecords($rawGazette),
            'executive_users' => $this->executiveUserModel->fetchExecutiveUsers(),
        ];
    }

    /**
     * Builds the dedicated Presidential Workspace payload (`?page=presiden`).
     *
     * @return array<string, mixed> Structured data for the President's separate page.
     */
    public function getPresidentWorkspacePayload(): array
    {
        $rawDecrees = $this->decreeModel->fetchDecreesByRole('ROLE_PRESIDEN');

        return [
            'telemetry' => $this->telemetryModel->fetchNationalTelemetry(),
            'president_permissions' => $this->decreeModel->fetchAuthorizedPermissionsForRole('ROLE_PRESIDEN'),
            'president_decrees' => $this->formatRoleDecrees($rawDecrees),
        ];
    }

    /**
     * Builds the dedicated Vice-Presidential Workspace payload (`?page=wakil-presiden`).
     *
     * @return array<string, mixed> Structured data for the Vice President's separate page.
     */
    public function getVicePresidentWorkspacePayload(): array
    {
        $rawDecrees = $this->decreeModel->fetchDecreesByRole('ROLE_WAKIL_PRESIDEN');

        return [
            'telemetry' => $this->telemetryModel->fetchNationalTelemetry(),
            'vice_president_permissions' => $this->decreeModel->fetchAuthorizedPermissionsForRole('ROLE_WAKIL_PRESIDEN'),
            'vice_president_decrees' => $this->formatRoleDecrees($rawDecrees),
        ];
    }

    /**
     * Enriches political party rows with seat percentage calculations.
     *
     * @param array<int, array<string, mixed>> $parties Raw rows from `political_parties`.
     * @return array<int, array<string, mixed>> Enriched party rows.
     */
    private function enrichPartiesWithPercentage(array $parties): array
    {
        $totalSeats = 0;
        foreach ($parties as $party) {
            $totalSeats += (int) ($party['dpr_seats'] ?? 0);
        }
        $denominator = max(1, $totalSeats);

        $enriched = [];
        foreach ($parties as $party) {
            $seats = (int) ($party['dpr_seats'] ?? 0);
            $party['seat_percentage'] = round(($seats / $denominator) * 100, 1);
            $party['coalition_label'] = ($party['coalition_status'] ?? '') === 'KOALISI_PEMERINTAH'
                ? 'Koalisi Pemerintah'
                : 'Oposisi Parlemen';
            $enriched[] = $party;
        }

        return $enriched;
    }

    /**
     * Computes aggregate DPR RI composition metrics (Total Seats, Coalition vs Opposition).
     *
     * @param array<int, array<string, mixed>> $parties Raw party rows.
     * @return array<string, mixed> Aggregated parliament metrics.
     */
    private function computeDprCompositionSummary(array $parties): array
    {
        $totalSeats = 0;
        $coalitionSeats = 0;
        $oppositionSeats = 0;
        $coalitionPartiesCount = 0;
        $oppositionPartiesCount = 0;

        foreach ($parties as $party) {
            $seats = (int) ($party['dpr_seats'] ?? 0);
            $totalSeats += $seats;

            if (($party['coalition_status'] ?? '') === 'KOALISI_PEMERINTAH') {
                $coalitionSeats += $seats;
                $coalitionPartiesCount++;
            } else {
                $oppositionSeats += $seats;
                $oppositionPartiesCount++;
            }
        }

        $denom = max(1, $totalSeats);

        return [
            'total_seats' => $totalSeats,
            'coalition_seats' => $coalitionSeats,
            'opposition_seats' => $oppositionSeats,
            'coalition_percent' => round(($coalitionSeats / $denom) * 100, 1),
            'opposition_percent' => round(($oppositionSeats / $denom) * 100, 1),
            'coalition_parties_count' => $coalitionPartiesCount,
            'opposition_parties_count' => $oppositionPartiesCount,
            'total_factions' => count($parties),
        ];
    }

    /**
     * Formats news rows from `national_news`.
     *
     * @param array<int, array<string, mixed>> $rows Raw news rows.
     * @return array<int, array<string, string>> Formatted news list.
     */
    private function formatNewsRecords(array $rows): array
    {
        $formatted = [];
        foreach ($rows as $row) {
            $publishedAt = (string) ($row['published_at'] ?? '');
            $dateLabel = $publishedAt !== ''
                ? date('d M Y, H:i', strtotime($publishedAt)) . ' WIB'
                : '-';

            $formatted[] = [
                'news_code' => (string) ($row['news_code'] ?? ''),
                'category' => (string) ($row['category'] ?? ''),
                'headline' => (string) ($row['headline'] ?? ''),
                'summary' => (string) ($row['summary'] ?? ''),
                'author_name' => (string) ($row['author_name'] ?? ''),
                'published_label' => $dateLabel,
            ];
        }

        return $formatted;
    }

    /**
     * Formats active law rows from `active_laws`.
     *
     * @param array<int, array<string, mixed>> $rows Raw law rows.
     * @return array<int, array<string, string>> Formatted law list.
     */
    private function formatActiveLaws(array $rows): array
    {
        $formatted = [];
        foreach ($rows as $row) {
            $enactedDate = (string) ($row['enacted_date'] ?? '');
            $dateLabel = $enactedDate !== ''
                ? date('d M Y', strtotime($enactedDate))
                : '-';

            $formatted[] = [
                'law_number' => (string) ($row['law_number'] ?? ''),
                'law_type' => (string) ($row['law_type'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'category' => (string) ($row['category'] ?? ''),
                'summary' => (string) ($row['summary'] ?? ''),
                'enacted_by' => (string) ($row['enacted_by'] ?? ''),
                'enacted_date_label' => $dateLabel,
                'status_label' => ($row['status'] ?? '') === 'BERLAKU_AKTIF' ? 'Berlaku Aktif' : 'Dalam Evaluasi',
            ];
        }

        return $formatted;
    }

    /**
     * Formats raw decree rows from `executive_decrees_log` for display in Lembaran Berita Negara.
     *
     * @param array<int, array<string, mixed>> $rows Raw database rows.
     * @return array<int, array<string, string>> Formatted gazette entries.
     */
    private function formatGazetteRecords(array $rows): array
    {
        $formatted = [];
        foreach ($rows as $row) {
            $executedAt = (string) ($row['executed_at'] ?? '');
            $timestampLabel = $executedAt !== ''
                ? date('d M Y, H:i', strtotime($executedAt)) . ' WIB'
                : '-';

            $formatted[] = [
                'code' => (string) ($row['code'] ?? ''),
                'document_type' => (string) ($row['document_type'] ?? ''),
                'branch' => (string) ($row['role_title'] ?? ''),
                'actor' => (string) ($row['actor'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'summary_content' => (string) ($row['summary_content'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'timestamp' => $timestampLabel,
            ];
        }

        return $formatted;
    }

    /**
     * Formats role-specific decree history rows for the President or Vice President page.
     *
     * @param array<int, array<string, mixed>> $rows Raw decree rows.
     * @return array<int, array<string, string>> Formatted decree list.
     */
    private function formatRoleDecrees(array $rows): array
    {
        $formatted = [];
        foreach ($rows as $row) {
            $executedAt = (string) ($row['executed_at'] ?? '');
            $timestampLabel = $executedAt !== ''
                ? date('d M Y, H:i', strtotime($executedAt)) . ' WIB'
                : '-';

            $formatted[] = [
                'document_number' => (string) ($row['document_number'] ?? ''),
                'document_type' => (string) ($row['document_type'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'summary_content' => (string) ($row['summary_content'] ?? ''),
                'permission_name' => (string) ($row['permission_name'] ?? ''),
                'status' => (string) ($row['counter_approval_status'] ?? ''),
                'timestamp' => $timestampLabel,
            ];
        }

        return $formatted;
    }
}
