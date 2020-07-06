<?php
class Migration_20181112150624_AddUniquenessUseCookies extends Migration 
{
    const DESCRIPTION_RU = 'Добавление campaigns.uniqueness_use_cookies';

    const DESCRIPTION_EN = 'Add campaigns.uniqueness_use_cookies';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns ADD COLUMN uniqueness_use_cookies INT(1) unsigned DEFAULT 1";
        self::execute($sql);
    }
}