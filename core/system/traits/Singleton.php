<?php
namespace P\lib\framework\core\system\traits;

use P\lib\framework\core\utils\Debug;

trait Singleton
{
    static $oInstance;


    public static function getInstance()
    {
        if (!(self::$oInstance instanceof self))
        {
            self::$oInstance = new self;
        }

        return self::$oInstance;
    }
}