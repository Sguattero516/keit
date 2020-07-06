<?php
class Migration_20170125162047_MakeTokenDefaultNull extends Migration 
{
    const DESCRIPTION_RU = 'Установка default null в campaign token';

    const DESCRIPTION_EN = 'Set default null to campaign token';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns CHANGE COLUMN token token varchar(50) default null";
        self::execute($sql);
    }
}