<?php
namespace Traffic\GeoDb;

class IpInfoType
{
    const COUNTRY = 'country';
    const REGION = 'region';
    const CITY = 'city';
    const CITY_RU = 'city_ru';
    const ISP = 'isp';
    const PROXY_TYPE = 'proxy_type';
    const BOT_TYPE = 'bot_type';
    const CONNECTION_TYPE = 'connection_type';
    const OPERATOR = 'operator';

    public static function all()
    {
        return [self::COUNTRY, self::REGION, self::CITY, self::CITY_RU, self::ISP, self::PROXY_TYPE, self::BOT_TYPE, self::CONNECTION_TYPE, self::OPERATOR];
    }
}
