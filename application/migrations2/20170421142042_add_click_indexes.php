<?php
class Migration_20170421142042_AddClickIndexes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление индексов в clicks';

    const DESCRIPTION_EN = 'Add indexes to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
          ADD INDEX parent_campaign_id(parent_campaign_id),
          ADD INDEX search_engine_id(search_engine_id),
          ADD INDEX ad_campaign_id_id(ad_campaign_id_id)
          ";
        self::execute($sql);
    }
}