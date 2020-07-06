<?php
class Migration_20170329080023_IncreaseDeviceTypeValueSize extends Migration 
{
    const DESCRIPTION_RU = 'Увеличение размера ref_device_types.value';

    const DESCRIPTION_EN = 'Increase size ref_device_types.value';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}ref_device_types CHANGE `value` `value` varchar(50)";
        self::execute($sql);
    }
}