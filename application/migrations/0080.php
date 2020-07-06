<?php
class Migration_80 extends Migration {

    const DESCRIPTION_RU = 'Добавление опции extra_action';

    const DESCRIPTION_EN = 'Add option extra_action';

    public static function up()
    {
        $sql = 'INSERT INTO `' . self::getDb()->getPrefix() . 'settings` (`key`, `value`) values ("extra_action", "redirect")';
        self::silentExecute($sql);
    }
}