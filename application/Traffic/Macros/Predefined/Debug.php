<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Debug extends AbstractClickMacro
{
    public function process(BaseStream $stream, RawClick $rawClick)
    {
        $uri = $this->getServerRequest()->getUri();
        $output = [
            'headers' => $this->getServerRequest()->getHeaders(),
            'server_params' => $this->getServerRequest()->getServerParams(),
            'click' => $rawClick->serialize(),
            'method' => $this->getServerRequest()->getMethod(),
            'uri' => [
                'scheme' => $uri->getScheme(),
                'host' => $uri->getHost(),
                'path' => $uri->getPath(),
                'port' => $uri->getPort(),
                'query' => $uri->getQuery(),
                'user_info' => $uri->getUserInfo(),
                'fragment' => $uri->getFragment(),
            ],
            'url' => (string) $uri
        ];
        return json_encode($output, JSON_PRETTY_PRINT);
    }
}