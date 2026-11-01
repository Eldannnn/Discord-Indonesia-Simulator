<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CabinetModel;

/**
 * Domain Service for Presidential Cabinet Administration & Ministerial Appointments (`Services/CabinetService.php`).
 * Orchestrates business rules, ensures presidential authorization (`ROLE_PRESIDEN`),
 * enforces the constitutional single-office rule, and handles input sanitization.
 */
final class CabinetService
{
    /** @var CabinetModel Data access model for cabinet and ministries */
    private CabinetModel $cabinetModel;

    /**
     * Initializes the Cabinet Service.
     *
     * @param CabinetModel|null $cabinetModel Optional injected model.
     */
    public function __construct(?CabinetModel $cabinetModel = null)
    {
        $this->cabinetModel = $cabinetModel ?? new CabinetModel();
    }

    /**
     * Aggregates all data required to render the Cabinet Workspace tab on the President's page.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @return array<string, mixed> Aggregated cabinet presentation payload.
     */
    public function getCabinetWorkspaceData(?array $authUser): array
    {
        $settings = $this->cabinetModel->getCabinetSettings();
        $golonganIMinistries = $this->cabinetModel->getGolonganIMinistries();
        $candidates = $this->cabinetModel->getEligibleCandidates();

        // Enrich Golongan I ministries with formatted labels
        $enrichedMinistries = [];
        $occupiedCount = 0;
        foreach ($golonganIMinistries as $min) {
            $isOccupied = ($min['office_status'] ?? '') === 'MENJABAT' && !empty($min['user_id']);
            if ($isOccupied) {
                $occupiedCount++;
            }

            $appointedAt = (string) ($min['appointed_at'] ?? '');
            $formattedAppointed = $appointedAt !== ''
                ? date('d M Y, H:i', strtotime($appointedAt)) . ' WIB'
                : 'Belum Ditetapkan';

            $min['is_occupied'] = $isOccupied;
            $min['appointed_label'] = $formattedAppointed;
            $enrichedMinistries[] = $min;
        }

        // Definition of upcoming ministerial groups (Coming Soon)
        $comingSoonGroups = [
            [
                'group_key' => 'GOLONGAN_II',
                'group_title' => 'Menteri Golongan II (Urusan Pemerintahan Substantif)',
                'badge' => 'Coming Soon — Tahap Pembentukan Lanjutan',
                'description' => 'Kementerian yang membidangi urusan substantif dan ruang lingkup sektoral pembangunan nasional (Pasal 17 ayat 3 UUD NRI 1945).',
                'examples' => [
                    'Kementerian Keuangan (Kemenkeu)',
                    'Kementerian Hukum & HAM (Kemenkumham)',
                    'Kementerian Pendidikan, Sains, dan Teknologi',
                    'Kementerian Kesehatan (Kemenkes)',
                    'Kementerian Perindustrian & Perdagangan',
                    'Kementerian Energi dan Sumber Daya Mineral',
                ],
            ],
            [
                'group_key' => 'GOLONGAN_III',
                'group_title' => 'Menteri Koordinator (Kemenko)',
                'badge' => 'Coming Soon — Penataan Kemenko',
                'description' => 'Kementerian koordinator yang menyinkronkan dan mengoordinasikan urusan kementerian-kementerian dalam lingkup koordinasinya.',
                'examples' => [
                    'Kemenko Bidang Politik dan Keamanan (Kemenko Polkam)',
                    'Kemenko Bidang Perekonomian',
                    'Kemenko Bidang Pembangunan Manusia dan Kebudayaan (Kemenko PMK)',
                    'Kemenko Bidang Pangan, Energi, dan Infrastruktur',
                ],
            ],
            [
                'group_key' => 'SETINGKAT_MENTERI',
                'group_title' => 'Pejabat Setingkat Menteri & Lembaga Pemerintah',
                'badge' => 'Coming Soon — Integrasi Lembaga Negara',
                'description' => 'Pejabat tinggi negara yang kedudukan dan hak keuangannya setingkat menteri kabinet sesuai undang-undang.',
                'examples' => [
                    'Jaksa Agung Republik Indonesia',
                    'Panglima Tentara Nasional Indonesia (TNI)',
                    'Kepala Kepolisian Negara Republik Indonesia (Polri)',
                    'Kepala Badan Intelijen Negara (BIN)',
                ],
            ],
        ];

        return [
            'settings' => $settings,
            'golongan_i' => $enrichedMinistries,
            'total_golongan_i' => count($enrichedMinistries),
            'occupied_count' => $occupiedCount,
            'vacant_count' => count($enrichedMinistries) - $occupiedCount,
            'eligible_candidates' => $candidates,
            'candidate_count' => count($candidates),
            'coming_soon_groups' => $comingSoonGroups,
        ];
    }

