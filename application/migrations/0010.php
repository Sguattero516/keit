<?php


class Migration_10 extends Migration {

    const DESCRIPTION_RU = 'Настройки для ботов в группах';

    const DESCRIPTION_EN = 'New settings to configure bots in groups';

    public static function up()
    {
        $db = self::getDb();
        $sqls = array(
            "ALTER TABLE `{$db->getPrefix()}stream_groups` ADD `action_for_bots` VARCHAR( 50 ) NOT NULL DEFAULT '404',
                ADD `bot_redirect_url` TEXT NULL DEFAULT NULL ,
                ADD `bot_text` TEXT NULL DEFAULT NULL
            "
        );

        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }
}