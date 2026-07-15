<?php

/**
 * ApiController — REST API for the Android POS app.
 * All endpoints require:  Authorization: Bearer <api_key>
 * All responses:          JSON, no HTML
 */
class ApiController extends Controller
{
    private ?array $authCtx = null;

    // ── Auth middleware ───────────────────────────────────────────────────

    private function guard(): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';
        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            $this->fail('Missing or invalid Authorization header.', 401);
            return false;
        }
        $ctx = (new ApiKey())->authenticate($m[1]);
        if (!$ctx) {
            $this->fail('Invalid or revoked API key.', 401);
            return false;
        }
        $this->authCtx = $ctx;
        return true;
    }

    // ── GET /api/v1/ping ─────────────────────────────────────────────────

    public function ping(): void
    {
        if (!$this->guard()) return;
        $this->ok([
            'message'     => 'SamPay Integrator online.',
            'business'    => $this->authCtx['biz_name'],
            'server_time' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── GET /api/v1/items ─────────────────────────────────────────────────

    public function items(): void
    {
        if (!$this->guard()) return;

        $db    = Database::getInstance();
        $rows  = $db->prepare(
            "SELECT item_code, item_cls_code, item_name, tax_ty_cd,
                    qty_unit_cd, pkg_unit_cd, selling_price, stock_qty,
                    vsdc_registered, bcd, description
             FROM items
             WHERE business_id = ? AND is_active = 1
             ORDER BY item_name"
        );
        $rows->execute([(int)$this->authCtx['business_id']]);
        $items = $rows->fetchAll();

        $this->ok([
            'count' => count($items),
            'items' => array_map(fn($i) => [
                'code'       => $i['item_code'],
                'cls_code'   => $i['item_cls_code'],
                'name'       => $i['item_name'],
                'tax_type'   => $i['tax_ty_cd'],
                'qty_unit'   => $i['qty_unit_cd'],
                'pkg_unit'   => $i['pkg_unit_cd'],
                'price'      => (float)$i['selling_price'],
                'stock'      => (float)$i['stock_qty'],
                'vsdc_ready' => (bool)$i['vsdc_registered'],
                'barcode'    => $i['bcd'],
                'description'=> $i['description'],
            ], $items),
        ]);
    }

    // ── POST /api/v1/sales ───────────────────────────────────────────────

    public function submitSale(): void
    {
        if (!$this->guard()) return;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->fail('POST required.', 405); return;
        }

        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['items'])) {
            $this->fail('items array is required.', 422); return;
        }

        $businessId = (int)$this->authCtx['business_id'];
        $db         = Database::getInstance();

        // Resolve & validate items
        $saleRef    = $body['ref'] ?? ('INV-' . strtoupper(bin2hex(random_bytes(4))));
        $subtotal   = $taxTotal = 0.0;
        $lineItems  = [];

        foreach ($body['items'] as $line) {
            $stmt = $db->prepare(
                "SELECT * FROM items WHERE business_id=? AND item_code=? AND is_active=1"
            );
            $stmt->execute([$businessId, $line['code'] ?? '']);
            $item = $stmt->fetch();
            if (!$item) { $this->fail('Item not found: ' . ($line['code'] ?? '?'), 422); return; }

            $qty      = (float)($line['qty']      ?? 1);
            $price    = (float)($line['price']    ?? $item['selling_price']);
            $discount = (float)($line['discount'] ?? 0);
            $lineAmt  = ($price - $discount) * $qty;
            $taxRate  = $item['tax_ty_cd'] === 'A' ? 0.16 : 0.0;
            $taxAmt   = round($lineAmt - ($lineAmt / (1 + $taxRate)), 2);

            $subtotal += $lineAmt;
            $taxTotal += $taxAmt;

            $lineItems[] = [
                'item_id'      => (int)$item['id'],
                'item_code'    => $item['item_code'],
                'item_cls_code'=> $item['item_cls_code'],
                'item_name'    => $item['item_name'],
                'tax_ty_cd'    => $item['tax_ty_cd'],
                'qty_unit_cd'  => $item['qty_unit_cd'],
                'pkg_unit_cd'  => $item['pkg_unit_cd'],
                'bcd'          => $item['bcd'],
                'qty'          => $qty,
                'unit_price'   => $price,
                'discount'     => $discount,
                'tax_amount'   => $taxAmt,
                'total_amount' => $lineAmt,
            ];
        }

        // Persist sale + lines
        $saleModel = new Sale();
        $saleData  = [
            'business_id'       => $businessId,
            'sale_ref'          => $saleRef,
            'sale_date'         => date('Y-m-d'),
            'customer_tpin'     => $body['customer_tpin']  ?? '1000000000',
            'customer_name'     => $body['customer_name']  ?? 'Cash Customer',
            'customer_email'    => $body['customer_email'] ?? null,
            'payment_method'    => $body['payment_method'] ?? 'CASH',
            'subtotal'          => $subtotal,
            'discount_amount'   => 0,
            'tax_amount'        => $taxTotal,
            'total_amount'      => $subtotal,
            'source'            => 'android',
            'android_device_id' => $this->authCtx['device_info'] ?? null,
        ];
        $saleId = $saleModel->createWithItems($saleData, $lineItems);

        // Deduct stock
        foreach ($lineItems as $li) {
            $db->prepare("UPDATE items SET stock_qty = stock_qty - ? WHERE id = ?")
               ->execute([$li['qty'], $li['item_id']]);
        }

        // Fiscalise with VSDC
        $fiscal = null;
        $vsdc   = VsdcService::forBusiness($businessId);
        if ($vsdc) {
            $saleRow  = $saleModel->findById($saleId);
            $business = $db->prepare("SELECT * FROM businesses WHERE id=?")->execute([$businessId])
                          ? $db->query("SELECT * FROM businesses WHERE id=$businessId")->fetch()
                          : [];

            $vsdcBiz = $db->prepare("SELECT * FROM businesses WHERE id=?");
            $vsdcBiz->execute([$businessId]);
            $biz = $vsdcBiz->fetch();

            $result = $vsdc->saveSales($saleRow, $lineItems, $biz ?: []);

            if ($result['success'] && !empty($result['data']['data'])) {
                $d = $result['data']['data'];
                $saleModel->markFiscalised($saleId, $d);
                $fiscal = [
                    'rcpt_no'    => $d['rcptNo']    ?? null,
                    'intrl_data' => $d['intrlData'] ?? null,
                    'rcpt_sign'  => $d['rcptSign']  ?? null,
                    'rcpt_dt'    => $d['rcptDt']    ?? null,
                ];
            } else {
                $saleModel->update($saleId, ['vsdc_error' => $result['message'] ?? 'VSDC error']);
            }
        }

        $this->ok([
            'sale_id'    => $saleId,
            'sale_ref'   => $saleRef,
            'total'      => $subtotal,
            'tax'        => $taxTotal,
            'fiscalised' => $fiscal !== null,
            'fiscal'     => $fiscal,
            'message'    => $fiscal
                ? 'Sale submitted and fiscalised by ZRA VSDC.'
                : 'Sale saved. VSDC fiscalisation pending.',
        ], 201);
    }

    // ── GET /api/v1/sales/{id} ────────────────────────────────────────────

    public function getReceipt(string $id): void
    {
        if (!$this->guard()) return;

        $sale = (new Sale())->getWithItems((int)$id);
        if (!$sale || (int)$sale['business_id'] !== (int)$this->authCtx['business_id']) {
            $this->fail('Sale not found.', 404); return;
        }

        $this->ok([
            'sale_ref'      => $sale['sale_ref'],
            'sale_date'     => $sale['sale_date'],
            'customer'      => $sale['customer_name'],
            'customer_tpin' => $sale['customer_tpin'],
            'payment'       => $sale['payment_method'],
            'subtotal'      => (float)$sale['subtotal'],
            'tax'           => (float)$sale['tax_amount'],
            'total'         => (float)$sale['total_amount'],
            'fiscalised'    => (bool)$sale['is_fiscalised'],
            'rcpt_no'       => $sale['vsdc_rcpt_no'],
            'intrl_data'    => $sale['vsdc_intrl_data'],
            'rcpt_sign'     => $sale['vsdc_rcpt_sign'],
            'rcpt_dt'       => $sale['vsdc_rcpt_dt'],
            'items'         => array_map(fn($l) => [
                'code'  => $l['item_code'],
                'name'  => $l['item_name'],
                'qty'   => (float)$l['qty'],
                'price' => (float)$l['unit_price'],
                'tax'   => (float)$l['tax_amount'],
                'total' => (float)$l['total_amount'],
            ], $sale['items']),
        ]);
    }

    // ── JSON response helpers ─────────────────────────────────────────────

    private function ok(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function fail(string $message, int $status = 400): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
