<?php

namespace P\lib\framework\core\system\abstractClasses;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system\interfaces as interfaces;
use P\lib\framework\core\system\traits as traits;

abstract class Dal
{
	public 		$_scheme;
	public 		$haltNoResults  = false;
	protected 	$_table;
	protected 	$_infos;
	protected 	$_bGenerated    = false;
	protected 	$_browsable	= array();
	
	
	/**
	 * Configure the DAL to use the $psTableName table
	 *
	 * @param String $psTableName
	 * @throws ErrorException
	 */
	public function __construct($psTableName)
	{
		$this->_scheme 		= new \stdClass();
		
		$this->_table 		= $psTableName;
		
		$sSchemeClassName 	= system\dal\Dal::getSchemeClassName(get_called_class(), $this->_table);
		
		$sConstantFile 		= P_Core_Utils_Path::getConstDir($sSchemeClassName).$this->_table.'.php';
		$sInstructionsFile 	= P_Core_Utils_Path::getInstructionsDir($sSchemeClassName).$this->_table.'.php';
		
		if (is_file($sConstantFile)) 		require_once $sConstantFile;
		if (is_file($sInstructionsFile)) 	require_once $sInstructionsFile;
				
		if (system\Settings::getParam('environement', 'force_scheme_creation'))
		{
			// On génère le schema
			system\dal\SchemeCreator::createDbScheme($sSchemeClassName, $this->_table, $this);
			$this->_bGenerated = true;
		}
		
		try
		{
			// On essaye de charger le schema de la table
			$oScheme = new $sSchemeClassName();
			$this->_scheme = $oScheme->getScheme();
			$this->_infos = $oScheme->_infos;
		}
		catch (\Exception $e)
		{
		    if ($this->_bGenerated)
		    {
		        utils\Debug::dump($e->getMessage());
		        throw new \ErrorException('The generation seems to have failed. Program halted');
		    }
		        
			// On génère le schema
			//$this->_createDbSchema($sSchemeClassName);
			system\dal\SchemeCreator::createDbScheme($sSchemeClassName, $this->_table, $this);
		}
	}
	
	
	/**
	 * Methode permettant de configurer specifiquement certains champs de la base de donnée
	 */
	protected function _customFields()
	{
		return array();
	}
	
	
	/**
	 * Execute _customFields();
	 */
	public function customFields()
	{
		return $this->_customFields();
	}
	
	
	/**
	 * Retourne le nom des champs de la table du Dal en cours
	 * Peut être filtré avec les valeurs suivants :
	 *  - all (tous les champs)  DEFAULT
	 *  - readable (seulement les champs lisibles)
	 *  - writable (seulement les champs pouvant etre ecrits)
	 *  - browsable (seulement les champs listables)
	 *
	 * @param String $psFilter
	 */
	public function getFieldNames($psFilter='all', $pbDebug=false)
	{
		$asFields = array();
		
		foreach ($this->_scheme as $sName =>$oField)
		{
			if ($psFilter == 'readable')
			{
				if ($oField->readable)
					$asFields[] = $sName;
			
			}
			elseif($psFilter == 'writable')
			{
				if ($oField->writable)
					$asFields[] = $sName;
			}
			elseif($psFilter == 'browsable')
			{
				if (!empty($this->_browsable))
				{
					if (in_array($sName, $this->_browsable))
					{
						$nIndice = array_search($sName, $this->_browsable);
						$asFields[$nIndice] = $sName;
					}
				}
				else
				{
					if ($oField->browsable)
						$asFields[] = $sName;
				}
			}
			else
			{
				$asFields[] = $sName;
			}
		}
		
		ksort($asFields);
		
		if ($pbDebug)
			utils\Debug::dump($this->_scheme);
			
		if ($pbDebug)
			utils\Debug::dump($asFields);
			
		return $asFields;
	}
	
	
	/**
	 * Retourne le nom des champs de la table du Dal en cours
	 * Peut être filtré avec les valeurs suivants :
	 *  - all (tous les champs)  DEFAULT
	 *  - readable (seulement les champs lisibles)
	 *  - writable (seulement les champs pouvant etre ecrits)
	 *  - browsable (seulement les champs listables)
	 *
	 * @param String $psFilter
	 */
	public function getFieldLabels($psFilter='all')
	{
		if (!in_array($psFilter, array('all', 'readable', 'writable', 'browsable'))) throw new \ErrorException('$psFilter must be "all", "readable", "writable" or "browsable"');
		
		if ($psFilter == 'all')
			$psFilter = '';
		
		$asFields = array();
		
		foreach ($this->_scheme as $sName => $oField)
		{
			if (!empty($psFilter))
			{
				if ($psFilter == 'browsable' && !empty($this->_browsable))
				{
					if (in_array($sName, $this->_browsable))
					{
						$nIndice 			= array_search($sName, $this->_browsable);
						$asFields[$nIndice] = $oField->label;
					}
					else
					{
						//dump("champ inconnu => ".$sName);
					}
				}
				elseif ($oField->$psFilter)
					$asFields[] = $oField->label;
					
				// else we ignore the field
			}
			else
			{
				$asFields[] = $oField->label;
			}
		}
		
		ksort($asFields);
		
		return $asFields;
	}
	
	
	/**
	 * Return the scheme
	 */
	public function getScheme()
	{
		return $this->_scheme;
	}
	
	
	/**
	 *
	 * Returns the name of the current table.
	 * Useful for component-like request to the Dal
	 */
	public function getTable()
	{
		return $this->_table;
	}
	
	
	/**
	 *
	 * Returns the name of the field primary key
	 */
	public function getPrimary()
	{
	    if (isset($this->_infos['primary']))
	        return $this->_infos['primary'];
	    else
	        return $this->_table.'pk';
	}
	
	
	/**
	 * Returns the value of the primary field
	 */
	public function getPrimaryValue()
	{
		$sPrimaryName = $this->getPrimary();
		
		return $this->_scheme->$sPrimaryName->value;
	}
	
	
	/**
	 * Load the values of the $_scheme from the Primary Key value of its record
	 *
	 * @param Integer $pnPK
	 */
	public function load($pnPK, $pbDebug=false)
	{
		$pnPK 	 = (int) $pnPK;
		
		$bLoaded = false;
	    if ($pnPK > 0)
	    {
	        $oFields = $this->selectByPK($pnPK, $pbDebug);
	      
	        if (!is_object($oFields)) return false;
	        
	        foreach ($oFields as $sFieldName => $sFieldValue)
	        {
	            if (isset($this->_scheme->$sFieldName))
	            {
	                $bLoaded = true;
	                
	                $this->_scheme->$sFieldName->value = stripslashes($sFieldValue);
	            }
	            //else
	            	//utils\Debug::dump($sFieldName.' is not set');
	        }
	    }
            
	    return $bLoaded;
	}
	
	
	/**
	 * Shortcut to $this->select() for the search on the primary key exclusively.
	 * Return a dbResponse object.
	 *
	 * @param Integer $pnPK
	 */
	public function selectByPK($pnPK, $pbDebug=false)
	{
            $pnPK = (int) $pnPK;
            if ($pnPK > 0)
            {
                $oDbResponse = $this->select('', $this->getPrimary().'='.(int) $pnPK, '', $pbDebug);
                $oDbResponse->render = false;
                return $oDbResponse->readNext();
            }
            
            return false;
	}
	
	
	/**
	 *  Generic method for selection row(s) depending of the where clause
	 *
	 * @param Array $pasFields
	 * @param String $psWhere
	 * @param Array $pasLimit
	 */
	public function select($pasFields='', $psWhere='', $pasLimit='', $pbDebug=false)
	{
		$oSelect = new Select($this);
		$oSelect->addField($pasFields);
		$oSelect->addTable($this->_table);
		$oSelect->where($psWhere);
			
		if (!empty($pasLimit) && is_array($pasLimit))
		{
			$nStart = isset($pasLimit['start']) ? $pasLimit['start'] : 0;
			$nNb 	= isset($pasLimit['nb']) ? $pasLimit['nb'] : 0;
			
			if ($nNb > 0 || $nStart > 0)
			{
				$oSelect->limit($nStart, $nNb);
			}
		}
		
		if ($pbDebug)
		    $oSelect->_debug = true;
		
		if ($pbDebug)
		{
                    utils\Debug::dump($oSelect->__toString());			
		    die();
		}
		    
	    return $oSelect->fetchAll();
	}
	
	
	/**
	 * Return a single record (P_Core_System_Dal_RowResponse Object)
	 *
	 * @param Array $pasFields
	 * @param String $psWhere
	 * @param Array $pasLimit
	 * @param Boolean $pbDebug
	 * @throws ErrorException
	 * @return P_Core_System_Dal_RowResponse
	 */
	public function selectOne($pasFields='', $psWhere='', $pasLimit='', $pbDebug=false)
	{
	    $oDbResponse = $this->select($pasFields, $psWhere, $pasLimit, $pbDebug);
	    
	    while ($oRecord = $oDbResponse->readNext())
	    {
	        return $oRecord;
	    }
	    
	    if ($this->haltNoResults)
	  	throw new \ErrorException( $oDbResponse->query.'<br />There is not any records in this query <br /><br />');
	}
	
	
	/**
	 * Generic method to count row depending of the Where clause
	 *
	 * @param Array $pasWhere
	 * @param String $psSeparator
	 * @throws ErrorException
	 */
	public function count($pasWhere=array(), $psSeparator='AND', $pbDebug=false)
	{
	    if (!is_array($pasWhere)) throw new ErrorException('$pasWhere must be an array');
	    
	    $sWhere = '';
	    if (!empty($pasWhere))
	    {
	        $asQuery = array();
	        foreach ($pasWhere as $sField => $sValue)
	        {
	            $sOperator = '=';
	            if (preg_match('/^([^a-z]+)(.*)/i', $sField, $asMatches))
	            {
	                
	                $sOperator = $asMatches[1];
	                $sField = $asMatches[2];
	            }
	            
	            $asQuery[] = $sField.$sOperator.'"'.$sValue.'"';
	        }
	        
	        $sWhere .= ' '.implode(' '.$psSeparator.' ', $asQuery);
	    }
	    
	    
	    $oRecord = $this->selectOne(array('COUNT(*) as count'), $sWhere, '');
	    
	    //Debug::dump($oRecord);
	    
	    if (isset($oRecord->count))
	        return (int) $oRecord->count;
	        
	    //throw new \ErrorException('$oRecord[0]->count is not set');
	}
	
	
	/**
	 * Shortcut for execution Insert or Update on the current record
	 *
	 * @param Array $pasFields
	 * @param Integer $pnPK
	 */
	public function save($pasFields, $pnPK=0, $pbDebug=false)
	{
	    if ($pnPK > 0)
	        return $this->update($pasFields, (int) $pnPK, $pbDebug);
	    else
	        return $this->insert($pasFields, $pbDebug);
	}

	
	/**
	 * Generic Method to insert a record in the current $_table
	 *
	 * @param Array $pasFields
	 */
	public function insert($pasFields, $pbDebug = false)
	{
	    $sQuery  = '';
	    $sQuery .= ' INSERT INTO '.$this->_table;
	    $sQuery .= ' SET ';
	    
	    // on gère la date automatique
	    $sCreate = $this->_table.'_date_create';
	    $sUpdate = $this->_table.'_date_update';
	    
	    if (isset($this->_scheme->$sCreate) && !isset($pasFields[$sCreate]))
	    {
                $pasFields[$sCreate] = date('Y-m-d H:i:s');
	    }
	    
	    if (isset($this->_scheme->$sUpdate) && !isset($pasFields[$sUpdate]))
	    {
                $pasFields[$sUpdate] = date('Y-m-d H:i:s');
	    }
	    
	    $asQuery = array();
	    foreach ($pasFields as $sField => $sValue)
	    {
	        if (isset($this->_scheme->$sField))
	        {
	            $asQuery[] = $this->_renderValue($sField, $sValue);
	        }
	    }
	    
	    $sQuery .= implode(', ', $asQuery);
	    
	    if ($pbDebug)
	    	utils\Debug::dump($sQuery);
	    
            system\dal\Dal::$oDb->query($sQuery) or die($sQuery.' <br /> '.utils\Debug::dump(system\dal\Dal::$oDb->errorInfo()));
	    
	    return system\dal\Dal::$oDb->lastInsertId();
	}
	
	
	/**
	 * Generic method to update a row of the current $_table
	 *
	 * @param Array $pasFields
	 * @param Integer $pnId
	 */
	public function update($pasFields, $pnPK, $pbDebug=false)
	{
	    $sQuery  = '';
	    $sQuery .= ' UPDATE '.$this->_table;
	    $sQuery .= ' SET ';
	    
	    // on gère la date automatique
	    
	    // on gère la date automatique
	    $sUpdate = $this->_table.'_date_update';
	    
	    if (isset($this->_scheme->$sUpdate) && !isset($pasFields[$sUpdate]))
	    {
    	    $pasFields[$sUpdate] = date('Y-m-d H:i:s');
	    }
	    
	    $asQuery = array();
	    foreach ($pasFields as $sField => $sValue)
	    {
	    	// foreign == forcement une cle numerique
	    	if ($this->_scheme->$sField->foreign)
	        	$asQuery[] = $sField.' = '.$sValue;
	        else
	        	$asQuery[] = $sField.' = "'.$sValue.'"';
	    }
	    
	    $sQuery .= implode(', ', $asQuery);
	    
	    $sQuery .= ' WHERE '.$this->_table.'pk = '.(int) $pnPK;
	    
	    if ($pbDebug)
	    {
                utils\Debug::dump($sQuery);
                die();
	    }
	    
	    system\dal\Dal::$oDb->query($sQuery) or die($sQuery.' <br /> '.dump(system\dal\Dal::$oDb->errorInfo()));;
	    
	    return (int) $pnPK;
	}
	
	
	/**
	 * Generic method for deleting a row by its Primary Key value
	 *
	 * @param Integer $pnPK
	 */
	public function delete($pnPK, $pbDebug=false)
	{
            $sQuery = ' DELETE FROM '.$this->_table.' WHERE '.$this->getPrimary().' = '.(int) $pnPK;

            if ($pbDebug)
            {
                    utils\Debug::dump($sQuery);
                    die();
            }

            system\dal\Dal::$oDb->query($sQuery) or die($sQuery.'<br />'.utils\Debug::dump(system\dal\Dal::$oDb->errorInfo()));

            return !(bool) $this->count(array($this->getPrimary() => $pnPK));
	}
	
	
	/**
	 * Performs the query and return the generic oDbResponse Object needed by the Dal
	 */
	public function query($psQuery)
	{
	    $oResults = system\dal\Dal::$oDb->query($psQuery) or die(mysql_errno().$psQuery.'<br />'.Dump(system\dal\Dal::$oDb->errorInfo()));
	    
	    if (is_object($oResults))
	    {
	        $oDbResponse = new system\dal\DbResponse($oResults->fetchAll(PDO::FETCH_OBJ), $this);

	        return $oDbResponse;
	    }
	}
	
	
	/**
	 *  Reset the values of the scheme loaded in
	 */
	public function reset()
	{
	    foreach ($this->_scheme as $oField)
	    {
	        $oField->value = '';
	    }
	}
	
	
	/**
	 * handle the rendering of the item according to its type
	 *
	 * @param String $psField
	 * @param String $psValue
	 * @throws ErrorException
	 */
	private function _renderValue($psField, $psValue)
	{
		$sType = 'unknown';
		if (isset($this->_scheme->$psField) && is_object($this->_scheme->$psField))
	    	$sType = $this->_scheme->$psField->getType();
	    else
	    {
	    	utils\Debug::dump($this->_scheme->$psField);
	    	throw new \ErrorException($psField.' n existe pas (valeur : '.$psValue.') - table '.$this->_table);
	    }
	    
	    switch ($sType)
	    {
	        case 'varchar':
	        case 'text':
	            return $psField.'="'.addslashes($psValue).'"';
	            break;
	            
	        case 'date':
	        case 'datetime':
	            return $psField.'="'.Date::toDatabase($psValue).'"';
	            break;
	            
	        default:
	            return $psField.'="'.$psValue.'"';
	            break;
	    }
	}
}