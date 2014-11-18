<?php
namespace P\lib\framework\core\utils;

use P\override\Context;

/**
 * Utility static class for basic String manipulation
 *
 * @author johan
 *
 */
class Number
{

    public static function toFloat($psNumber, $pnRound = 2)
    {
        $nNumber = trim(str_replace(array(
                    ',',
                    ' '
                ), array(
                    '.',
                    ''
                ), $psNumber));

//        Debug::e($psNumber.' ==  '.$nNumber);
//        Debug::e($psNumber.' ==  '.(float) $nNumber.' (float');

        if ($pnRound > 0)
        {
            return (float)round($nNumber, $pnRound);
        }
        else
        {
            return (float)$nNumber;
        }
    }


    public static function toFloatFloor($psNumber, $pnRound = 2)
    {
        $nNumber = str_replace(array(
                ',',
                ' '
            ), array(
                '.',
                ''
            ), $psNumber);

        return (float)round($nNumber, $pnRound, PHP_ROUND_HALF_UP);
    }


    public static function money($pnNumber, $psDevise = '&euro;', $toTTC = false)
    {
        if (empty($pnNumber))
        {
            return '00,00 ' . $psDevise;
        }

        if ($toTTC)
        {
            $pnNumber = $pnNumber * (1 + Context::getTaxRate());
        }

        $sString = number_format(self::toFloatFloor($pnNumber), 2, ',', ' ');

        if (strlen(trim($psDevise)) > 0)
        {
            $sString .= ' ' . $psDevise;
        }

        return $sString;
    }


    public static function convertMemory($size)
    {
        $unit = array(
            'b',
            'kb',
            'mb',
            'gb',
            'tb',
            'pb'
        );

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}

