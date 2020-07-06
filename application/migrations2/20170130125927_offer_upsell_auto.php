<?php
class Migration_20170130125927_OfferUpsellAuto extends Migration 
{
    const DESCRIPTION_RU = 'Добавление поля payout_auto и payout_upsell в таблицу offers';

    const DESCRIPTION_EN = 'Add field payout_auto and payout_upsell to offers table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}offers 
                ADD COLUMN payout_auto TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                ADD COLUMN payout_upsell TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
        self::execute($sql);
    }
}