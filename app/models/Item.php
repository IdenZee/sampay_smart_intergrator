<?php

class Item extends Model
{
    protected string $table = 'items';

    public function allForBusiness(int $businessId, bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'AND i.is_active = 1' : '';
        return $this->query(
            "SELECT i.*, b.name AS business_name
             FROM items i
             JOIN businesses b ON b.id = i.business_id
             WHERE i.business_id = ? $where
             ORDER BY i.item_name",
            [$businessId]
        );
    }

    public function allWithBusiness(?int $businessId = null): array
    {
        $where = $businessId ? "WHERE i.business_id = $businessId" : '';
        return $this->query(
            "SELECT i.*, b.name AS business_name
             FROM items i
             JOIN businesses b ON b.id = i.business_id
             $where
             ORDER BY b.name, i.item_name"
        );
    }

    public function codeExists(int $businessId, string $code, ?int $excludeId = null): bool
    {
        $sql  = "SELECT id FROM items WHERE business_id = ? AND item_code = ?";
        $args = [$businessId, $code];
        if ($excludeId) { $sql .= " AND id != ?"; $args[] = $excludeId; }
        return (bool) $this->queryOne($sql, $args);
    }

    public function markVsdcRegistered(int $id): void
    {
        $this->update($id, [
            'vsdc_registered'    => 1,
            'vsdc_registered_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateStock(int $id, float $delta): void
    {
        $db = Database::getInstance();
        $db->query(
            "UPDATE items SET stock_qty = stock_qty + ? WHERE id = ?",
            [$delta, $id]
        );
    }

    /** ZRA standard tax type codes */
    public static function taxTypes(): array
    {
        return [
            'A' => 'A — Standard (16% VAT)',
            'B' => 'B — VAT Zero-rated (0%)',
            'C' => 'C — Excise Duty',
            'D' => 'D — VAT + Excise',
            'E' => 'E — Exempt',
        ];
    }

    /** Common ZRA quantity unit codes */
    public static function qtyUnits(): array
    {
        return [
            'U'  => 'U — Unit',
            'KG' => 'KG — Kilogram',
            'G'  => 'G — Gram',
            'L'  => 'L — Litre',
            'ML' => 'ML — Millilitre',
            'M'  => 'M — Metre',
            'CM' => 'CM — Centimetre',
            'M2' => 'M2 — Square Metre',
            'NT' => 'NT — Net (Piece)',
            'BX' => 'BX — Box',
            'CS' => 'CS — Case',
            'PK' => 'PK — Pack',
            'DZ' => 'DZ — Dozen',
        ];
    }

    /** Common ZRA package unit codes */
    public static function pkgUnits(): array
    {
        return [
            'NT' => 'NT — Net (Single)',
            'BX' => 'BX — Box',
            'CS' => 'CS — Case',
            'PK' => 'PK — Pack',
            'BO' => 'BO — Bottle',
            'CT' => 'CT — Carton',
            'JR' => 'JR — Jar',
            'TN' => 'TN — Tin',
            'BG' => 'BG — Bag',
        ];
    }
}
