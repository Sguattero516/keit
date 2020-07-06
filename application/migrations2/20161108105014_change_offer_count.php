<?php
class Migration_20161108105014_ChangeOfferCount extends Migration 
{
    const DESCRIPTION_RU = 'Смена значения по умолчанию в landings.offer_count';

    const DESCRIPTION_EN = 'Change default value of landings.offer_count';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings CHANGE COLUMN `offer_count` `offer_count` INT(10) NOT NULL DEFAULT 1";
        self::execute($sql);
    }
}