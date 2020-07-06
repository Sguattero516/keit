<?php
class Migration_20181227121843_AddCapFieldsToOffers extends Migration 
{
    const DESCRIPTION_RU = 'Добавление cap-полей в offers';

    const DESCRIPTION_EN = 'Add cap-fields to offers';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}offers
            ADD COLUMN conversion_cap_enabled int(1) unsigned DEFAULT 0,
            ADD COLUMN daily_cap int unsigned DEFAULT 0,
            ADD COLUMN conversion_timezone varchar(50) default 'UTC',
            ADD COLUMN alternative_offer_id int DEFAULT NULL          
        ";
        self::silentExecute($sql);
    }
}