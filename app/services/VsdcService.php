<?php

/**
 * VsdcService
 *
 * Wraps all ZRA VSDC API calls for one business's VSDC instance.
 * All calls are POST to the VSDC URL with JSON body.
 *
 * ZRA VSDC 6-step flow:
 *  1. selectInitDevice   — initialise device
 *  2. selectCdCls        — fetch standard codes
 *  3. selectItemClsList  — fetch item class list
 *  4. saveItem           — register an item
 *  5. saveBhfCustomer    — register a customer (optional)
 *  6. saveSales          — submit a sale for signing
 */
class VsdcService
{
    private string $baseUrl;
    private array  $config;   // row from vsdc_config

    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->baseUrl = rtrim($config['vsdc_url'], '/');
    }

    /**
     * Factory: build VsdcService for a given business_id.
     * Returns null if no active VSDC config exists.
     */
    public static function forBusiness(int $businessId): ?self
    {
        $db  = Database::getInstance();
        $cfg = $db->query(
            "SELECT * FROM vsdc_config WHERE business_id = ? AND is_active = 1 LIMIT 1",
            [$businessId]
        )->fetch();
        return $cfg ? new self($cfg) : null;
    }

    // ── Step 1: Initialise Device ─────────────────────────────────────────

    public function initDevice(array $business): array
    {
        $payload = [
            'tin'   => $business['tpin'],
            'bhfId' => $business['branch_code'] ?? '000',
            'dvcSrlNo' => $this->config['device_serial'] ?? '',
        ];
        $response = $this->post('/initializer/selectInitDevice', $payload);

        if ($response['success'] && isset($response['data']['resultCd'])) {
            $ok = in_array($response['data']['resultCd'], ['000', '001']);
            if ($ok) {
                // Mark as initialised in DB
                $db = Database::getInstance();
                $db->query(
                    "UPDATE vsdc_config SET initialized=1, initialized_at=NOW() WHERE id=?",
                    [$this->config['id']]
                );
                $response['initialised'] = true;
            }
        }
        return $response;
    }

    // ── Step 2: Fetch Standard Codes ─────────────────────────────────────

    public function fetchStandardCodes(array $business): array
    {
        $payload = [
            'tin'   => $business['tpin'],
            'bhfId' => $business['branch_code'] ?? '000',
            'lastReqDt' => '20200101000000',
        ];
        return $this->post('/code/selectCdList', $payload);
    }

    // ── Step 3: Fetch Item Class List ─────────────────────────────────────

    public function fetchItemClasses(array $business): array
    {
        $payload = [
            'tin'   => $business['tpin'],
            'bhfId' => $business['branch_code'] ?? '000',
            'lastReqDt' => '20200101000000',
        ];
        $response = $this->post('/itemClass/selectItemClsList', $payload);

        // Cache item classes in DB
        if ($response['success'] && !empty($response['data']['itemClsList'])) {
            $db = Database::getInstance();
            foreach ($response['data']['itemClsList'] as $cls) {
                $db->query(
                    "INSERT INTO item_classes (business_id, cls_code, cls_name, tax_ty_cd)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE cls_name=VALUES(cls_name), tax_ty_cd=VALUES(tax_ty_cd)",
                    [
                        $this->config['business_id'],
                        $cls['itemClsCd'],
                        $cls['itemClsNm'],
                        $cls['taxTyCd'] ?? null,
                    ]
                );
            }
            $db->query(
                "UPDATE vsdc_config SET last_std_codes=NOW() WHERE id=?",
                [$this->config['id']]
            );
        }
        return $response;
    }

    // ── Step 4: Register Item ─────────────────────────────────────────────

    public function saveItem(array $item, array $business = []): array
    {
        // Load business if not supplied
        if (empty($business)) {
            $db       = Database::getInstance();
            $business = $db->query("SELECT * FROM businesses WHERE id=?", [$item['business_id']])->fetch() ?: [];
        }

        $payload = [
            'tin'         => $business['tpin']        ?? '',
            'bhfId'       => $business['branch_code'] ?? '000',
            'itemCd'      => $item['item_code'],
            'itemClsCd'   => $item['item_cls_code'],
            'itemNm'      => $item['item_name'],
            'orgnNatCd'   => $item['orgin_natrs_cd']  ?? 'ZM',
            'pkgUnitCd'   => $item['pkg_unit_cd']     ?? 'NT',
            'qtyUnitCd'   => $item['qty_unit_cd']     ?? 'U',
            'taxTyCd'     => $item['tax_ty_cd']       ?? 'A',
            'btchNo'      => $item['btch_no']         ?? null,
            'bcd'         => $item['bcd']             ?? null,
            'dftPrc'      => (float)$item['selling_price'],
            'addInfo'     => $item['description']     ?? null,
            'sftyQty'     => 0,
            'isrcAplcbYn' => 'N',
            'useYn'       => 'Y',
            'regrId'      => 'admin',
            'regrNm'      => 'SamPay Integrator',
            'modrId'      => 'admin',
            'modrNm'      => 'SamPay Integrator',
        ];
        return $this->post('/items/saveItem', $payload);
    }

    // ── Step 5: Save Customer ─────────────────────────────────────────────

    public function saveCustomer(array $business, array $customer): array
    {
        $payload = [
            'tin'        => $business['tpin'],
            'bhfId'      => $business['branch_code'] ?? '000',
            'custNo'     => $customer['tpin']  ?? '1000000000',
            'custTin'    => $customer['tpin']  ?? '1000000000',
            'custNm'     => $customer['name']  ?? 'Cash Customer',
            'adrs'       => $customer['address'] ?? '',
            'telNo'      => $customer['phone'] ?? '',
            'email'      => $customer['email'] ?? '',
            'faxNo'      => '',
            'useYn'      => 'Y',
            'regrId'     => 'admin',
            'regrNm'     => 'SamPay Integrator',
            'modrId'     => 'admin',
            'modrNm'     => 'SamPay Integrator',
        ];
        return $this->post('/customers/saveBhfCustomer', $payload);
    }

    // ── Step 6: Submit Sale ───────────────────────────────────────────────

    public function saveSales(array $sale, array $saleItems, array $business): array
    {
        $itemList = [];
        foreach ($saleItems as $i => $si) {
            $qty      = (float)$si['qty'];
            $price    = (float)$si['unit_price'];
            $discount = (float)($si['discount'] ?? 0);
            $taxable  = ($price - $discount) * $qty;
            $taxRate  = $this->taxRateForCode($si['tax_ty_cd'] ?? 'A');
            $taxAmt   = round($taxable - ($taxable / (1 + $taxRate)), 2);

            $itemList[] = [
                'itemSeq'   => $i + 1,
                'itemCd'    => $si['item_code'],
                'itemClsCd' => $si['item_cls_code'] ?? '',
                'itemNm'    => $si['item_name'],
                'bcd'       => $si['bcd'] ?? null,
                'pkgUnitCd' => $si['pkg_unit_cd'] ?? 'NT',
                'pkg'       => $qty,
                'qtyUnitCd' => $si['qty_unit_cd'] ?? 'U',
                'qty'       => $qty,
                'prc'       => $price,
                'splyAmt'   => round($price * $qty, 2),
                'dcRt'      => 0,
                'dcAmt'     => $discount,
                'isrccCd'   => null,
                'isrccNm'   => null,
                'isrcRt'    => null,
                'isrcAmt'   => null,
                'taxTyCd'   => $si['tax_ty_cd'] ?? 'A',
                'taxblAmt'  => round($taxable, 2),
                'taxAmt'    => $taxAmt,
                'totAmt'    => round($taxable, 2),
            ];
        }

        $payload = [
            'tin'        => $business['tpin'],
            'bhfId'      => $business['branch_code'] ?? '000',
            'invcNo'     => $sale['sale_ref'],
            'orgInvcNo'  => 0,
            'salesTyCd'  => 'N',     // N=Normal
            'rcptTyCd'   => 'S',     // S=Sale
            'pmtTyCd'    => $sale['payment_method'] ?? 'CASH',
            'salesSttsCd'=> '02',    // 02=Completed
            'cfmDt'      => date('YmdHis'),
            'salesDt'    => date('Ymd', strtotime($sale['sale_date'])),
            'stockRlsDt' => null,
            'cnclReqDt'  => null,
            'cnclDt'     => null,
            'rfdDt'      => null,
            'rfdRsnCd'   => null,
            'totItemCnt' => count($itemList),
            'taxblAmtA'  => (float)$sale['subtotal'],
            'taxblAmtB'  => 0,
            'taxblAmtC'  => 0,
            'taxblAmtD'  => 0,
            'taxRtA'     => 16,
            'taxRtB'     => 0,
            'taxRtC'     => 0,
            'taxRtD'     => 0,
            'taxAmtA'    => (float)$sale['tax_amount'],
            'taxAmtB'    => 0,
            'taxAmtC'    => 0,
            'taxAmtD'    => 0,
            'tlAmt'      => (float)$sale['total_amount'],
            'custTin'    => $sale['customer_tpin'] ?? '1000000000',
            'custNm'     => $sale['customer_name'] ?? 'Cash Customer',
            'rcptPbctDt' => date('YmdHis'),
            'tradeTerms' => null,
            'adrs'       => null,
            'remark'     => null,
            'destnCountryCd' => null,
            'itemList'   => $itemList,
        ];

        return $this->post('/trnsSales/saveSales', $payload);
    }

    // ── Private: HTTP helper ──────────────────────────────────────────────

    private function post(string $endpoint, array $payload): array
    {
        $url  = $this->baseUrl . $endpoint;
        $body = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body),
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno) {
            return ['success' => false, 'message' => "cURL error: $error", 'http' => 0, 'data' => null];
        }

        $decoded = json_decode($raw, true);
        $resultCd = $decoded['resultCd'] ?? null;
        $success  = in_array($resultCd, ['000', '001']) || ($http >= 200 && $http < 300 && $resultCd === null);

        return [
            'success' => $success,
            'http'    => $http,
            'message' => $decoded['resultMsg'] ?? ($success ? 'OK' : 'Unknown error'),
            'data'    => $decoded,
            'raw'     => $raw,
        ];
    }

    private function taxRateForCode(string $code): float
    {
        return match(strtoupper($code)) {
            'A' => 0.16,
            'C', 'D' => 0.16,
            default  => 0.0,
        };
    }
}
