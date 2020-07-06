<?php
class Migration_49 extends Migration {

    const DESCRIPTION_RU = 'Добавление version в таблицу shards';

    const DESCRIPTION_EN = 'Add version to table shards';

    public static function up()
    {
        $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'shards` ADD `version` VARCHAR( 10 ) NULL DEFAULT NULL';
        self::execute($sql);
    }
}