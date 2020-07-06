<?php
class Migration_20170329091946_MakeValuesBinary extends Migration 
{
    const DESCRIPTION_RU = 'Установка бинарного сравнения для ref таблиц';

    const DESCRIPTION_EN = 'Make binary collocation in ref tables';

    public static function up()
    {
        $prefix = self::getPrefix();
        $tables = ['ref_device_models', 'ref_destinations', 'ref_creative_ids', 'ref_cities',
            'ref_params', 'ref_isp', 'ref_external_ids'];
        foreach ($tables as $table) {
            $sql = "ALTER TABLE {$prefix}{$table} CHANGE `value` `value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL";
            self::execute($sql);
        }
    }
}