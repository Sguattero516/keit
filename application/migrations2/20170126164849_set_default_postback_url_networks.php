<?php
class Migration_20170126164849_SetDefaultPostbackUrlNetworks extends Migration 
{
    const DESCRIPTION_RU = 'Установка default null для affiliate_networks.postback_url';

    const DESCRIPTION_EN = 'Set default null for affiliate_networks.postback_url';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}affiliate_networks CHANGE COLUMN postback_url postback_url text DEFAULT NULL";
        self::execute($sql);
    }
}