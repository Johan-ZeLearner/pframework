<?php
namespace P\lib\framework\helpers;

class Params
{
	/**
	 * Take an associative array and serialize it in an query string fashion (by default)
	 *
	 * @param Array $pasParams
	 * @param String $psStartChar
	 * @param String $sGlue
	 */
	public static function serialize($pasParams, $psStartChar='', $sGlue=' ')
	{
		if (empty($pasParams) || !is_array($pasParams)) return '';
		
		$sOutput = '';
		foreach ($pasParams as $sKey => $sValue)
		{
			if (!empty($psStartChar))
			{
				if ( (bool) preg_match('/^(['.$psStartChar.']+)/', $sKey))
				{
					$sKey = substr($sKey, (strlen($psStartChar)));
				}
			}
			
			if (is_array($sValue))
				$sValue = implode($sGlue, $sValue);
			
			$sOutput .= ' '.$sKey.'="'.$sValue.'"';
			
		}
		
		return $sOutput;
	}
	
	
	/**
	 * Deep copy of an array, not just the copy of the symbolic link
	 *
	 * @param Array $pasParams
	 * @param String $psStartChar
	 */
	public static function copyArray($pasParams, $psStartChar='')
	{
		if (empty($pasParams) || !is_array($pasParams)) return '';
		
		$asOutput = array();
		foreach ($pasParams as $sKey => $sValue)
		{
			if (!empty($psStartChar))
			{
				if ((bool) preg_match('/^(['.$psStartChar.']+)/', $sKey))
					$asOutput[substr($sKey, (strlen($psStartChar)))] = $sValue;
			}
			else
			{
				$asOutput[$sKey] = $sValue;
			}
		}
		
		return $asOutput;
	}
}
