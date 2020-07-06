<?php
class Migration_20161003163037_AddTypeToStreams extends Migration 
{
    const DESCRIPTION_RU = 'Добавление type в streams';

    const DESCRIPTION_EN = 'Add type to streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}streams ADD  `type` VARCHAR(50) NOT NULL DEFAULT 'regular' AFTER `id`";
        self::silentExecute($sql);
    }
}