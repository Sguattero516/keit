<?php
class Migration_20170209161316_AddParentCampaignIdToClicks extends Migration 
{
    const DESCRIPTION_RU = 'Добавление parent_campaign_id в clicks';

    const DESCRIPTION_EN = 'Add parent_campaign_id to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks ADD COLUMN parent_campaign_id int(10) unsigned DEFAULT NULL AFTER campaign_id";
        self::execute($sql);
    }
}