<?php
class Migration_20170131131619_AddClickCreativeExternalIdsToConversion extends Migration
{
    const DESCRIPTION_RU = 'Нормализация conversions_2';

    const DESCRIPTION_EN = 'Normalize conversions_2';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions_2 
            ADD COLUMN click_id int(10) unsigned DEFAULT NULL AFTER sub_id,
            ADD COLUMN creative_id int(10) unsigned DEFAULT NULL,
            ADD COLUMN external_id int(10) unsigned DEFAULT NULL
        ";
        self::execute($sql);
    }
}