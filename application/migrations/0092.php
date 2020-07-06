<?php
class Migration_92 extends Migration {

    const DESCRIPTION_RU = 'Удаление старых полей из streams';

    const DESCRIPTION_EN = 'Delete old columns from streams';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $fields = array('status', 'countries_filter', 'countries_filter_value',
                        'cities_filter', 'cities_filter_value', 'ip_filter', 'ip_filter_value', 'browser_filter',
                        'browser_filter_value', 'os_filter', 'os_filter_value', 'keywords_filter_value', 'langs_filter',
                        'langs_filter_value', 'referer_filter', 'referer_filter_value', 'proxy_filter', 'unique_filter_scope',
                        'unique_filter', 'unique_filter_storage', 'schedule', 'start_date', 'end_date', 'manual_referer',
                        'manual_filters', 'ua_filter', 'ua_filter_value', 'mobile_filter', 'operator_filter',
                        'operator_filter_value', 'regions_filter', 'regions_filter_value', 'device_type_filter',
                        'device_type_filter_value', 'device_model_filter', 'device_model_filter_value');
        foreach ($fields as $field) {
            $sql = "ALTER TABLE `{$prefix}streams` DROP `{$field}`;";
            self::silentExecute($sql);
        }
    }
}