<?php
class Migration_20161019223117_GroupType extends Migration 
{
    const DESCRIPTION_RU = 'Добавление type в groups';

    const DESCRIPTION_EN = 'Add type to groups table';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}groups ADD COLUMN type  varchar(255) DEFAULT 'campaign'";
        self::execute($sql);
    }
}