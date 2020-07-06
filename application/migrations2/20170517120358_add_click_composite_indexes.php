<?php
class Migration_20170517120358_AddClickCompositeIndexes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление составных индексов в clicks';

    const DESCRIPTION_EN = 'Adding composite indexes to click';

    public static function up($rerun = false)
    {
        $prefix = self::getPrefix();
        $count = self::getDb()->getOne("SELECT count(*) from {$prefix}clicks");
        if ($count < 1000000 || $rerun) {
            $prefix = self::getPrefix();
            $sql = "ALTER TABLE {$prefix}clicks 
          ADD INDEX campaign_id_datetime(campaign_id, datetime),
          ADD INDEX stream_id_datetime(stream_id, datetime),
          ADD INDEX ts_id_datetime(ts_id, datetime),
          ADD INDEX landing_id_datetime(landing_id, datetime),
          ADD INDEX offer_id_datetime(offer_id, datetime)";
            self::silentExecute($sql);
        }
    }
}