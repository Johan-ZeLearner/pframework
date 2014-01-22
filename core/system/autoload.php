<?php

function __autoload($psClassName)
{
//    echo 'chargement de '.$psClassName.'<br />';
    
    // exception override Object
    if ($psClassName == 'P\override\Object')
    {
        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
        if (!is_file($sPath))
        {
            $psClassName = 'P\lib\framework\core\system\abstractClasses\Object';
        }
    }
    elseif ($psClassName == 'P\override\Controller')
    {
        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
        if (!is_file($sPath))
        {
            $psClassName = 'P\lib\framework\core\system\abstractClasses\Controller';
        }
    }
    
    try
    {
        $sPath = \P\lib\framework\core\system\PathFinder::classToPath($psClassName);
        
//        echo 'class to path : '.$sPath.'<br />';
        
    }
    catch (\ErrorException $e)
    {
        echo 'AUTOLOAD ERROR =====> '.$e->getMessage();
        die();
    }

    
    if (is_readable($sPath))
    {
//        echo 'chargé : '.$sPath.' <br />';
        require $sPath;
    }
    else
    {
        //\P\lib\framework\core\system\Debug::dump($sPath);
        throw new \ErrorException('AUTOLOAD ERROR ==> The class '.$psClassName.' doesn\'t exists');
    }
    
//    echo '===============================> '.$psClassName.'<br />';
}