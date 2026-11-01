<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use Throwable;

/**
 * Data Access Model for Presidential and Vice-Presidential Decrees & Directives (`executive_decrees_log`).
 * Enforces parameterized SQL queries for reading and persisting state acts.
 */
final class ExecutiveDecreeModel
{
    /** @var PDO|null Database connection handle */
    private ?PDO $db;

    /**
     * Initializes the Executive Decree Model.
     *
     * @param PDO|null $db Injected PDO instance or null for default resolution.
     */
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Retrieves policy fields that a specific role is authorized to execute.
     *
     * @param string $roleCode Role identifier (`ROLE_PRESIDEN` or `ROLE_WAKIL_PRESIDEN`).
     * @return array<int, array<string, mixed>> Authorized policy scope rows from MySQL.
     */
    public function fetchAuthorizedPermissionsForRole(string $roleCode): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT p.id AS permission_id,
                        p.permission_key,
                        p.permission_name,
                        p.legal_basis,
                        rp.access_label,
                        rp.check_and_balance_organ
                 FROM rbac_role_permissions rp
                 INNER JOIN rbac_roles r ON rp.role_id = r.id
                 INNER JOIN rbac_permissions p ON rp.permission_id = p.id
                 WHERE r.role_code = :role_code
                   AND rp.authorization_status != :denied_status
                 ORDER BY p.id ASC'
            );
            $stmt->execute([
                ':role_code' => $roleCode,
                ':denied_status' => 'DENIED_CONSTITUTIONAL',
            ]);

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[ExecutiveDecreeModel::fetchAuthorizedPermissionsForRole] Error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Retrieves all decrees or instructions issued by a specific role (`ROLE_PRESIDEN` or `ROLE_WAKIL_PRESIDEN`).
     *
     * @param string $roleCode Role identifier.
     * @return array<int, array<string, mixed>> Chronological list of decrees for that role.
     */
    public function fetchDecreesByRole(string $roleCode): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT d.id,
                        d.document_number,
                        d.document_type,
                        d.title,
                        d.summary_content,
                        d.counter_approval_status,
                        d.executed_at,
                        u.character_name,
                        p.permission_name
                 FROM executive_decrees_log d
                 INNER JOIN users u ON d.user_id = u.id
                 INNER JOIN rbac_roles r ON u.role_id = r.id
                 INNER JOIN rbac_permissions p ON d.permission_id = p.id
                 WHERE r.role_code = :role_code
                 ORDER BY d.executed_at DESC, d.id DESC'
            );
            $stmt->execute([':role_code' => $roleCode]);

            return $stmt->fetchAll();
        } catch (Throwable $exception) {
            error_log('[ExecutiveDecreeModel::fetchDecreesByRole] Error: ' . $exception->getMessage());
            return [];
        }
    }

    /**
     * Verifies whether a user's role is authorized to use a given permission ID.
     *
     * @param int $roleId Role primary key from `rbac_roles`.
     * @param int $permissionId Permission primary key from `rbac_permissions`.
     * @return array<string, mixed>|null Authorization record if allowed, null if denied or missing.
     */
    public function findAuthorizedRolePermission(int $roleId, int $permissionId): ?array
    {
        if ($this->db === null) {
            return null;
        }

        try {
            $stmt = $this->db->prepare(
                'SELECT rp.authorization_status,
                        rp.access_label,
                        rp.check_and_balance_organ,
                        p.legal_basis,
                        p.permission_name
                 FROM rbac_role_permissions rp
                 INNER JOIN rbac_permissions p ON rp.permission_id = p.id
                 WHERE rp.role_id = :role_id
                   AND rp.permission_id = :permission_id
                   AND rp.authorization_status != :denied_status
                 LIMIT 1'
            );
            $stmt->execute([
                ':role_id' => $roleId,
                ':permission_id' => $permissionId,
                ':denied_status' => 'DENIED_CONSTITUTIONAL',
            ]);

            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (Throwable $exception) {
            error_log('[ExecutiveDecreeModel::findAuthorizedRolePermission] Error: ' . $exception->getMessage());
            return null;
        }
    }

    /**
     * Inserts a new Presidential or Vice-Presidential decree/directive into `executive_decrees_log`,
     * and synchronizes it into `national_news` and `active_laws` (for regulations) within a transaction.
     *
     * @param array<string, mixed> $data Validated decree parameters.
     * @return bool True on successful insertion.
     */
    public function insertDecree(array $data): bool
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                'INSERT INTO executive_decrees_log (
                    user_id, permission_id, document_number, document_type,
                    title, summary_content, counter_approval_status, executed_at
                ) VALUES (
                    :user_id, :permission_id, :document_number, :document_type,
                    :title, :summary_content, :counter_approval_status, NOW()
                )'
            );

            $stmt->execute([
                ':user_id' => (int) $data['user_id'],
                ':permission_id' => (int) $data['permission_id'],
                ':document_number' => (string) $data['document_number'],
                ':document_type' => (string) $data['document_type'],
                ':title' => (string) $data['title'],
                ':summary_content' => (string) $data['summary_content'],
                ':counter_approval_status' => (string) $data['counter_approval_status'],
            ]);

            $docType = (string) $data['document_type'];
            $docNumber = (string) $data['document_number'];
            $title = (string) $data['title'];
            $summary = (string) $data['summary_content'];
            $categoryLabel = (string) ($data['category_label'] ?? 'Pemerintahan & Kebijakan');
            $actorLabel = (string) ($data['actor_label'] ?? 'Pemerintah Republik Indonesia');

            // If it is a general regulation (PERPRES, PP, PERPPU), also register in active_laws
            if (in_array($docType, ['PERPRES', 'PP', 'PERPPU'], true)) {
                $lawStmt = $this->db->prepare(
                    'INSERT INTO active_laws (
                        law_number, law_type, title, category, summary, enacted_by, enacted_date, status
                    ) VALUES (
                        :law_number, :law_type, :title, :category, :summary, :enacted_by, CURDATE(), :status
                    )'
                );
                $lawStmt->execute([
                    ':law_number' => $docNumber,
                    ':law_type' => $docType,
                    ':title' => $title,
                    ':category' => $categoryLabel,
                    ':summary' => $summary,
                    ':enacted_by' => $actorLabel,
                    ':status' => 'BERLAKU_AKTIF',
                ]);
            }

            // Also publish an official entry in national_news so it appears on the Main Information Portal
            $newsStmt = $this->db->prepare(
                'INSERT INTO national_news (
                    news_code, category, headline, summary, author_name, published_at
                ) VALUES (
                    :news_code, :category, :headline, :summary, :author_name, NOW()
                )'
            );
            $newsCode = 'SIARAN-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $docNumber)) . '-' . random_int(100, 999);
            $newsCategory = strpos($docType, 'WAPRES') !== false || $docType === 'AUDIT_KINERJA_KABINET'
                ? 'SEKRETARIAT WAPRES'
                : 'KEPRESIDENAN';

            $newsStmt->execute([
                ':news_code' => substr($newsCode, 0, 60),
                ':category' => $newsCategory,
                ':headline' => '[' . $docNumber . '] ' . $title,
                ':summary' => $summary,
                ':author_name' => $actorLabel,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[ExecutiveDecreeModel::insertDecree] Error: ' . $exception->getMessage());
            return false;
        }
    }
}
