<?php
class Migration_20161212104538_ExtractPreferences extends Migration 
{
    const DESCRIPTION_RU = 'Восстановление настроек пользователей';

    const DESCRIPTION_EN = 'Restore user preferences';

    public static function up()
    {
        $preferences = self::getDefaultPreferences();
        foreach (self::getUsers() as $user) {
            if (!self::preferencesExist($user['id'])) {
                self::createPreferences($user['id'], $preferences);
            }
        }
    }

    private static function getDefaultPreferences()
    {
        $prefix = self::getPrefix();
        $keys = ['language'];
        $preferences = [];
        foreach ($keys as $key) {
            $sql = "SELECT value FROM {$prefix}settings WHERE `key` = '{$key}'";
            $preferences[$key] = self::getDb()->getOne($sql);
        }
        $preferences['timezone'] = 'Europe/Moscow';
        return $preferences;
    }

    private static function createPreferences($userId, $preferences)
    {
        $prefix = self::getPrefix();
        foreach ($preferences as $name => $value) {
            $sql = "INSERT INTO {$prefix}user_preferences (user_id, pref_name, pref_value) VALUES 
                    ('{$userId}', '{$name}', '$value')";
            self::execute($sql);
        }
    }

    private static function preferencesExist($userId)
    {
        $prefix = self::getPrefix();
        $sql = "SELECT COUNT(*) FROM {$prefix}user_preferences WHERE user_id = {$userId}";
        return self::getDb()->getOne($sql);
    }

    private static function getUsers()
    {
        $prefix = self::getPrefix();
        $sql = "SELECT * FROM {$prefix}users";
        return self::execute($sql);
    }
}