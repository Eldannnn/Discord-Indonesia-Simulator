<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

/**
 * Data Access Model for Executive & Citizen Users (`users` table).
 * Queries all user accounts, role assignments, and branch counts directly from MySQL.
 */
final class ExecutiveUserModel
{
    /** @var PDO|null Database connection handle */
    private ?PDO $db;

    /**
     * Initializes the Executive User Model.
     *
     * @param PDO|null $db Injected PDO instance or null for default resolution.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Fetches the active President and Vice President user accounts from MySQL.
     *
     * @return array<int, array<string, mixed>> List of executive user records.
     */
    public function fetchExecutiveUsers(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.nik_rp, u.character_name, u.username, u.email,
                        u.faction_party, u.domicile_province, u.office_status,
                        u.inauguration_decree, u.term_start_date, u.term_end_date,
                        r.id AS role_id, r.role_code, r.role_title, r.constitutional_basis, r.clearance_level
                 FROM users u
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 WHERE r.role_code IN (:presiden, :wapres)
                 ORDER BY r.clearance_rank ASC'
            );
            $stmt->execute([
                ':presiden' => 'ROLE_PRESIDEN',
                ':wapres' => 'ROLE_WAKIL_PRESIDEN',
            ]);

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[ExecutiveUserModel::fetchExecutiveUsers] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Finds a user account by username, NIK Roleplay, or email for authentication.
     *
     * @param string $identifier Username, NIK, or Email entered by the player.
     * @return array<string, mixed>|null User record with role metadata and password_hash, or null if not found.
     */
    public function findUserByIdentifier(string $identifier): ?array
    {
        if ($this->db === null) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT u.id, u.nik_rp, u.character_name, u.username, u.email, u.password_hash,
                        u.role_id, u.career_track, u.faction_party, u.domicile_province, u.office_status,
                        u.inauguration_decree, u.term_start_date, u.term_end_date,
                        r.role_code, r.role_title, r.branch_name, r.constitutional_basis, r.clearance_level
                 FROM users u
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 WHERE u.username = :id_user OR u.nik_rp = :id_nik OR u.email = :id_email
                 LIMIT 1'
            );
            $stmt->execute([
                ':id_user' => $identifier,
                ':id_nik' => $identifier,
                ':id_email' => $identifier,
            ]);
            $row = $stmt->fetch();

            return is_array($row) ? $row : null;
        } catch (Throwable $exception) {
            error_log('[ExecutiveUserModel::findUserByIdentifier] Query error: ' . $exception->getMessage());
            return null;
        }
    }

    /**
     * Fetches all roles from `rbac_roles` along with live active user counts from `users`.
     *
     * @return array<int, array<string, mixed>> Dynamic role summary rows from MySQL.
     */
    public function fetchDynamicRoleSummaries(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->query(
                'SELECT r.id, r.role_code, r.role_title, r.branch_code, r.branch_name,
                        r.constitutional_basis, r.quota_limit, r.summary_text,
                        COUNT(u.id) AS active_user_count
                 FROM rbac_roles r
                 LEFT JOIN users u ON u.role_id = r.id
                 GROUP BY r.id, r.role_code, r.role_title, r.branch_code, r.branch_name,
                          r.constitutional_basis, r.quota_limit, r.summary_text
                 ORDER BY r.clearance_rank ASC'
            );

            return $stmt ? $stmt->fetchAll() : [];
        } catch (Throwable $exception) {
            error_log('[ExecutiveUserModel::fetchDynamicRoleSummaries] Query error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Persists a newly registered citizen user into the MySQL `users` table.
     *
     * @param array<string, string> $data Validated registration payload.
     * @return bool True if inserted into database.
     */
    public function createCitizenUser(array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (
                    nik_rp, character_name, username, email, password_hash,
                    role_id, career_track, faction_party, domicile_province,
                    character_backstory, office_status, inauguration_decree,
                    term_start_date, term_end_date
                ) VALUES (
                    :nik_rp, :character_name, :username, :email, :password_hash,
                    :role_id, :career_track, :faction_party, :domicile_province,
                    :character_backstory, :office_status, :inauguration_decree,
                    :term_start_date, :term_end_date
                )'
            );

            return $stmt->execute([
                ':nik_rp' => $data['nik_rp'],
                ':character_name' => $data['character_name'],
                ':username' => $data['username'],
                ':email' => $data['username'] . '@warga.nusantara.rp',
                ':password_hash' => $data['password_hash'],
                ':role_id' => 3,
                ':career_track' => $data['career_track_label'],
                ':faction_party' => 'Warga Negara Independen',
                ':domicile_province' => $data['domicile'],
                ':character_backstory' => $data['character_backstory'],
                ':office_status' => 'WARGA_AKTIF',
                ':inauguration_decree' => 'Akta Kependudukan Nasional',
                ':term_start_date' => date('Y-m-d'),
                ':term_end_date' => date('Y-m-d', strtotime('+5 years')),
            ]);
        } catch (Throwable $exception) {
            error_log('[ExecutiveUserModel::createCitizenUser] Insert failed: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * Checks whether a username is already registered in the `users` table.
     *
     * @param string $username Candidate username.
     * @return bool True if already taken.
     */
    public function isUsernameTaken(string $username): bool
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $exception) {
            return false;
        }
    }
}
