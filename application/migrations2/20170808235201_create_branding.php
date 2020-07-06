<?php
class Migration_20170808235201_CreateBranding extends Migration 
{
    const DESCRIPTION_RU = 'Создание таблицы branding';

    const DESCRIPTION_EN = 'Create table branding';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}branding ( 
            id int(20) unsigned NOT NULL AUTO_INCREMENT,
            logo LONGBLOB NULL DEFAULT NULL,
            favicon LONGBLOB NULL DEFAULT NULL,
            PRIMARY KEY (id)
        )";
        self::execute($sql);
    }
}