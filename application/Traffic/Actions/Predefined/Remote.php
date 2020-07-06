<?php
namespace Traffic\Actions\Predefined;

use Traffic\Actions\AbstractAction;
use Traffic\Cache\Cache;

class Remote extends AbstractAction
{
    protected $_weight = 130;

    protected $_ttl = 60; // seconds. How much time to cache every link.

    private static $_stubs = [];

    protected function _execute()
    {
        $this->_executeInContext();
    }

    protected function _executeDefault()
    {
        $url = $this->_getRemoteUrl($this->getActionPayload());
        $this->setDestinationInfo($url);
        $this->redirect($url);
    }

    protected function _executeForFrame()
    {
        $url = $this->_getRemoteUrl($this->getActionPayload());
        $this->setDestinationInfo($url);

        $this->setContent('
        <script type="application/javascript">
            function process() {
                top.location = "' . $url . '";
            }

            window.onerror = process;

            if (top.location.href != window.location.href) {
                process()
            }
        </script>');
    }

    protected function _executeForScript()
    {
        $url = $this->_getRemoteUrl($this->getActionPayload());
        $this->setDestinationInfo($url);

        $this->setContent('
            function process() {
                window.location = "' . $url . '";
            }

            window.onerror = process;

            process();
        ');
    }

    protected function _getRemoteUrl($from)
    {
        $filename = $this->_fileName($from);
        if (is_file($filename) && time() - filemtime($filename) < $this->_ttl) {
            $url = trim(@file_get_contents($filename));
        } else {

            $url = trim(strip_tags($this->_request($from)));
            if ($url) {
                file_put_contents($filename, $url);
            }
        }

        if ($url && !strstr($url, '://')) {
            $url = $this->_appendParams($url, $from);
        }

        return $url;
    }

    private function _request($url)
    {
        if (isset(static::$_stubs[$url])) {
            return static::$_stubs[$url];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, html_entity_decode($url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'REMOTE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        return curl_exec($ch);
    }

    protected function _fileName($url)
    {
        return ROOT . Cache::DEFAULT_CACHE_DIR . '/' . md5($url) . '.link';
    }

    protected function _appendParams($actualUrl, $url)
    {
        if (!$actualUrl) {
            return '';
        }
        $url = parse_url($url);
        parse_str(@$url['query'], $queryParams1);


        $actualUrl = parse_url($actualUrl);
        parse_str(@$actualUrl['query'], $queryParams2);

        if (!isset($actualUrl['host']) && isset($actualUrl['path'])) {
            $actualUrl['host'] = $actualUrl['path'];
            $actualUrl['path'] = '/';
        }

        if (!isset($actualUrl['scheme'])) {
            $actualUrl['scheme'] = 'http';
        }

        $actualUrl['query'] = http_build_query(array_merge($queryParams1, $queryParams2));
        $newUrl = $actualUrl['scheme'] . '://';
        $newUrl .= $actualUrl['host'];

        if (isset($actualUrl['port'])) {
            $newUrl .= ':' . $actualUrl['port'];
        }

        $newUrl .= $actualUrl['path'];

        if (isset($actualUrl['query'])) {
            $newUrl .= '?' . $actualUrl['query'];
        }

        return $newUrl;
    }

    public static function stub($url, $content)
    {
        static::$_stubs[$url] = $content;
    }
}