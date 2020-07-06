<?php
class Migration_20161220122451_MigrateVisitors extends Migration 
{
    const DESCRIPTION_RU = 'Миграция старых записей events';

    const DESCRIPTION_EN = 'Migrate old events';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "SELECT COUNT(*) FROM {$prefix}visitors";
        if (!self::getDb()->getOne($sql)) {
            $sql = "INSERT INTO {$prefix}visitors (code, ip_id, user_agent_id, country_id, region_id, city_id, device_type_id, device_model_id)
              VALUES ('old', 1, 1, 1, 1, 1, 1, 1)";
            self::execute($sql);

            $sql = "UPDATE {$prefix}events SET visitor_id = 1 WHERE visitor_id = 0";
            self::execute($sql);
        }
    }
}