<?php


class Migration_11 extends Migration {

    const DESCRIPTION_RU = 'Переименовка ip.dat в bots.dat, my.ip.dat в bots.additional.dat';

    const DESCRIPTION_EN = 'Rename ip.dat to bots.dat, my.ip.dat to bots.additional.dat';

    public static function up()
    {
        if (!file_exists(ROOT . '/var/bots/bots.dat')) {
            rename(ROOT . '/var/bots/ip.dat', ROOT . '/var/bots/bots.dat');
        }
        if (!file_exists(ROOT . '/var/bots/bots.additional.dat') && file_exists(ROOT . '/var/bots/my.ip.dat')) {
            rename(ROOT . '/var/bots/my.ip.dat', ROOT . '/var/bots/bots.additional.dat');
        }
    }
}