<?php
class Migration_20170721113953_RemoveStreamLandingOfferId extends Migration 
{
    const DESCRIPTION_RU = 'Удаление landing_id, offer_id у streams';

    const DESCRIPTION_EN = 'Remove landing_id, offer_id from streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}streams 
                    DROP COLUMN landing_id,
                    DROP COLUMN offer_id";
        self::silentExecute($sql);
    }
}