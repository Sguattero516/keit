<?php
class Migration_20190509073714_ConvertLpTokens extends Migration
{
    const DESCRIPTION_RU = 'Конвертация старых LP-токенов в новый формат';

    const DESCRIPTION_EN = 'Convert old LP-tokens to new format';

    public static function up()
    {
        if (!\Traffic\Redis\Service\RedisStorageService::instance()->isEnabled()) {
            return ;
        }
        if (!\Traffic\Redis\Service\RedisStorageService::instance()->isRedisAlive()) {
            return ;
        }
            
        $compression = \Core\Application\Application::instance()->isCompressionEnabled();
        $newStorage = new \Traffic\LpToken\Storage\RedisStorage($compression);

        $keys = \Traffic\Redis\Service\RedisStorageService::instance()->keys('*LANDING_OFFER*');

        foreach ($keys as $key) {
            preg_match('/\[(.*?)\]/', $key, $result);
            $tokenName = $result[1];
            $value = \Traffic\Redis\Service\RedisStorageService::instance()->getOriginalClient()->get($key);
            $ttl = \Traffic\Redis\Service\RedisStorageService::instance()->getOriginalClient()->ttl($key);
            $newStorage->set($tokenName, unserialize($value), $ttl);
        }
    }
}