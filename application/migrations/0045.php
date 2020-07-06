<?php


class Migration_45 extends Migration {

    const DESCRIPTION_RU = 'Удаление ненужных настроек';

    const DESCRIPTION_EN = 'Delete unnecessary settings';

    public static function up()
    {
        $sql = 'DELETE FROM ' .  self::getDb()->getPrefix() . 'settings
        WHERE `key` IN ("admin_login", "admin_password", "geoip_db", "geoip_update", "links_styles",
        "identify_device_models", "ips_migrated_at", "archive_countries_period",
        "archive_hours_period", "archive_keywords_period", "archive_referers_period",
        "archive_se_period", "archive_sources_period", "archive_store_period", "archive_streams_period")';
        self::execute($sql);
    }
}