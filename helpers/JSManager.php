<?php

namespace P\lib\framework\helpers;
use P\lib\framework\core\system as system;

class JSManager
{
	static $_instructions 	= array();
	static $_jquery;
	static $_jqueryUi;
	static $_publicPath;
	static $_paths 			= array();
	
	
	/**
	 * Defini le nom (et donc la version) de jQuery à utiliser
	 *
	 * @param String $psFile
	 */
	public static function setJQueryFile($psFile)
	{
		self::$_jquery = $psFile;
	}

	
	/**
	 * Defini le nom (et donc la version) de jQuery UI à utiliser
	 *
	 * @param String $psFile
	 */
	public static function setJQueryUiFile($psFile)
	{
		self::$_jqueryUi = $psFile;
	}
	
	
	/**
	 * Active jQuery
	 *
	 * @param Boolean $pbUiEnable
	 */
	public static function jQueryEnable($pbUiEnable=false)
	{
		if (empty(self::$_jquery)) throw new \ErrorException('You must specify jquery file name');
		
		self::addFile(self::$_jquery);
		
		if ($pbUiEnable)
		{
			if (empty(self::$_jquery)) throw new \ErrorException('You must specify jquery UI file name');
			self::addFile(self::$_jqueryUi);
		}
	}

	
	/**
	 * Défini l'emplacement par défaut des fichiers javascript
	 *
	 * @param String $psPath
	 */
	public static function setPublicPath($psPath)
	{
		self::$_publicPath = $psPath;
	}
	
	
	/**
	 * Ajoute un fichier javascript et son chemin
	 * pour l'insertion
	 *
	 * @param String $psFile
	 * @param String $psPath
	 */
	public static function addFile($psFile, $psPath='')
	{
		$sPath = self::$_publicPath;
		if (!empty($psPath))
			$sPath = $psPath;
		
		$sGlobalPath = system\Settings::getParam('temp', 'path');
			
		self::$_paths[] =  $sGlobalPath.$sPath.$psFile;
	}
	
	
	/**
	 * Ajoute des instructions javascript
	 *
	 * @param String $psInstructions
	 * @param String $psDestination
	 */
	public static function addInstructions($psInstructions)
	{
		self::$_instructions[] = $psInstructions;
	}
	
	
	/**
	 * Retourne de façon formatté les fichiers js à inclure
	 */
	public static function getFiles()
	{
            if (empty(self::$_paths)) return '';

            self::$_paths = array_unique(self::$_paths);

            $sJs = '';

            foreach (self::$_paths as $sPath)
            {
                    $sJs .= \P\tag('script', '', array('type' => 'text/javascript', 'src' => $sPath));
            }
            
            return $sJs;
	}

	
	/**
	 * Retourne de façon formatté les instructions JS
	 */
	public static function getInstructions()
	{
		if (empty(self::$_instructions)) return '';
		
		$sJs = '';
		
		foreach (self::$_instructions as $sInstruction)
		{
                    $sJs .= "\n\t".$sInstruction."\n";
		}
		
		return \P\tag('script', $sJs, array('type' => 'text/javascript'));
	}
	
	
	/**
	 * Format the json response of all ajax request
	 *
	 * @param Integer $pnStatus
	 * @param String $psError
	 * @param String $psHtml
	 * @param String $psTitle
	 * @param String $psJavascript
	 */
	public static function jsonResponse($pnStatus=200, $psError='', $psHtml='', $psTitle='', $psJavascript='', $pbHeaders=true)
	{
		$asJSON = array(
			'status' 		=> $pnStatus,
			'error' 		=> $psError,
			'html'			=> $psHtml,
			'title'			=> $psTitle,
			'javascript' 	=> $psJavascript
		);
		
		Layout::setFile('ajax.php');
		
		if ($pbHeaders)
			header('Content-type: text/json');
		else
			return $psHtml;	
			
		return json_encode($asJSON);
	}
}