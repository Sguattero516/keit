<?php
class Migration_20190129070615_UpdateOfferTypes extends Migration
{
    const DESCRIPTION_RU = 'Обновление старых offer_type';

    const DESCRIPTION_EN = 'Update all offer_type';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}offers SET offer_type = 'action' where offer_type = 'other'";
        self::execute($sql);
        $sql = "UPDATE {$prefix}offers SET offer_type = 'preloaded' where action_type = 'curl'";
        self::execute($sql);
    }
}