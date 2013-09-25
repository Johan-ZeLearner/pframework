<?php

namespace P\lib\framework\core\system;

class Session
{
	/**
	 * Setter
	 *
	 * @param String $psParam
	 * @param Mixed $psValue
	 */
	public static function set($psParam, $psValue)
	{
            $_SESSION[$psParam] = $psValue;
            self::setCookie($psParam, $psValue, (3600 * 24 * 30));
	}
        
	
	/**
	 * Getter for the session variables
	 *
	 * @param String $psParam
	 * @param String $psDefaultValue
	 */
	public static function get($psParam, $psDefaultValue='')
	{
            if (isset($_SESSION[$psParam]))
                return $_SESSION[$psParam];
            elseif (isset($_COOKIE[$psParam]))
                return $_COOKIE[$psParam];

            return $psDefaultValue;
	}
        
        
        public static function destroy($psName)
        {
            if (isset($_SESSION[$psName]))
                unset($_SESSION[$psName]);
            
            if (isset($_COOKIE[$psName]))
            {
               self::destroyCookie($psName);
            }
        }
        
        
        public static function setCookie($psName, $psValue, $pnTTL)
        {
            setcookie($psName, $psValue, time() + $pnTTL, '/'); 
        }
        
        
        public static function destroyCookie($psName)
        {
            setcookie($psName, '', 1, '/'); 
        }
        
        
        public static function issetCookie($psName)
        {
            if (isset($_SESSION[$psName]))
                return true;
            
            if (isset($_COOKIE[$psName]))
                return true;
            
            return false;
        }
}

