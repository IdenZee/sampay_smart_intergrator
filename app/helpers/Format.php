<?php

class Format
{
    public static function currency(float $amount, string $currency = CURRENCY): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }

    public static function litres(float $qty): string
    {
        return number_format($qty, 2) . ' L';
    }

    public static function date(string $datetime, string $format = 'd M Y'): string
    {
        return date($format, strtotime($datetime));
    }

    public static function datetime(string $datetime): string
    {
        return date('d M Y, H:i', strtotime($datetime));
    }

    public static function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        return match(true) {
            $diff < 60     => 'just now',
            $diff < 3600   => floor($diff / 60) . ' mins ago',
            $diff < 86400  => floor($diff / 3600) . ' hrs ago',
            $diff < 604800 => floor($diff / 86400) . ' days ago',
            default        => self::date($datetime),
        };
    }

    public static function yesNo(mixed $value): string
    {
        return $value ? 'Yes' : 'No';
    }

    public static function statusBadge(int $active): string
    {
        return $active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    public static function roleBadge(string $role): string
    {
        $map = [
            'admin'     => 'bg-danger',
            'director'  => 'bg-primary',
            'manager'   => 'bg-warning text-dark',
            'attendant' => 'bg-info text-dark',
        ];
        $class = $map[$role] ?? 'bg-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($role) . '</span>';
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
