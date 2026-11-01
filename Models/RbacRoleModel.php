<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

/**
 * Data Access Model for Role-Based Access Control (RBAC) government hierarchies and permissions.
 * Queries the normalized MySQL relational schema (`rbac_roles`, `rbac_role_permissions`, `rbac_permissions`)
 * without static hardcoded fallbacks.
 */
final class RbacRoleModel
{
    /** @var PDO|null Database connection handle */
    private ?PDO $db;

    /**
     * Initializes the RBAC Role Model with an optional PDO instance.
     *
     * @param PDO|null $db Injected PDO connection or null to resolve automatically.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Retrieves all active roles along with their normalized UUD 1945 permission matrix from MySQL.
     *
     * @return array<int, array<string, mixed>> List of structured role definitions.
     */
    public function fetchAllRolesWithPermissions(): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $roleStmt = $this->db->prepare(
                'SELECT id, role_code, role_title, branch_code, branch_name, constitutional_basis,
                        clearance_level, quota_limit, active_holders, salary_rp, recruitment_type,
                        summary_text, command_modules_json
                 FROM rbac_roles
                 WHERE is_active = :is_active
                 ORDER BY clearance_rank ASC'
            );
            $roleStmt->execute([':is_active' => 1]);
            $roles = $roleStmt->fetchAll();

            if (!empty($roles)) {
                return $this->hydrateNormalizedDatabaseRoles($roles);
            }
        } catch (Throwable $exception) {
            error_log('[RbacRoleModel::fetchAllRolesWithPermissions] MySQL query error: ' . $exception->getMessage());
        }

        return [];
    }

    /**
     * Finds a single role record by its unique role code.
     *
     * @param string $roleCode Unique identifier such as ROLE_PRESIDEN or ROLE_WAKIL_PRESIDEN.
     * @return array<string, mixed>|null Role record or null if not found.
     */
    public function findRoleByCode(string $roleCode): ?array
    {
        $allRoles = $this->fetchAllRolesWithPermissions();
        foreach ($allRoles as $role) {
            if ($role['role_code'] === $roleCode) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Hydrates role records by joining normalized permissions from `rbac_role_permissions` and `rbac_permissions`.
     *
     * @param array<int, array<string, mixed>> $roles Raw rows from `rbac_roles`.
     * @return array<int, array<string, mixed>> Hydrated role records with nested permissions.
     */
    private function hydrateNormalizedDatabaseRoles(array $roles): array
    {
        if ($this->db === null) {
            return [];
        }

        $permStmt = $this->db->prepare(
            'SELECT p.id AS permission_id,
                    p.permission_key AS scope,
                    p.permission_name AS label,
                    p.authority_category AS category,
                    p.legal_basis,
                    rp.authorization_status,
                    rp.access_label AS access,
                    rp.can_execute_directly,
                    rp.check_and_balance_organ,
                    rp.constitutional_explanation
             FROM rbac_role_permissions rp
             INNER JOIN rbac_permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = :role_id
             ORDER BY p.id ASC'
        );

        $userStmt = $this->db->prepare(
            'SELECT nik_rp, character_name, username, faction_party, office_status, inauguration_decree, term_start_date, term_end_date
             FROM users
             WHERE role_id = :role_id
             LIMIT 1'
        );

        $hydrated = [];
        foreach ($roles as $role) {
            $roleId = (int) $role['id'];

            $permStmt->execute([':role_id' => $roleId]);
            $permissions = $permStmt->fetchAll();

            $userStmt->execute([':role_id' => $roleId]);
            $holderUser = $userStmt->fetch() ?: null;

            $role['permissions'] = $permissions;
            $role['holder_user'] = $holderUser;
            $role['command_modules'] = json_decode((string) ($role['command_modules_json'] ?? '[]'), true) ?: [];
            unset($role['command_modules_json']);

            $hydrated[] = $role;
        }

        return $hydrated;
    }
}
