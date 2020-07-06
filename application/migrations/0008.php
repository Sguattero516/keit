<?php


class Migration_8 extends Migration {

    const DESCRIPTION_RU = 'Хранение переходов в памяти';

    const DESCRIPTION_EN = 'Use memory storage for visitors';

    public static function up()
    {
        $db = self::getDb();
        $sqls = array(
            "INSERT IGNORE INTO `{$db->getPrefix()}settings` (`key`, `value`) VALUES ('ips_migrated_at', '".time()."');",
        );

        foreach ($sqls as $sql) {
            $db->execute($sql);
        }
    }
}