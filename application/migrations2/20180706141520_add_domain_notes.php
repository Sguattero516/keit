<?php
class Migration_20180706141520_AddDomainNotes extends Migration 
{
    const DESCRIPTION_RU = 'Добавление notes к domains';

    const DESCRIPTION_EN = 'Add notes to domains';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}domains 
                ADD notes TEXT NULL DEFAULT NULL";
        self::silentExecute($sql);
    }
}