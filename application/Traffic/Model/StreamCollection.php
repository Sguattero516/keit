<?php
namespace Traffic\Model;

class StreamCollection
{
    private $_streams;

    public function __construct($streams)
    {
        $this->_streams = $streams;
    }

    public function byType($type)
    {
        if (!isset($this->_streams[$type])) {
            return [];
        }

        return $this->_streams[$type];
    }
}