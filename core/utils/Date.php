<?php
namespace P\lib\framework\core\utils;
use P\lib\framework\core\system as System;

/**
 * Static class for basic Date manipulation
 * @author johan
 *
 */
class Date
{
    /**
        * It takes a Y-m-d H:i:s or d/m/Y H:i:s date and convert it to a datetime one
        *
        * @param Date $psValue
        * @param Char $psSeparator
        * @return String
     */
    
    const frFR = 'frFR';
    const enUS = 'enUS';
    
    
    public static function toDateDisplay($psValue)
    {
        return self::toDisplay($psValue, true);
    }
    
    
    public static function toDateDatabase($psValue)
    {
        $date = self::toDatabase($psValue, '/', true);
        
        $asDate = explode(' ', $date);
        
        return $asDate[0];
    }
    
    
    public static function toDisplayForm($date)
    {
        return date('d/m/Y H:i:s', self::strToTime($date));
    }
    
    
    public static function frToTime($date)
    {
        if (preg_match('/([0-9]{2}\/[0-9]{2}\/[0-9]{4})([0-9]{2}:[0-9]{2})?(:[0-9]{2})?/', $date, $asReq))
        {
            $asDate = explode('/', $asReq[1]);
            
            $sUsDate = $asDate[2].'-'.$asDate[1].'-'.$asDate[0];
            
            if (isset($asReq[2]))
            {
                $sUsDate .= $asReq[2];
            }
            
            if (isset($asReq[3]))
            {
                $sUsDate .= $asReq[3];
            }
            elseif(isset($asReq[2]))
            {
                $sUsDate .= ':00';
            }
            
            return strtotime($sUsDate);
        }
        
        return 0;
    }
    
    
    public static function strToTime($date)
    {
        // format FR 
        $time = self::frToTime($date);
        
        if ($time > 0)
        {
            return $time;
        }

        return strtotime($date);
    }
    
    
    public static function toDatabase($psValue, $psSeparator='/', $pbSimple=false)
    {
        $time = self::strToTime($psValue);
        
        if ($pbSimple)
        {
            return date('Y-m-d', $time);
        }
                
        return date('Y-m-d H:i:s', $time);
    }

    
    /**
     * Like toDatabase but it return a well formatted display date (see config.ini)
     * By $pbSimple you can specify if you want seconds displayed or not
     *
     * @param Date $psValue
     * @param Boolean $pbSimple
     * @return String
     */
    public static function toDisplay($psValue, $pbSimple=false)
    {
        $time = self::strToTime($psValue);
        
        if ($pbSimple)
            return date(System\Settings::getParam('format', 'date', 'd/m/Y'), $time);
        else
            return date(System\Settings::getParam('format', 'datetime', 'd/m/Y à H:i'), $time);
    }
    
    
    /**
     * Similar to self::toDisplay() but returns
     * the current date if $pdDate is truncate
     *
     * @param Date $pdDate
     * @param Boolean $pbSimple
     * @return String
     */
    public static function orNowDisplay($pdDate='', $pbSimple = true)
    {
    	$sDate = self::toDatabase($pdDate);
    	if (strtotime($sDate) > 0)
    		return self::toDisplay($sDate, $pbSimple);
    	else
    		return self::toDisplay(self::now(), $pbSimple);
    }
    
    
    /**
     * Return the current datetime in the intrnationnal date format
     *
     * @return String
     */
    public static function now($pbToDisplay=false)
    {
        if ($pbToDisplay)
            return date('d/m/Y');
        else
            return date('Y-m-d H:i:s');
    }
    
    
    /**
     * Extract the time of a datetime date
     * Return a String of the pattern  hh:ii:ss
     *
     * @param String $psDate
     * @return String
     */
    public static function extractHour($psDate)
    {
    	return date('H', self::strToTime($psDate));
    }
    
    
    /**
     * Check wether the date $psDate is valid or truncate
     *
     * @param Date $psDate
     * @return Boolean
     */
    public static function isEmpty($psDate)
    {
    	switch(trim($psDate))
    	{
    		case '0000-00-00':
    		case '0000-00-00 00:00:00':
    		case '1970-01-01':
    		case '1970-01-01 00:00:00':
    		case '1970-01-01 23:59:59':
    		case '1970-01-01 01:00:00':
    		case '00/00/0000':
    		case '00/00/0000 00:00:00':
    		case '00/00/00':
    		case '00/00/00 00:00:00':
    		case '01/01/1970':
    		case '01/01/1970 00:00:00':
    		case '':
    			return true;
    			
    		default:
    			return false;
    	}
    }
    
    
    /**
     * Translate a number of seconds in a number of day(s)
     *
     * @param Integer $pnSeconds
     * @return Integer
     */
    public static function secondToDay($pnSeconds)
    {
    	return (int) ($pnSeconds / (24 * 3600));
    }
    
    
    public static function stripSeparators($datetime)
    {
        $time = self::strToTime($datetime);
        
        return date('Ymd', $time);
    }
    
    
    public static function valid($date)
    {
        // format FR 
        if (preg_match('/([0-9]{2}\/[0-9]{2}\/[0-9]{4})( [0-9]{2}:[0-9]{2}:[0-9]{2})?/', $date))
        {
            return !self::isEmpty($date);
        }
        
        // format us
        if (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2})( [0-9]{2}:[0-9]{2}:[0-9]{2})?/', $date))
        {
            return !self::isEmpty($date);
        }
        
        return false;
    }
}
