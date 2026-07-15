<?php

class Business extends Model
{
    protected string $table = 'businesses';

    /** All active businesses with VSDC init status */
    public function allWithVsdc(): array
    {
        return $this->query(
            "SELECT b.*,
                    v.vsdc_url, v.initialized, v.initialized_at,
                    (SELECT COUNT(*) FROM items i WHERE i.business_id = b.id AND i.is_active = 1) AS item_count,
                    (SELECT COUNT(*) FROM sales  s WHERE s.business_id = b.id) AS sale_count
             FROM businesses b
             LEFT JOIN vsdc_config v ON v.business_id = b.id AND v.is_active = 1
             ORDER BY b.name"
        );
    }

    public function findWithVsdc(int $id): ?array
    {
        return $this->queryOne(
            "SELECT b.*, v.id AS vsdc_id, v.vsdc_url, v.device_serial, v.tax_office_name,
                    v.mrc_no, v.is_active AS vsdc_active, v.initialized, v.initialized_at,
                    v.last_std_codes
             FROM businesses b
             LEFT JOIN vsdc_config v ON v.business_id = b.id AND v.is_active = 1
             WHERE b.id = ?",
            [$id]
        );
    }

    public function tpinExists(string $tpin, ?int $excludeId = null): bool
    {
        $sql  = "SELECT id FROM businesses WHERE tpin = ?";
        $args = [$tpin];
        if ($excludeId) { $sql .= " AND id != ?"; $args[] = $excludeId; }
        return (bool) $this->queryOne($sql, $args);
    }

    public function upsertVsdc(int $businessId, array $data): void
    {
        $existing = $this->queryOne(
            "SELECT id FROM vsdc_config WHERE business_id = ?",
            [$businessId]
        );
        if ($existing) {
            $db = Database::getInstance();
            $db->query(
                "UPDATE vsdc_config SET label=?, vsdc_url=?, device_serial=?, tax_office_name=?,
                         mrc_no=?, is_active=?, updated_at=NOW()
                  WHERE business_id=?",
                [$data['label'], $data['vsdc_url'], $data['device_serial'],
                 $data['tax_office_name'], $data['mrc_no'], $data['is_active'], $businessId]
            );
        } else {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO vsdc_config (business_id, label, vsdc_url, device_serial, tax_office_name, mrc_no, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$businessId, $data['label'], $data['vsdc_url'], $data['device_serial'],
                 $data['tax_office_name'], $data['mrc_no'], $data['is_active']]
            );
        }
    }
}
