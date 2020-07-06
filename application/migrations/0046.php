<?php
class Migration_46 extends Migration {

    const DESCRIPTION_RU = 'Добавление campaign_id в keitaro_streams';

    const DESCRIPTION_EN = 'Add campaign_id to keitaro_streams';

    public static function up()
    {
        $sql = 'ALTER TABLE `'.self::getDb()->getPrefix() . 'streams`
            ADD `campaign_id` INT UNSIGNED DEFAULT NULL AFTER `id` , ADD INDEX ( campaign_id )';
        self::silentExecute($sql);

        $sql = 'ALTER TABLE ' . self::getDb()->getPrefix() . 'streams CHANGE `group_id` `group_id` INT( 11 ) NULL DEFAULT NULL';
        self::execute($sql);

        $sql = 'UPDATE `'.self::getDb()->getPrefix() . 'streams`
            SET campaign_id = group_id WHERE campaign_id IS NULL';
        self::execute($sql);
    }
}