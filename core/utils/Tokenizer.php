<?php
namespace P\lib\framework\core\utils;
/**
 *
 * This class is used for Array to Json converting issues, such as Integer, js code
 * or other script structures escaped as String by the default json_encode behaviour
 *
 * Tokenizer must be called after json_encode have been executed
 *
 * @author johan
 *
 */
class Tokenizer
{
	public static $_asTokens = array();
	
	/**
	 * Stock the data into an associative array wich the key is a hash of
	 * the passed value
	 *
	 * You can specify a namespace for improved performance when you use
	 * the Tokenizer within many concurential data processors
	 *
	 * @param String $psString
	 * @param String $psNameSpace
	 */
	public static function tokenize($psString, $psNameSpace='default')
	{
		$sHash = md5($psString);
		
		self::$_asTokens[$psNameSpace][$sHash] = $psString;
		
		return $sHash;
	}
	
	
	/**
	 *
	 * replace the hashed content given by self::Tokenize with
	 * the right - voluntary non escaped values
	 *
	 * @param String $psString
	 * @param String $psNamespace
	 */
	public static function replace($psString, $psNamespace='default')
	{
		$asSearch 	= array();
		$asReplace 	= array();
		
		foreach (self::$_asTokens[$psNamespace] as $sHash => $sString)
		{
			$asSearch[] 	= '"'.$sHash.'"';
			$asReplace[] 	= $sString;
		}
		
		return str_replace($asSearch, $asReplace, $psString);
	}
}
