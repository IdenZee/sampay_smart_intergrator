<?php

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ── Login ──────────────────────────────────────────────────────────────

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $error = null;

        if ($this->isPost()) {
            $email    = $this->post('email', '');
            $password = $this->post('password', '');

            if (empty($email) || empty($password)) {
                $error = 'Email and password are required.';
            } else {
                $user = $this->userModel->findByEmail($email);

                if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
                    $error = 'Invalid email or password.';
                    AuditLog::record(null, 'LOGIN_FAILED', 'auth', null, "Failed login for: {$email}");
                } elseif (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Contact your administrator.';
                } else {
                    $this->userModel->recordLogin($user['id']);
                    Auth::attempt($user);
                    AuditLog::record($user['id'], 'LOGIN', 'auth', null, 'Successful login');

                    if ($user['must_change_password']) {
                        Flash::warning('Please change your password before continuing.');
                        $this->redirect('profile');
                    }

                    $this->redirect('dashboard');
                }
            }
        }

        $this->view('auth.login', compact('error'), 'auth');
    }

    // ── Logout ────────────────────────────────────────────────────────────

    public function logout(): void
    {
        AuditLog::record(Auth::id(), 'LOGOUT', 'auth');
        Auth::logout();
        Flash::info('You have been logged out.');
        $this->redirect('login');
    }

    // ── Forgot Password ───────────────────────────────────────────────────

    public function forgotPassword(): void
    {
        if (Auth::check()) $this->redirect('dashboard');

        $sent  = false;
        $error = null;

        if ($this->isPost()) {
            $email = $this->post('email', '');
            $user  = $this->userModel->findByEmail($email);

            // Always show success to prevent email enumeration
            if ($user && $user['is_active']) {
                $token = $this->userModel->createResetToken($user['id']);
                $this->sendResetEmail($user, $token);
            }

            $sent = true;
        }

        $this->view('auth.forgot-password', compact('sent', 'error'), 'auth');
    }

    // ── Reset Password ────────────────────────────────────────────────────

    public function resetPassword(string $token): void
    {
        if (Auth::check()) $this->redirect('dashboard');

        $reset = $this->userModel->findResetToken($token);
        $error = null;

        if (!$reset) {
            $this->view('auth.reset-invalid', [], 'auth');
            return;
        }

        if ($this->isPost()) {
            $password = $this->post('password', '');
            $confirm  = $this->post('password_confirm', '');

            if (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $this->userModel->consumeResetToken($token, $password);
                Flash::success('Password reset successfully. Please log in.');
                $this->redirect('login');
            }
        }

        $this->view('auth.reset-password', compact('token', 'reset', 'error'), 'auth');
    }

    // ── Private: send reset email ─────────────────────────────────────────

    private function sendResetEmail(array $user, string $token): void
    {
        $link    = APP_URL . '/reset-password/' . $token;
        $name    = htmlspecialchars($user['first_name']);
        $appName = APP_NAME;

        $subject = "{$appName} — Password Reset";
        $body    = "Hi {$name},\n\nClick the link below to reset your password:\n\n{$link}\n\nThis link expires in 1 hour.\n\nIf you did not request this, please ignore this email.";

        $headers = 'From: ' . ($_ENV['MAIL_FROM_NAME'] ?? $appName) . ' <' . ($_ENV['MAIL_FROM'] ?? '') . '>';
        @mail($user['email'], $subject, $body, $headers);
    }
}
