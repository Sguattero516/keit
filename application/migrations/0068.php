<?php
class Migration_68 extends Migration {

    const DESCRIPTION_RU = 'Добавление новых настроек для статистики';

    const DESCRIPTION_EN = 'Added new settings for reports';

    public static function up()
    {
        $renames = array(
            'store_details_period' => 'store_detailed_period',
            'store_archive_daily_period' => 'store_campaigns_period',
            'store_archive_streams_period' => 'store_streams_period',
            'store_archive_hours_period' => 'store_hours_period',
            'store_archive_countries_period'  => 'store_countries_period',
            'store_archive_keywords_period' => 'store_keywords_period',
            'store_archive_sources_period' => 'store_sources_period',
            'store_archive_se_period' => 'store_se_period',
            'store_archive_referers_period' => 'store_referrers_period',
            'store_archive_browsers_period' => 'store_browsers_period',
            'store_archive_os_period' => 'store_os_period',
            'store_archive_device_types_period' => 'store_device_types_period',
            'store_sales_period' => 'store_conversions_period',

            'daily_statistic_enabled' => 'campaigns_report_enabled',
            'referers_statistic_enabled' => 'referrers_report_enabled',
            'countries_statistic_enabled' => 'countries_report_enabled',
            'keywords_statistic_enabled' => 'keywords_report_enabled',
            'sources_statistic_enabled' => 'sources_report_enabled',
            'se_statistic_enabled' => 'se_report_enabled',
            'streams_statistic_enabled' => 'streams_report_enabled',
            'browsers_statistic_enabled' => 'browsers_report_enabled',
            'os_statistic_enabled' => 'os_report_enabled',
            'device_types_statistic_enabled' => 'device_types_report_enabled'
        );

        $new = array(
            'store_regions_period' => 10,
            'store_cities_period' => 10,
            'store_device_models_period' => 10,
            'store_isp_period' => 10,
            'store_operators_period' => 10,
            'store_sub_id_1_period' => 10,
            'store_sub_id_2_period' => 10,
            'store_sub_id_3_period' => 10,
            'store_sub_id_4_period' => 10,
            'store_connection_types_period' => 10,

            'campaigns_report_enabled' => 1,
            'streams_report_enabled' => 1,
            'sources_report_enabled' => 1,
            'detailed_report_enabled' => 1,
            'hours_report_enabled' => 1,
            'daily_report_enabled' => 1,
            'regions_report_enabled' => 1,
            'cities_report_enabled' => 1,
            'device_models_report_enabled' => 1,
            'isp_report_enabled' => 1,
            'operators_report_enabled' => 1,
            'conversions_report_enabled' => 1,
            'connection_types_report_enabled' => 1,
            'os_report_enabled' => 1,
            'device_types_report_enabled' => 1,
            'sub_id_1_report_enabled' => 1,
            'sub_id_2_report_enabled' => 1,
            'sub_id_3_report_enabled' => 1,
            'sub_id_4_report_enabled' => 1
        );

        foreach ($renames as $from => $to) {
            $sql = 'INSERT INTO ' . self::getDb()->getPrefix() . 'settings (`key`, value)
                (SELECT "'.$to.'", value FROM ' . self::getDb()->getPrefix() . 'settings WHERE `key` = "' . $from . '")';
            self::silentExecute($sql);
        }


        foreach ($new as $key => $value) {
            $sql = 'INSERT INTO ' . self::getDb()->getPrefix() . 'settings (`key`, value) VALUES ("'.$key.'", "'.$value.'")';
            self::silentExecute($sql);
        }
    }
}