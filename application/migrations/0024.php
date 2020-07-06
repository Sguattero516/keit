<?php


class Migration_24 extends Migration {

    const DESCRIPTION_RU = 'Добавление директивы postback_log в config.ini';

    const DESCRIPTION_EN = 'Add new setting postback_log to config.ini';

    public static function up()
    {
        $rows = array();
        $rows[] = '; Enable loggin for postbacks';
        $rows[] = 'postback_log = true';

        self::addToConfig($rows);
    }
}