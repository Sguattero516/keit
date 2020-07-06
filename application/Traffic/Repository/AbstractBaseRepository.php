<?php
namespace Traffic\Repository;

abstract class AbstractBaseRepository
{
    protected static $_instances = [];

    /**
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$_instances[$className])) {
            self::$_instances[$className] = new $className;
        }

        return self::$_instances[$className];
    }

    public static function reset()
    {
        $className = get_called_class();
        self::$_instances[$className] = null;
    }   
}