<?php
class Migration_20170328155219_AffiliateNetworkOfferParam extends Migration 
{
    const DESCRIPTION_RU = 'Добавление offer_param в affiliate_networks';

    const DESCRIPTION_EN = 'Add offer_param to affiliate_networks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}affiliate_networks ADD `offer_param` varchar(255) default null AFTER `postback_url`";
        self::execute($sql);
    }
}