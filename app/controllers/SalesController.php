<?php

class SalesController extends Controller
{
    private Sale     $model;
    private Business $bizModel;

    public function __construct()
    {
        $this->model    = new Sale();
        $this->bizModel = new Business();
    }

    public function index(): void
    {
        Auth::requireLogin();

        $page    = max(1, (int)$this->get('page', 1));
        $perPage = 30;

        // Scope to own business if not SamPay admin
        if (Auth::isBusiness()) {
            $businessId = Auth::businessId();
            $businesses = [];
        } else {
            $businessId = (int)$this->get('business_id', 0) ?: null;
            $businesses = $this->bizModel->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY name");
        }

        $sales = $this->model->allPaginated($page, $perPage, $businessId);

        $db    = Database::getInstance();
        $where = $businessId ? "WHERE business_id = $businessId" : '';
        $total = (int)$db->query("SELECT COUNT(*) FROM sales $where")->fetchColumn();
        $fiscalised = (int)$db->query(
            "SELECT COUNT(*) FROM sales WHERE is_fiscalised = 1 " . ($businessId ? "AND business_id = $businessId" : '')
        )->fetchColumn();

        $this->view('sales.index', [
            'pageTitle'      => 'Sales / Invoices',
            'activeMenu'     => 'sales',
            'sales'          => $sales,
            'businesses'     => $businesses,
            'filterBusiness' => $businessId,
            'page'           => $page,
            'pages'          => (int)ceil($total / $perPage),
            'total'          => $total,
            'fiscalised'     => $fiscalised,
        ]);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();

        $sale = $this->model->getWithItems((int)$id);
        if (!$sale) $this->abort(404);

        // Business users can only view their own business sales
        if (Auth::isBusiness() && Auth::businessId() !== (int)$sale['business_id']) {
            $this->abort(403);
        }

        $this->view('sales.show', [
            'pageTitle'  => 'Sale ' . $sale['sale_ref'],
            'activeMenu' => 'sales',
            'sale'       => $sale,
            'items'      => $sale['items'],
        ]);
    }
}
