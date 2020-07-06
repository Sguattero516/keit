<?php
namespace Traffic\Macros\Predefined;

use Traffic\Model\BaseStream;
use Traffic\RawClick;
use Traffic\Macros\AbstractClickMacro;

class Keyword extends AbstractClickMacro
{
    private $_defaultCharset;

    const UTF8 = 'utf-8';

    const CP1251 = 'cp1251';

    public function __construct($defaultCharset)
    {
        $this->_defaultCharset = $defaultCharset;
    }

    public function process(BaseStream $stream, RawClick $rawClick, $charset = null)
    {
        if (empty($charset)) {
            $charset = $this->_defaultCharset;
        }

        $keyword = $rawClick->getKeyword();
        if (!empty($charset) && $charset != self::UTF8) {
            $keyword = iconv('utf-8', $charset, $keyword);
        }
        return $keyword ?? '';
    }
}