<?php
class Migration_20170514140937_AddTrafficSourcePostbackType extends Migration 
{
    const DESCRIPTION_RU = 'Добавление postback_statuses к traffic_sources';

    const DESCRIPTION_EN = 'Add postback_statuses to traffic_sources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}traffic_sources 
                ADD postback_statuses varchar(255) DEFAULT '[\"sell\",\"lead\",\"rejected\"]' AFTER postback_url";
        self::execute($sql);
    }
}