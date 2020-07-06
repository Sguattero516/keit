<?php
class Migration_20170413104235_AllowEmptyIp extends Migration 
{
    const DESCRIPTION_RU = 'Поддержка ip 0 (ipv6)';

    const DESCRIPTION_EN = 'Allow storing ip 0 (ipv6)';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}ref_ips CHANGE COLUMN `value` `value` int(10) unsigned default 0";
        self::execute($sql);
    }
}