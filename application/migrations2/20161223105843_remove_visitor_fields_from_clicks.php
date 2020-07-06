<?php
class Migration_20161223105843_RemoveVisitorFieldsFromClicks extends Migration 
{
    const DESCRIPTION_RU = 'Удаление полей относящихся к visitors из таблицы clicks';

    const DESCRIPTION_EN = 'Remove visitors fields from clicks table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
          DROP COLUMN ip_id, 
          DROP COLUMN user_agent_id, 
          DROP COLUMN country_id, 
          DROP COLUMN region_id, 
          DROP COLUMN city_id, 
          DROP COLUMN device_type, 
          DROP COLUMN device_model_id, 
          DROP COLUMN screen_id, 
          DROP COLUMN `language`, 
          DROP COLUMN browser_id, 
          DROP COLUMN browser_version_id, 
          DROP COLUMN os_id, 
          DROP COLUMN os_version_id, 
          DROP COLUMN connection_type, 
          DROP COLUMN operator_id, 
          DROP COLUMN isp_id,
          DROP COLUMN destination 
        ";
        self::execute($sql);
    }
}