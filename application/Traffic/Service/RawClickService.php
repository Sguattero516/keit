<?php
namespace Traffic\Service;

use Traffic\GeoDb\IpInfoType;
use Component\GeoDb\Repository\ConnectionTypesRepository;
use Traffic\Device\Service\IpInfoService;
use Traffic\RawClick;
use Traffic\Redis\Service\RedisStorageService;

class RawClickService extends AbstractService
{
    const INSERT_LIMIT = 1000;
    const LIMIT = 30;

    const SUBIDSEQ = 'SUBIDSEQ';

    public function resolveGeo(RawClick $rawClick)
    {
        if ($rawClick->isGeoResolved() || $rawClick->get('country')) {
            return;
        }
        $rawClick->set('is_geo_resolved', true);
        $info = IpInfoService::instance()->getIpInfo($rawClick->getIpString());
        foreach ($info as $dataType => $value) {
            $rawClick->set($dataType, $value);
        }

        if ($rawClick->getCFCountry()) {
            $rawClick->set(IpInfoType::COUNTRY, $rawClick->getCFCountry());
        }


        if ($rawClick->getOperator() && !$rawClick->getConnectionType()) {
            $rawClick->set(IpInfoType::CONNECTION_TYPE, ConnectionTypesRepository::CELLULAR);
        }
    }

    public function generate($visitorCode)
    {
        if (RedisStorageService::instance()->draftStorageUsesRedis()) {
            $randomness = base_convert(RedisStorageService::instance()->incr(self::SUBIDSEQ), 10, 32);
        } else {
            $randomness = uniqid();
        }

        return substr($visitorCode . $randomness, 0, self::LIMIT);
    }
}