<?php


class Migration_36 extends Migration {

    const DESCRIPTION_RU = 'Добавление поля position в таблицу keitaro_groups';

    const DESCRIPTION_EN = 'Add field "position" to table keitaro_groups';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();

        $sql = "ALTER TABLE `{$prefix}stream_groups` ADD `position` INT UNSIGNED NULL DEFAULT NULL ";
        self::execute($sql);

        $sql = "SELECT * FROM `{$prefix}stream_groups` ORDER BY id";
        $pos = 0;
        foreach (self::execute($sql) as $row) {
            $pos++;
            $sql = "UPDATE `{$prefix}stream_groups` SET position = {$pos} WHERE id = {$row['id']}";
            self::execute($sql);
        }
    }
}