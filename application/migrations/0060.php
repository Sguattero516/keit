<?php
class Migration_60 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы index_streams';

    const DESCRIPTION_EN = 'Create table index_streams';

    public static function up()
    {
        $table = self::getDb()->getPrefix() . 'index_streams';
        $oldTable = self::getDb()->getPrefix() . 'archive_streams';

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table. ' (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` int(10) unsigned NOT NULL,
              `value` int(10) unsigned NOT NULL,
              `date` date NOT NULL,
              `count` int(10) unsigned DEFAULT 0,
              `unique` int(10) unsigned DEFAULT 0,
              `leads` int(10) unsigned DEFAULT 0,
              `sales` int(10) unsigned DEFAULT 0,
              `revenue` DECIMAL(13, 4) unsigned DEFAULT 0,
              `cost` DECIMAL(13, 4) unsigned DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `date_value` (`date`, `value`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
        self::execute($sql);

        $sql = 'INSERT INTO ' . $table. ' (campaign_id, value, date, `count`, `unique`, sales)
            SELECT group_id, stream_id, date, `raws`, `raws`, sales FROM ' . $oldTable .'';
        self::execute($sql);
    }
}