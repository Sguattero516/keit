<?php
class Migration_76 extends Migration {

    const DESCRIPTION_RU = 'Переименование index_referrers.conversions в leads';

    const DESCRIPTION_EN = 'Rename index_referrers.conversions to leads';

    public static function up()
    {
        $sql = 'ALTER TABLE  ' . self::getDb()->getPrefix() . 'index_referrers
         CHANGE `conversions` `leads` INT( 10 ) UNSIGNED NULL DEFAULT \'0\'';
        self::silentExecute($sql);
    }
}