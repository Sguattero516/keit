<?php
class Migration_20170418191031_MultipleAuthHash extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы user_password_hashes';

    const DESCRIPTION_EN = 'Create table user_password_hashes';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}user_password_hashes (
           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
           `user_id` int(10) unsigned NOT NULL,
           `password_hash` VARCHAR(255) NULL DEFAULT NULL,
           `expires_at` DATETIME NOT NULL,
           PRIMARY KEY (id),
           KEY user_id (user_id)
        ) ENGINE=InnoDB";
        self::execute($sql);

        $sql = "INSERT INTO {$prefix}user_password_hashes(`user_id`, `password_hash`, `expires_at`) 
            SELECT id, password_hash, NOW() + INTERVAL 1 WEEK
            FROM {$prefix}users
            WHERE password_hash IS NOT NULL";
        self::execute($sql);
    }
}