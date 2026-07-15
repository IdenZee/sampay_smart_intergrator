<?php

class Setting extends Model
{
    protected string $table = 'settings';

    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row  = $stmt->fetch();
        $val  = $row ? $row['setting_value'] : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, mixed $value, ?int $updatedBy = null): void
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?"
        );
        $stmt->execute([$value, $updatedBy, $key]);
        self::$cache[$key] = $value;
    }

    public function byGroup(string $group): array
    {
        return $this->query(
            "SELECT * FROM settings WHERE setting_group = ? ORDER BY display_name",
            [$group]
        );
    }

    public function allGrouped(): array
    {
        $rows   = $this->query("SELECT * FROM settings ORDER BY setting_group, display_name");
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row['setting_group']][] = $row;
        }
        return $groups;
    }

    public function saveGroup(array $postData, string $group, int $userId): void
    {
        $rows = $this->byGroup($group);
        foreach ($rows as $setting) {
            $key = $setting['setting_key'];
            if (array_key_exists($key, $postData)) {
                self::set($key, $postData[$key], $userId);
            }
        }
    }
}
