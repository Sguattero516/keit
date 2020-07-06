<?php


class Migration_27 extends Migration {

    const DESCRIPTION_RU = 'Добавление avscan_engines в config.ini';

    const DESCRIPTION_EN = 'Add avscan_engines to config.ini';

    public static function up()
    {
        self::addToConfig(array(
            '; Engines that are used to check in avscan.ru (available: kis, nod, avt, dwb)',
            'avscan_engines = kis, nod, avt, dwb'
        ), true);
    }
}