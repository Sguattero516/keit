<?php
class Migration_20161210140656_RemoveCountryIdFromRefRegions extends Migration 
{
    const DESCRIPTION_RU = 'Удаление country_id из ref_regions';

    const DESCRIPTION_EN = 'Remove country_id from ref_regions';

    public static function up()
    {
        $prefix = self::getPrefix();
        try {
            $sql = "ALTER IGNORE TABLE {$prefix}ref_regions DROP country_id";
            self::execute($sql);
        } catch (\Exception $e) {
            $sql = "ALTER TABLE {$prefix}ref_regions DROP country_id";
            self::silentExecute($sql);
        }
    }
}