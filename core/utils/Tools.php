<?php
namespace P\lib\framework\core\utils;
use P\lib\framework\core\system as System;

class Tools
{
    public static function isEmpty($psValue)
    {
        return empty($psValue);
    }
    
    
    public static function file_get_contents($psFile)
    {
        $rCURL = curl_init();

        curl_setopt($rCURL, CURLOPT_URL, $psFile);
        curl_setopt($rCURL, CURLOPT_HEADER, 0);
        curl_setopt($rCURL, CURLOPT_RETURNTRANSFER, 1);

        $aData = curl_exec($rCURL);

        curl_close($rCURL);

        return $aData;
    }
}