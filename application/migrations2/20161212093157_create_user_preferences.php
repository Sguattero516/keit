<?php
class Migration_20161212093157_CreateUserPreferences extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы user_preferences';

    const DESCRIPTION_EN = 'Create table user_preferences';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}user_preferences (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(10) unsigned NOT NULL,
            `pref_name` varchar(50) NOT NULL,
            `pref_value` text DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id_pref_name` (`user_id`, `pref_name`) 
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        self::execute($sql);
    }
}