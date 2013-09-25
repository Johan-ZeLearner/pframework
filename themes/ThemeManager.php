<?php
namespace P\lib\framework\themes;
use P\lib\framework\core\system as system;

class ThemeManager extends \P\lib\extra\Savant3\Savant3
{
    static $_self;
    static $_sTheme         = '';
    static $_sLayout        = '';
    static $_hook;
    static $path_set        = false;
    static $is_ajax         = false;
    static $is_simple       = false;
    static $is_simple_noscript       = false;
    static $debugMessage    = '';
    
    public static function load()
    {
        if (!self::$_self instanceof ThemeManager)
            self::$_self = new ThemeManager ();
        
        return self::$_self;
    }
    
    
    public static function setTheme($psTheme='default')
    {
        if (empty(self::$_sTheme) || self::$_sTheme == 'default')
            self::$_sTheme = $psTheme;
    }
    
    
    public static function debugMessage($psMessage)
    {
        self::$debugMessage .= $psMessage;
    }
    
    
    protected function _getPath()
    {
        $sPublic = '';
        if ((bool) system\Settings::getParam('path', 'public', true))
            $sPublic = 'public/';
                
        return system\PathFinder::getRootDir().$sPublic.'themes/'.self::$_sTheme.'/';
    }

    
    public function getPath()
    {
        return $this->_getPath();
    }

    
    public function display($tpl = null, $pbFullPath=false)
    {
        $sPath = '';
        if (!$pbFullPath)
            $sPath = $this->_getPath();
        
        $sTemplate = $sPath.$tpl;
        
        if (!is_readable($sTemplate))
            die('File don\'t exists : '.$sTemplate);
        
        
        if (!self::$path_set)
        {
            $this->setPath ('template', $sPath);
            self::$path_set = true;
        }
        
        $this->debug = self::$debugMessage;
        
        return parent::getOutput($sTemplate);
    }
    
    
    public static function debugMessageDisplay()
    {
        return self::$debugMessage;
    }
    
    
    public static function haveTheme()
    {
        return !empty(self::$_sTheme);
    }
    
    
    public static function setHookContent($psName, $psContent)
    {
        self::$_hook[$psName][] = $psContent;
    }
    
    
    public function getHook($psName)
    {
        $sOutput = '';
        
        try
        {
            foreach (self::$_hook[$psName] as $sContent)
            {
                $sOutput .= $sContent;
            }
        }
        catch (\Exception $error_string)
        {
            \P\lib\framework\core\utils\Debug::dump($error_string);
            die();
        }
        
        return $sOutput;
    }
    
    
    public static function displayLayout()
    {
        if (self::$is_ajax)
            return self::load()->display('layout/ajax.tpl.php');
        if (self::$is_simple)
            return self::load()->display('layout/layout_simple.tpl.php');
        if (self::$is_simple_noscript)
            return self::load()->display('layout/layout_simple_noscript.tpl.php');
        else
            return self::load()->display('layout.tpl.php');
    }
    
    
    public static function setVar($psName, $psValue)
    {
        $oSavant = self::load();
        
        $oSavant->$psName = $psValue;
    }
    
    
    public static function setAjax()
    {
        self::$is_ajax = true;
    }
    
    
    public static function setSimple()
    {
        self::$is_simple = true;
    }
    
    
    public static function setSimpleNoScript()
    {
        self::$is_simple_noscript = true;
    }
    
    
    public static function useCss($psFile)
    {
        
        \P\lib\framework\helpers\CssManager::addFile('themes/'.self::$_sTheme.'/css/'.$psFile, '');
    }
    
    
    public static function useJS($psFile)
    {
        $sPath = '/themes/'.self::$_sTheme.'/js/'.$psFile;
        \P\lib\framework\helpers\JSManager::addFile($sPath);
    }
}
?>
