<?php
class Migration_20170719123810_OfferAddCountryAttribute extends Migration 
{
    const DESCRIPTION_RU = 'Добавление country к offers';

    const DESCRIPTION_EN = 'Add country to offers';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}offers 
                ADD country varchar(255) NULL DEFAULT NULL";
        self::silentExecute($sql);
    }
}