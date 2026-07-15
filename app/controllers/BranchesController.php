<?php

class BranchesController extends Controller
{
    private Branch $model;

    public function __construct()
    {
        $this->model = new Branch();
    }

    public function index(): void
    {
        Auth::requireRole(['admin', 'director', 'manager']);
        $branches = $this->model->all('name');
        $this->view('branches.index', [
            'pageTitle'  => 'Branches',
            'activeMenu' => 'branches',
            'branches'   => $branches,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $errors = [];

        if ($this->isPost()) {
            $data = $this->validateForm();
            $errors = $data['errors'];

            if (empty($errors)) {
                if ($this->model->codeExists($data['fields']['code'])) {
                    $errors['code'] = 'Branch code already exists.';
                } else {
                    $id = $this->model->insert($data['fields']);
                    AuditLog::record(Auth::id(), 'CREATE', 'branches', $id, 'Branch created: ' . $data['fields']['name']);
                    Flash::success('Branch created successfully.');
                    $this->redirect('branches');
                }
            }
        }

        $this->view('branches.form', [
            'pageTitle'  => 'New Branch',
            'activeMenu' => 'branches',
            'branch'     => null,
            'errors'     => $errors,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['admin']);
        $branch = $this->model->findById((int)$id);
        if (!$branch) $this->abort(404, 'Branch not found.');

        $errors = [];

        if ($this->isPost()) {
            $data = $this->validateForm();
            $errors = $data['errors'];

            if (empty($errors)) {
                if ($this->model->codeExists($data['fields']['code'], (int)$id)) {
                    $errors['code'] = 'Branch code already in use.';
                } else {
                    $old = $branch;
                    $this->model->update((int)$id, $data['fields']);
                    AuditLog::record(Auth::id(), 'UPDATE', 'branches', (int)$id, 'Branch updated', $old, $data['fields']);
                    Flash::success('Branch updated.');
                    $this->redirect('branches');
                }
            }
            $branch = array_merge($branch, $data['fields']);
        }

        $this->view('branches.form', [
            'pageTitle'  => 'Edit Branch',
            'activeMenu' => 'branches',
            'branch'     => $branch,
            'errors'     => $errors,
        ]);
    }

    public function delete(string $id): void
    {
        Auth::requireRole(['admin']);
        $branch = $this->model->findById((int)$id);
        if (!$branch) $this->abort(404);

        // Soft-delete: set inactive
        $this->model->update((int)$id, ['is_active' => 0]);
        AuditLog::record(Auth::id(), 'DELETE', 'branches', (int)$id, 'Branch deactivated: ' . $branch['name']);
        Flash::success('Branch deactivated.');
        $this->redirect('branches');
    }

    private function validateForm(): array
    {
        $fields = [
            'name'      => $this->post('name', ''),
            'code'      => strtoupper($this->post('code', '')),
            'address'   => $this->post('address', ''),
            'city'      => $this->post('city', ''),
            'phone'     => $this->post('phone', ''),
            'is_hq'     => $this->post('is_hq') ? 1 : 0,
            'is_active' => $this->post('is_active') ? 1 : 0,
        ];
        $errors = [];
        if (empty($fields['name'])) $errors['name'] = 'Branch name is required.';
        if (empty($fields['code'])) $errors['code'] = 'Branch code is required.';
        return compact('fields', 'errors');
    }
}
