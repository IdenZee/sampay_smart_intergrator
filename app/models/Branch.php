<?php

class Branch extends Model
{
    protected string $table = 'branches';

    public function allActive(): array
    {
        return $this->query("SELECT * FROM branches WHERE is_active = 1 ORDER BY name");
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $sql    = "SELECT COUNT(*) FROM branches WHERE code = ?";
        $params = [$code];
        if ($excludeId) { $sql .= " AND id != ?"; $params[] = $excludeId; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
