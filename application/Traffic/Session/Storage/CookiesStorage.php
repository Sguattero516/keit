<?php
namespace Traffic\Session\Storage;

use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\RawClick;
use Traffic\Cookies\Service\CookiesService;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;
use Traffic\Session\SessionEntry;

class CookiesStorage implements StorageInterface
{
    /**
     * @var SessionEntry
     */
    private $_ipSessionEntry;

    public static function getCookieName()
    {
        return substr(md5(SALT), 2, 5);
    }

    public function __construct(ServerRequest $serverRequest)
    {
        $data = CookiesService::instance()->decodeAndGet($serverRequest, static::getCookieName());
        $this->_ipSessionEntry = SessionEntry::restore($data);
    }


    public function save(ServerRequest $serverRequest, Response $response, RawClick $rawClick, Campaign $campaign, BaseStream $stream = NULL)
    {
        if ($rawClick->alwaysEmptyCookies()) {
            return [$serverRequest, $response];
        }

        $this->_ipSessionEntry->touch($rawClick, $campaign, $stream);

        $data = $this->_ipSessionEntry->getData();
        return CookiesService::instance()->encodeAndSet($serverRequest, $response, static::getCookieName(), $data);
    }

    public function isUniqueForCampaign(RawClick $rawClick, Campaign $campaign)
    {
        if ($rawClick->alwaysEmptyCookies()) {
            return true;
        }
        return $this->_ipSessionEntry->isUniqueForCampaign($rawClick, $campaign);
    }

    public function isUniqueForStream(RawClick $rawClick, Campaign $campaign, BaseStream $stream)
    {
        if ($rawClick->alwaysEmptyCookies()) {
            return true;
        }
        return $this->_ipSessionEntry->isUniqueForStream($rawClick, $campaign, $stream);
    }

    public function isUniqueGlobal(RawClick $rawClick, Campaign $campaign)
    {
        if ($rawClick->alwaysEmptyCookies()) {
            return true;
        }
        return $this->_ipSessionEntry->isUniqueGlobal($rawClick, $campaign);
    }
}