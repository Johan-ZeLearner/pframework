<?php
namespace P\lib\framework\core\system;
use P\lib\framework\core\utils as utils;

class PathFinder
{
    static $_baseHref;
    static $rootDir;
    
	public static function classToPath($psClassName)
	{
		if (empty($psClassName)) 
		{
                    throw new \ErrorException('Parameter $psClassName is truncate');
		}
		
		$asPath = explode('\\', $psClassName);
                
               
		if (!empty($asPath))
		{
			if ($asPath[0] == 'P')
			{
				unset($asPath[0]);
			}
			
			return self::getRootDir().implode('/', $asPath).'.php';
		}
		
		return '';
	}
        
        
        public static function getTempDir()
        {
            return self::getRootDir('temp');
        }
        
        
        public static function setRoot($psDir)
        {
            $nCount1 = substr_count($psDir, '/');
            $nCount2 = substr_count($psDir, '\\');
            
            $sSeparator = '\\';
            if ($nCount1 > $nCount2)
                $sSeparator = '/';
            
            $asPath = explode($sSeparator, $psDir);
            
            $nCount = count($asPath);
            unset($asPath[($nCount - 1)]);
            
            $sPath = implode($sSeparator, $asPath).$sSeparator;
            
            self::$rootDir = $sPath;
        }
        
        
        public static function getRootDir($psArgs='')
        {
            $sPath = self::$rootDir;
            
            $nCount1 = substr_count($sPath, '/');
            $nCount2 = substr_count($sPath, '\\');
            
            $sSeparator = '\\';
            if ($nCount1 > $nCount2)
                $sSeparator = '/';

            if (is_array($psArgs) && !empty($psArgs))
            {
                foreach($psArgs as $sItem)
                {
                    $sPath .= $sItem.$sSeparator;
                }
            }
            elseif (!empty($psArgs))
            {
                $sPath .= $psArgs.$sSeparator;
            }
            
            
            return $sPath;
        }
	
	
	/**
	 *
	 * Return the shortname of a class. Usefull for internal Url processing
	 *
	 * @param String $psClassName
	 */
	public static function getClassShortName($psClassName)
	{
            if (preg_match('/\\\\/', $psClassName))
            {
                $asName = explode('\\', $psClassName);
                
                $sClass = $asName[(count($asName) - 1)];
                
                return $sClass;
            }
            else
               return $psClassName;
            
            throw new \ErrorException('The classname specified is not a full qualified name : '.$psClassName);
	}
	
	
	/**
	 * Like classToPath but use the short name to
	 * build the filepath
	 *
	 * ShortName must be prefixed by "sys_" if it is a system component
	 *
	 * @param String $psShortName
	 */
	public static function shortNameToNamespace($psShortName, $psPath='')
	{
            $psShortName = self::tableToShortname($psShortName);
            $sDir = strtolower($psShortName{0}).substr($psShortName, 1);
//            
//            utils\Debug::e('dir : '.$sDir);
//            utils\Debug::e('shortName : '.$psShortName);
//            utils\Debug::e('path : '.$psPath);
            
            if (!empty($psPath))
                $sName = 'P\apps\\'.strtolower($psPath).'\\'.$sDir.'\\'.ucfirst ($psShortName);
            else
                $sName = 'P\apps\\'.$sDir.'\\'.ucfirst ($psShortName);
            
            
//            utils\Debug::e('namespace : '.$sName);
            
            return $sName;
	}
        
	
	/**
	 * Like classToPath but use the short name to
	 * build the filepath
	 *
	 * ShortName must be prefixed by "sys_" if it is a system component
	 *
	 * @param String $psShortName
	 */
	public static function namespaceToTable($psNamespace)
	{
            $asDir = explode('\\', $psNamespace);
            
            
            return strtolower($asDir[(count($asDir) - 1)]);
	}
	
	
	/**
	 * Shortcut to the controller's views path
	 * The full classname must be used
	 *
	 * @param String $psClassName
	 */
	public static function getViewDir($psClassName)
	{
		return self::getFileDir(self::classToPath($psClassName)).'/views';
	}
        
        
        public static function removeTrailingFile($psClassPath, $psSeparator='/')
	{
            $asItem = explode($psSeparator, $psClassPath);
            
            unset($asItem[count($asItem) - 1]);
            
            return implode($psSeparator, $asItem);
            
        }
        
	
	/**
	 * Shortcut to the controller's constants path
	 * The full classname must be used
	 *
	 * @param String $psClassName
	 */
	public static function getConstDir($psClassName)
	{
            return self::removeTrailingFile(self::classToPath($psClassName)).'/constant/';
	}
	
	
	/**
	 * Shortcut to the controller's instructions path
	 * The full classname must be used
	 *
	 * @param String $psClassName
	 */
	public static function getInstructionsDir($psClassName)
	{
            return self::removeTrailingFile(self::classToPath($psClassName)).'/instructions/';
	}
	
	
	/**
	 * Shortcut to the controller's directory path
	 * The classpath of the controller must be used
	 *
	 * @param String $psClassPath
	 */
	public static function getFileDir($psClassPath)
	{
		$asPath = explode('/', $psClassPath);
	
		unset($asPath[(count($asPath) - 1)]);
	
		return implode('/', $asPath);
	}
        
        
        public static function tableToShortname($psTableName)
        {
            if (preg_match('/[A-Z]/', $psTableName))
            {
                if (!preg_match('/(\\|\/)/', $psTableName))
                    return $psTableName;
            }
            
            $sControllerShortName = ucfirst(strtolower($psTableName));
                
            if (preg_match('/(_)+/', $sControllerShortName))
            {
                $asTable = explode('_', $sControllerShortName);

                $sControllerShortName = '';
                foreach ($asTable as $sTable)
                {
                    $sControllerShortName .= ucfirst(strtolower($sTable));
                }
            }
            
            return $sControllerShortName;
        }
	
	
	/**
	 *
	 * Convert the table's name into its theorical well-named controller
	 *
	 * @param String $psTableName
	 * @param String $psPrefix
	 */
	public static function tableToController($psTableName, $psPrefix='P\apps')
	{
            $sControllerShortName = self::tableToShortname($psTableName);

            $sDir = strtolower($sControllerShortName{0}).substr($sControllerShortName, 1);

            return $psPrefix.'\\'.$sDir.'\\'.$sControllerShortName;
	}
	
	
	/**
	 * Convert the DAL name into its theorical well-named parent controller
	 *
	 * @param String $psDal
	 */
	public static function dalToController($psDal)
	{
		return utils\String::substr($psDal, -3, 0, false);
	}
        
        
        public static function getRoot()
        {
            return self::getRootDir();
        }
        
        
        public static function getWithExtension($psFilename, $psExtension)
        {
            return str_replace('.'.$psExtension, '', $psFilename).'.'.$psExtension;
        }
        
        public static function getBaseHref()
        {
            if (empty(self::$_baseHref))
            {
                $sHost = utils\Http::getProtocol().'://'.$_SERVER['SERVER_NAME'];
                $sFolder = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
                
                self::$_baseHref = $sHost.$sFolder;
            }
            
            return self::$_baseHref;
        }
        
        public static function qualified($psName)
        {
            if (preg_match('/\\\\/', $psName))
                return $psName;
            
            $sNamespace = 'P\\apps\\'.strtolower($psName);
            
            return $sNamespace;
        }


    public static function getControllerNamespace($controllerFullPath)
    {
        $parts = explode('\\', $controllerFullPath);

        return str_replace('\\'.$parts[count($parts) - 1], '', $controllerFullPath);
    }
}