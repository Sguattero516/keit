<?php
class Migration_91 extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы campaign_postbacks';

    const DESCRIPTION_EN = 'Create table campaign_postbacks';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "CREATE TABLE IF NOT EXISTS {$prefix}campaign_postbacks (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `campaign_id` INT UNSIGNED NOT NULL ,
            `method` VARCHAR( 10 ) NOT NULL,
            `url` VARCHAR( 255 ) NOT NULL ,
            `statuses` VARCHAR( 255 ) NULL DEFAULT NULL,
            `source` VARCHAR( 255 ) NULL DEFAULT NULL,
            INDEX (  `campaign_id` )
            ) ENGINE = INNODB;";

        self::execute($sql);
    }
}