<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExecutiveDecreeModel;

/**
 * Domain Service for Presidential (`ROLE_PRESIDEN`) and Vice-Presidential (`ROLE_WAKIL_PRESIDEN`)
 * Official Actions (`Services/ExecutiveActionService.php`).
 * Enforces role separation and custom input validation.
 */
final class ExecutiveActionService
{
    /** @var ExecutiveDecreeModel Data access model for executive decrees */
    private ExecutiveDecreeModel $decreeModel;

    /**
     * Initializes the Executive Action Service.
     *
     * @param ExecutiveDecreeModel|null $decreeModel Optional injected model.
     */
    public function __construct(?ExecutiveDecreeModel $decreeModel = null)
    {
        $this->decreeModel = $decreeModel ?? new ExecutiveDecreeModel();
    }

    /**
     * Validates and executes a Presidential Decree/Regulation (`PERPRES`, `KEPPRES`, `PP`, `PERPPU`, `NOTA_KEUANGAN`).
     * Exclusively restricted to an authenticated user holding `ROLE_PRESIDEN`.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @param array<string, mixed> $payload Submitted form fields.
     * @return array<string, mixed> Validation/execution result.
     */
    public function issuePresidentialAct(?array $authUser, array $payload): array
    {
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_PRESIDEN') {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [],
                'message' => 'Akses ditolak: Hanya Presiden Republik Indonesia yang berwenang menerbitkan regulasi di halaman ini.',
            ];
        }

        $allowedTypes = ['PERPRES', 'KEPPRES', 'PP', 'PERPPU', 'NOTA_KEUANGAN'];
        return $this->processExecutiveSubmission($authUser, $payload, $allowedTypes, 'Presiden RI');
    }

    /**
     * Validates and executes a Vice-Presidential Instruction/Coordination Act (`INSTRUKSI_WAPRES`, `RISALAH_RAKOR_WAPRES`, `AUDIT_KINERJA_KABINET`).
     * Exclusively restricted to an authenticated user holding `ROLE_WAKIL_PRESIDEN`.
     *
     * @param array<string, mixed>|null $authUser Authenticated session user.
     * @param array<string, mixed> $payload Submitted form fields.
     * @return array<string, mixed> Validation/execution result.
     */
    public function issueVicePresidentialAct(?array $authUser, array $payload): array
    {
        if ($authUser === null || ($authUser['role_code'] ?? '') !== 'ROLE_WAKIL_PRESIDEN') {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [],
                'message' => 'Akses ditolak: Hanya Wakil Presiden Republik Indonesia yang berwenang menerbitkan instruksi di halaman ini.',
            ];
        }

        $allowedTypes = ['INSTRUKSI_WAPRES', 'RISALAH_RAKOR_WAPRES', 'AUDIT_KINERJA_KABINET'];
        return $this->processExecutiveSubmission($authUser, $payload, $allowedTypes, 'Wakil Presiden RI');
    }

    /**
     * Shared validation and persistence workflow for Presidential and Vice-Presidential submissions.
     *
     * @param array<string, mixed> $authUser Authenticated user record.
     * @param array<string, mixed> $payload Raw request parameters.
     * @param array<int, string> $allowedTypes Allowed `document_type` values for the role.
     * @param string $issuerTitle Display title of the issuing office.
     * @return array<string, mixed> Result array.
     */
    private function processExecutiveSubmission(
        array $authUser,
        array $payload,
        array $allowedTypes,
        string $issuerTitle
    ): array {
        $errors = [];

        $docType = strtoupper(trim((string) ($payload['document_type'] ?? '')));
        $docNumber = trim((string) ($payload['document_number'] ?? ''));
        $permissionId = (int) ($payload['permission_id'] ?? 0);
        $title = trim((string) ($payload['title'] ?? ''));
        $summary = trim((string) ($payload['summary_content'] ?? ''));

        if ($docType === '' || !in_array($docType, $allowedTypes, true)) {
            $errors['document_type'] = 'Pilih jenis instrumen keputusan yang sesuai dengan jabatan Anda.';
        }

        if ($docNumber === '') {
            $errors['document_number'] = 'Nomor registrasi naskah wajib diisi.';
        } elseif (mb_strlen($docNumber) < 4 || mb_strlen($docNumber) > 60) {
            $errors['document_number'] = 'Nomor registrasi naskah harus terdiri dari 4 hingga 60 karakter.';
        }

        if ($permissionId <= 0) {
            $errors['permission_id'] = 'Pilih bidang kebijakan atau urusan pemerintahan.';
        }

        if ($title === '') {
            $errors['title'] = 'Judul peraturan atau keputusan wajib diisi.';
        } elseif (mb_strlen($title) < 10 || mb_strlen($title) > 200) {
            $errors['title'] = 'Judul keputusan harus terdiri dari 10 hingga 200 karakter.';
        }

        if ($summary === '') {
            $errors['summary_content'] = 'Ringkasan pokok keputusan wajib diisi.';
        } elseif (mb_strlen($summary) < 20 || mb_strlen($summary) > 25000) {
            $errors['summary_content'] = 'Ringkasan pokok keputusan harus terdiri dari 20 hingga 25.000 karakter.';
        }

        if ($errors !== []) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => $errors,
                'message' => 'Terdapat kolom naskah keputusan yang belum sesuai ketentuan.',
            ];
        }

        $roleId = (int) $authUser['role_id'];
        $authPerm = $this->decreeModel->findAuthorizedRolePermission($roleId, $permissionId);
        if ($authPerm === null) {
            return [
                'is_valid' => false,
                'http_code' => 403,
                'errors' => [
                    'permission_id' => 'Bidang kebijakan tersebut tidak termasuk dalam lingkup jabatan Anda.',
                ],
                'message' => 'Bidang kebijakan tidak sesuai dengan lingkup jabatan Anda.',
            ];
        }

        $statusLabel = 'Disahkan ' . $issuerTitle;
        $actorLabel = $issuerTitle . ' (' . (string) ($authUser['character_name'] ?? '') . ')';

        $inserted = $this->decreeModel->insertDecree([
            'user_id' => (int) $authUser['id'],
            'permission_id' => $permissionId,
            'document_number' => $docNumber,
            'document_type' => $docType,
            'title' => $title,
            'summary_content' => $summary,
            'counter_approval_status' => $statusLabel,
            'category_label' => (string) ($authPerm['permission_name'] ?? 'Pemerintahan Nasional'),
            'actor_label' => $actorLabel,
        ]);

        if (!$inserted) {
            return [
                'is_valid' => false,
                'http_code' => 422,
                'errors' => [
                    'document_number' => 'Nomor registrasi naskah sudah pernah digunakan pada Lembaran Negara.',
                ],
                'message' => 'Nomor naskah tersebut sudah tercatat sebelumnya. Gunakan nomor registrasi baru.',
            ];
        }

        return [
            'is_valid' => true,
            'http_code' => 200,
            'message' => sprintf('Naskah %s (%s) berhasil disahkan oleh %s.', $docNumber, $docType, $issuerTitle),
        ];
    }
}
