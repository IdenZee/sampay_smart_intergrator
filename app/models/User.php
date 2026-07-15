<?php

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?",
            [$email]
        );
    }

    public function findWithRole(int $id): ?array
    {
        return $this->queryOne(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ?",
            [$id]
        );
    }

    /** All users — SamPay admin view. */
    public function allWithRole(): array
    {
        return $this->query(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_label,
                    b.name AS business_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN businesses b ON b.id = u.business_id
             ORDER BY u.first_name"
        );
    }

    /** All users belonging to a specific business. */
    public function allForBusiness(int $businessId): array
    {
        return $this->query(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_label
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.business_id = ?
             ORDER BY u.first_name",
            [$businessId]
        );
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    public function recordLogin(int $id): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->execute(
            "UPDATE users SET last_login = NOW(), last_login_ip = ? WHERE id = ?",
            [$ip, $id]
        );
    }

    public function createResetToken(int $userId): string
    {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $this->execute("UPDATE password_resets SET used = 1 WHERE user_id = ?", [$userId]);
        $this->execute(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$userId, $token, $expires]
        );
        return $token;
    }

    public function findResetToken(string $token): ?array
    {
        return $this->queryOne(
            "SELECT pr.*, u.email, u.first_name
             FROM password_resets pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
             LIMIT 1",
            [$token]
        );
    }

    public function consumeResetToken(string $token, string $newPassword): bool
    {
        $reset = $this->findResetToken($token);
        if (!$reset) return false;

        $this->execute(
            "UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?",
            [$this->hashPassword($newPassword), $reset['user_id']]
        );
        $this->execute("UPDATE password_resets SET used = 1 WHERE token = ?", [$token]);
        return true;
    }
}
