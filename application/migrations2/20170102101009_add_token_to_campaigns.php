<?php
class Migration_20170102101009_AddTokenToCampaigns extends Migration 
{
    const DESCRIPTION_RU = 'Добавление token к campaigns';

    const DESCRIPTION_EN = 'Add token to campaigns';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}campaigns ADD COLUMN token varchar(50) NOT NULL";
        self::silentExecute($sql);

        $sql = "UPDATE {$prefix}campaigns SET token = LEFT(UUID(), 8)";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}campaigns ADD UNIQUE INDEX token (`token`)";
        self::execute($sql);
    }
}