<?php
class Migration_20170403155739_AddSaleDatetimeToConversions extends Migration
{
    const DESCRIPTION_RU = 'Добавление sale_datetime в conversions_2';

    const DESCRIPTION_EN = 'Add sale_datetime to conversions_2';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions_2 ADD sale_datetime datetime default null";
        self::execute($sql);
    }
}