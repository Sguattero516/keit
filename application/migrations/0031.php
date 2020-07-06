<?php


class Migration_31 extends Migration {

    const DESCRIPTION_RU = 'Добавление device_model_filter, device_model_filter_value, device_type_filter, device_type_filter_value в streams';

    const DESCRIPTION_EN = 'Add device_model_filter, device_model_filter_value, device_type_filter, device_type_filter_value to streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE `{$prefix}streams` ADD `device_type_filter` ENUM( '', 'allow', 'block' ) NULL DEFAULT NULL ,
            ADD `device_type_filter_value` VARCHAR( 250 ) NULL DEFAULT NULL ,
            ADD `device_model_filter` ENUM( '', 'allow', 'block' ) NULL DEFAULT NULL ,
            ADD `device_model_filter_value` TEXT NULL DEFAULT NULL ";
        self::silentExecute($sql);
    }
}