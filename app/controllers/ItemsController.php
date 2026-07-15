<?php

class ItemsController extends Controller
{
    private Item     $model;
    private Business $bizModel;

    public function __construct()
    {
        $this->model    = new Item();
        $this->bizModel = new Business();
    }

    public function index(): void
    {
        Auth::requireLogin();

        [$businessId, $businesses] = $this->resolveBusinessScope();
        $items = $this->model->allWithBusiness($businessId);

        $this->view('items.index', [
            'pageTitle'      => 'Items / Stock',
            'activeMenu'     => 'items',
            'items'          => $items,
            'businesses'     => $businesses,
            'filterBusiness' => $businessId,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        [$preselect, $businesses] = $this->resolveBusinessScope(
            (int)$this->get('business_id', 0) ?: null
        );
        $errors = [];

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validate();

            // Business users must be scoped to their own business
            if (Auth::isBusiness()) {
                $fields['business_id'] = Auth::businessId();
            }

            if (empty($errors)) {
                if ($this->model->codeExists($fields['business_id'], $fields['item_code'])) {
                    $errors['item_code'] = 'This item code is already used for that business.';
                } else {
                    $fields['created_by'] = Auth::id();
                    $id = $this->model->insert($fields);
                    AuditLog::record(Auth::id(), 'CREATE', 'items', $id, 'Item created: ' . $fields['item_name'], null, null, $fields['business_id']);
                    Flash::success('Item "' . $fields['item_name'] . '" created. Register it with VSDC to enable fiscalisation.');
                    $this->redirect('items');
                }
            }
        }

        $itemClasses = $preselect ? $this->getItemClasses($preselect) : [];

        $this->view('items.form', [
            'pageTitle'   => 'New Item',
            'activeMenu'  => 'items',
            'item'        => null,
            'businesses'  => $businesses,
            'preselect'   => $preselect,
            'taxTypes'    => Item::taxTypes(),
            'qtyUnits'    => Item::qtyUnits(),
            'pkgUnits'    => Item::pkgUnits(),
            'itemClasses' => $itemClasses,
            'errors'      => $errors,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['admin', 'business_admin']);
        $item = $this->model->findById((int)$id);
        if (!$item) $this->abort(404);

        // Business admin can only edit their own business items
        if (Auth::isBusiness() && Auth::businessId() !== (int)$item['business_id']) {
            $this->abort(403);
        }

        [, $businesses] = $this->resolveBusinessScope();
        $errors = [];

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validate();

            if (Auth::isBusiness()) {
                $fields['business_id'] = Auth::businessId();
            }

            if (empty($errors)) {
                if ($this->model->codeExists($fields['business_id'], $fields['item_code'], (int)$id)) {
                    $errors['item_code'] = 'This item code is already used for that business.';
                } else {
                    $old = $item;
                    if ((float)$fields['selling_price'] !== (float)$item['selling_price']) {
                        $fields['vsdc_registered'] = 0;
                    }
                    $this->model->update((int)$id, $fields);
                    AuditLog::record(Auth::id(), 'UPDATE', 'items', (int)$id, 'Item updated', $old, $fields, $fields['business_id']);
                    Flash::success('Item updated.');
                    $this->redirect('items');
                }
            }
            $item = array_merge($item, $fields);
        }

        $itemClasses = $this->getItemClasses($item['business_id']);

        $this->view('items.form', [
            'pageTitle'   => 'Edit Item',
            'activeMenu'  => 'items',
            'item'        => $item,
            'businesses'  => $businesses,
            'preselect'   => $item['business_id'],
            'taxTypes'    => Item::taxTypes(),
            'qtyUnits'    => Item::qtyUnits(),
            'pkgUnits'    => Item::pkgUnits(),
            'itemClasses' => $itemClasses,
            'errors'      => $errors,
        ]);
    }

    public function delete(string $id): void
    {
        Auth::requireRole(['admin', 'business_admin']);
        $item = $this->model->findById((int)$id);
        if (!$item) $this->abort(404);
        if (Auth::isBusiness() && Auth::businessId() !== (int)$item['business_id']) {
            $this->abort(403);
        }
        $this->model->update((int)$id, ['is_active' => 0]);
        AuditLog::record(Auth::id(), 'DELETE', 'items', (int)$id, 'Item deactivated: ' . $item['item_name'], null, null, $item['business_id']);
        Flash::success('Item deactivated.');
        $this->redirect('items');
    }

    public function registerVsdc(string $id): void
    {
        Auth::requireAdmin(); // VSDC ops: SamPay admin only
        $item = $this->model->findById((int)$id);
        if (!$item) $this->abort(404);

        $vsdc = VsdcService::forBusiness($item['business_id']);
        if (!$vsdc) {
            Flash::error('No active VSDC configured for this business.');
            $this->redirect('items');
            return;
        }

        $result = $vsdc->saveItem($item);
        if ($result['success']) {
            $this->model->markVsdcRegistered((int)$id);
            AuditLog::record(Auth::id(), 'UPDATE', 'items', (int)$id, 'Item registered with VSDC', null, null, $item['business_id']);
            Flash::success('Item "' . $item['item_name'] . '" registered with ZRA VSDC.');
        } else {
            Flash::error('VSDC registration failed: ' . ($result['message'] ?? 'Unknown error'));
        }
        $this->redirect('items');
    }

    public function registerAllVsdc(string $businessId): void
    {
        Auth::requireAdmin();
        $items = $this->model->query(
            "SELECT * FROM items WHERE business_id = ? AND vsdc_registered = 0 AND is_active = 1",
            [(int)$businessId]
        );

        $vsdc = VsdcService::forBusiness((int)$businessId);
        if (!$vsdc) {
            Flash::error('No active VSDC configured.');
            $this->redirect('items');
            return;
        }

        $ok = $fail = 0;
        foreach ($items as $item) {
            $result = $vsdc->saveItem($item);
            if ($result['success']) {
                $this->model->markVsdcRegistered($item['id']);
                $ok++;
            } else {
                $fail++;
            }
        }
        Flash::success("VSDC registration: $ok succeeded, $fail failed.");
        $this->redirect('items?business_id=' . $businessId);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Returns [resolvedBusinessId, businessList].
     * For business users: forces their business_id, returns empty list (no selector shown).
     * For SamPay admin: honours the query param, returns all businesses.
     */
    private function resolveBusinessScope(?int $fallback = null): array
    {
        if (Auth::isBusiness()) {
            return [Auth::businessId(), []];
        }
        $businessId = $fallback ?? ((int)$this->get('business_id', 0) ?: null);
        $businesses = $this->bizModel->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY name");
        return [$businessId, $businesses];
    }

    private function getItemClasses(int $businessId): array
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT cls_code, cls_name, tax_ty_cd FROM item_classes WHERE business_id = ? ORDER BY cls_code");
        $stmt->execute([$businessId]);
        return $stmt->fetchAll();
    }

    private function validate(): array
    {
        $fields = [
            'business_id'    => (int)$this->post('business_id', 0),
            'item_code'      => trim($this->post('item_code', '')),
            'item_cls_code'  => trim($this->post('item_cls_code', '')),
            'item_name'      => trim($this->post('item_name', '')),
            'tax_ty_cd'      => $this->post('tax_ty_cd', 'A'),
            'qty_unit_cd'    => $this->post('qty_unit_cd', 'U'),
            'pkg_unit_cd'    => $this->post('pkg_unit_cd', 'NT'),
            'orgin_natrs_cd' => $this->post('orgin_natrs_cd', 'ZM'),
            'btch_no'        => trim($this->post('btch_no', '')) ?: null,
            'bcd'            => trim($this->post('bcd', ''))    ?: null,
            'selling_price'  => (float)$this->post('selling_price', 0),
            'stock_qty'      => (float)$this->post('stock_qty', 0),
            'description'    => trim($this->post('description', '')),
            'is_active'      => (int)$this->post('is_active', 1),
        ];
        $errors = [];
        if (!Auth::isBusiness() && !$fields['business_id']) $errors['business_id'] = 'Select a business.';
        if (empty($fields['item_code']))    $errors['item_code']    = 'Item code required.';
        if (empty($fields['item_name']))    $errors['item_name']    = 'Item name required.';
        if (empty($fields['item_cls_code'])) $errors['item_cls_code'] = 'Item class code required (from ZRA).';
        if ($fields['selling_price'] < 0)  $errors['selling_price'] = 'Price cannot be negative.';
        return compact('fields', 'errors');
    }
}
