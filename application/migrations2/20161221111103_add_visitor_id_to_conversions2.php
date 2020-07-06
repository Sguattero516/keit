<?php
class Migration_20161221111103_AddVisitorIdToConversions2 extends Migration 
{
    const DESCRIPTION_RU = 'Добавление visitor_id в conversions_2';

    const DESCRIPTION_EN = 'Add visitor_id to conversions_2';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions_2 ADD COLUMN visitor_id bigint unsigned DEFAULT NULL AFTER conversion_id";
        self::getDb()->execute($sql);
    }
}