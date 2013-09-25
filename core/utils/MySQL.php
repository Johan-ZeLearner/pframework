<?php
namespace P\lib\framework\core\utils;
/**
 * Utility static class for basic String manipulation
 *
 * @author johan
 *
 */
class MySQL
{
    public static function escapeArray($pasArray)
    {
        $asFinal = array();
        
        foreach ($pasArray as $sValue)
        {
            if (!preg_match('/^\"(.*)\"$/', $sValue))
                    $asFinal[] = '"'.$sValue.'"';
            else 
                $asFinal[] = $sValue;
        }
        
        return $asFinal;
    }
}

