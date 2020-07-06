<?php
namespace Traffic\Device\Service;

use Component\GeoDb\Ip2Location\Ip2LocationDb3Lite;
use Component\GeoDb\Ip2Location\Ip2LocationDb4;
use Core\Db\DataService;
use Traffic\GeoDb\IpInfoType;
use Traffic\GeoDb\Repository\GeoDbRepository;
use Traffic\GeoDb\Service\GeoDbService;
use Traffic\Logging\Service\LoggerService;
use Traffic\Service\AbstractService;

class IpInfoService extends AbstractService
{
    /**
     * @param $ip string
     * @return array
     * @throws
     */
    public function getIpInfo($ip)
    {
        $result = [];
        $dbFields = $this->getDbSettings();
        foreach ($dbFields as $dbId => $fields) {
            $result = array_merge($result, $this->getDbInfo($ip, $dbId, $fields));
        }
        return $result;
    }

    /**
     * Return setting for Geo Databases
     * Return array
     * [
     *     ...
     *     'dbid' => ['f1', 'f2', 'f3']
     *     ...
     * ]
     *  where fn is fields that detected from database with 'dbid'
     * @return array
     */
    public function getDbSettings()
    {
        $settings = GeoDbService::instance()->settings();
        $dbFields = [];
        foreach ($settings as $dataType => $dbId) {
            if (!empty($dbId)) {
               if (!isset($dbFields[$dbId])) {
                    $dbFields[$dbId] = [];
                }
                $dbFields[$dbId][] = $dataType;
            }
        }
        return $dbFields;
    }

    /**
     * Detect fields from geo database
     * @param string $ip - IP for detect
     * @param string $dbId - ID for geo database
     * @param array $fields - list of fields
     * @return array
     */
    public function getDbInfo($ip, $dbId, $fields)
    {
        $result = [];
        $db = GeoDbRepository::instance()->getDb($dbId);
        $info = GeoDbService::instance()->info($db, $ip);
        if (empty($info)) {
            LoggerService::instance()->error("Db {$dbId} returns non array result");
        }
        foreach ($fields as $f) {
            if (isset($info[$f])) {
                $result[$f] = $info[$f];
            } else {
                $result[$f] = null;
            }
        }
        return $result;
    }
}