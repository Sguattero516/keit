<?php
class Migration_20161210141020_RemoveCountryREgionFromRefCities extends Migration
{
    const DESCRIPTION_RU = 'Удаление country_id, region_id из ref_cities';

    const DESCRIPTION_EN = 'Remove country_id, region_id from ref_cities';

    public static function up()
    {
        $prefix = self::getPrefix();
        try {
            $sql = "ALTER IGNORE TABLE {$prefix}ref_cities DROP country_id, DROP region_id";
            self::execute($sql);
        } catch (\Exception $e) {
            $sql = "ALTER TABLE {$prefix}ref_cities DROP country_id, DROP region_id";
            self::silentExecute($sql);
        }
    }
}
