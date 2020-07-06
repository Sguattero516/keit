<?php
class Migration_20161220075354_AddvisitorIdToEvents extends Migration 
{
    const DESCRIPTION_RU = 'Добавление visitor_id в events';

    const DESCRIPTION_EN = 'Add visitor_id to events';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}events ADD COLUMN visitor_id bigint unsigned NOT NULL AFTER click_id";
        self::getDb()->execute($sql);
    }
}