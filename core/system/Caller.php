<?php
namespace P\lib\framework\core\system;

class Caller 
{
    public static function getCaller()
    {
        $asDebug = debug_backtrace();
        
        if (isset($asDebug[2]))
        {
            return $asDebug[2];
        }

        throw new \ErrorException('Echec du debug_backtrace');
    }
}

?>
