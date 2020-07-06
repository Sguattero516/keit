<?php
class Migration_20190122134734_RemoveEnumFromMode extends Migration
{
    const DESCRIPTION_RU = 'Убрать enum с filter.mode';

    const DESCRIPTION_EN = 'Remove enum from filter.mode';

    public static function up()
    {
        $prefix = self::getPrefix();
        $sql = "ALTER TABLE {$prefix}stream_filters CHANGE COLUMN mode mode varchar(40) NOT NULL;";
        self::execute($sql);
    }
}