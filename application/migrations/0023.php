<?php


class Migration_23 extends Migration {

    const DESCRIPTION_RU = 'Добавление директив в config.ini для viruscheckmate';

    const DESCRIPTION_EN = 'Add new settings for viruscheckmate to config.ini';

    public static function up()
    {
        $rows = array();
        $rows[] = '';
        $rows[] = '; Comma-separated list (http://wiki.keitarotds.com/vcm) or leave empty to check all';
        $rows[] = 'vcm_engines = "avast, avira, avgtl, nod32, google, kasper, yandex, bitdef, drwebcl, fortinet, sophoscl, spamhaus, trendcl, vipre"';
        $rows[] = '';
        $rows[] = '; Set value of objects that will be checked by one request according your tariff plan https://viruscheckmate.com/tariffs/';
        $rows[] = 'vcm_objects_per_request = 1';
        $rows[] = '';
        $rows[] = '; Enter UA Profile or leave empty to check all';
        $rows[] = 'vcm_ua = "Chrome 33 on Windows 7"';
        $rows[] = '';
        $rows[] = '; Logging to file /var/log/vcm.log';
        $rows[] = 'vcm_log = false';

        self::addToConfig($rows);
    }
}