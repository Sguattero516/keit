<?php
namespace Traffic\Device\Service;

use Traffic\Service\AbstractService;

class ReferrerParserService extends AbstractService
{
    public function parse($referrer)
    {
        if (empty($referrer)) {
            return null;
        }

        foreach ($this->_getData() as $key => $info) {
            if (@stripos($referrer, $info['host'])) {
                return $this->_parse($info, $referrer);
            }
        }
    }
    
    private function _parse($info, $referrer)
    {
        $pattern = "/[&\\?]" . $info['var'] . "=([^&]*)/i";

        $keyword = null;

        if (preg_match($pattern, $referrer, $result)) {
            $keyword = $result[1];
            if (@stripos($referrer, $keyword) === 0) {
                return null;
            }
        }

        if ('UTF-8' != $info['charset']) {
            $keyword = iconv($info['charset'], 'UTF-8', $keyword);
        }

        return $keyword;
    }

    private function _getData()
    {
        return require(ROOT . '/application/Traffic/Device/dictionaries/se.php');
    }
}