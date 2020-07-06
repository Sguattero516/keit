<?php
class Migration_20190125072639_FixBotStreamNameAndSchema extends Migration
{
    const DESCRIPTION_RU = 'Добавление схемы к ботовым потокам';

    const DESCRIPTION_EN = 'Added schema to bot streams';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "UPDATE {$prefix}streams SET `name`='antibot' WHERE `name` IS NULL and `comments` = '';";
        self::execute($sql);
        $sql = "UPDATE {$prefix}streams SET `schema`='action' WHERE `schema` IS NULL;";
        self::execute($sql);
    }
}