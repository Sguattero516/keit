<?php
class Migration_20161214125109_RenameEventQueue extends Migration 
{
    const DESCRIPTION_RU = 'Переименование таблицы event_queue в db_draft_queue';

    const DESCRIPTION_EN = 'Rename table event_queue to db_draft_queue';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}event_queue TO {$prefix}db_draft_queue";
        self::execute($sql);    
    }
}