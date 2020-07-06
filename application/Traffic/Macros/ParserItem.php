<?php
namespace Traffic\Macros;

class ParserItem
{
    private $_name;
    private $_originalString;
    /**
     * @var bool
     */
    private $_rawMode;
    /**
     * @var array
     */
    private $_arguments;

    /**
     * @param $name string Имя макроса
     * @param $originalString string Оригинальная подстрока
     * @param $rawMode boolean Если true - вставка произойдет без url-кодирования
     * @param $arguments array Параметры переданные в макросе
     */
    public function __construct($name, $originalString, $rawMode, $arguments)
    {
        $this->_name = $name;
        $this->_originalString = $originalString;
        $this->_rawMode = $rawMode;
        $this->_arguments = $arguments;
    }

    public function name()
    {
        return $this->_name;
    }

    public function originalString()
    {
        return $this->_originalString;
    }

    public function rawMode()
    {
        return $this->_rawMode;
    }

    public function arguments()
    {
        return $this->_arguments;
    }

    public function setRawMode($mode)
    {
        $this->_rawMode = $mode;
    }
}