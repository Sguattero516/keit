<?php
class Migration_20161222132145_AddOperatorIdIspIdToVisitors extends Migration 
{
    const DESCRIPTION_RU = 'Добавление operator_id и isp_id к visitors';

    const DESCRIPTION_EN = 'Add operator_id and isp_id to visitors';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}visitors 
          ADD COLUMN screen_id int unsigned DEFAULT NULL,
          ADD COLUMN language_id int unsigned NOT NULL,
          ADD COLUMN browser_id int unsigned NOT NULL,
          ADD COLUMN browser_version_id int unsigned NOT NULL,
          ADD COLUMN os_id int unsigned NOT NULL,
          ADD COLUMN os_version_id int unsigned NOT NULL,
          ADD COLUMN connection_type_id int unsigned NOT NULL,
          ADD COLUMN operator_id int unsigned NOT NULL,
          ADD COLUMN isp_id int unsigned DEFAULT NULL,
          ADD INDEX browser_version_id (browser_id),
          ADD INDEX os_version_id (browser_id),
          ADD INDEX operator_id (operator_id),
          ADD INDEX isp_id (isp_id)";
        self::execute($sql);
    }
}