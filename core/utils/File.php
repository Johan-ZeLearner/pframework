<?php
namespace P\lib\framework\core\utils;

class File
{
    public static function save($asFile, $finalName, $type='image')
    {
        if (!isset($asFile['tmp_name']) && is_string($asFile)) { return self::saveStatic($asFile, $finalName, $type); }
        
        $finalPath = \P\lib\framework\core\system\PathFinder::getRoot().'public/'.self::getDir($type);
        
        if (!is_dir($finalPath))
        {
            mkdir($finalPath, 0777);
        }
        
        if (move_uploaded_file($asFile['tmp_name'], $finalPath.$finalName))
        {
            return self::getDir($type).$finalName;
        }
        
        return false;
    }
    
    
    
    public static function getDir($type)
    {
        switch($type)
        {
            case 'image':
            case 'picture':
                return 'img/';
                
            default:
                return 'files/';
        }
    }
    
    
    public static function saveUrl($url, $finalName, $type='image')
    {
        $tmp = tempnam(\P\lib\framework\core\system\PathFinder::getTempDir(), 'img');
        
        $file = file_get_contents($url);
        
        file_put_contents($tmp, $file);
        
        unset($file);
        
        $success = self::saveStatic($tmp, $finalName, $type);
        
        unlink($tmp);
        
        return $success;
    }
    
    
    public static function saveStatic($sourcePath, $finalName, $type='image')
    {
        if (!is_file($sourcePath)) { return false; }
        
        $finalPath = \P\lib\framework\core\system\PathFinder::getRoot().'public/'.self::getDir($type);
        
        if (!is_dir($finalPath))
        {
            mkdir($finalPath, 0777);
        }
        
        if (file_put_contents($finalPath.$finalName, file_get_contents($sourcePath)))
        {
            return self::getDir($type).$finalName;
        }
        
        return false;
        
    }
}
?>
