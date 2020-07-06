<?php
class Migration_20161213151819_RenameQueueToDelayedTask extends Migration 
{
    const DESCRIPTION_RU = 'Переименование таблицы queue в delayed_tasks';

    const DESCRIPTION_EN = 'Rename table queue to delayed_tasks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "RENAME TABLE {$prefix}queue TO {$prefix}delayed_tasks";
        self::execute($sql);    
    }
}