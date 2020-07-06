<?php
namespace Traffic\HitLimit\Storage;

class Factory
{
    public static function build($redisAvailable, $avoidMysql)
    {
        if ($redisAvailable) {
            return new RedisStorage();
        }

        if (!$avoidMysql) {
            return new DbStorage();
        }

        return new NullStorage();
    }
}