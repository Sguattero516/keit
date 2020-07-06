<?php
namespace Traffic\Logging;

use Traffic\Profiler\Profiler;
use Traffic\RawClick;
use Traffic\Request\ServerRequest;

class TrafficLogEntry
{
    private $_lines = [];

    /**
     * @var Profiler
     */
    protected $_profiler;

    public function startProfiling()
    {
        $this->_profiler = new Profiler();
    }

    public function stopProfiling($msg)
    {
        $this->add($this->_profiler->step($msg));
    }

    public function flush()
    {
        $lines = $this->getLines();
        $this->_lines = [];
        return $lines;
    }

    public function getLines()
    {
        return $this->_lines;
    }

    public function add($msg)
    {
        $this->_lines[] = $msg;
    }

    /**
     * @param callable $msgLazy
     */
    public function addLazy($msgLazy)
    {
        $this->add($msgLazy());
    }

    public function logRawClick(RawClick $rawClick, ServerRequest $request)
    {
        $info = [];
        $info['SubID'] = $rawClick->getSubId();
        $info['IP'] =  $rawClick->getIpString();
        $info['UserAgent'] = $rawClick->getUserAgent();

        $info['Language'] = $rawClick->getLanguage();

        if (!$rawClick->getLanguage() && $request->getHeaderLine('Accept-Language')) {
            $info['Accept-Language'] = $request->getHeaderLine('Accept-Language');
        }

        if ($rawClick->getSource()) {
            $info['Source'] = $rawClick->getSource();
        }

        if ($rawClick->getXRequestedWith()) {
            $info['X-Requested-With'] = $rawClick->getXRequestedWith();
        }

        if ($rawClick->getReferrer()) {
            $info['Referer'] = $rawClick->getReferrer();
        }

        if ($rawClick->getKeyword()) {
            $info['Keyword'] = $rawClick->getKeyword();
        }

        if ($rawClick->getSeReferrer()) {
            $info['SeReferer'] = $rawClick->getSeReferrer();
        }

        if ($rawClick->getSearchEngine()) {
            $info['SE'] = $rawClick->getSearchEngine();
        }

        $info['Country'] = $rawClick->getCountry();
        $info['Region'] = $rawClick->getRegion();
        $info['City'] = $rawClick->getCity();
        $info['Carrier'] = $rawClick->getIsp();
        $info['Connection Type'] = $rawClick->getConnectionType();
        $info['Isp'] = $rawClick->getIsp();

        $info['OS'] = $rawClick->getOs();
        $info['Browser'] = $rawClick->getBrowser();
        $info['Device model'] = $rawClick->getDeviceModel();
        $info['Device type'] = $rawClick->getDeviceType();
        $info['Is mobile'] = ($rawClick->isMobile() ? 'yes' : 'no');

        $info['Is bot'] = ($rawClick->isBot() ? 'yes' : 'no');
        $info['Is proxy'] = ($rawClick->isUsingProxy() ? 'yes' : 'no');


        if ($rawClick->getCost()) {
            $info['Cost'] = $rawClick->getCost();
        }

        $info['Is unique in campaign'] = ($rawClick->isUniqueCampaign() ? 'yes' : 'no');
        $info['Is unique in stream'] = ($rawClick->isUniqueStream() ? 'yes' : 'no');
        $this->add("User info: " . json_encode($info));
    }
}