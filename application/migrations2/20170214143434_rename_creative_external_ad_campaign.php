<?php
class Migration_20170214143434_RenameCreativeExternalAdCampaign extends Migration 
{
    const DESCRIPTION_RU = 'Добавление "_id" к ad_campaign_id, external_id, creative_id';

    const DESCRIPTION_EN = 'Add "_id" to ad_campaign_id, external_id, creative_id';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
          CHANGE COLUMN ad_campaign_id ad_campaign_id_id int(10) unsigned default null, 
          CHANGE COLUMN external_id external_id_id int(10) unsigned default null, 
          CHANGE COLUMN creative_id creative_id_id int(10) unsigned default null 
        ";
        self::execute($sql);
    }
}