<?php
namespace Traffic\Actions;

use Component\StreamFilters\CheckFilters;
use Traffic\Cookies\Service\CookiesService;
use Traffic\Logging\TrafficLogEntry;
use Traffic\Model\BaseStream;
use Traffic\Model\Campaign;
use Traffic\Model\Stream;
use Traffic\RawClick;
use Traffic\Request\ServerRequest;
use Traffic\Response\Response;

class StreamRotator
{
    private $_cookieName;
    private $_bindEnabled;
    private $_maxAge;
    private $_logEntry;
    private $_rawClick;

    const COOKIE_NAME = 's';

    public function __construct(Campaign $campaign, TrafficLogEntry $logEntry, RawClick $rawClick)
    {
        $this->_cookieName = $this->_getCookieName($campaign);
        $this->_bindEnabled = $campaign->isBindVisitorsEnabled();
        $this->_maxAge = $this->_cookieTtlToMaxAge($campaign->getCookiesTtl());
        $this->_rawClick = $rawClick;
        $this->_logEntry = $logEntry;
    }

    public function chooseByPosition(ServerRequest $serverRequest, $streams)
    {
        $i = 0;
        while ($i <= count($streams) - 1) {
            $stream = $streams[$i];
            $checkFilter = new CheckFilters($serverRequest, $stream, $this->_rawClick, $this->_logEntry);
            if ($checkFilter->isPass()) {
                $this->_logEntry->add('Passed. Checking the schema and action.');
                return $stream;
            }
            $i++;
        }
        return null;
    }

    public function chooseByWeight(ServerRequest $serverRequest, $streams)
    {
        if ($this->_bindEnabled) {
            $stream = $this->_findBoundStream($serverRequest, $streams);
            if (!empty($stream)) {
                $msg = 'Visitor is bound by cookie to stream #' . $stream->getId();
                $ip = CookiesService::instance()->decodeAndGet($serverRequest, $this->_cookieName . 'ip');
                if (!empty($ip)) {
                    $msg .= " (previous IP was {$ip})";
                }
                $this->_logEntry->add($msg);
            }
        }

        if (empty($stream)) {
            $stream = $this->_rollDice($serverRequest, $streams);
        }

        return $stream;
    }

    /**
     * @param $serverRequest ServerRequest
     * @param $streams Stream[]
     * @return null
     */
    protected function _rollDice(ServerRequest $serverRequest, $streams)
    {
        if (!count($streams)) {
            return null;
        }

        shuffle($streams);

        $totalWeight = 0;
        foreach ($streams as $i => $stream) {
            $weight = $stream->getWeight();
            $totalWeight += $weight;
        }

        if ($totalWeight == 0) {
            return null;
        }
        $rand = mt_rand(0, $totalWeight - 1); // intervals are [x;y) type

        $currentWeight = 0;
        $selected  = 0;
        foreach ($streams as $i => $stream) {
            $weight = $stream->getWeight();
            // we don't include right border
            if ($currentWeight <= $rand && $rand < $currentWeight + $weight) {
                break;
            }
            $currentWeight += $weight;
            $selected++;
        }


        $stream = $streams[$selected];
        $checkFilter = new CheckFilters($serverRequest, $stream, $this->_rawClick, $this->_logEntry);

        if ($checkFilter->isPass()) {
            return $stream;
        } else {
            unset($streams[$selected]);
            return $this->_rollDice($serverRequest, $streams);
        }
    }

    /**
     * @param $serverRequest ServerRequest
     * @param $streams Stream[]
     * @return Stream
     */
    private function _findBoundStream(ServerRequest $serverRequest, $streams)
    {
        $streamId = CookiesService::instance()->decodeAndGet($serverRequest, $this->_cookieName);
        if (!empty($streamId)) {
            foreach ($streams as $stream) {
                if ($stream->getId() == $streamId) {
                    return $stream;
                }
            }
        }
        return null;
    }

    public function bindToStream(ServerRequest $serverRequest, Response $response, BaseStream $stream)
    {
        list($serverRequest, $response) = CookiesService::instance()->encodeAndSet($serverRequest, $response, $this->_cookieName, $stream->getId(), $this->_maxAge);
        return [$serverRequest, $response];
    }

    public function saveIp(ServerRequest $serverRequest, Response $response, $ip)
    {
        list($serverRequest, $response) = CookiesService::instance()->encodeAndSet($serverRequest, $response, $this->_cookieName . 'ip', $ip, $this->_maxAge);
        return [$serverRequest, $response];
    }

    private function _cookieTtlToMaxAge($ttl)
    {
        return time() + 60 * 60 * $ttl;
    }

    private function _getCookieName(Campaign $campaign)
    {
        return substr(md5(SALT), 5, 8) . self::COOKIE_NAME . $campaign->getId();
    }
}