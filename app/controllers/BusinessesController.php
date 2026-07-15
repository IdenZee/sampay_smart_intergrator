<?php

class BusinessesController extends Controller
{
    private Business $model;
    private User     $userModel;

    public function __construct()
    {
        $this->model     = new Business();
        $this->userModel = new User();
    }

    public function index(): void
    {
        Auth::requireAdmin();
        $businesses = $this->model->allWithVsdc();
        $this->view('businesses.index', [
            'pageTitle'  => 'Businesses',
            'activeMenu' => 'businesses',
            'businesses' => $businesses,
        ]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $errors  = [];
        $tempPw  = null; // shown once after creation

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validate();

            // Validate first admin fields (required on create)
            $adminFirst = trim($this->post('admin_first_name', ''));
            $adminLast  = trim($this->post('admin_last_name', ''));
            $adminEmail = trim($this->post('admin_email', ''));

            if (empty($adminFirst)) $errors['admin_first_name'] = 'First name required.';
            if (empty($adminLast))  $errors['admin_last_name']  = 'Last name required.';
            if (empty($adminEmail)) {
                $errors['admin_email'] = 'Admin email required.';
            } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors['admin_email'] = 'Invalid email address.';
            } elseif ($this->userModel->findByEmail($adminEmail)) {
                $errors['admin_email'] = 'This email is already registered.';
            }

            if (empty($errors)) {
                if ($this->model->tpinExists($fields['tpin'])) {
                    $errors['tpin'] = 'A business with this TPIN already exists.';
                } else {
                    $fields['created_by'] = Auth::id();
                    $id = $this->model->insert($fields);

                    // Auto-create VSDC config row if URL provided
                    $vsdcUrl = trim($this->post('vsdc_url', ''));
                    if ($vsdcUrl) {
                        $this->model->upsertVsdc($id, [
                            'label'           => $fields['name'] . ' VSDC',
                            'vsdc_url'        => $vsdcUrl,
                            'device_serial'   => $this->post('device_serial', ''),
                            'tax_office_name' => $this->post('tax_office_name', ''),
                            'mrc_no'          => $this->post('mrc_no', ''),
                            'is_active'       => 1,
                        ]);
                    }

                    // Auto-create business admin user
                    $tempPw = $this->generateTempPassword();
                    $db     = Database::getInstance();
                    $roleId = $db->query("SELECT id FROM roles WHERE name = 'business_admin' LIMIT 1")->fetchColumn();

                    $this->userModel->insert([
                        'role_id'              => (int)$roleId,
                        'business_id'          => $id,
                        'first_name'           => $adminFirst,
                        'last_name'            => $adminLast,
                        'email'                => $adminEmail,
                        'password_hash'        => $this->userModel->hashPassword($tempPw),
                        'must_change_password' => 1,
                        'created_by'           => Auth::id(),
                        'is_active'            => 1,
                    ]);

                    AuditLog::record(Auth::id(), 'CREATE', 'businesses', $id,
                        'Business registered: ' . $fields['name'] . ' — admin account created for ' . $adminEmail);

                    Flash::success(
                        'Business "' . $fields['name'] . '" registered. ' .
                        'Admin login: <strong>' . htmlspecialchars($adminEmail) . '</strong> ' .
                        '/ <strong>' . htmlspecialchars($tempPw) . '</strong> ' .
                        '(user must change password on first login)'
                    );
                    $this->redirect('businesses');
                }
            }
        }

