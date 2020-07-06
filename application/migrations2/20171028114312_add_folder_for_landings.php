<?php
class Migration_20171028114312_AddFolderForLandings extends Migration
{
    const DESCRIPTION_RU = 'Добавление folder для landings';

    const DESCRIPTION_EN = 'Add folder for landing';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings
            ADD COLUMN folder varchar(100) default null";
        self::execute($sql);
    }
}