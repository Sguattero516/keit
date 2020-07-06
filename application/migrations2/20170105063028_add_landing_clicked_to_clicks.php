<?php
class Migration_20170105063028_AddLandingClickedToClicks extends Migration 
{
    const DESCRIPTION_RU = 'Добавление landing_clicked и landing_clicked_datetime в clicks';

    const DESCRIPTION_EN = 'Add landing_clicked and landing_clicked_datetime to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks
          ADD landing_clicked tinyint(1) unsigned DEFAULT 0 AFTER landing_id,
          ADD landing_clicked_datetime tinyint(1) unsigned DEFAULT 0 AFTER landing_clicked
        ";
        self::execute($sql);
    }
}