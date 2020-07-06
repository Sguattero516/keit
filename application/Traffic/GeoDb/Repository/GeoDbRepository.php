<?php

namespace Traffic\GeoDb\Repository;

use Component\GeoDb\Ip2Location\Ip2LocationPx2;
use Traffic\GeoDb\IpInfoType;
use Component\GeoDb\Error\DbError;
use Component\GeoDb\AbstractGeoDb;
use Component\GeoDb\Ip2Location\Ip2LocationDb3Full;
use Component\GeoDb\Ip2Location\Ip2LocationDb3Lite;
use Component\GeoDb\Ip2Location\Ip2LocationDb4;
use Component\GeoDb\Keitaro\KeitaroBotDb2;
use Component\GeoDb\Keitaro\KeitaroCarrierDb;
use Component\GeoDb\Maxmind\MaxmindCityFull;
use Component\GeoDb\Maxmind\MaxmindCityLite;
use Component\GeoDb\Maxmind\MaxmindConnectionType;
use Component\GeoDb\Maxmind\MaxmindCountryFull;
use Component\GeoDb\Maxmind\MaxmindIsp;
use Traffic\GeoDb\Service\GeoDbService;
use Component\GeoDb\Sypex\SypexCityFull;
use Component\GeoDb\Sypex\SypexCityLite;
use Core\Application\Exception\Error;
use Component\GeoDb\Keitaro\InternalUserBotDb;
use Traffic\Repository\AbstractBaseRepository;

class GeoDbRepository extends AbstractBaseRepository
{
    /**
     * @var AbstractGeoDb[]
     */
    private $_dbs = [];

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->addDb(new Ip2LocationDb3Lite());
        $this->addDb(new Ip2LocationDb3Full());
        $this->addDb(new Ip2LocationDb4());
        $this->addDb(new Ip2LocationPx2());
        $this->addDb(new SypexCityLite());
        $this->addDb(new SypexCityFull());
        $this->addDb(new MaxmindCityLite());
        $this->addDb(new MaxmindCityFull());
        $this->addDb(new MaxmindCountryFull());
        $this->addDb(new MaxmindIsp());
        $this->addDb(new MaxmindConnectionType());
        $this->addDb(new KeitaroCarrierDb());
        $this->addDb(new KeitaroBotDb2());
        $this->addDb(new InternalUserBotDb());
    }

    public function all()
    {
        return $this->_dbs;
    }

    public function addDb(AbstractGeoDb $db)
    {
        $this->_dbs[$db->definition()->id()] = $db;
    }


    /**
     * @param $dbs AbstractGeoDb[]
     */
    public function setDbs($dbs)
    {
        $this->_dbs = $dbs;
    }

    public function isAvailable($id)
    {
        return $this->getDb($id)->exists();
    }

    /**
     * @param $id
     * @return AbstractGeoDb
     * @throws Error
     */
    public function getDb($id)
    {
        if (!isset($this->_dbs[$id])) {
            throw new DbError('Unknown geo db "' . $id .'" (available ' . json_encode(array_keys($this->_dbs)) .')');
        }
        return $this->_dbs[$id];
    }

    public function getDbInfo($id)
    {
        if (!isset($this->_dbs[$id])) {
            throw new DbError('Unknown db repository "' . $id .'"');
        }
        return $this->_dbs[$id];
    }

    public function isDataTypeAvailable($dataType)
    {
        foreach ($this->all() as $db) {
            if (in_array($dataType, $db->definition()->dataTypes())) {
                return true;
            }
        }
        return false;
    }

    public function getDbForDataType($dataType)
    {
        $settings = GeoDbService::instance()->settings();
        if (!in_array($dataType, IpInfoType::all())) {
            throw new DbError("Incorrect data type '{$dataType}''");
        }
        if (isset($settings[$dataType])) {
            return $this->getDb($settings[$dataType]);
        }

        return null;
    }
}