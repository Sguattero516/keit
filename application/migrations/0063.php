<?php
class Migration_63 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы index_referrers и копирование данных';

    const DESCRIPTION_EN = 'Create table index_referrers and copy data';

    public static function up()
    {
        $table = self::getDb()->getPrefix() . 'index_referrers';
        $oldTable = self::getDb()->getPrefix() . 'archive_referers';

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table. ' (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` int(10) unsigned NOT NULL,
              `date` date NOT NULL,
              `value` varchar(250) NOT NULL,
              `count` int(10) unsigned DEFAULT 0,
              `unique` int(10) unsigned DEFAULT 0,
              `leads` int(10) unsigned DEFAULT 0,
              `sales` int(10) unsigned DEFAULT 0,
              `revenue` DECIMAL(13, 4) unsigned DEFAULT 0,
              `cost` DECIMAL(13, 4) unsigned DEFAULT 0,
              PRIMARY KEY (`id`),
              KEY `date_value` (`date`,`value`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';
        self::execute($sql);

        $sql = 'SELECT COUNT(*) FROM ' . $oldTable.'';
        if (self::getDb()->getOne($sql)  < 100000) {
            $sql = 'INSERT INTO ' . $table . ' (campaign_id, date, value, `count`, `unique`)
            SELECT group_id, date, referer, `count`, `count` FROM ' . $oldTable . '';
        }
        self::execute($sql);
    }
}