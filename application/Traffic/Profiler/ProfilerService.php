<?php
namespace Traffic\Profiler;

use Traffic\Service\AbstractService;

class ProfilerService extends AbstractService
{
    private $_profiler;

    public function __construct()
    {
        $this->_profiler = new Profiler();
    }

    public function getProfiler()
    {
        return $this->_profiler;
    }

    public function step($label, $requests = null)
    {
        return $this->_profiler->step($label, $requests);
    }

    public function enableXhprof()
    {
        $this->_profiler->enableXhprof();
    }

    public function disableXhprof()
    {
        $this->_profiler->disableXhprof();
    }

    public function resetState()
    {
        $this->_profiler->resetState();
    }
}