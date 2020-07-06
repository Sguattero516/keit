<?php
class Migration_20161223130012_UpdateConversions2 extends Migration 
{
    const DESCRIPTION_RU = 'Синхронизация колонок в conversions_2';

    const DESCRIPTION_EN = 'Sync columns in conversions_2';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions_2
        	DROP COLUMN ip_id,
        	DROP COLUMN country_id,
        	DROP COLUMN region_id,
        	DROP COLUMN city_id,
        	DROP COLUMN user_agent_id,
        	DROP COLUMN `language`,
        	DROP COLUMN os_id,
        	DROP COLUMN os_version_id,
        	DROP COLUMN connection_type,
        	DROP COLUMN browser_id,
        	DROP COLUMN browser_version_id,
        	DROP COLUMN device_type,
        	DROP COLUMN device_model_id,
        	DROP COLUMN isp_id,
        	DROP COLUMN operator_id
        ";
        self::execute($sql);
    }
}