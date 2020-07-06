<?php
class Migration_20190708092604_AddDomainCheckErrorColumn extends Migration
{
    const DESCRIPTION_RU = 'Добавление колонки error_description в таблицу доменов';

    const DESCRIPTION_EN = 'Adding error_description column to domains table';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}domains
                ADD error_description varchar(255)";
        self::silentExecute($sql);
    }
}
