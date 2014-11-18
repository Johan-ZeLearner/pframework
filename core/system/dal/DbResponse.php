<?php

namespace P\lib\framework\core\system\dal;
use P\lib\framework\core\utils as utils;
use P\lib\framework\core\system as system;

define('RESPONSE_RAW', 		'raw');
define('RESPONSE_ALL', 		'all');
define('RESPONSE_COMPUTED', 	'computed');

class DbResponse implements system\interfaces\isDbCollection
{
	private $_response;
	private $_cursor;
	public $render = true;
	public $query;
        public $count = 0;
        private $_scheme;
	static $_row;
	
	/**
	 * Init the Response
	 *
	 * @param Array of Object $paoRecord
	 * @param Object $poDal
	 */
	public function __construct($paoRecord, $poModel='null')
	{
            if (!empty($poModel))
		$this->_scheme = $poModel->getScheme();
            
            
            if (is_object($paoRecord))
            {
                $this->_response 	= array(0 => $paoRecord);
            }
            else
            {
                $this->_response 	= $paoRecord;
            }

            $this->reset();
            
            self::$_row  = new RowResponse('', 0);
            
	}
	
	
	/**
	 * Reset the cursor position to 0
	 */
	public function reset()
	{
	    $this->_cursor = 0;
            return $this;
	}
	
	
	/**
	 * Map the next row of results into a RowResponse Object.
	 * You can specify what kind of data you want to handle
	 * - "all" stands for raw and computed data
	 * - "raw" stands for raw data ONLY (foreign keys as integer)
	 * - "computed" stands for computed data ONLY (regular values plus foreign keys as string)
	 *
	 * @param String $psData
	 */
        public function next($psData=RESPONSE_RAW)
        {
            return $this->readNext($psData);
        }


    public function hasNext()
    {
        return isset($this->_response[($this->_cursor + 1)]);
    }
        
        
        
	public function readNext($psData=RESPONSE_ALL)
	{
	  //  if (isset($this->_response[0]->count)) return $this->_handleCount();
            if (isset($this->_response[$this->_cursor]))
            {
                self::$_row->setCursor($this->_cursor);
                self::$_row  = new RowResponse('', 0);
                foreach ($this->_response[$this->_cursor] as $sField => $sValue)
                {
                    if (isset($this->_scheme->$sField))
                    {
                        $oDataField = $this->_scheme->$sField;
                        $sRaw = 'raw_'.$sField;

                        if ($psData == RESPONSE_ALL && $this->render)
                        {
                            self::$_row->$sField = $this->_render($sValue, $oDataField, $sField);
                            self::$_row->$sRaw 	= $sValue;
                        }
                        elseif($psData == RESPONSE_COMPUTED)
                        {
                            self::$_row->$sField = $this->_render($sValue, $oDataField, $sField);
                        }
                        else
                        {
                            self::$_row->$sField = $sValue;
                        }
                    }
                    else
                    {
                        self::$_row->$sField = $sValue;
                    }
                }
                
                $this->_cursor++;
                return self::$_row;
            }
            
            return false;
	}
	
	
	/**
	 * Use the response Object to return the result count
	 * Shortcut to $this->_response[0]->count;
	 */
	private function _handleCount()
	{
	    $oResponse  = new \stdClass();
	    
	    $oResponse->count = $this->_response[0]->count;
	    
	    return $oResponse;
	}
        
        
        /**
	 * Render the value $psValue to be Human readable
	 *
	 * @param Mixed $psValue
	 * @param Object $poDataField
	 * @param String $psField
	 */
	private function _render($psValue, $poDataField, $psField)
	{
            $sType = $poDataField->getSqlType();
                        
            switch ($sType)
            {
                case 'int':
                case 'smallint':
                case 'mediumint':
                        $sForeignTable = $poDataField->getForeignTable();
                        if (!empty($sForeignTable))
                            return $this->_getForeignValue($psValue, $poDataField, $psField);
                        else
                            return $psValue;
                        break;

                case 'tinyint':
                case 'bit':
                        return ((int)$psValue == 1) ? 'Oui' : 'Non';
                        break;

                case 'date':
                    return utils\Date::toDisplay($psValue, true);
                        break;

                case 'datetime':
                        return utils\Date::toDisplay($psValue, true);
                        break;

                case 'color':
                        return \P\tag('div', '', array('title' => 'code Hex : #'.$psValue , 'style' => 'background-color: #'.$psValue.'; width: 30px; height: 15px;'));
                        break;

                case 'varchar':
                case 'mediumtext':
                case 'text':
                default:
                        return stripslashes($psValue);
                        break;
            }
	}
        
        /**
	 *
	 * Fatch the label of a foreign key
	 *
	 * @param Integer $psValue
	 * @param Object $poDataField
	 * @param String $psField
	 */
	private function _getForeignValue($psValue, $poDataField, $psField)
	{
            if (isset($this->_foreignFieldCache[$psField][$psValue]))
            {
                $this->_usedCache++;
                return $this->_foreignFieldCache[$psField][$psValue];
            }
            
            $sTable = $poDataField->getForeignTable();

            $sApp = system\PathFinder::tableToController($sTable);
            
            $oClass = system\ClassManager::getInstance($sApp);

            $sLabel = $oClass->getLabel($psValue);
            
            if ($sLabel)
            {
                $this->_foreignFieldCache[$psField][$psValue] = $sLabel;
                return $sLabel;
            }

            $sFieldLabel 	= $poDataField->getForeignLabelField();
            $sForeignField      = $poDataField->getForeignField();

            try {
                $oRecord = $oClass->model->selectByPK($psValue);
                
                if (isset($oRecord->$sFieldLabel))
                    return $oRecord->$sFieldLabel;
//                $oRecord = $oClass->selectOne($sFieldLabel, $sForeignField.' = "'.$psValue.'"');
            }
            catch (Exception $e)
            {
                return 'non trouvé';
            }


            if (is_object($oRecord))
            {
                $this->_foreignFieldCache[$psField][$psValue] = $oRecord->$sFieldLabel;

                return $oRecord->$sFieldLabel;
            }

            return 'non trouvé';
	}
        
        
        public function toArray($pbIgnoreEmpty=false)
        {
            $asData = array();
            while ($oRecord = $this->readNext(RESPONSE_RAW))
            {
                $asLine = array();
                foreach ($oRecord as $sName => $sValue)
                {
                    if ($pbIgnoreEmpty)
                    {
                        if (!empty($sValue))
                            $asLine[$sName] = $sValue;
                    }
                    else    
                    {
                        $asLine[$sName] = $sValue;
                    }
                }
                $asData[] = $asLine;
            }
            
            return $asData;
        }
        
        
        public function toJSON($pbIgnoreEmpty=false)
        {
            return json_encode($this->toArray($pbIgnoreEmpty));
        }
        
        
        public function setValue($psField, $psValue)
        {
            if (isset($this->_response[($this->_cursor - 1 )]))
            {
                $this->_response[($this->_cursor - 1)]->$psField = $psValue;
            }
            else
            {
                throw new \ErrorException('Impossible de mettre l\'index '.$this->_cursor.' du champ '.$psField.' à jour');
            }
        }
}