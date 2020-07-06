<?php
class Migration_20161202133027_AddIsUniqueGlobalToClicks extends Migration
{
    const DESCRIPTION_RU = 'Добавление is_unique_global в clicks';

    const DESCRIPTION_EN = 'Add is_unique_global to clicks';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}clicks ADD COLUMN is_unique_global INT(1) unsigned DEFAULT 0 AFTER is_unique_campaign";
        self::execute($sql);
    }
}