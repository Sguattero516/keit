<?php
class Migration_20170213202841_AddAdCampaignId extends Migration 
{
    const DESCRIPTION_RU = 'Добавление ad_campaign_id в clicks';

    const DESCRIPTION_EN = 'Add ad_campaign_id to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks ADD ad_campaign_id int(10) unsigned default null";
        self::execute($sql);
    }
}