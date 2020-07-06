<?php
namespace Traffic\Session\Service;

use Core\Db\Db;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\RawClick;
use Traffic\Redis\Service\RedisStorageService;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;
use Traffic\Service\AbstractService;
use Traffic\Session\Storage\CookiesStorage;
use Traffic\Session\Storage\MysqlStorage;
use Traffic\Session\Storage\RedisStorage;
use Traffic\Session\Storage\StorageInterface;

class UniquenessSessionService extends AbstractService
{
    private $_storages;

    /**
     * @param ServerRequest $serverRequest
     * @param Response $response
     * @param RawClick $rawClick
     * @param Campaign $campaign
     * @param BaseStream|NULL $stream
     * @return ServerRequest[]|Response[]
     */
    public function save(ServerRequest $serverRequest, Response $response, RawClick $rawClick, Campaign $campaign, BaseStream $stream = NULL)
    {
        foreach ($this->getStorages($serverRequest, $campaign->isCookiesForUniquenessUsing()) as $storage) {
            list($serverRequest, $response) = $storage->save($serverRequest, $response, $rawClick, $campaign, $stream);
        }
        return [$serverRequest, $response];
    }

    public function isUniqueForCampaign(ServerRequest $serverRequest, RawClick $rawClick, Campaign $campaign)
    {
        foreach ($this->getStorages($serverRequest, $campaign->isCookiesForUniquenessUsing()) as $storage) {
            if (!$storage->isUniqueForCampaign($rawClick, $campaign)) {
                return false;
            }
        }
        return true;
    }

    public function isUniqueForStream(ServerRequest $serverRequest, RawClick $rawClick, Campaign $campaign, BaseStream $stream)
    {
        foreach ($this->getStorages($serverRequest, $campaign->isCookiesForUniquenessUsing()) as $storage) {
            if (!$storage->isUniqueForStream($rawClick, $campaign, $stream)) {
                return false;
            }
        }
        return true;
    }

    public function isUniqueGlobal(ServerRequest $serverRequest, RawClick $visitor, Campaign $campaign)
    {
        foreach ($this->getStorages($serverRequest, $campaign->isCookiesForUniquenessUsing()) as $method) {
            if (!$method->isUniqueGlobal($visitor, $campaign)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $serverRequest ServerRequest
     * @param $enableCookiesStorage
     * @return StorageInterface[]
     */
    public function getStorages(ServerRequest $serverRequest, $enableCookiesStorage)
    {
        $this->_storages = [];

        if ($enableCookiesStorage) {
            $this->_storages = [new CookiesStorage($serverRequest)];
        }

        if (RedisStorageService::instance()->draftStorageUsesRedis()) {
            $this->_storages[] = new RedisStorage($serverRequest);
        } else {
            if (Db::instance()->isEnabled()) {
                $this->_storages[] = new MysqlStorage($serverRequest);
            }
        }
        return $this->_storages;
    }
}