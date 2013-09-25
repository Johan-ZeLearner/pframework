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
    
    public static function toDatabase($psValue, $psSeparator='/')
    {
        if ($psValue == '0000-00-00' || $psValue == '0000-00-00 00:00:00') return '';
        
        if (empty($psValue)) return '0000-00-00 00:00:00';
        
        $asDate 	= explode(' ', $psValue);
        $sFinalDate     = '0000-00-00 00:00:00';
        
        if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})/', $asDate[0], $asMatches))
        {
            $sFinalDate = $asMatches[3].'-'.$asMatches[2].'-'.$asMatches[1];
            
            if (isset($asDate[1]) && $asDate[1] == 'à')
            {
                if (isset($asDate[2]))
                {
                    if(preg_match('/^([0-9]{2):([0-9]{2)/', $asDate[2], $asMatches))
                    {
                        $sFinalDate .= ' '.$asMatches[1].':'.$asMatches[2].':00';
                    }
                }
            }
        }
        elseif(preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $psValue, $asMatches))
        {
            $sFinalDate = $psValue;
            
            if (isset($asDate[1]))
            {
                if(preg_match('/^([0-9]{2):([0-9]{2):([0-9]{2)/', $asDate[1], $asMatches))
                {
                    $sFinalDate .= ' '.$asMatches[1].':'.$asMatches[2].':'.$asMatches[3];
                }
            }
        }
            
        return $sFinalDate;
    }
    
    
    public function format($psDateUs, $psOutput=self::frFR, $pbShort=true)
    {
        $nTime = strtotime($psDateUs);
        
        if ($nTime > 0)
        {
            switch ($psOutput)
            {
                case self::frFR:
                    if ($pbShort)
                        return date('d/m/Y');
                    else
                        return date('d/m/Y H:i:s');
                    break;
                
                
                case self::enUS:
                    if ($pbShort)
                        return date('Y-m-d');
                    else
                        return date('Y-m-d H:i:s');
                    break;
            }
        }
    }
    
    
    public static function implode($psFragment01, $psFragment02, $psSeparator='/')
    {
        $psFragment01 = self::toDatabase($psFragment01, $psSeparator);
        
        return $psFragment01.' '.$psFragment02;
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
        $sDate = self::toDatabase($psValue);
        
        if (empty($sDate))
            return '';

        if ($pbSimple)
            return date(System\Settings::getParam('format', 'date', 'd/m/Y'), strtotime($sDate));
        else
            return date(System\Settings::getParam('format', 'datetime', 'd/m/Y à H:i'), strtotime($sDate));
    }
    
    
    /**
     * Similar to self::toDisplay() but returns
     * the current date if $pdDate is empty
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
    public static function now()
    {
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
    	if (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})/i', $psDate) || preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $psDate))
    	{
    		$asDate = explode(' ',$psDate);
    		
    		if (isset($asDate[1])) return $asDate[1];
    	}
    
    	return '';
    }
    
    
    /**
     * Check wether the date $psDate is valid or empty
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
    		case '00/00/0000':
    		case '00/00/0000 00:00:00':
    		case '00/00/00':
    		case '00/00/00 00:00:00':
    		case '01/01/1970':
    		case '01/01/1970 00:00:00':
    		case '':
    			return true;
    			break;
    			
    		default:
    			return false;
    			break;
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
}
