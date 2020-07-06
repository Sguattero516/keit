<?php
class Migration_20161219200151_RenameDbDraft extends Migration 
{
    const DESCRIPTION_RU = 'Переименование таблицы db_draft_queue в command_queue';

    const DESCRIPTION_EN = 'Rename table db_draft_queue to command_queue';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}db_draft_queue TO {$prefix}command_queue";
        self::execute($sql);    
    }
}