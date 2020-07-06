<?php
class Migration_20171218141002_ReturnUrlsForOffersAndLandings extends Migration 
{
    const DESCRIPTION_RU = 'Возврат url в offers и landings';

    const DESCRIPTION_EN = 'Return url for offers and landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER IGNORE TABLE {$prefix}landings ADD COLUMN url text default null";
        self::silentExecute($sql);

        $sql = "ALTER IGNORE TABLE {$prefix}offers ADD COLUMN url text default null";
        self::silentExecute($sql);
    }
}