<?php
class Migration_93 extends Migration {

    const DESCRIPTION_RU = 'Изменение таблиц users, stream_filters';

    const DESCRIPTION_EN = 'Modification of table users, stream_filters';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE  `{$prefix}users` CHANGE  `rules`  `rules` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
        self::silentExecute($sql);

        $sql = "ALTER TABLE  `{$prefix}stream_filters` CHANGE  `payload`  `payload` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
        self::silentExecute($sql);
    }
}