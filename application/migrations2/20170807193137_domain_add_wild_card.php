<?php
class Migration_20170807193137_DomainAddWildCard extends Migration 
{
    const DESCRIPTION_RU = 'Добавление wildcard к domains';

    const DESCRIPTION_EN = 'Add wildcard to domains';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}domains 
                ADD wildcard TINYINT(1) NOT NULL DEFAULT 0";
        self::silentExecute($sql);
    }
}