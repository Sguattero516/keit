<?php
class Migration_20161223065716_RenameCodeToVisitorCode extends Migration 
{
    const DESCRIPTION_RU = 'Переименование code в visitor_code';

    const DESCRIPTION_EN = 'Rename code to visitor_code';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}visitors CHANGE COLUMN `code` `visitor_code` varchar(100) NOT NULL";
        self::execute($sql);
    }
}