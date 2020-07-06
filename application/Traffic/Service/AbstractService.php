<?php
namespace Traffic\Service;

class AbstractService
{
    protected static $_instances = [];

    /**
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(static::$_instances[$className])) {
            static::$_instances[$className] = new $className();
        }
        return static::$_instances[$className];
    }

    public static function reset()
    {
        $className = get_called_class();
        static::$_instances[$className] = null;
    }
}