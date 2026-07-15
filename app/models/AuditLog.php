<?php

class AuditLog extends Model
{
    protected string $table = 'audit_log';

    public static function record(
        ?int    $userId,
        string  $action,
        string  $module      = '',
        ?int    $recordId    = null,
        string  $description = '',
        ?array  $oldValues   = null,
        ?array  $newValues   = null,
        ?int    $businessId  = null
    ): void {
        try {
            $db   = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO audit_log
                    (user_id, business_id, action, module, record_id, description, old_values, new_values, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $businessId,
                $action,
                $module,
                $recordId,
                $description,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('AuditLog::record failed: ' . $e->getMessage());
        }
    }

    public function paginate(int $page = 1, int $perPage = 50, ?int $businessId = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = $businessId ? "AND al.business_id = $businessId" : '';
        return $this->query(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                    b.name AS business_name
             FROM audit_log al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN businesses b ON b.id = al.business_id
             WHERE 1=1 $where
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    public function totalCount(?int $businessId = null): int
    {
        if ($businessId) {
            $db = Database::getInstance();
            return (int)$db->query("SELECT COUNT(*) FROM audit_log WHERE business_id = ?", [$businessId])->fetchColumn();
        }
        return $this->count();
    }
}
