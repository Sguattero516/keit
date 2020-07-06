<?php
class Migration_20170610102246_ChangeSchemaOffersToLandings extends Migration 
{
    const DESCRIPTION_RU = 'Замена схем в потоках offers на landings';

    const DESCRIPTION_EN = 'Change schema in streams from offers to landings';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}streams SET `schema` = 'landings' WHERE `schema` = 'offers'";
        self::execute($sql);
    }
}