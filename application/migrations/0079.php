<?php
class Migration_79 extends Migration {

    const DESCRIPTION_RU = 'Добавление previous_status в conversations';

    const DESCRIPTION_EN = 'Add previous_status to conversions';

    public static function up()
    {
        $sql = 'ALTER TABLE `' . self::getDb()->getPrefix() . 'conversions` ADD `previous_status` VARCHAR( 100 ) NULL DEFAULT NULL AFTER `status`';
        self::execute($sql);
    }
}