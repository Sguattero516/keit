<?php
namespace Traffic\CachedData;

class WarmupScheduler
{
    const FILE_NAME = '/var/cache/need-warmup';

    public static function filePath()
    {
        return ROOT . self::FILE_NAME;
    }

    public static function scheduleWarmup()
    {
        touch(WarmupScheduler::filePath());
    }

    public static function done()
    {
        if (is_file(WarmupScheduler::filePath())) {
            unlink(WarmupScheduler::filePath());
        }
    }

    public static function isScheduled()
    {
        $filePath = static::filePath();
        if (file_exists($filePath)) {

            return true;
        }
        return false;
    }
}