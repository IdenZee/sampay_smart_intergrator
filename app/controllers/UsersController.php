<?php

class UsersController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function index(): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $users = Auth::isBusiness()
            ? $this->model->allForBusiness(Auth::businessId())
            : $this->model->allWithRole();

        $this->view('users.index', [
            'pageTitle'  => 'Users',
            'activeMenu' => 'users',
            'users'      => $users,
        ]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $roles  = $this->getRolesForCurrentUser();
        $errors = [];

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validateForm();

            // Business admin always creates users for their own business
            if (Auth::isBusinessAdmin()) {
                $fields['business_id'] = Auth::businessId();
                // Force role to business_user — they can't create admins
                $db = Database::getInstance();
                $fields['role_id'] = (int)$db->query("SELECT id FROM roles WHERE name = 'business_user' LIMIT 1")->fetchColumn();
            }

            if (empty($errors)) {
                if ($this->model->findByEmail($fields['email'])) {
                    $errors['email'] = 'Email already registered.';
                } else {
                    $fields['password_hash']        = $this->model->hashPassword($this->post('password', ''));
                    $fields['created_by']           = Auth::id();
                    $fields['must_change_password'] = 1;

                    $id = $this->model->insert($fields);
                    AuditLog::record(Auth::id(), 'CREATE', 'users', $id,
                        'User created: ' . $fields['email'], null, null, $fields['business_id'] ?? null);
                    Flash::success('User created. They must change their password on first login.');
                    $this->redirect('users');
                }
            }
        }

        $this->view('users.form', [
            'pageTitle'  => 'New User',
            'activeMenu' => 'users',
            'user'       => null,
            'roles'      => $roles,
            'errors'     => $errors,
        ]);
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $user = $this->model->findWithRole((int)$id);
        if (!$user) $this->abort(404, 'User not found.');

        // Business admin can only edit users in their own business
        if (Auth::isBusinessAdmin() && (int)($user['business_id'] ?? 0) !== Auth::businessId()) {
            $this->abort(403);
        }

        $roles  = $this->getRolesForCurrentUser();
        $errors = [];

        if ($this->isPost()) {
            ['fields' => $fields, 'errors' => $errors] = $this->validateForm(false);

            if (Auth::isBusinessAdmin()) {
                $fields['business_id'] = Auth::businessId();
            }

            if (empty($errors)) {
                $old = $user;
                $this->model->update((int)$id, $fields);
                AuditLog::record(Auth::id(), 'UPDATE', 'users', (int)$id, 'User updated', $old, $fields,
                    $fields['business_id'] ?? null);
                Flash::success('User updated.');
                $this->redirect('users');
            }
            $user = array_merge($user, $fields);
        }

        $this->view('users.form', [
            'pageTitle'  => 'Edit User',
            'activeMenu' => 'users',
            'user'       => $user,
            'roles'      => $roles,
            'errors'     => $errors,
        ]);
    }

    public function delete(string $id): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $user = $this->model->findById((int)$id);
        if (!$user) $this->abort(404);

        // Business admin can only delete users in their own business
        if (Auth::isBusinessAdmin() && (int)($user['business_id'] ?? 0) !== Auth::businessId()) {
            $this->abort(403);
        }

        if ((int)$id === Auth::id()) {
            Flash::error('You cannot deactivate your own account.');
            $this->redirect('users');
            return;
        }

        $this->model->update((int)$id, ['is_active' => 0]);
        AuditLog::record(Auth::id(), 'DELETE', 'users', (int)$id, 'User deactivated',
            null, null, $user['business_id'] ?? null);
        Flash::success('User deactivated.');
        $this->redirect('users');
    }

    public function resetPassword(string $id): void
    {
        Auth::requireRole(['admin', 'business_admin']);

        $user = $this->model->findById((int)$id);
        if (!$user) $this->abort(404);

        if (Auth::isBusinessAdmin() && (int)($user['business_id'] ?? 0) !== Auth::businessId()) {
            $this->abort(403);
        }

        if ($this->isPost()) {
            $pw = $this->post('password', '');
            if (strlen($pw) < 8) {
                Flash::error('Password must be at least 8 characters.');
            } else {
                $this->model->update((int)$id, [
                    'password_hash'        => $this->model->hashPassword($pw),
                    'must_change_password' => 1,
                ]);
                AuditLog::record(Auth::id(), 'UPDATE', 'users', (int)$id, 'Password reset by admin',
                    null, null, $user['business_id'] ?? null);
                Flash::success('Password reset. User must change it on next login.');
                $this->redirect('users');
            }
        }

        $this->view('users.reset-password', [
            'pageTitle'  => 'Reset Password',
            'activeMenu' => 'users',
            'user'       => $user,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function validateForm(bool $requirePassword = true): array
    {
        $fields = [
            'first_name'  => trim($this->post('first_name', '')),
            'last_name'   => trim($this->post('last_name', '')),
            'email'       => trim($this->post('email', '')),
            'phone'       => trim($this->post('phone', '')),
            'employee_id' => trim($this->post('employee_id', '')),
            'role_id'     => (int)$this->post('role_id', 0),
            'is_active'   => $this->post('is_active') ? 1 : 0,
        ];
        $errors = [];
        if (empty($fields['first_name'])) $errors['first_name'] = 'First name required.';
        if (empty($fields['last_name']))  $errors['last_name']  = 'Last name required.';
        if (empty($fields['email']))      $errors['email']      = 'Email required.';
        if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
        // role_id only validated for SamPay admin (business admin has it forced in create())
        if (Auth::isSamPayAdmin() && !$fields['role_id']) $errors['role_id'] = 'Role required.';

        if ($requirePassword) {
            $pw = $this->post('password', '');
            if (strlen($pw) < 8) $errors['password'] = 'Password must be at least 8 characters.';
        }
        return compact('fields', 'errors');
    }

    /**
     * SamPay admin gets all roles.
     * Business admin only sees business_user (they create staff, not admins).
     */
    private function getRolesForCurrentUser(): array
    {
        $db = Database::getInstance();
        if (Auth::isBusinessAdmin()) {
            return $db->query("SELECT * FROM roles WHERE name = 'business_user'")->fetchAll();
        }
        return $db->query("SELECT * FROM roles ORDER BY id")->fetchAll();
    }
}
