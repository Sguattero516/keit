<?php
namespace Traffic\Macros\Predefined;

use Traffic\Macros\AbstractClickMacro;
use Traffic\Model\BaseStream;
use Traffic\RawClick;

class CurrentDomain extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        $uri = $this->getServerRequest()->getUri();
        return $uri->getScheme() . '://' . $uri->getHost();
    }
}