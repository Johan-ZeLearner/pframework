<?php
require 'PathFinder.php';

function __autoload($psClassName)
{
//    echo 'chargement de '.$psClassName.'<br />';
    
    // exception override Object
//    if ($psClassName == 'P\override\Object')
//    {
//        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
//        if (!is_file($sPath))
//        {
//            $psClassName = 'P\lib\framework\core\system\abstractClasses\Object';
//        }
//    }
//    elseif ($psClassName == 'P\override\Controller')
//    {
//        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
//        if (!is_file($sPath))
//        {
//            $psClassName = 'P\lib\framework\core\system\abstractClasses\Controller';
//        }
//    }

    if (preg_match('/^whoops/i', $psClassName))
    {
        $psClassName = 'P\lib\extra\\'.$psClassName;
    }
    
    try
    {
        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
    }
    catch (\ErrorException $e)
    {
        echo 'AUTOLOAD ERROR =====> '.$e->getMessage();
        die();
    }

    if (is_file($sPath))
    {
        require $sPath;
    }
    elseif(preg_match('/imagine/i', $psClassName))
    {

        $sPath = P\lib\framework\core\system\PathFinder::getRootDir().str_replace('\\', '/', $psClassName).'.php';
        if (file_exists($sPath)) {
            include $sPath;
        }
    }
    else
    {
        //\P\lib\framework\core\system\Debug::dump($sPath);
        throw new \ErrorException('AUTOLOAD ERROR ==> The class '.$psClassName.' doesn\'t exists');
    }
    
//    echo '===============================> '.$psClassName.'<br />';
}