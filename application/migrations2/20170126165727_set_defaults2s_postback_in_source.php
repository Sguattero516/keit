<?php
class Migration_20170126165727_SetDefaults2sPostbackInSource extends Migration 
{
    const DESCRIPTION_RU = 'Замена traffic_sources.postback_url на postback_url';

    const DESCRIPTION_EN = 'Change traffic_sources.postback_url to postback_url';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}traffic_source TO {$prefix}traffic_sources";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}traffic_sources CHANGE COLUMN s2s_postback postback_url text DEFAULT NULL";
        self::execute($sql);
    }
}