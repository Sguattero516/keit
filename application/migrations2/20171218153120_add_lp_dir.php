<?php
class Migration_20171218153120_AddLpDir extends Migration 
{
    const DESCRIPTION_RU = 'Добавление lp_dir';

    const DESCRIPTION_EN = 'Add lp_dir';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "INSERT IGNORE INTO {$prefix}settings (`key`, `value`) values ('lp_dir', 'landers')";
        self::execute($sql);
    }
}