<?php
class Migration_20170213203606_CreateRefAdCampaignIds extends Migration 
{
    const DESCRIPTION_RU = 'Создание ref_ad_campaign_ids';

    const DESCRIPTION_EN = 'Create ref_ad_campaign_ids';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}ref_ad_campaign_ids` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `value` varchar(15) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `value` (`value`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
        self::execute($sql);

    }
}