<?php
namespace P\lib\framework\core\utils;
/**
 * Utility static class for basic String manipulation
 *
 * @author johan
 *
 */
class Number
{
    
    public static function toFloat($psNumber, $pnRound=2)
    {
        $nNumber = trim(str_replace(array(',', ' '), array('.', ''), $psNumber));
        
//        Debug::e($psNumber.' ==  '.$nNumber);
//        Debug::e($psNumber.' ==  '.(float) $nNumber.' (float');
        
        return (float) round($nNumber, $pnRound);
    }
    
    
    public static function toFloatFloor($psNumber, $pnRound=2)
    {
        $nNumber = str_replace(array(',', ' '), array('.', ''), $psNumber);
        
        return (float) round($nNumber, $pnRound, PHP_ROUND_HALF_UP);
    }
    
    
    public static function money($pnNumber, $psDevise='&euro;')
    {
        if (empty($pnNumber)) return '00,00 '.$psDevise;
        
        $sString = number_format(self::toFloatFloor($pnNumber),  2, ',', ' ');
        
        if (strlen(trim($psDevise)) > 0)
            $sString .= ' '.$psDevise;
        
        return $sString;
    }
}

