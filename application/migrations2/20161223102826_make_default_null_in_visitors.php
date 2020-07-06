<?php
class Migration_20161223102826_MakeDefaultNullInVisitors extends Migration 
{
    const DESCRIPTION_RU = 'По умолчанию установка NULL для полей visitors';

    const DESCRIPTION_EN = 'By default set null for visitors fields';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}visitors 
         CHANGE `country_id` `country_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `region_id` `region_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `city_id` `city_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `device_type_id` `device_type_id` INT(10) UNSIGNED NULL,
         CHANGE `device_model_id` `device_model_id` INT(10) UNSIGNED NULL,
         CHANGE `screen_id` `screen_id` INT(10) UNSIGNED NULL DEFAULT NULL,
         CHANGE `language_id` `language_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `browser_id` `browser_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `browser_version_id` `browser_version_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `os_id` `os_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `os_version_id` `os_version_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `connection_type_id` `connection_type_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `operator_id` `operator_id` INT(10) UNSIGNED DEFAULT NULL,
         CHANGE `isp_id` `isp_id` INT(10) UNSIGNED DEFAULT NULL
         ";
        self::execute($sql);
    }
}