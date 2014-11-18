<?php
namespace P\lib\framework\core\utils;
use P\lib\framework\core\system as system;
use P\lib\framework\core\system\interfaces as interfaces;


/**
 *
 * Generic class for handling controller's rewriting rules
 *
 * Note this is a early version of what may be a router.
 *
 * @author johan
 * @version : 0.1a
 */
class Route implements interfaces\isSingleton
{
	static $_oInstance;
	static $_bCreated;
	
	private function __construct()
	{
		
	}
	
	/**
	 * @see P_Core_Interfaces_isSingleton::getInstance()
	 */
	public function getInstance()
	{
		if (!self::$_bCreated)
			self::$_oInstance = new Route();
			
		self::$_bCreated = true;
		
		return self::$_oInstance;
	}
	
	
	/**
	 *
	 * Take a Url and convert it into a valid rewrited String
	 * @param Url $poUrl
	 */
	public static function setRoute(Url $poUrl)
	{
            $sController            = $poUrl->getParam(CONTROLLER);
            $sController            = system\PathFinder::tableToShortname($sController);
            $sUrl                   = false;
            
            $oController = system\ClassManager::getInstance($sController, system\ClassManager::CONTROLLER_URL);
            
            if (is_object($oController) && $oController instanceof interfaces\HaveSmartUrl)
            {
                $sUrl = $oController->getRewritePath($poUrl);
            }

            if ($sUrl) return $sUrl;
            
            $sAction            = $poUrl->getParam(ACTION);
            $sQueryString	= $poUrl->getQueryString();

            $sUrl = '/'.$sController.'/'.$sAction.'/?'.$sQueryString;

            return substr($sUrl, 0, (strlen($sUrl) - 1));

            return "EchecRewrite";
	}
}