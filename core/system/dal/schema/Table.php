<?php
namespace P\lib\framework\core\system\dal\schema;
use P\lib\framework\core\system\dal as dal;
use P\lib\framework\core\utils as utils;

class Table
{
	private $_name;
	private $_engine;
	private $_defaultEncoding;
	private $_collate;
	private $_comments;
	
        public $dal;
	//private $_partitionning; @TODO in a future release

        private $_keys = array();
        
	private $_columns = array();
        
        const PRIMARY   = 'primary';
        const UNIQUE    = 'unique';
        const INDEX     = 'index';
        const FOREIGN   = 'foreign';
        
        private $_keysValues = array(self::PRIMARY, self::UNIQUE, self::INDEX, self::FOREIGN);
	
	public function __construct($psName, $poDal, $psEngine='innoDB', $psDefaultEncoding='utf8_unicode_ci', $psCollate='utf8_unicode_ci')
	{
		$this->_name                = $psName;
		$this->_engine              = $psEngine ? $psEngine : 'innoDB';
		$this->_defaultEncoding     = $psDefaultEncoding ? $psDefaultEncoding : 'utf8_unicode_ci';
		$this->_collate             = $psCollate ? $psCollate : 'utf8_unicode_ci';
                $this->dal                  = $poDal;
	}
        
        
        public function overrideColumns()
        {
            throw new \ErrorException('overrideColumns must be overrided');
        }
	
	
	public function listColumnsName()
	{
		$asColumns = array();
        
        foreach ($this->_columns as $oColumn)
            $asColumns[] = $oColumn->name;
        
        
        //utils\Debug::dump($asColumns);
                
        
        return $asColumns;
	}

	
	public function addColumn($pasArgs)
	{
            if (!isset($this->_columns[$pasArgs['name']]))
                $this->_columns[$pasArgs['name']] = new Column($pasArgs);
            else
                $this->_overrideColumn($pasArgs);
	}
        
	public function __toString()
	{
		
	}
	
	
	public function getName()
	{
		return $this->_name;
	}
        
        
    private function _handleKeys()
    {
        foreach ($this->_columns as $oColumn)
        {
                if (is_array($oColumn->key))
                {
                    //$asColumn[] = $oColumn->key;
                }
                else
                {
                    $sKeyType = strtolower($oColumn->key);

                    if (in_array($sKeyType, $this->_keysValues))
                    $this->_keys[$sKeyType][] = '`'.$oColumn->name.'`';
                }
        }
    }
        
        
    public function create()
    {
        $this->_handleKeys();

        $sSQL = '';
        $sSQL .= 'CREATE TABLE IF NOT EXISTS  `'.$this->getName().'` ('."\r\n";

        $asColumn = array();
        foreach ($this->_columns as $oColumn)
        {
            $asColumn[] = $oColumn->__toString();
        }

        $sSQL .= implode(','."\r\n", $asColumn);

        $asKeys = array();
        if (isset($this->_keys[self::PRIMARY]))
            $asKeys[] = 'PRIMARY KEY ('.implode(',', $this->_keys[self::PRIMARY]).')';

        if (isset($this->_keys[self::UNIQUE]))
            $asKeys[] = 'UNIQUE KEY '.$this->_keys[self::UNIQUE][0].' ('.implode(',', $this->_keys[self::UNIQUE]).')';

        if (isset($this->_keys[self::INDEX]))
            $asKeys[] = 'KEY '.$this->_keys[self::INDEX][0].' ('.implode(',', $this->_keys[self::INDEX]).')';

        if (empty($asKeys))
            $sSQL .= "\r\n";
        else
            $sSQL .= ", \r\n";

        $sSQL .= implode(",\r\n", $asKeys);

        $sSQL .= "\r\n".')';

        $sSQL .= ' ENGINE = '.$this->_engine;
        $sSQL .= ' COLLATE '.$this->_collate;

        $sSQL .= ';';

        if (!$this->dal->query($sSQL))
            throw new \ErrorException('Error during query - '.$sSQL);

        return true;
    }
    
    
    private function _overrideColumn($pasArgs)
    {
        $oColumn = $this->_columns[$pasArgs['name']];
        
        if ($oColumn->type != $pasArgs['type'])
        {
            $oColumn->setType($pasArgs['type']);
            
        }
    }

    /**
        * Retourne les infos de SHOW FIELDS sur la table courante
        *
        */
    public function getTableInfo()
    {
        $sQuery = 'SHOW FIELDS FROM `'.$this->_name.'`';
        
        $oRecord = $this->dal->query($sQuery);
        
        if ($oRecord)
            return $oRecord->fetchAll();// or die($sQuery.' '.dal\Dal::$oDb->errorCode().' - '.dal\Dal::$oDb->errorInfo());
        else
        {
            utils\Debug::e($sQuery);
        }
    }
    
    
    public function getForeignInformation($psField, $psTable)
    {
        switch($this->dal->driver)
        {
            case 'mysql':
                return $this->_mysqlGetForeignInformation($psField, $psTable);
                break;
            
            default :
                // @todo Not implemented
                return false;
                break;
        }
    }
    
    
    private function _mysqlGetForeignInformation($psField, $psTable)
    {
        $oIS = dal\DBHandler::getDB('information_schema');
        
        
        if (is_object($oIS))
        {
            $sQuery = 'SELECT * FROM KEY_COLUMN_USAGE WHERE `CONSTRAINT_SCHEMA` LIKE  "'.$this->dal->schema.'" AND  `REFERENCED_TABLE_SCHEMA` IS NOT NULL ';
            $sQuery .= ' AND TABLE_NAME LIKE "'.$psTable.'"';
            $sQuery .= ' AND COLUMN_NAME LIKE "'.$psField.'"';
            
        
            $oSth   = $oIS->prepare($sQuery);
            $oSth   ->execute();

            $asData = $oSth->fetchall();
            
            if (isset($asData[0]))
            {
                $asData = $asData[0]; 
                $asData['label_field'] = $asData['REFERENCED_COLUMN_NAME'];
            }
            else
                return false;
            
            
            $oIS2 = dal\DBHandler::getDB('phpmyadmin');
            if (is_object($oIS2))
            {
                $sQuery = 'SELECT display_field FROM  pma_table_info WHERE db_name LIKE "'.$this->dal->schema.'" AND table_name LIKE "'.$asData['REFERENCED_TABLE_NAME'].'"';
                $oSth   = $oIS2->prepare($sQuery);
                $oSth   ->execute();

                $asField = $oSth->fetchall();
                
                if (isset($asField[0]))
                    $asData['label_field'] = $asField[0]['display_field'];
            }
            
            return $asData;
        }
        else 
        {
//            \P\lib\framework\helpers\JSManager::addInstructions('console.log("INFORMATION_SCHEMA n\'est pas configuré ! '.$psTable.'::'.$psField.'");');
//            \P\lib\framework\helpers\JSManager::addInstructions('console.log("COnfigurez manuellement les contraintes de clés étrangères dans Model::_customFields()");');
            return false;
        }
    }
}