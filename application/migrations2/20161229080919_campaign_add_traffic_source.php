<?php
class Migration_20161229080919_CampaignAddTrafficSource extends Migration 
{
    const DESCRIPTION_RU = 'Добавление traffic_source_id к campaign';

    const DESCRIPTION_EN = 'Add traffic_source_id to campaign';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns 
          ADD COLUMN traffic_source_id int(10) unsigned DEFAULT NULL,
          ADD INDEX traffic_source_id (traffic_source_id)";
        self::execute($sql);
    }
}