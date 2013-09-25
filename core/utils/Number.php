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
        $nNumber = str_replace(',', '.', $psNumber);
        
        return (float) round($nNumber, $pnRound);
    }
    
    
    public static function money($pnNumber, $psDevise='&euro;')
    {
        if (empty($pnNumber)) return '';
        
        $sString = number_format(self::toFloat($pnNumber),  2, ',', ' ');
        
        if (strlen(trim($psDevise)) > 0)
            $sString .= ' '.$psDevise;
        
        return $sString;
    }
}

