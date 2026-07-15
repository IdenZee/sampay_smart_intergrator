<?php

class Sale extends Model
{
    protected string $table = 'sales';

    public function createWithItems(array $saleData, array $saleItems): int
    {
        $saleId = $this->insert($saleData);

        foreach ($saleItems as $si) {
            $this->query(
                "INSERT INTO sale_items
                    (sale_id, item_id, item_code, item_name, tax_ty_cd, qty, unit_price, discount, tax_amount, total_amount)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $saleId, $si['item_id'], $si['item_code'], $si['item_name'],
                    $si['tax_ty_cd'], $si['qty'], $si['unit_price'],
                    $si['discount'], $si['tax_amount'], $si['total_amount'],
                ]
            );
        }
        return $saleId;
    }

    public function markFiscalised(int $id, array $d): void
    {
        $this->update($id, [
            'is_fiscalised'  => 1,
            'vsdc_rcpt_no'   => $d['rcptNo']    ?? null,
            'vsdc_intrl_data'=> $d['intrlData'] ?? null,
            'vsdc_rcpt_sign' => $d['rcptSign']  ?? null,
            'vsdc_rcpt_dt'   => $d['rcptDt']    ?? null,
            'fiscalised_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function getWithItems(int $id): ?array
    {
        $sale = $this->queryOne(
            "SELECT s.*, b.name AS business_name
             FROM sales s JOIN businesses b ON b.id = s.business_id
             WHERE s.id = ?",
            [$id]
        );
        if (!$sale) return null;

        $sale['items'] = $this->query(
            "SELECT * FROM sale_items WHERE sale_id = ?",
            [$id]
        );
        return $sale;
    }

    public function paginateForBusiness(int $businessId, int $page = 1, int $perPage = 30): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->query(
            "SELECT * FROM sales WHERE business_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$businessId, $perPage, $offset]
        );
    }

    public function allPaginated(int $page = 1, int $perPage = 30, ?int $businessId = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = $businessId ? "WHERE s.business_id = $businessId" : '';
        return $this->query(
            "SELECT s.*, b.name AS business_name
             FROM sales s JOIN businesses b ON b.id = s.business_id
             $where
             ORDER BY s.created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }
}
