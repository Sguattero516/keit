<?php
class Migration_20170214174804_SyncFieldInConversions extends Migration 
{
    const DESCRIPTION_RU = 'Обновление колоне в conversions_2';

    const DESCRIPTION_EN = 'Update columns in conversions_2';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}conversions_2 
           CHANGE COLUMN external_id external_id_id int(10) unsigned default null, 
           CHANGE COLUMN creative_id creative_id_id int(10) unsigned default null,
           ADD COLUMN ad_campaign_id_id int(10) unsigned default null
        ";
        self::execute($sql);

    }
}