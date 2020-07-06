<?php
class Migration_20161115111649_AddBcryptPassword extends Migration 
{
    const DESCRIPTION_RU = 'Добавление колонки password_hash';

    const DESCRIPTION_EN = 'Add column password_hash';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}users ADD COLUMN `password_hash` varchar(255) AFTER `password`";
        self::execute($sql);
    }
}