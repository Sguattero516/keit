<?php
class Migration_20200319131723_AddIndexParentSubIdClickLinks extends Migration
{
    const DESCRIPTION_RU = 'Добавление индекса для ссылок на sub_id';

    const DESCRIPTION_EN = 'Add index for sub_id links';

    public static function up()
    {
        $prefix = self::getDb()->getPrefix();
        $sql = "ALTER TABLE {$prefix}click_links ADD INDEX parent_sub_id(parent_sub_id)";
        self::silentExecute($sql);
    }
}
