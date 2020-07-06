<?php
use Traffic\Redis\Service\RedisStorageService;

class Migration_29 extends Migration {

    const DESCRIPTION_RU = 'Перевод данных в Redis в новый формат';

    const DESCRIPTION_EN = 'Move data in Redis to new lists';

    public static function up()
    {
        if (!RedisStorageService::instance()->isEnabled()) {
            return;
        }

        while ($date = RedisStorageService::instance()->RPOP('ip_keys_dates')) {
            while ($key = RedisStorageService::instance()->RPOP('ip_keys::' . $date)) {
                list($d, $ip) = explode('::', $key);
                $data = RedisStorageService::instance()->GET($key);
                RedisStorageService::instance()->HSET($date, $ip, $data);
                RedisStorageService::instance()->DEL($key);
                RedisStorageService::instance()->SADD('ip_dates_set', $date);
            }
        }
    }
}