<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

/**
 * Data Access Model for Presidential Cabinet Management & Ministerial Appointments (`Models/CabinetModel.php`).
 * Manages `cabinet_settings`, `cabinet_ministries`, and enforces atomic transactions
 * for appointment and dismissal of ministers with presidential prerogative decrees.
 */
final class CabinetModel
{
    /** @var PDO|null Database connection handle */
    private ?PDO $db;

    /**
     * Initializes the Cabinet Model.
     *
     * @param PDO|null $db Injected PDO connection or null for default resolution.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Retrieves the current Cabinet configuration settings and presidential leadership details.
     *
     * @return array<string, mixed> Current cabinet configuration with default fallbacks.
     */
    public function getCabinetSettings(): array
    {
        $default = [
            'id' => 1,
            'cabinet_name' => 'Kabinet Indonesia Emas',
            'cabinet_vision' => 'Akselerasi Pembangunan Terpadu, Kedaulatan Nasional, dan Transformasi Digital 2026–2031',
            'president_name' => 'Ir. H. Pratama Wijaya, M.P.A.',
            'formation_date' => '2026-10-21',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db === null) {
            return $default;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT cs.id,
                        cs.cabinet_name,
                        cs.cabinet_vision,
                        cs.formation_date,
                        cs.updated_at,
                        u.character_name AS president_name
                 FROM cabinet_settings cs
                 LEFT JOIN users u ON cs.president_user_id = u.id
                 ORDER BY cs.id ASC
                 LIMIT 1'
            );
            $stmt->execute();
            $row = $stmt->fetch();

            return is_array($row) ? $row : $default;
        } catch (Throwable $exception) {
            error_log('[CabinetModel::getCabinetSettings] Error: ' . $exception->getMessage());
            return $default;
        }
    }

    /**
     * Updates the official Cabinet name and strategic mission statement.
     *
     * @param string $cabinetName New official name of the cabinet.
     * @param string $cabinetVision Strategic development vision statement.
     * @param int $presidentUserId Authenticated president user ID.
     * @return bool True on successful update, false on error.
     */
    public function updateCabinetSettings(string $cabinetName, string $cabinetVision, int $presidentUserId): bool
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE cabinet_settings
                 SET cabinet_name = :name,
                     cabinet_vision = :vision,
                     president_user_id = :pres_id,
                     updated_at = NOW()
                 WHERE id = 1'
            );

            return $stmt->execute([
                ':name' => $cabinetName,
                ':vision' => $cabinetVision,
                ':pres_id' => $presidentUserId,
            ]);
        } catch (Throwable $exception) {
            error_log('[CabinetModel::updateCabinetSettings] Error: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Retrieves all Golongan I (Kelompok Triumvirat Konstitusional) ministries
     * along with current incumbent details.
     *
     * @return array<int, array<string, mixed>> List of Golongan I ministries.
     */
    public function getGolonganIMinistries(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT cm.id,
                        cm.ministry_group,
                        cm.ministry_code,
                        cm.ministry_name,
                        cm.minister_title,
                        cm.constitutional_basis,
                        cm.scope_description,
                        cm.user_id,
                        cm.office_status,
                        cm.appointed_at,
                        cm.decree_reference,
                        u.character_name,
                        u.nik_rp,
                        u.email,
                        u.faction_party,
                        u.domicile_province
                 FROM cabinet_ministries cm
                 LEFT JOIN users u ON cm.user_id = u.id
                 WHERE cm.ministry_group = :group
                 ORDER BY cm.id ASC'
            );
            $stmt->execute([':group' => 'GOLONGAN_I']);

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[CabinetModel::getGolonganIMinistries] Error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves a single ministry record by primary key.
     *
     * @param int $ministryId Ministry primary key.
     * @return array<string, mixed>|null Ministry details or null if not found.
     */
    public function getMinistryById(int $ministryId): ?array
    {
        if ($this->db === null) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT cm.id,
                        cm.ministry_group,
                        cm.ministry_code,
                        cm.ministry_name,
                        cm.minister_title,
                        cm.constitutional_basis,
                        cm.scope_description,
                        cm.user_id,
                        cm.office_status,
                        cm.appointed_at,
                        cm.decree_reference,
                        u.character_name,
                        u.nik_rp
                 FROM cabinet_ministries cm
                 LEFT JOIN users u ON cm.user_id = u.id
                 WHERE cm.id = :id
                 LIMIT 1'
            );
            $stmt->execute([':id' => $ministryId]);
            $row = $stmt->fetch();

            return is_array($row) ? $row : null;
        } catch (Throwable $exception) {
            error_log('[CabinetModel::getMinistryById] Error: ' . $exception->getMessage());
            return null;
        }
    }

    /**
     * Retrieves all eligible citizen users who do NOT currently hold any public or ministerial office.
     * Enforces the constitutional constraint: "selama dia belum ada jabatan lain".
     *
     * @return array<int, array<string, mixed>> List of eligible candidate users.
     */
    public function getEligibleCandidates(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT u.id,
                        u.nik_rp,
                        u.character_name,
                        u.username,
                        u.email,
                        u.faction_party,
                        u.domicile_province,
                        u.career_track,
                        u.office_status
                 FROM users u
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 WHERE u.office_status = :status_warga
                   AND r.role_code NOT IN (:role_pres, :role_wapres, :role_menteri)
                   AND u.id NOT IN (
                       SELECT user_id FROM cabinet_ministries WHERE user_id IS NOT NULL
                   )
                 ORDER BY u.character_name ASC'
            );
            $stmt->execute([
                ':status_warga' => 'WARGA_AKTIF',
                ':role_pres' => 'ROLE_PRESIDEN',
                ':role_wapres' => 'ROLE_WAKIL_PRESIDEN',
                ':role_menteri' => 'ROLE_MENTERI',
            ]);

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[CabinetModel::getEligibleCandidates] Error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Appoints an eligible citizen as a Cabinet Minister within an atomic transaction.
     * Updates ministry assignment, user role/status, writes executive decree log, and publishes state news.
     *
     * @param int $ministryId Target ministry ID.
     * @param int $candidateUserId Eligible user to appoint.
     * @param int $presidentUserId Authenticated president executing the act.
     * @return array<string, mixed> Result status with message.
     */
    public function appointMinister(int $ministryId, int $candidateUserId, int $presidentUserId): array
    {
        if ($this->db === null) {
            return ['success' => false, 'message' => 'Koneksi basis data tidak tersedia.'];
        }

        try {
            $this->db->beginTransaction();

            // 1. Verify candidate eligibility
            $userStmt = $this->db->prepare(
                'SELECT u.id, u.character_name, u.nik_rp, u.office_status, r.role_code
                 FROM users u
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 WHERE u.id = :id
                 LIMIT 1'
            );
            $userStmt->execute([':id' => $candidateUserId]);
            $candidate = $userStmt->fetch();

            if (!is_array($candidate)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Pengguna calon menteri tidak ditemukan dalam data kependudukan.'];
            }

            if (($candidate['office_status'] ?? '') !== 'WARGA_AKTIF'
                || in_array($candidate['role_code'] ?? '', ['ROLE_PRESIDEN', 'ROLE_WAKIL_PRESIDEN', 'ROLE_MENTERI'], true)
            ) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Calon tidak dapat diangkat karena sedang memegang jabatan kenegaraan lain.'];
            }

            // Check if user is already assigned to any ministry
            $assignedCheck = $this->db->prepare(
                'SELECT id FROM cabinet_ministries WHERE user_id = :user_id LIMIT 1'
            );
            $assignedCheck->execute([':user_id' => $candidateUserId]);
            if ($assignedCheck->fetch()) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Calon menteri sudah aktif memimpin kementerian lain.'];
            }

            // 2. Fetch ministry details
            $minStmt = $this->db->prepare(
                'SELECT id, ministry_name, minister_title, user_id FROM cabinet_ministries WHERE id = :id LIMIT 1'
            );
            $minStmt->execute([':id' => $ministryId]);
            $ministry = $minStmt->fetch();

            if (!is_array($ministry)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Data kementerian tidak ditemukan.'];
            }

            // If an incumbent exists, relieve them first
            if (!empty($ministry['user_id'])) {
                $formerUserId = (int) $ministry['user_id'];
                $resetFormer = $this->db->prepare(
                    'UPDATE users
                     SET role_id = (SELECT id FROM rbac_roles WHERE role_code = :role_warga LIMIT 1),
                         office_status = :warga_status,
                         inauguration_decree = :former_note
                     WHERE id = :former_id'
                );
                $resetFormer->execute([
                    ':role_warga' => 'ROLE_WARGA_SIPIL',
                    ':warga_status' => 'WARGA_AKTIF',
                    ':former_note' => 'Purna Tugas Menteri Kabinet',
                    ':former_id' => $formerUserId,
                ]);
            }

            // 3. Generate Official Presidential Appointment Decree Number
            $decreeNumber = 'KEPPRES-' . random_int(10, 99) . '/P/' . date('Y');
            $charName = (string) $candidate['character_name'];
            $minName = (string) $ministry['ministry_name'];
            $minTitle = (string) $ministry['minister_title'];

            // 4. Update Cabinet Ministry record
            $updMin = $this->db->prepare(
                'UPDATE cabinet_ministries
                 SET user_id = :user_id,
                     office_status = :status,
                     appointed_at = NOW(),
                     decree_reference = :decree
                 WHERE id = :id'
            );
            $updMin->execute([
                ':user_id' => $candidateUserId,
                ':status' => 'MENJABAT',
                ':decree' => $decreeNumber,
                ':id' => $ministryId,
            ]);

            // 5. Update user role and office status to ROLE_MENTERI
            $updUser = $this->db->prepare(
                'UPDATE users
                 SET role_id = (SELECT id FROM rbac_roles WHERE role_code = :role_menteri LIMIT 1),
                     office_status = :status_menjabat,
                     inauguration_decree = :decree
                 WHERE id = :user_id'
            );
            $updUser->execute([
                ':role_menteri' => 'ROLE_MENTERI',
                ':status_menjabat' => 'MENJABAT_AKTIF',
                ':decree' => $decreeNumber,
                ':user_id' => $candidateUserId,
            ]);

            // 6. Record Presidential Decree in executive_decrees_log
            $logStmt = $this->db->prepare(
                'INSERT INTO executive_decrees_log (
                    user_id, permission_id, document_number, document_type,
                    title, summary_content, counter_approval_status, executed_at
                ) VALUES (
                    :user_id, 1, :doc_num, :doc_type,
                    :title, :summary, :status, NOW()
                )'
            );
            $logStmt->execute([
                ':user_id' => $presidentUserId,
                ':doc_num' => $decreeNumber,
                ':doc_type' => 'KEPPRES',
                ':title' => 'Pengangkatan ' . $charName . ' sebagai ' . $minTitle,
                ':summary' => 'Presiden Republik Indonesia menetapkan pengangkatan ' . $charName . ' sebagai ' . $minTitle . ' guna menyelenggarakan urusan pemerintahan pada ' . $minName . ' dengan penuh tanggung jawab kenegaraan.',
                ':status' => 'Disahkan Presiden RI',
            ]);

            // 7. Publish Official State News Bulletin
            $newsStmt = $this->db->prepare(
                'INSERT INTO national_news (
                    news_code, category, headline, summary, author_name, published_at
                ) VALUES (
                    :news_code, :category, :headline, :summary, :author, NOW()
                )'
            );
            $newsCode = 'SIARAN-LANTIK-' . random_int(1000, 9999);
            $newsStmt->execute([
                ':news_code' => $newsCode,
                ':category' => 'KEPRESIDENAN',
                ':headline' => '[' . $decreeNumber . '] Presiden Resmi Angkat ' . $charName . ' sebagai ' . $minTitle,
                ':summary' => 'Berdasarkan hak prerogatif konstitusional, Presiden menetapkan ' . $charName . ' untuk memimpin ' . $minName . ' guna memperkuat efektivitas tata kelola pemerintahan nasional.',
                ':author' => 'Biro Pers, Media, dan Informasi Sekretariat Presiden',
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Pejabat menteri berhasil ditetapkan dan dilantik melalui ' . $decreeNumber . '.',
                'decree_number' => $decreeNumber,
                'minister_name' => $charName,
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[CabinetModel::appointMinister] Error: ' . $exception->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses penetapan menteri.'];
        }
    }

    /**
     * Dismisses an incumbent minister from office with honorable discharge within an atomic transaction.
     * Resets ministry slot to vacant, returns former minister to civilian status, and logs presidential decree.
     *
     * @param int $ministryId Target ministry ID.
     * @param string $reason Official presidential reasoning or notation.
     * @param int $presidentUserId Authenticated president executing the act.
     * @return array<string, mixed> Result status with message.
     */
    public function dismissMinister(int $ministryId, string $reason, int $presidentUserId): array
    {
        if ($this->db === null) {
            return ['success' => false, 'message' => 'Koneksi basis data tidak tersedia.'];
        }

        try {
            $this->db->beginTransaction();

            // 1. Fetch current ministry and incumbent details
            $minStmt = $this->db->prepare(
                'SELECT cm.id, cm.ministry_name, cm.minister_title, cm.user_id,
                        u.character_name, u.nik_rp
                 FROM cabinet_ministries cm
                 INNER JOIN users u ON cm.user_id = u.id
                 WHERE cm.id = :id
                 LIMIT 1'
            );
            $minStmt->execute([':id' => $ministryId]);
            $ministry = $minStmt->fetch();

            if (!is_array($ministry) || empty($ministry['user_id'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Kementerian ini sedang kosong atau pejabat menteri tidak ditemukan.'];
            }

            $formerUserId = (int) $ministry['user_id'];
            $charName = (string) $ministry['character_name'];
            $minName = (string) $ministry['ministry_name'];
            $minTitle = (string) $ministry['minister_title'];
            $dismissalDecree = 'KEPPRES-PBR-' . random_int(10, 99) . '/' . date('Y');

            // 2. Reset Ministry slot to KOSONG
            $vacateStmt = $this->db->prepare(
                'UPDATE cabinet_ministries
                 SET user_id = NULL,
                     office_status = :status_kosong,
                     appointed_at = NULL,
                     decree_reference = NULL
                 WHERE id = :id'
            );
            $vacateStmt->execute([
                ':status_kosong' => 'KOSONG',
                ':id' => $ministryId,
            ]);

            // 3. Reset former minister's user role and status back to civilian
            $resetUser = $this->db->prepare(
                'UPDATE users
                 SET role_id = (SELECT id FROM rbac_roles WHERE role_code = :role_warga LIMIT 1),
                     office_status = :warga_status,
                     inauguration_decree = :decree
                 WHERE id = :user_id'
            );
            $resetUser->execute([
                ':role_warga' => 'ROLE_WARGA_SIPIL',
                ':warga_status' => 'WARGA_AKTIF',
                ':decree' => 'Purna Tugas Menteri (' . $dismissalDecree . ')',
                ':user_id' => $formerUserId,
            ]);

            // 4. Record Dismissal Decree in executive_decrees_log
            $cleanReason = trim($reason) !== '' ? trim($reason) : 'Penataan susunan kabinet dan penyegaran kelembagaan pemerintahan negara.';
            $logStmt = $this->db->prepare(
                'INSERT INTO executive_decrees_log (
                    user_id, permission_id, document_number, document_type,
                    title, summary_content, counter_approval_status, executed_at
                ) VALUES (
                    :user_id, 1, :doc_num, :doc_type,
                    :title, :summary, :status, NOW()
                )'
            );
            $logStmt->execute([
                ':user_id' => $presidentUserId,
                ':doc_num' => $dismissalDecree,
                ':doc_type' => 'KEPPRES',
                ':title' => 'Pemberhentian Dengan Hormat ' . $charName . ' dari Jabatan ' . $minTitle,
                ':summary' => 'Memberhentikan dengan hormat ' . $charName . ' dari jabatan ' . $minTitle . ' (' . $minName . ') disertai ucapan terima kasih atas pengabdian dan jasa-jasanya kepada bangsa dan negara. Catatan: ' . $cleanReason,
                ':status' => 'Disahkan Presiden RI',
            ]);

            // 5. Publish State News
            $newsStmt = $this->db->prepare(
                'INSERT INTO national_news (
                    news_code, category, headline, summary, author_name, published_at
                ) VALUES (
                    :news_code, :category, :headline, :summary, :author, NOW()
                )'
            );
            $newsCode = 'SIARAN-PBR-' . random_int(1000, 9999);
            $newsStmt->execute([
                ':news_code' => $newsCode,
                ':category' => 'KEPRESIDENAN',
                ':headline' => '[' . $dismissalDecree . '] Presiden Berhentikan dengan Hormat ' . $charName . ' dari Jabatan ' . $minTitle,
                ':summary' => 'Presiden Republik Indonesia secara resmi menetapkan pemberhentian dengan hormat ' . $charName . ' sebagai pimpinan ' . $minName . ' dalam rangka evaluasi dan penataan kabinet.',
                ':author' => 'Biro Pers, Media, dan Informasi Sekretariat Presiden',
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Pejabat menteri berhasil diberhentikan dengan hormat melalui ' . $dismissalDecree . '.',
                'decree_number' => $dismissalDecree,
                'former_minister' => $charName,
            ];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[CabinetModel::dismissMinister] Error: ' . $exception->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses pemberhentian menteri.'];
        }
    }
}
