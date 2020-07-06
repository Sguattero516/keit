<?php
class Migration_20161208160631_UpdateIndexesOfEvents extends Migration
{
    const DESCRIPTION_RU = 'Изменение индексов у таблицы events';

    const DESCRIPTION_EN = 'Change indexes in events';

    public static function up()
    {
        $prefix = self::getPrefix();

        $sql = "DROP INDEX event_type ON {$prefix}events";
        self::silentExecute($sql);

        $sql = "DROP INDEX visited_at_campaign_id_steam_id ON {$prefix}events";
        self::silentExecute($sql);

        $sql = "CREATE INDEX `datetime` ON {$prefix}events  (`datetime`)";
        self::silentExecute($sql);
    }
}