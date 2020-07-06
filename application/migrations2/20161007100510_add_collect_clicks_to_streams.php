<?php
class Migration_20161007100510_AddCollectClicksToStreams extends Migration
{
    const DESCRIPTION_RU = 'Добавление collect_clicks в streams';

    const DESCRIPTION_EN = 'Add collect_clicks to streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}streams ADD COLUMN collect_clicks INT(1) unsigned DEFAULT 1";
        self::silentExecute($sql);

        $sql = "UPDATE {$prefix}streams SET collect_clicks = 0 WHERE `type` = 'default' AND campaign_id IN 
            (SELECT id FROM {$prefix}campaigns WHERE action_tracking_disabled = 1)";
        self::execute($sql);
    }
}