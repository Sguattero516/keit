<?php
class Migration_89 extends Migration {

    const DESCRIPTION_RU = 'Увеличение количества знаков после запятой в денежных значениях';

    const DESCRIPTION_EN = 'Replaced decimal(13,2) to decinal(13,4)';

    public static function up()
    {
        $tables = array('campaigns', 'browsers', 'cities', 'connection_types', 'countries',
                        'device_models', 'device_types', 'hours', 'isp', 'keywords', 'operators',
                        'os', 'params', 'referrers', 'regions', 'sources', 'streams');

        $prefix = self::getDb()->getPrefix();
        foreach ($tables as $table) {
            $sql = "
                ALTER TABLE `{$prefix}index_{$table}`
                CHANGE `revenue` `revenue` DECIMAL(13, 4) UNSIGNED NULL DEFAULT '0.0000',
                CHANGE `cost` `cost` DECIMAL(13, 4) UNSIGNED NULL DEFAULT '0.0000'
            ";
            self::execute($sql);
        }
    }
}