<?php
namespace P\lib\framework\core\utils;
/**
 *
 * Static class for debug perpective
 * @author johan
 *
 */
class Debug
{
        static $log;
        
    
	/**
	 * Enhanced version of the built in php function print_r
	 *
	 * @param Mixed $psString
	 * @param String $psTitle
	 */
	static function var_dump($psString, $psTitle='')
	{
		$sOutput = '';
		
		$sOutput .= '<strong>'.$psTitle.'</strong><br />';
		$sOutput .= '<pre>';

		// Output buffering befor var_dump call
		ob_start();
		var_dump($psString, true);
		$sOutput .= ob_get_contents();
		ob_end_clean();
		
		$sOutput .= '</pre>';
		$sOutput .= '<br />';
		
		echo $sOutput;
	}
	
	
	static function dump($psString, $psTitle='', $pbTemplate=false, $pbReturn=false)
	{
            $oTheme = \P\lib\framework\themes\ThemeManager::load();
            $oTheme->debug_temp          = array(
                'title'     => $psTitle,
                'content'   => $psString,
            );
            
            $sOutput = $oTheme->display('layout/debug.tpl.php');

            if ($pbReturn)
                return $sOutput;
            
            elseif ($pbTemplate) 
            {
                    $oTheme->debugMessage($sOutput);
            }    
            else
                echo $sOutput;	
	}
        
        
        static function e($psString, $title='')
	{
            return self::dump($psString, $title, false);
        }

        
        static function d($psString)
        {
            if (\P\apps\employee\Employee::isJohan())
                return self::dump($psString, '', false);
        }
        
        
	static function error($psString, $psTitle='')
	{
            $sOutput = '
                <div class="alert alert-error">
                <a class="close" data-dismiss="alert" href="#">×</a>
                    <h4 class="alert-heading">'.$psTitle.'!</h4>
                    '.$psString.'
                </div>';
            
            return $sOutput;
	}
        
        
        static function returnDump($psString, $psTitle='')
	{
            return self::dump($psString, $psTitle, false, true);
	}
	
	
	/**
	 * Same as self::dump() but it encode html entities for better visualization
	 *
	 * @param Mixed $psString
	 * @param String $psTitle
	 */
	static function html($psString, $psTitle='')
	{
		echo self::dump(htmlentities($psString), $psTitle);
	}
	
	
	/**
	 *Trigger an Error and stock the program with the backtrace stack of calls
	 *
	 * @param String $psMessage
	 * @param String $psErrorType
	 */
	static function trigger_error($psMessage, $psErrorType=E_USER_ERROR)
	{
		throw new \ErrorException($psMessage, $psErrorType);
		die();
	}
        
        
        public static function log($psMessage, $pbDisplay=true)
        {
            if (self::$log === true)
            {
                $sTempFolder = \P\lib\framework\core\system\PathFinder::getTempDir();

                file_put_contents($sTempFolder.'debug_log.txt', $psMessage."\n", FILE_APPEND);

                if ($pbDisplay) { self::dump ($psMessage); }
            }
        }
        
        
        public static function logDump($psMessage)
        {
            $sTempFolder = \P\lib\framework\core\system\PathFinder::getTempDir();
            
            file_put_contents($sTempFolder.'debug_log.txt', self::dump($psMessage, '', true, true)."\n\n", FILE_APPEND);
        }
}