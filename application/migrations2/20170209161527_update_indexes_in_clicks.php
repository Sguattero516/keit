<?php
class Migration_20170209161527_UpdateIndexesInClicks extends Migration 
{
    const DESCRIPTION_RU = 'Добавление индексов в clicks';

    const DESCRIPTION_EN = 'Add indexes to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks
          ADD INDEX landing_id(landing_id),
          ADD INDEX offer_id(offer_id),
          ADD INDEX stream_id(stream_id)
        ";
        self::execute($sql);
    }
}