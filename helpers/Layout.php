<?php
namespace P\lib\framework\helpers;
use P\lib\framework\core\system as system;

class Layout
{
    static $customFile;
    
    public static function getLayoutPath()
    {
        $sPath = system\Settings::getParam('layout', 'path');
        
        if (!empty(self::$customFile))
            return $sPath.self::$customFile;
            
        return $sPath.system\Settings::getParam('layout', 'default');
    }
    
    
    public static function setFile($psFile)
    {
        if (empty($psFile)) throw new \ErrorException('$psFile is truncate');
        
        self::$customFile = $psFile;
    }
    
    
    public static function isAjax()
    {
    	if (self::$customFile == 'ajax.php') return true;
    	
    	return false;
    }
}
