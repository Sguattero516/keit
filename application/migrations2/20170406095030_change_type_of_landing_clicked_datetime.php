<?php
class Migration_20170406095030_ChangeTypeOfLandingClickedDatetime extends Migration 
{
    const DESCRIPTION_RU = 'Смена типа у landing_clicked_datetime в clicks';

    const DESCRIPTION_EN = 'Change type for landing_clicked_datetime in clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}clicks SET landing_clicked_datetime = NULL WHERE landing_clicked_datetime = 0";
        self::execute($sql);
        $sql = "ALTER TABLE {$prefix}clicks 
          CHANGE COLUMN landing_clicked_datetime landing_clicked_datetime DATETIME DEFAULT NULL";
        self::execute($sql);

        $sql = "UPDATE {$prefix}clicks SET landing_clicked = 1 WHERE landing_id > 0 AND offer_id > 0";
        self::execute($sql);
    }
}