<?php
use Core\Db\Db;

class Migration_20171021203304_MoveListToDbAndRedis extends Migration
{
    const DESCRIPTION_RU = 'Миграция списка ботов в новый формат';

    const DESCRIPTION_EN = 'Migrate bots list to new format';

    public static function up()
    {
        self::moveUserBotListV8ToV9();
    }
}