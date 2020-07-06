<?php
use Component\Migrations\Migrator7\TdsMigrator7;

require_once ROOT . '/application/Component/Migrations/Migrator7/TdsMigrator7.php';

class Migration_40 extends Migration {

    const DESCRIPTION_RU = 'Перенос фильтров в stream_filters';

    const DESCRIPTION_EN = 'Transfer filters to stream_filters';

    protected static $_table = 'streams';

    public static function setTable($name)
    {
        static::$_table = $name;
    }

    public static function up()
    {
        $sql = 'SELECT * FROM ' . self::getDb()->getPrefix() . static::$_table;
        $migrator = new TdsMigrator7();
        
        foreach (self::execute($sql) as $row) {
            if (self::getDb()->getOne('SELECT count(*) FROM ' . self::getDb()->getPrefix() . 'stream_filters WHERE stream_id = ' . $row['id'])) {
                continue;
            }

            $migrator->run($row);
        }
    }
}
