<?php
class Migration_95 extends Migration {

    const DESCRIPTION_RU = 'Добавление колонки conversions в индексы';

    const DESCRIPTION_EN = 'Added column conversions to indexes';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $indexes = array('campaigns', 'streams', 'hours', 'referrers', 'countries', 'regions', 'cities', 'browsers',
                         'os', 'keywords', 'sources', 'isp', 'operators', 'device_types', 'device_models', 'connection_types',
                         'params');

        foreach ($indexes as $index) {
            $sql = "ALTER TABLE  `{$prefix}index_{$index}` ADD  `conversions` INT UNSIGNED DEFAULT  '0' AFTER  `sales`";
            self::silentExecute($sql);
        }
    }
}