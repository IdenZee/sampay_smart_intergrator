<?php

class ProfileController extends Controller
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function edit(): void
    {
        Auth::requireLogin();

        $user   = $this->model->findWithRole(Auth::id());
        $errors = [];

        if ($this->isPost()) {
            $action = $this->post('action', 'profile');

            if ($action === 'password') {
                // ── Change password ────────────────────────────────────
                $current  = $this->post('current_password', '');
                $new      = $this->post('new_password', '');
                $confirm  = $this->post('password_confirm', '');

                if (!$this->model->verifyPassword($current, $user['password_hash'])) {
                    $errors['current_password'] = 'Current password is incorrect.';
                } elseif (strlen($new) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                } elseif ($new !== $confirm) {
                    $errors['password_confirm'] = 'Passwords do not match.';
                } else {
                    $this->model->update(Auth::id(), [
                        'password_hash'        => $this->model->hashPassword($new),
                        'must_change_password' => 0,
                    ]);
                    AuditLog::record(Auth::id(), 'UPDATE', 'users', Auth::id(), 'Password changed');
                    Flash::success('Password updated successfully.');
                    $this->redirect('dashboard');
                }

            } else {
                // ── Update profile details ─────────────────────────────
                $data = [
                    'first_name' => $this->post('first_name', ''),
                    'last_name'  => $this->post('last_name', ''),
                    'phone'      => $this->post('phone', ''),
                ];
                if (empty($data['first_name'])) $errors['first_name'] = 'First name required.';
                if (empty($data['last_name']))  $errors['last_name']  = 'Last name required.';

                if (empty($errors)) {
                    $this->model->update(Auth::id(), $data);
                    // Refresh session name
                    $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
                    AuditLog::record(Auth::id(), 'UPDATE', 'users', Auth::id(), 'Profile updated');
                    Flash::success('Profile updated.');
                    $this->redirect('profile');
                }
                $user = array_merge($user, $data);
            }
        }

        $mustChange = (bool)($user['must_change_password'] ?? false);

        $this->view('profile.edit', [
            'pageTitle'  => 'My Profile',
            'activeMenu' => '',
            'user'       => $user,
            'errors'     => $errors,
            'mustChange' => $mustChange,
        ]);
    }
}
