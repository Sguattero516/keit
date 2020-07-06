<?php
class Migration_20170515145610_AddLandingTypeAndMacrosEnabled extends Migration 
{
    const DESCRIPTION_RU = 'Добавление landing_type в landings';

    const DESCRIPTION_EN = 'Add column landing_type to landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}landings 
          ADD COLUMN landing_type varchar(10) default 'external'";
        self::execute($sql);
    }
}