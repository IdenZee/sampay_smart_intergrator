<?php

class VsdcController extends Controller
{
    private Business $bizModel;

    public function __construct()
    {
        $this->bizModel = new Business();
    }

    // ── VSDC Dashboard ────────────────────────────────────────────────────

    public function dashboard(string $id): void
    {
        Auth::requireAdmin();
        $business = $this->bizModel->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM item_classes WHERE business_id = ? ORDER BY cls_code");
        $stmt->execute([(int)$id]);
        $classes = $stmt->fetchAll();

        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM items WHERE business_id = ? AND vsdc_registered = 0 AND is_active = 1"
        );
        $stmt->execute([(int)$id]);
        $unregistered = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM items WHERE business_id = ? AND is_active = 1");
        $stmt->execute([(int)$id]);
        $totalItems = (int)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM items WHERE business_id = ? AND vsdc_registered = 1");
        $stmt->execute([(int)$id]);
        $registered = (int)$stmt->fetchColumn();

        // Load unregistered items list for the table
        $stmt = $db->prepare(
            "SELECT * FROM items WHERE business_id = ? AND vsdc_registered = 0 AND is_active = 1 ORDER BY item_name"
        );
        $stmt->execute([(int)$id]);
        $pendingItems = $stmt->fetchAll();

        $this->view('vsdc.dashboard', [
            'pageTitle'    => 'VSDC — ' . $business['name'],
            'activeMenu'   => 'businesses',
            'business'     => $business,
            'classes'      => $classes,
            'unregistered' => $unregistered,
            'registered'   => $registered,
            'totalItems'   => $totalItems,
            'pendingItems' => $pendingItems,
        ]);
    }

    // ── Step 1: Initialise Device ─────────────────────────────────────────

    public function initDevice(string $id): void
    {
        Auth::requireRole(['admin']);
        $business = $this->bizModel->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        if (!$business['vsdc_url']) {
            Flash::error('No VSDC URL configured. Edit the business first.');
            $this->redirect('businesses/' . $id);
            return;
        }

        $vsdc   = VsdcService::forBusiness((int)$id);
        $result = $vsdc->initDevice($business);

        if ($result['success'] || !empty($result['initialised'])) {
            AuditLog::record(Auth::id(), 'UPDATE', 'vsdc', (int)$id,
                'VSDC device initialised', null, null, (int)$id);

            // Auto-fetch codes + classes after successful init
            $vsdc->fetchStandardCodes($business);
            $classResult = $vsdc->fetchItemClasses($business);

            $classCount = count($classResult['data']['itemClsList'] ?? []);
            Flash::success(
                'VSDC device initialised. ' .
                ($classCount ? "$classCount item classes cached automatically." : '')
            );
        } else {
            Flash::error('VSDC init failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        $this->redirect('vsdc/dashboard/' . $id);
    }

    // ── Step 2: Refresh Standard Codes ───────────────────────────────────

    public function fetchCodes(string $id): void
    {
        Auth::requireRole(['admin']);
        $business = $this->bizModel->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        $vsdc   = VsdcService::forBusiness((int)$id);
        $result = $vsdc->fetchStandardCodes($business);

        if ($result['success']) {
            Flash::success('Standard codes refreshed.');
        } else {
            Flash::error('Failed: ' . ($result['message'] ?? 'Unknown error'));
        }
        $this->redirect('vsdc/dashboard/' . $id);
    }

    // ── Step 3: Fetch & Cache Item Classes ───────────────────────────────

    public function fetchItemClasses(string $id): void
    {
        Auth::requireRole(['admin']);
        $business = $this->bizModel->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        $vsdc   = VsdcService::forBusiness((int)$id);
        $result = $vsdc->fetchItemClasses($business);

        if ($result['success']) {
            $count = count($result['data']['itemClsList'] ?? []);
            Flash::success("$count item classes fetched and cached.");
        } else {
            Flash::error('Failed: ' . ($result['message'] ?? 'Unknown error'));
        }
        $this->redirect('vsdc/dashboard/' . $id);
    }

    // ── Step 4: Register one item with VSDC ──────────────────────────────

    public function registerItem(string $itemId): void
    {
        Auth::requireAdmin();
        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([(int)$itemId]);
        $item = $stmt->fetch();
        if (!$item) $this->abort(404);

        $vsdc = VsdcService::forBusiness((int)$item['business_id']);
        if (!$vsdc) {
            Flash::error('No active VSDC configured for this business.');
            $this->redirect('vsdc/dashboard/' . $item['business_id']);
            return;
        }

        $stmt = $db->prepare("SELECT * FROM businesses WHERE id = ?");
        $stmt->execute([(int)$item['business_id']]);
        $business = $stmt->fetch();

        $result = $vsdc->saveItem($item, $business ?: []);

        if ($result['success']) {
            $db->prepare("UPDATE items SET vsdc_registered=1, vsdc_registered_at=NOW() WHERE id=?")
               ->execute([(int)$itemId]);
            AuditLog::record(Auth::id(), 'UPDATE', 'items', (int)$itemId,
                'VSDC registered: ' . $item['item_name'], null, null, (int)$item['business_id']);
            Flash::success('"' . $item['item_name'] . '" registered with ZRA VSDC.');
        } else {
            Flash::error('VSDC failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        $this->redirect('vsdc/dashboard/' . $item['business_id']);
    }

    // ── Step 4b: Bulk register all pending items ──────────────────────────

    public function registerAllItems(string $id): void
    {
        Auth::requireRole(['admin']);
        $business = $this->bizModel->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        $vsdc = VsdcService::forBusiness((int)$id);
        if (!$vsdc) {
            Flash::error('No active VSDC configured.');
            $this->redirect('vsdc/dashboard/' . $id);
            return;
        }

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM items WHERE business_id = ? AND vsdc_registered = 0 AND is_active = 1"
        );
        $stmt->execute([(int)$id]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            Flash::info('No items pending VSDC registration.');
            $this->redirect('vsdc/dashboard/' . $id);
            return;
        }

        $ok = $fail = 0;
        foreach ($items as $item) {
            $result = $vsdc->saveItem($item, $business);
            if ($result['success']) {
                $db->prepare("UPDATE items SET vsdc_registered=1, vsdc_registered_at=NOW() WHERE id=?")
                   ->execute([$item['id']]);
                $ok++;
            } else {
                $fail++;
            }
        }

        AuditLog::record(Auth::id(), 'UPDATE', 'items', null,
            "Bulk VSDC: $ok ok, $fail failed", null, null, (int)$id);
        Flash::success("Done — $ok registered" . ($fail ? ", $fail failed (check item class codes)." : '.'));
        $this->redirect('vsdc/dashboard/' . $id);
    }
}
