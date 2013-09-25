<?php

namespace P\lib\framework\core\utils;

use P\tag;

class Http
{
	static  $_oHttp;
	static  $_bCreated;
        static  $_custom = array();
        static  $_protocol = false;
	
	private function __construct()
	{
		
	}
	
	/**
	 * If the singleton is cloned, this method prevents any problem by halting the program
	 */
	private function __clone()
	{
		trigger_error(__CLASS__.'deja instanci�e (singleton', E_USER_ERROR);
	}
        
        
        public static function set($psName, $psValue)
	{
            self::$_custom[$psName] = $psValue;
        }
	
        
	/**
	 * Returns the instance of the singleton. It create it if it doesn't already exists
	 */
	public static function getInstance()
	{
		if (!self::$_bCreated)
		{
			self::$_oHttp 		= new Http();
			self::$_bCreated 	= true;
		}
		return self::$_oHttp;
	}
	
	
        public static function exist($psParam)
        {
            return (isset($_REQUEST[$psParam]));
        }
        
        
	/**
	 * Returns the param stored in the $_REQUEST global
	 *
	 * @param String $psParam
	 * @param String $psDefault
	 */
	public static function getParam($psParam, $psDefault='')
	{
		if (isset($_REQUEST[$psParam]))
                    return $_REQUEST[$psParam];
                elseif(isset(self::$_custom[$psParam]))
                    return self::$_custom[$psParam];
                
		return $psDefault;
	}
	

	/**
	 *  Returns the $_GET array
	 */
	public function get()
	{
            return array_merge($_GET, self::$_custom);
	}
	
	
	/**
	 * Checks if the form is posted
	 */
	public static function isPosted()
	{
	    if (isset($_POST) && !empty($_POST))
	        return true;
	        
	    return false;
	}
	
	
	/**
	 * Handle the redirection to the $psUrl
	 *
	 * @param String $psUrl
	 */
	public static function redirect($psUrl, $pnStatus=0)
	{
            if ($pnStatus == 301)
                header("Status: 301 Moved Permanently", false, 301);
            
	    header('Location: '.$psUrl);
	    die();
	}
	
	

	/**
	 *  Returns the $_POST array
	 */
	public function post()
	{
		return $_POST;
	}
	
	
	/**
	 * Displays a 503 error page
	 */
	public static function forbidden()
	{
            $oTheme = \P\lib\framework\themes\ThemeManager::load();
            
            return $oTheme->display('http_status/403.tpl.php');
	}
	
	
	/**
	 * Displays a 404 error page
	 */
	public static function error404()
	{
            $oTheme = \P\lib\framework\themes\ThemeManager::load();
            
            return $oTheme->display('http_status/404.tpl.php');
	}
        
        
        
        public static function getUserIP()
        {
            if (isset($_SERVER["REMOTE_ADDR"]))
            { 
                return $_SERVER["REMOTE_ADDR"]; 
            } 
            elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            { 
                return $_SERVER["HTTP_X_FORWARDED_FOR"]; 
            } 
            elseif (isset($_SERVER["HTTP_CLIENT_IP"]))    
            { 
                return $_SERVER["HTTP_CLIENT_IP"]; 
            }
            
            return 0;
        }
        
        
        public static function checkUrl($url)
        {
            $url_data = parse_url ($url);
            
            if (!$url_data) return false;

            $errno  = '';
            $errstr = '';
            $fp     = 0;

            $fp = fsockopen($url_data['host'], 80, $errno, $errstr, 30);

            if ($fp === 0) return false;
            
            $path ='';
            if  (isset( $url_data['path'])) 
                $path .=  $url_data['path'];
            
            if  (isset( $url_data['query'])) 
                $path .=  '?' .$url_data['query'];

            $out    = "GET /$path HTTP/1.1\r\n";
            $out    .= "Host: {$url_data['host']}\r\n";
            $out    .= "Connection: Close\r\n\r\n";

            fwrite($fp, $out);
            $content = fgets($fp);
            $code = trim(substr($content, 9, 4)); //get http code
            
            fclose($fp);
            
            // if http code is 2xx or 3xx url should work
            return  ($code[0] == 2 || $code[0] == 3) ? true : false;
        }
        
        
        public static function isUrl($psString)
        {
            return substr($psString, 0, 7) == 'http://' || substr($psString, 0, 8) == 'https://';
        }

        
        
        public static function getProtocol()
        {
            if (!self::$_protocol)
            {
                $bSSL = \P\lib\framework\core\system\Settings::getParam('ssl', 'enable', false);
                
                if ($bSSL)
                    self::$_protocol = 'https';
                else 
                    self::$_protocol = 'http';
                
            }
            
            return self::$_protocol;
        }
        
}