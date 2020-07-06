<?php
class Migration_20161111111151_AddNameToStreams extends Migration 
{
    const DESCRIPTION_RU = 'Добавление name в streams';

    const DESCRIPTION_EN = 'Add name to streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}streams ADD COLUMN `name` varchar(100) AFTER `type`";
        self::executeIgnore($sql, 'Duplicate column');
    }
}