<?php

class ApiKey extends Model
{
    protected string $table = 'api_keys';

    /**
     * Generate a new raw API key and store its hash.
     * Returns the raw key (shown once) and the inserted row id.
     */
    public function generate(int $businessId, string $name, string $deviceInfo = '', int $createdBy = 0): array
    {
        $raw  = 'sk_' . bin2hex(random_bytes(24));
        $hash = hash('sha256', $raw);

        $id = $this->insert([
            'business_id' => $businessId,
            'key_name'    => $name,
            'key_hash'    => $hash,
            'device_info' => $deviceInfo,
            'is_active'   => 1,
            'created_by'  => $createdBy ?: null,
        ]);

        return ['id' => $id, 'raw_key' => $raw];
    }

    /**
     * Validate an inbound API key (from Authorization header).
     * Returns the api_keys row + business row on success, null on failure.
     */
    public function authenticate(string $rawKey): ?array
    {
        $hash = hash('sha256', $rawKey);
        $row  = $this->queryOne(
            "SELECT k.*, b.id AS biz_id, b.name AS biz_name, b.tpin, b.branch_code,
                    b.currency_code, b.is_active AS biz_active
             FROM api_keys k
             JOIN businesses b ON b.id = k.business_id
             WHERE k.key_hash = ? AND k.is_active = 1 AND b.is_active = 1",
            [$hash]
        );
        if ($row) {
            // Update last_used_at
            $this->update($row['id'], ['last_used_at' => date('Y-m-d H:i:s')]);
        }
        return $row ?: null;
    }

    public function allForBusiness(int $businessId): array
    {
        return $this->query(
            "SELECT k.*, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
             FROM api_keys k
             LEFT JOIN users u ON u.id = k.created_by
             WHERE k.business_id = ?
             ORDER BY k.created_at DESC",
            [$businessId]
        );
    }
}
