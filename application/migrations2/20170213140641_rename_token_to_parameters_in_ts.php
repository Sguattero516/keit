<?php
class Migration_20170213140641_RenameTokenToParametersInTs extends Migration 
{
    const DESCRIPTION_RU = 'Переименовка token в parameters, accept_tokens в accept_parameters у traffic_sources';

    const DESCRIPTION_EN = 'Rename token to parameters, accept_tokens to accept_parameters in traffic_sources';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}traffic_sources CHANGE COLUMN token parameters text DEFAULT NULL";
        self::execute($sql);

        $sql = "ALTER TABLE {$prefix}traffic_sources CHANGE COLUMN accept_tokens accept_parameters tinyint(1) unsigned DEFAULT 1";
        self::execute($sql);
    }
}