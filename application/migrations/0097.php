<?php
class Migration_97 extends Migration {

    const DESCRIPTION_RU = 'Увеличение размера url в keitaro_campaign_postbacks';

    const DESCRIPTION_EN = 'Increase size of postback url in keitaro_campaign_postbacks';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE  `{$prefix}campaign_postbacks` CHANGE  `url`  `url` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
        self::execute($sql);
    }
}