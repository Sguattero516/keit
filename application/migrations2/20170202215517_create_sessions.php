<?php
class Migration_20170202215517_CreateSessions extends Migration 
{
    const DESCRIPTION_RU = 'Создание sessions';

    const DESCRIPTION_EN = 'Create sessions';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "CREATE TABLE {$prefix}sessions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            visitor_code varchar(50) NOT NULL,
            expires_at datetime NOT NULL,
            payload text NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY visitor_code (visitor_code),
            KEY expires_at (expires_at) 
            ) ENGINE = InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
        ";
        self::execute($sql);
    }
}