<?php
namespace Traffic\Service;

use GuzzleHttp\Psr7\Uri;
use Traffic\Logging\Service\LoggerService;

class UrlService extends AbstractService
{
    public function getBaseUrl(Uri $uri, $strip = 2)
    {
        $result = $uri->getScheme() . '://';
        $result .= $this->stripHostWww($uri);
        if ($uri->getPort() && $uri->getPort() != 80) {
            $result .= ':' . $uri->getPort();
        }
        $result .= $this->getBasePath($uri, $strip);
        return $result;
    }


    public function getBasePath(Uri $uri, $depth = 1)
    {
        $uri = $uri->getPath();
        if ($uri) {
            $uri = preg_replace('/\?.*/', '', $uri);
            $tmp = explode('/', $uri);
            for ($i = 0; $i < $depth; $i++) {
                unset($tmp[count($tmp) - 1]);
            }
            return implode('/', $tmp);
        }
        return null;
    }

    public function stripHostWww(Uri $uri)
    {
        return preg_replace('#^www\.#si', '', $uri->getHost());
    }

    public function getBasePathWithSlash(Uri $uri, $depth = 1)
    {
        $basePath = $this->getBasePath($uri, $depth);
        if (strlen($basePath)) {
            $basePath .= '/';
        }
        return $basePath;
    }

    /**
     * @param $oldUrl string
     * @param $addToQuery string
     * @return string
     */
    public function addParameterToUrl($oldUrl, $addToQuery)
    {
        try {
            $uri = new Uri($oldUrl);
        } catch (\InvalidArgumentException $e) {
            LoggerService::instance()->warning("URI: incorrect offer URL {$oldUrl}");
            return $oldUrl;
        }
        if (in_array($addToQuery[0], ['\\','/'])) {
            $lastChar = substr($uri->getPath(), -1);
            if (in_array($lastChar, ['\\','/'])) {
                $addToQuery = substr($addToQuery, 1);
            }
            $uri = $uri->withPath($uri->getPath() . $addToQuery);
        } else {
            $initialQuery = $uri->getQuery();
            $initialQueryParams = $this->parseStr($initialQuery);
            $paramQueryParams = $this->parseStr($addToQuery);
            $newQueryParams = array_merge($initialQueryParams, $paramQueryParams);
            $newQuery = urldecode(http_build_query($newQueryParams));
            $uri = $uri->withQuery($newQuery);
        }

        $newUrl = (string) $uri;
        $newUrl = str_replace(['%7B', '%7D', '%3A'], ['{', '}', ':'], $newUrl);
        return $newUrl;
    }

    public function filterDoubleSlashes($url)
    {
        return preg_replace('/([^:])(\/{2,})/', '$1/', $url);
    }

    /**
     * @param string $str
     * @return array
     */
    public function parseStr($str)
    {
        $out = [];
        $str = trim(ltrim($str, '&?'));
        if ($str != "") {
            $params = explode("&", $str);
            foreach ($params AS $p) {
                if ($p != "") {
                    $temp = explode("=", $p, 2);
                    $value = isset($temp[1]) ? $temp[1] : '';
                    $value = str_replace(['%7B', '%7D', '%3A'], ['{', '}', ':'], $value);
                    $out[$temp[0]] = $value;
                }
            }
        }
        return $out;
    }
}