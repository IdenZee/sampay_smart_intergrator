<?php

class ApiKeysController extends Controller
{
    private ApiKey   $model;
    private Business $bizModel;

    public function __construct()
    {
        $this->model    = new ApiKey();
        $this->bizModel = new Business();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $businessId = (int)$this->get('business_id', 0) ?: null;
        $businesses = $this->bizModel->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY name");

        $keys = $businessId
            ? $this->model->allForBusiness($businessId)
            : $this->model->query(
                "SELECT k.*, b.name AS biz_name, CONCAT(u.first_name,' ',u.last_name) AS created_by_name
                 FROM api_keys k
                 JOIN businesses b ON b.id = k.business_id
                 LEFT JOIN users u ON u.id = k.created_by
                 ORDER BY k.created_at DESC"
            );

        $this->view('api-keys.index', [
            'pageTitle'      => 'API Keys',
            'activeMenu'     => 'api-keys',
            'keys'           => $keys,
            'businesses'     => $businesses,
            'filterBusiness' => $businessId,
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $businesses = $this->bizModel->query("SELECT * FROM businesses WHERE is_active = 1 ORDER BY name");
        $newKey     = null;
        $errors     = [];
        $preselect  = (int)$this->get('business_id', 0) ?: null;

        if ($this->isPost()) {
            $businessId = (int)$this->post('business_id', 0);
            $keyName    = trim($this->post('key_name', ''));
            $deviceInfo = trim($this->post('device_info', ''));

            if (!$businessId)    $errors['business_id'] = 'Select a business.';
            if (empty($keyName)) $errors['key_name']    = 'Key name is required.';

            if (empty($errors)) {
                $result = $this->model->generate($businessId, $keyName, $deviceInfo, Auth::id());
                $newKey = $result['raw_key'];
                AuditLog::record(Auth::id(), 'CREATE', 'api_keys', $result['id'], 'API key created: ' . $keyName, null, null, $businessId);
                // Don't redirect — show the key once
            }
        }

        $this->view('api-keys.create', [
            'pageTitle'  => 'New API Key',
            'activeMenu' => 'api-keys',
            'businesses' => $businesses,
            'preselect'  => $preselect,
            'newKey'     => $newKey,
            'errors'     => $errors,
        ]);
    }

    public function revoke(string $id): void
    {
        Auth::requireAdmin();
        $key = $this->model->findById((int)$id);
        if (!$key) $this->abort(404);

        $this->model->update((int)$id, ['is_active' => 0]);
        AuditLog::record(Auth::id(), 'DELETE', 'api_keys', (int)$id, 'API key revoked: ' . $key['key_name'], null, null, $key['business_id']);
        Flash::success('API key revoked.');
        $this->redirect('api-keys');
    }
}
