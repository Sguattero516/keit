<?php
class Migration_20161206231240_AddLpClickDatetime extends Migration 
{
    const DESCRIPTION_RU = 'Добавление lp_click_datetime в clicks';

    const DESCRIPTION_EN = 'Add lp_click_datetime to clicks';

    public static function up()
    {
        $sql = "ALTER TABLE {$prefix}events 
            ADD COLUMN lp_click tinyint(1) unsigned DEFAULT 0
            ADD COLUMN lp_click_datetime DATETIME DEFAULT NULL";
    }
}