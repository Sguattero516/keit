<?php
class Migration_20161208222930_RenameRawClicks extends Migration 
{
    const DESCRIPTION_RU = 'Переименование таблицы raw_clicks в event_queue';

    const DESCRIPTION_EN = 'Rename table raw_clicks to event_queue';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}raw_clicks TO {$prefix}event_queue";
        self::execute($sql);    
    }
}