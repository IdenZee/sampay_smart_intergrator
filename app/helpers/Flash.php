<?php

class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void { self::set('success', $message); }
    public static function error(string $message): void   { self::set('error',   $message); }
    public static function warning(string $message): void { self::set('warning', $message); }
    public static function info(string $message): void    { self::set('info',    $message); }

    /** Like success() but message is rendered as raw trusted HTML (server-generated only). */
    public static function successHtml(string $html): void
    {
        $_SESSION['flash'] = ['type' => 'success', 'message' => $html, 'raw' => true];
    }

    public static function get(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    public static function has(): bool
    {
        return isset($_SESSION['flash']);
    }

    /**
     * Render the flash message as a Bootstrap alert.
     */
    public static function render(): string
    {
        $flash = self::get();
        if (!$flash) return '';

        $map = [
            'success' => 'alert-success',
            'error'   => 'alert-danger',
            'warning' => 'alert-warning',
            'info'    => 'alert-info',
        ];
        $class = $map[$flash['type']] ?? 'alert-info';
        $msg   = !empty($flash['raw']) ? $flash['message'] : htmlspecialchars($flash['message']);

        return <<<HTML
        <div class="alert {$class} alert-dismissible fade show" role="alert">
            {$msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        HTML;
    }
}
