<?php
namespace P\lib\framework\core\utils;

class Validate
{
    public static function email($sEmail)
    {
        return preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/', $sEmail);
    }
    
    
    
    public static function siret($sSiret)
    {
        return preg_match('/^[0-9]{3} ?[0-9]{3} ?[0-9]{3} ?[0-9]{5}$/', $sSiret);
    }
}