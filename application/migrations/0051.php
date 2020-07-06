<?php
class Migration_51 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы conversions и копирование данных из sales';

    const DESCRIPTION_EN = 'Create table conversions and copy data from sales';

    public static function up()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . self::getDb()->getPrefix() . 'conversions` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `campaign_id` int(11) DEFAULT NULL,
            `stream_id` int(10) unsigned DEFAULT NULL,

            `sub_id` varchar(25) NOT NULL,
            `tid` varchar(100) DEFAULT NULL,
            `status` varchar(100) DEFAULT NULL,
            `original_status` varchar(100) DEFAULT NULL,

            `se` varchar(100) DEFAULT NULL,
            `sub_id_1` varchar(100) DEFAULT NULL,
            `sub_id_2` varchar(100) DEFAULT NULL,
            `sub_id_3` varchar(100) DEFAULT NULL,
            `sub_id_4` varchar(100) DEFAULT NULL,

            `extra_param_1` varchar(100) DEFAULT NULL,
            `extra_param_2` varchar(100) DEFAULT NULL,
            `extra_param_3` varchar(100) DEFAULT NULL,
            `extra_param_4` varchar(100) DEFAULT NULL,
            `extra_param_5` varchar(100) DEFAULT NULL,
            `extra_param_6` varchar(100) DEFAULT NULL,
            `extra_param_7` varchar(100) DEFAULT NULL,

            `revenue` decimal(13,4) DEFAULT \'0.0000\',
            `cost` decimal(14,4) DEFAULT \'0.0000\',

            `browser` varchar(150) DEFAULT NULL,
            `os` varchar(100) DEFAULT NULL,
            `country` varchar(2) DEFAULT NULL,
            `region` varchar(6) DEFAULT NULL,
            `city` varchar(60) DEFAULT NULL,
            `ip` bigint(11) DEFAULT NULL,
            `keyword` varchar(250) DEFAULT NULL,
            `source` varchar(250) DEFAULT NULL,
            `operators` varchar(50) DEFAULT NULL,
            `isp` varchar(100) DEFAULT NULL,
            `operator` varchar(50) DEFAULT NULL,
            `connection_type` varchar(10) DEFAULT NULL,
            `device_type` varchar(30) DEFAULT NULL,
            `device_model` VARCHAR(200) DEFAULT NULL,

            `params` text,
            `entry_date` DATE NOT NULL,
            `datetime` DATETIME NOT NULL,
            `processed` int(1) unsigned DEFAULT 0,

            PRIMARY KEY (`id`),
            UNIQUE KEY `sub_id_tid` (`sub_id`, `tid`),
            KEY `campaign_id` (`campaign_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

        self::execute($sql);

        $sql = 'INSERT IGNORE ' . self::getDb()->getPrefix() . 'conversions (id, campaign_id, stream_id, sub_id, revenue, params, datetime, status, processed)
            SELECT id, group_id, stream_id, sub_id, profit, params, FROM_UNIXTIME(datetime), \'sale\', 1 FROM ' . self::getDb()->getPrefix() . 'sales';
        self::execute($sql);
    }
}