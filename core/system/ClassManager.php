<?php
namespace P\lib\framework\core\system;
use P\lib\framework\core\system\abstractClasses\Controller;
use P\lib\framework\core\utils as utils;

class ClassManager
{
    public static $aoInstances      = array();
    const CONTROLLER_CORE   = 'controller_core';
    const CONTROLLER_URL    = 'controller_url';
    /**
        * returns the instance of the called class if it exists
        *
        * @param String $psClassName
        * @return Controller
    */
    public static function getInstance($psClassName, $psType=self::CONTROLLER_CORE, $pbDisplayInfos=false)
    {
        if (!(bool) preg_match('/\\\/i', $psClassName))
            $psClassName = PathFinder::tableToController($psClassName);
        
        if ($psType == self::CONTROLLER_URL)
            $psClassName .= '\Url\Url';


//        utils\Debug::e($psClassName);

        if (!key_exists($psClassName, self::$aoInstances))
        {
            try{
                self::$aoInstances[$psClassName] = $psClassName::getInstance($psClassName);
            }
            catch(\Exception $e)
            {
                if ($psType == self::CONTROLLER_URL)
                    return null;

                if ($pbDisplayInfos)
                {
                    utils\Debug::dump($e);
                    utils\Debug::dump(self::$aoInstances);
                    utils\Debug::e('debug ------- ');
                    utils\Debug::e('Chemin recherché : '.$psClassName);
                    utils\Debug::log('Chemin recherché : '.$psClassName);
                    utils\Debug::dump($psClassName.' not loaded - '.$e->getMessage() );
                    utils\Debug::log($psClassName.' not loaded - '.$e->getMessage() );
                    utils\Debug::e($psClassName.' not loaded - '.$e->getMessage() );
                    }

                    return false;
                }
        }
        
//        echo '------------------------ <br />';
//        foreach(self::$aoInstances as $key => $class)
//        {
//            echo  $key.'<br />';
//        }


        return self::$aoInstances[$psClassName];
    }

    
    public static function getSubClass($oController, $path, $name)
    {
        if (!empty($path))
            $name = '\\'.$name;
        
        $finalClassName = $oController->getNamespace().$path.$name;
        
        return self::getInstance($finalClassName);
    }
    

    /**
        * Shortcut from table name to controller
        * 
        * @param String $psTable
        */
    public static function fromTable($psTable)
    {
        return self::getInstance(PathFinder::tableToController($psTable));
    }
}
