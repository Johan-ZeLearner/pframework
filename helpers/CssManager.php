<?php

namespace P\lib\framework\helpers;

class CssManager
{
	static $_publicPath;
	static $_paths          = array();
	
	
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
	public static function addFile($psFile, $psMedia='screen', $psPath='')
	{
		$sPath = self::$_publicPath;
		if (!empty($psPath))
                    $sPath = $psPath;
		
		self::$_paths[] =  array(
                                            'path' => $sPath.$psFile,
                                            'media' => $psMedia
                );
                    
	}
	
	
	/**
	 * Retourne de façon formatté les fichiers js à inclure
	 */
	public static function getFiles()
	{
		if (empty(self::$_paths)) return '';
				
		$sCss = '';
		
		foreach (self::$_paths as $asPath)
		{
                    
			$sCss .= \P\tag(
                                            'link', 
                                            '', 
                                            array(
                                                    'media' => $asPath['media'], 
                                                    'href'  => $asPath['path'],
                                                    'rel'   => 'stylesheet'
                                                ),
                                            true
                                )."\n\r";
		}
		
		return $sCss;
	}
		
}