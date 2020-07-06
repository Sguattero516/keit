<?php
class Migration_20170103102302_AddExternalIdCreativeId extends Migration 
{
    const DESCRIPTION_RU = 'Добавление external_id и creative_id';

    const DESCRIPTION_EN = 'Adding external_id and creative_id';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks 
          ADD COLUMN creative_id int(10) unsigned DEFAULT NULL,
          ADD COLUMN external_id int(10) unsigned DEFAULT NULL,
          ADD INDEX creative_id (creative_id),
          ADD INDEX external_id (external_id)";
        self::silentExecute($sql);

    }
}