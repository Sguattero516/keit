<?php
class Migration_20171204160540_MigrateOfferCountry extends Migration 
{
    const DESCRIPTION_RU = 'Конвертация country в offers';

    const DESCRIPTION_EN = 'Convert country in offers';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}offers SET country = CONCAT ('[\"', country, '\"]') WHERE LENGTH(country) = 2";
        self::execute($sql);
    }
}