    /**
     * Validates and updates the Cabinet name and vision statement.
     * Exclusively authorized for `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @param array<string, mixed> $payload Request parameters (`cabinet_name`, `cabinet_vision`).
     * @return array<string, mixed> Response result.
     */
    public function updateCabinetName(?array $authUser, array $payload): array
    {
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [],
                'message' => 'Akses ditolak: Hanya Presiden Republik Indonesia yang berwenang mengubah identitas kabinet.',
            ];
        }

        $errors = [];
        $name = trim((string) ($payload['cabinet_name'] ?? ''));
        $vision = trim((string) ($payload['cabinet_vision'] ?? ''));

        if ($name === '') {
            $errors['cabinet_name'] = 'Nama kabinet wajib diisi.';
        } elseif (mb_strlen($name) < 4 || mb_strlen($name) > 120) {
            $errors['cabinet_name'] = 'Nama kabinet harus terdiri dari 4 hingga 120 karakter.';
        }

        if ($vision === '') {
            $errors['cabinet_vision'] = 'Visi dan pernyataan arah kabinet wajib diisi.';
        } elseif (mb_strlen($vision) < 10 || mb_strlen($vision) > 255) {
            $errors['cabinet_vision'] = 'Visi kabinet harus terdiri dari 10 hingga 255 karakter.';
        }

        if ($errors !== []) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => $errors,
                'message' => 'Terdapat kesalahan pada data nama atau visi kabinet.',
            ];
        }

        $presidentUserId = (int) $authUser['id'];
        $updated = $this->cabinetModel->updateCabinetSettings($name, $vision, $presidentUserId);

        if (!$updated) {
            return [
                'is_valid' => false,
                'http_code' => 500,
                'errors' => [],
                'message' => 'Gagal memperbarui setelan kabinet pada basis data.',
            ];
        }

        return [
            'is_valid' => true,
            'http_code' => 200,
            'errors' => [],
            'message' => 'Identitas nama dan visi kabinet berhasil diperbarui.',
            'cabinet_name' => $name,
            'cabinet_vision' => $vision,
        ];
    }

    /**
     * Validates and appoints an eligible citizen as a Cabinet Minister.
     * Enforces the constitutional requirement that the candidate must not hold another public office.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @param array<string, mixed> $payload Request parameters (`ministry_id`, `candidate_user_id`).
     * @return array<string, mixed> Response result.
     */
    public function appointMinister(?array $authUser, array $payload): array
    {
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [],
                'message' => 'Akses ditolak: Hanya Presiden Republik Indonesia yang memiliki hak prerogatif mengangkat menteri.',
            ];
        }

        $errors = [];
        $ministryId = (int) ($payload['ministry_id'] ?? 0);
        $candidateUserId = (int) ($payload['candidate_user_id'] ?? 0);

        if ($ministryId <= 0) {
            $errors['ministry_id'] = 'Pilih kementerian yang hendak diisi pejabat menteri.';
        }

        if ($candidateUserId <= 0) {
            $errors['candidate_user_id'] = 'Pilih warga negara yang memenuhi syarat pengangkatan menteri.';
        }

        if ($errors !== []) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => $errors,
                'message' => 'Pilihan kementerian atau calon menteri tidak valid.',
            ];
        }

        $presidentUserId = (int) $authUser['id'];
        $result = $this->cabinetModel->appointMinister($ministryId, $candidateUserId, $presidentUserId);

        if (!($result['success'] ?? false)) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => ['candidate_user_id' => $result['message']],
                'message' => $result['message'],
            ];
        }

        return [
            'is_valid' => true,
            'http_code' => 200,
            'errors' => [],
            'message' => $result['message'],
            'decree_number' => $result['decree_number'] ?? '',
            'minister_name' => $result['minister_name'] ?? '',
        ];
    }

    /**
     * Validates and dismisses an incumbent minister from office with honorable discharge.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @param array<string, mixed> $payload Request parameters (`ministry_id`, `reason`).
     * @return array<string, mixed> Response result.
     */
    public function dismissMinister(?array $authUser, array $payload): array
    {
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [],
                'message' => 'Akses ditolak: Hanya Presiden Republik Indonesia yang berwenang memberhentikan menteri.',
            ];
        }

        $ministryId = (int) ($payload['ministry_id'] ?? 0);
        $reason = trim((string) ($payload['reason'] ?? ''));

        if ($ministryId <= 0) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => ['ministry_id' => 'Kementerian yang dipilih tidak sah.'],
                'message' => 'Kementerian yang dipilih tidak sah.',
            ];
        }

        $presidentUserId = (int) $authUser['id'];
        $result = $this->cabinetModel->dismissMinister($ministryId, $reason, $presidentUserId);

        if (!($result['success'] ?? false)) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => ['ministry_id' => $result['message']],
                'message' => $result['message'],
            ];
        }

        return [
            'is_valid' => true,
            'http_code' => 200,
            'errors' => [],
            'message' => $result['message'],
            'decree_number' => $result['decree_number'] ?? '',
            'former_minister' => $result['former_minister'] ?? '',
        ];
    }
}
