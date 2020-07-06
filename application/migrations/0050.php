<?php
class Migration_50 extends Migration {

    const DESCRIPTION_RU = 'Добавление campaign_id в таблицу ips';

    const DESCRIPTION_EN = 'Add campaign_id to table ips';

    public static function up()
    {
        $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'ips` ADD `campaign_id` INT NULL DEFAULT NULL AFTER `id`, ADD INDEX ( campaign_id ) ';
        self::silentExecute($sql);

        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'ips CHANGE `group_id` `group_id` INT( 11 ) NULL DEFAULT NULL';
        self::silentExecute($sql);

        $sql = 'UPDATE `' . self::getDb()->getPrefix() . 'ips` SET campaign_id = group_id WHERE campaign_id IS NULL';
        self::silentExecute($sql);
    }
}