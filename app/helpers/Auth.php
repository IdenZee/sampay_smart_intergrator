<?php

class Auth
{
    // ── Login / Logout ────────────────────────────────────────────────────

    public static function attempt(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_name']   = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['role']        = $user['role_name'];
        $_SESSION['business_id'] = $user['business_id']; // NULL for SamPay admin
        $_SESSION['logged_in']   = true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']);
    }

    // ── Current user ──────────────────────────────────────────────────────

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Returns the business_id of the logged-in user, or NULL for SamPay admins.
     */
    public static function businessId(): ?int
    {
        $bid = $_SESSION['business_id'] ?? null;
        return ($bid !== null && $bid !== '') ? (int)$bid : null;
    }

    public static function user(): array
    {
        return [
            'id'          => $_SESSION['user_id']     ?? null,
            'name'        => $_SESSION['user_name']   ?? '',
            'email'       => $_SESSION['user_email']  ?? '',
            'role'        => $_SESSION['role']        ?? '',
            'business_id' => self::businessId(),
        ];
    }

    public static function role(): string
    {
        return $_SESSION['role'] ?? '';
    }

    // ── Role checks ───────────────────────────────────────────────────────

    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    public static function isAny(array $roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    /** SamPay platform admin — full access to everything. */
    public static function isAdmin(): bool        { return self::is('admin'); }
    public static function isSamPayAdmin(): bool  { return self::is('admin'); }

    /** Business administrator — manages their own business data. */
    public static function isBusinessAdmin(): bool { return self::is('business_admin'); }

    /** Business user — view-only access to own business items and sales. */
    public static function isBusinessUser(): bool  { return self::is('business_user'); }

    /** True for any user tied to a specific business (business_admin or business_user). */
    public static function isBusiness(): bool      { return self::businessId() !== null; }

    // ── Guards ────────────────────────────────────────────────────────────

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login');
            exit;
        }
    }

    /**
     * Allow access only if the current user has one of the given roles.
     * Roles: 'admin', 'business_admin', 'business_user'
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!self::isAny($roles)) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /** Restrict to SamPay admin only. */
    public static function requireAdmin(): void
    {
        self::requireRole(['admin']);
    }

    /**
     * Restrict to SamPay admin OR business admin.
     * Use for pages where both can manage (e.g. Users, Items).
     */
    public static function requireManager(): void
    {
        self::requireRole(['admin', 'business_admin']);
    }
}
