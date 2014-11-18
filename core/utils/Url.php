<?php
namespace P\lib\framework\core\utils;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;

/**
 *
 * Internal handling of Urls.
 * This class is intended as a front door to all callable actions
 * of the application framework.
 *
 * For chaining calls purpose, all method return $this
 *
 * @author Johan
 *
 */
class Url
{
	private $_asParams 	= array();
	private $_debug 	= false;
	private $_simple 	= false;
	static $_simpleOn	= false;
	static $_short		= false;
        public $render           = true;


        /**
	 * The constructor build the Url from the 3 main args.
	 * If nothing is specified, the url built is the current Url
	 * If false is specified as arg1, the Url Object will be truncate
	 *
	 * @param Mixed $psArg01
	 * @param Mixed $psArg02
	 * @param Mixed $psArg03
	 */
	public function __construct($psArg01='', $psArg02='', $psArg03='')
	{
            if (self::$_simpleOn)
                    $this->simple();

            // Exception : on a psArg01 == false
            if (is_bool($psArg01) && !$psArg01)
            {
                    return $this;
            }
            elseif (is_bool($psArg01) && $psArg01)
            {
                    $this->getUrlFromCurrent();
                    return $this;
            }

            $bEmptyArg01 = empty($psArg01);
            $bEmptyArg02 = empty($psArg02);
            $bEmptyArg03 = empty($psArg03);

            // Si aucun argument, on construit l'url actuelle
            if (($bEmptyArg01 && $bEmptyArg02 && $bEmptyArg03))
            {
                    $this->getUrlFromCurrent();
            }
            // sinon on construit l'url avec les paramètres donnés
            else
            {
                    $this->set($psArg01, $psArg02, $psArg03);
            }

            return $this;
	}
	
	
	/**
	 * Set a whole configuration of the Url
	 * $pasArgs is a set of key => values translated to &key=value in the Url
	 *
	 * @param String $psController
	 * @param String $psAction
	 * @param Array $pasArgs
	 */
	public function set($psController, $psAction, $pasArgs)
	{
		if (!empty($psController))
		{
                   // $this->setParam(CONTROLLER, system\PathFinder::tableToController($psController));
                    $this->setParam(CONTROLLER, $psController);
		}
		else
		{
                    $sController = Http::getInstance()->getParam(CONTROLLER, '');

                    if (!empty($sController))
                        $this->setParam(CONTROLLER, $sController);
		}
		
		if (!empty($psAction))
			$this->setParam(ACTION, $psAction);
		else
			$this->setParam(ACTION, 'index');
				
		if (is_array($pasArgs))
			$this->setParams($pasArgs);
			
		return $this;
	}
	
	
	/**
	 * Build the url with the current $_GET parameters
	 */
	public function getUrlFromCurrent()
	{
		$this->setParams(utils\Http::getInstance()->get());
		
		return $this;
	}
	
	
	/**
	 * Take an associative array and traslate it in key = value
	 *
	 * @param unknown_type $pasArray
	 */
	public function setParams($pasArray)
	{
		foreach ($pasArray as $sParam => $sValue)
		{
			$this->setParam($sParam, $sValue);
		}
		
		return $this;
	}
	
	
	/**
	 * takes a param name and its value
	 *
	 * @param String $psParam
	 * @param String $psValue
	 */
	public function setParam($psParam, $psValue)
	{
		if (empty($psParam))
			trigger_error('psParam must not be truncate', E_USER_ERROR);
			
		$this->_asParams[$psParam] = $psValue;
		
		return $this;
	}
        
        
        public function removeParam($psParam)
        {
            unset($this->_asParams[$psParam]);
            
            return $this;
        }
	
	
	/**
	 * Return the specified value of the params if it exists
	 *
	 * @param String $psParam
	 * @param String $psValue
	 */
	public function getParam($psParam, $psDefaultValue='')
	{
		if (isset($this->_asParams[$psParam]))
			return $this->_asParams[$psParam];
			
		return $psDefaultValue;
	}
	
	
	/**
	 *
	 * Return the query string of the url.
	 * $psNotIn is an array for specifying ignored parameters
	 *
	 * @param Array $psNotIn
	 */
	public function getQueryString($psNotIn=array(CONTROLLER, ACTION, PATH))
	{
		$sQueryString = '';
		foreach ($this->_asParams as $sParam => $sValue)
		{
			if (!in_array($sParam, $psNotIn))
			{
				$sQueryString .= $sParam.'='.$sValue.'&';
			}
		}
		
		return $sQueryString;
	}
	
	
	/**
	 *
	 * Internal method for accessing $this->getUrl()
	 */
	public function __toString()
	{
		return $this->getUrl();
	}
	
	
	/**
	 *
	 * Enable the debuging of Url processing
	 */
	public function debug()
	{
		$this->_debug = true;
	}
	
	
	/**
	 *
	 * Renders the url. Can call the P_Core_Utils_Route Class
	 * for rewriting such as Component specific rewrite Class
	 */
	public function getUrl()
	{
            if ($this->render && (bool) system\Settings::getParam('environement', 'smart_url') && !$this->_simple)
            {
                $sUrl = substr(system\PathFinder::getBaseHref(), 0, -1).str_replace(substr(system\PathFinder::getBaseHref(), 0, -1), '', Route::setRoute($this, !self::$_short));
            }
            else
            {
                $sUrl = system\PathFinder::getBaseHref().'index.php?';

                foreach ($this->_asParams as $sParam => $sValue)
                {
                    if ($sParam == CONTROLLER)
                    {
                        //$sValue = system\PathFinder::getClassShortName($sValue);
                    }

                    $sUrl .= $sParam.'='.$sValue.'&';
                }

                if (count($this->_asParams) > 0 && $sUrl{(strlen($sUrl) - 1)} == '&')
                    $sUrl = substr ($sUrl, 0, -1);
            }


            return $sUrl;
	}
	
	
	/**
	 * Remove thepecified parameter
	 *
	 * @param String $psParam
	 */
	public function remove($psParam)
	{
		if (isset($this->_asParam[$psParam]))
			unset($this->_asParam[$psParam]);
			
		return $this;
	}
	
	
	/**
	 * Taks a string URL and translate it in objet parameters
	 *
	 * @param String $psString
	 */
	public static function fromString($psString)
	{
		if (!preg_match('/^index(.*)/', $psString))
			return $psString;
			
		$asUrl = explode('?', $psString);
		
		$oUrl = new Url();
		
		if (isset($asUrl[1]))
		{
			$asPair = explode('&', $asUrl[1]);
			
			foreach ($asPair as $sPair)
			{
				$asParams = explode('=', $sPair);
				
				if (isset($asParams[1]))
					$oUrl->setParam($asParams[0], $asParams[1]);
			}
		}
		
		return $oUrl;
	}
	
	
	/**
	 * Renders the url without host prefix
	 */
	public function simple()
	{
		$this->_simple = true;
	}
	
	
	/**
	 * Start the rendering of urls without host prefix
	 *
	 * @param Boolean $pbShort
	 */
	public static function simpleStart($pbShort=false)
	{
		self::$_simpleOn = true;
		
		if ($pbShort)
			self::$_short = true;
	}
	
	
	/**
	 * Ends the rendering of urls without host prefix.
	 * It also disable the short ("simple") parameter
	 */
	public static function simpleEnd()
	{
		self::$_simpleOn = false;
		
		self::$_short = false;
	}
	
}
