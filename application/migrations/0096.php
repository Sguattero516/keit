<?php
class Migration_96 extends Migration {

    const DESCRIPTION_RU = 'Обновления показателя Конверсии';

    const DESCRIPTION_EN = 'Update conversions counters';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $indexes = array('campaigns', 'streams', 'hours', 'referrers', 'countries', 'regions', 'cities', 'browsers',
                         'os', 'keywords', 'sources', 'isp', 'operators', 'device_types', 'device_models', 'connection_types',
                         'params');

        foreach ($indexes as $index) {
            $sql = "UPDATE `{$prefix}index_{$index}` SET conversions = sales + leads";
            self::execute($sql);
        }
    }
}