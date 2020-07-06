<?php
class Migration_20170808192436_IncreaseAdCampaignSize extends Migration 
{
    const DESCRIPTION_RU = 'Увеличие размера ad_campaign_id';

    const DESCRIPTION_EN = 'Increase size of ad_campaign_id';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}ref_ad_campaign_ids CHANGE COLUMN `value` `value` varchar(255) NOT NULL";
        self::execute($sql);
    }
}