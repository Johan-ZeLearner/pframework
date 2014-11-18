<?php
namespace P\lib\framework\core\system;

/**
 * Controller Provider
 */
class CP
{
    /**
     * @param string $class
     *
     * @return abstractClasses\Controller
     */
    public static function get($class)
    {
        return ClassManager::getInstance($class);
    }


    /**
     * @param $obj
     * @param $path
     * @param $class
     * @deprecated since 1.2
     *
     * @return abstractClasses\Controller
     */
    public static function sub($obj, $path, $class)
    {
        if (is_string($obj))
            $obj = self::get ($obj);
        
        return ClassManager::getSubClass($obj, $path, $class);
    }
}