        $this->view('businesses.form', [
            'pageTitle'  => 'Register Business',
            'activeMenu' => 'businesses',
            'business'   => null,
            'errors'     => $errors,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $business = $this->model->findWithVsdc((int)$id);
        if (!$business) $this->abort(404, 'Business not found.');

        $errors = [];

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validate();

            if (empty($errors)) {
                if ($this->model->tpinExists($fields['tpin'], (int)$id)) {
                    $errors['tpin'] = 'Another business already uses this TPIN.';
                } else {
                    $old = $business;
                    $this->model->update((int)$id, $fields);

                    $vsdcUrl = trim($this->post('vsdc_url', ''));
                    if ($vsdcUrl) {
                        $this->model->upsertVsdc((int)$id, [
                            'label'           => $fields['name'] . ' VSDC',
                            'vsdc_url'        => $vsdcUrl,
                            'device_serial'   => $this->post('device_serial', ''),
                            'tax_office_name' => $this->post('tax_office_name', ''),
                            'mrc_no'          => $this->post('mrc_no', ''),
                            'is_active'       => (int)$this->post('vsdc_active', 1),
                        ]);
                    }

                    AuditLog::record(Auth::id(), 'UPDATE', 'businesses', (int)$id, 'Business updated', $old, $fields);
                    Flash::success('Business updated.');
                    $this->redirect('businesses');
                }
            }
            $business = array_merge($business, $fields);
        }

        $this->view('businesses.form', [
            'pageTitle'  => 'Edit Business',
            'activeMenu' => 'businesses',
            'business'   => $business,
            'errors'     => $errors,
        ]);
    }

    public function show(string $id): void
    {
        Auth::requireLogin();

        // Business users can only view their own business
        if (Auth::isBusiness() && Auth::businessId() !== (int)$id) {
            $this->abort(403);
        }
        if (!Auth::isSamPayAdmin() && !Auth::isBusinessAdmin()) {
            $this->abort(403);
        }

        $business = $this->model->findWithVsdc((int)$id);
        if (!$business) $this->abort(404);

        $db         = Database::getInstance();
        $items      = $db->query("SELECT * FROM items WHERE business_id = ? AND is_active = 1 ORDER BY item_name LIMIT 10", [(int)$id])->fetchAll();
        $salesCount = $db->query("SELECT COUNT(*) FROM sales WHERE business_id = ?", [(int)$id])->fetchColumn();
        $apiKeys    = $db->query("SELECT * FROM api_keys WHERE business_id = ? ORDER BY created_at DESC", [(int)$id])->fetchAll();

        $this->view('businesses.show', [
            'pageTitle'  => $business['name'],
            'activeMenu' => Auth::isBusinessAdmin() ? 'my-business' : 'businesses',
            'business'   => $business,
            'items'      => $items,
            'salesCount' => $salesCount,
            'apiKeys'    => $apiKeys,
        ]);
    }

    public function delete(string $id): void
    {
        Auth::requireAdmin();
        $business = $this->model->findById((int)$id);
        if (!$business) $this->abort(404);

        $this->model->update((int)$id, ['is_active' => 0]);
        AuditLog::record(Auth::id(), 'DELETE', 'businesses', (int)$id, 'Business deactivated: ' . $business['name']);
        Flash::success('Business deactivated.');
        $this->redirect('businesses');
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function validate(): array
    {
        $fields = [
            'name'               => trim($this->post('name', '')),
            'tpin'               => trim($this->post('tpin', '')),
            'branch_code'        => trim($this->post('branch_code', '')) ?: null,
            'sampay_business_id' => trim($this->post('sampay_business_id', '')) ?: null,
            'address'            => trim($this->post('address', '')),
            'city'               => trim($this->post('city', '')),
            'phone'              => trim($this->post('phone', '')),
            'email'              => trim($this->post('email', '')),
            'currency_code'      => $this->post('currency_code', 'ZMW'),
            'is_active'          => (int)$this->post('is_active', 1),
        ];
        $errors = [];
        if (empty($fields['name'])) $errors['name'] = 'Business name is required.';
        if (empty($fields['tpin'])) $errors['tpin'] = 'TPIN is required.';
        if ($fields['email'] && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        }
        return compact('fields', 'errors');
    }

    private function generateTempPassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
        $pw    = '';
        for ($i = 0; $i < 10; $i++) {
            $pw .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pw;
    }
}
