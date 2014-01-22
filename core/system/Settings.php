<?php

namespace P\lib\framework\core\system;

class Settings
{
	static $_settings;
	
	/**
	 * Get the parametter stored from the config.ini file
	 *
	 * @param String $psSection
	 * @param String $psParam
	 * @param String $psDefault
	 */
	public static function getParam($psSection, $psParam, $psDefault='')
	{
            self::checkSettings();

            if (isset(self::$_settings[$psSection][$psParam]))
                return self::$_settings[$psSection][$psParam];
            else
                return $psDefault;
	}
	
	
	/**
	 * Set a custom param
	 *
	 * @param String $psSection
	 * @param String $psParam
	 * @param String $psValue
	 */
	public static function setParam($psSection, $psParam, $psValue)
	{
	    self::$_settings[$psSection][$psParam] = $psValue;
	}
	
	
	/**
	 * Check and load the config.ini file
	 *
	 * @throws exception
	 */
	public static function checkSettings()
	{
            if (!is_array(self::$_settings))
            {
                if (!self::$_settings = parse_ini_file('../config/config.ini', true))
                    throw new \Exception('Unable to open ' . '../config/config.ini' . '.');
            }
	}
        
        
        public static function loadFile($path)
        {
            if (is_readable($path))
            {
                $asNew = parse_ini_file($path, true);

                self::$_settings = array_merge(self::$_settings, $asNew);
            }
        }
	
	
	public static function configureStartup()
	{
            // if the user is not logged, we try to unset the HOST_URL cache
            if (!isset($_SESSION['login']))
            {
                if (isset($_SESSION['HOST_URL']))
                    unset($_SESSION['HOST_URL']);	// empty the HOST_URL cache
            }

            if (isset($_SESSION['HOST_URL']))
                $sHost = $_SESSION['HOST_URL'];
            else
            {
                $sHttp = self::getCompleteURL();
                $sHost = substr($sHttp, 0, (strlen($sHttp) - 1));

                Session::set('HOST_URL', $sHost);
            }
	}
	

	/**
	 * Compile the current URL for rewritting purposes
	 */
	public static function getCompleteURL()
	{
		$sHost 			= $_SERVER['HTTP_HOST'];
		$sScriptName 	= $_SERVER['SCRIPT_NAME'];
	
		$sScriptName 	= str_replace(array('/index.php', '/server.php'), '', $sScriptName);
	
		return 'http://'.$sHost.$sScriptName.'/';
	}

}
