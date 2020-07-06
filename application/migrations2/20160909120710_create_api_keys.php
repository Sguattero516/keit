<?php
use Component\Users\Model\User;
use Traffic\Service\SettingsService;

class Migration_20160909120710_CreateApiKeys extends Migration {

    const DESCRIPTION_RU = 'Создание таблицы api_keys';

    const DESCRIPTION_EN = 'Create table api_keys';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql
            = "CREATE TABLE IF NOT EXISTS `{$prefix}api_keys` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `key` VARCHAR( 100 ) NOT NULL ,
            `user_id` INT(11) NOT NULL,
            `datetime` DATETIME NOT NULL,
            INDEX ( `key` )
            ) ENGINE = InnoDB;
        ";
        self::silentExecute($sql);

        // TODO do not use SettingsService and extract to another migration
        $sql = "SELECT `value` FROM `{$prefix}settings` WHERE `key` = 'api_key'";
        $row = self::getDb()->getRow($sql);
        $key = $row['value'];

        if ($key) {
            $sql = "SELECT COUNT(*) as cnt FROM `{$prefix}api_keys`";
            $row = self::getDb()->getRow($sql);
            if ($row['cnt'] == 0) {
                $sql
                    = "INSERT INTO `{$prefix}api_keys`(`key`, `user_id`, `datetime`) 
                    SELECT '{$key}', id, NOW()
                    FROM `{$prefix}users`
                    WHERE type = 'admin'
                    ORDER BY ID ASC
                    LIMIT 1;";
                self::execute($sql);
            }
        }
    }
}