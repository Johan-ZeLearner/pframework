<?php

namespace P\lib\framework\core\utils;

class Metrics
{
    const PAGE_WIDTH    = '210'; //en mm
    const PAGE_HEIGHT   = '297'; //en mm
    
    
    public static function percentToMilimetters($value, $fullLength=self::PAGE_WIDTH)
    {
        return $fullLength * ($value / 100).'mm';
    }
}
?>
