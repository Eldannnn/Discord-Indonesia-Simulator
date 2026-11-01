<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

/**
 * Data Access Model for the National Information Portal (`Models/ServerTelemetryModel.php`).
 * Queries News, DPR Composition, National Economy Metrics, Active Laws, and Political Parties
 * dynamically from the MySQL database.
 */
final class ServerTelemetryModel
{
    /** @var PDO|null Database connection handle */
    private ?PDO $db;

    /**
     * Initializes the telemetry and portal data model.
     *
     * @param PDO|null $db Injected PDO instance or null for default resolution.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Retrieves current national indicators dynamically from MySQL tables.
     *
     * @return array<string, mixed> Associative array of live database metrics.
     */
    public function fetchNationalTelemetry(): array
    {
        $metrics = [];

        if ($this->db === null) {
            return $metrics;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT metric_key, metric_value
                 FROM server_telemetry
                 WHERE is_current = :is_current'
            );
            $stmt->execute([':is_current' => 1]);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $metrics[(string) $row['metric_key']] = $row['metric_value'];
            }

            $userCountStmt = $this->db->query('SELECT COUNT(*) FROM users');
            $metrics['registered_citizens_db'] = (int) ($userCountStmt ? $userCountStmt->fetchColumn() : 0);

            $lawCountStmt = $this->db->query('SELECT COUNT(*) FROM active_laws WHERE status = "BERLAKU_AKTIF"');
            $metrics['active_laws_db'] = (int) ($lawCountStmt ? $lawCountStmt->fetchColumn() : 0);

            $partyCountStmt = $this->db->query('SELECT COUNT(*) FROM political_parties');
            $metrics['parties_count_db'] = (int) ($partyCountStmt ? $partyCountStmt->fetchColumn() : 0);

            $seatsStmt = $this->db->query('SELECT COALESCE(SUM(dpr_seats), 0) FROM political_parties');
            $metrics['total_dpr_seats_db'] = (int) ($seatsStmt ? $seatsStmt->fetchColumn() : 0);
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchNationalTelemetry] Query error: ' . $exception->getMessage());
        }

        return $metrics;
    }

    /**
     * Retrieves national news articles from `national_news`.
     *
     * @return array<int, array<string, mixed>> List of news articles ordered by newest first.
     */
    public function fetchNationalNews(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT id, news_code, category, headline, summary, author_name, published_at
                 FROM national_news
                 ORDER BY published_at DESC, id DESC
                 LIMIT 12'
            );

            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchNationalNews] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves national macroeconomic indicators from `national_economy_metrics`.
     *
     * @return array<int, array<string, mixed>> List of economic metrics.
     */
    public function fetchNationalEconomyMetrics(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT id, metric_code, metric_label, metric_value, metric_unit,
                        trend_direction, trend_change, period_label, description
                 FROM national_economy_metrics
                 ORDER BY display_order ASC, id ASC'
            );

            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchNationalEconomyMetrics] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves all political parties, their ideologies, descriptions, and DPR seat counts from `political_parties`.
     *
     * @return array<int, array<string, mixed>> List of political parties ordered by seat share.
     */
    public function fetchPoliticalParties(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT id, party_code, party_name, party_acronym, ideology,
                        coalition_status, chairperson_name, dpr_seats,
                        total_parliament_seats, badge_theme, description
                 FROM political_parties
                 ORDER BY dpr_seats DESC, display_order ASC'
            );

            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchPoliticalParties] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves all active laws and regulations from `active_laws`.
     *
     * @return array<int, array<string, mixed>> List of active laws ordered by enactment date.
     */
    public function fetchActiveLaws(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT id, law_number, law_type, title, category,
                        summary, enacted_by, enacted_date, status
                 FROM active_laws
                 ORDER BY enacted_date DESC, id DESC'
            );

            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchActiveLaws] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves the latest state Gazette (Lembaran Berita Negara) records directly from `executive_decrees_log`.
     *
     * @return array<int, array<string, mixed>> Chronological list of state decrees from MySQL.
     */
    public function fetchRecentStateGazette(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT d.id,
                        d.document_number AS code,
                        d.document_type,
                        d.title,
                        d.summary_content,
                        d.counter_approval_status AS status,
                        d.executed_at,
                        u.character_name AS actor,
                        r.role_title,
                        p.permission_name
                 FROM executive_decrees_log d
                 INNER JOIN users u ON d.user_id = u.id
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 INNER JOIN rbac_permissions p ON d.permission_id = p.id
                 ORDER BY d.executed_at DESC, d.id DESC
                 LIMIT 10'
            );
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[ServerTelemetryModel::fetchRecentStateGazette] Query error: ' . $exception->getMessage());
            return [];
        }
    }
}
