<?php
namespace P\lib\framework\core\system\dal;
/**
 *
 * Classe permettant de décrire une colonne de base de données.
 * Fonctionne de concert avec P_Core_System_Dal_Table
 *
 * @author johan
 * @version 0.1
 */
class Field
{
	protected $_sField;
	protected $_sType;
	protected $_sLength;
	protected $_sForeignTable;
	protected $_sForeignField;
	protected $_sForeignLabelField;
	
	/***********************************/
	
	public $label;
	public $readable 			= true;    // the field is readable by any program
	public $writable 			= true;    // the field is writable by any program
	public $browsable 			= true;    // the field can be listed by any program
	public $recordable          = true;    // the field can be recorded by any program
	public $required            = false;   // must be filled
	public $pattern 			= '';      // the value of the field must respect this pattern
	public $value;                         // the value of the field
	public $description;                   // the meaning of the field
	public $primary 			= false;
	public $foreign 			= false;
	public $options 			= array(); // various options of the field
	public $placeholder                     = '';   // value of the field placeholder
	public $inputType                       = 'text';   // value of the field placeholder
        
        
        
        
	/***********************************/
	
	
	public function __construct($psFieldName)
	{
            $this->setName($psFieldName);
	}
	
	
	/**
	 * set the name of the field
	 *
	 * @param String $psName
	 * @throws Exception
	 */
	public function setName($psName)
	{
		if (empty($psName)) throw new \Exception('$psName must not be truncate', E_USER_WARNING);
		
		$this->_sField = $psName;
	}
	
	
	/**
	 * set the placeholder of the field
	 *
	 * @param String $psName
	 * @throws Exception
	 */
	public function setPlaceHolder($psString)
	{
            $this->placeholder = $psString;
	}
	
	/**
	 * set the input type of the field
	 *
	 * @param String $psName
	 * @throws Exception
	 */
	public function setInputType($psString)
	{
            if (empty($psString)) throw new \Exception('$psString must not be truncate', E_USER_WARNING);

            $this->inputType = $psString;
	}
	
	
	/**
	 * Set up the config of the field and its length if necessary
	 *
	 * @param String $psType
	 * @throws Exception
	 */
	public function setType($psType)
	{
            if (empty($psType)) throw new \Exception('$psType must not be truncate', E_USER_WARNING);

            if ((bool)preg_match('/([a-zA-Z]+)\(([0-9]+)\)/i', $psType, $asMatches))
            {
                    $this->_sType 	= $asMatches[1];
                    $this->_sLength	= $asMatches[2];
            }
            else
            {
                    $this->_sType = $psType;
            }
	}
	
	
	/**
	 * Set the key of the field and compute external
	 * links to foreign tables if necessary
	 *
	 * @param String $psKey
	 * @param String $psForeignTable
	 * @param String $psForeignField
	 * @throws ErrorException
	 */
	public function setKey($psKey, $psForeignTable='', $psForeignField='')
	{
		if (!empty($psKey) && $psKey != 'PRI' && $this->_sType != 'varchar')
		{
			$this->foreign = true;
		}
	}
	
	
	/**
	 * Set the maximum length of the field
	 *
	 * @param String $psLength
	 */
	public function setLength($psLength)
	{
		if (empty($psLength)) return false;
		
		$this->_sLength = $psLength;
	}

	
	/**
	 * Returns the name of the field
	 */
	public function getName()
	{
		return $this->_sField;
	}

	
	/**
	 * Returns the type of the field
	 */
	public function getType()
	{
		return $this->_sType;
	}

	
	/**
	 * Returns the length of the field
	 */
	public function getLength()
	{
		return (int) $this->_sLength;
	}


	/**
	 * Return the name of the foreign table
	 */
	public function getForeignTable()
	{
		return $this->_sForeignTable;
	}

	
	/**
	 * Returns the name of the foreign field linked to
	 * the current field
	 */
	public function getForeignField()
	{
		return $this->_sForeignField;
	}
	
	
	/**
	 * Return the label field of the current field
	 */
	public function getForeignLabelField()
	{
		return $this->_sForeignLabelField;
	}

	
	/**
	 * Set the name of the foreign table
	 *
	 * @param String $psTable
	 */
	public function setForeignTable($psTable='')
	{
	    if (empty($psTable)) return false;
	    
		$this->_sForeignTable = $psTable;
	}

	
	/**
	 * Set the name of the foreign field
	 *
	 * @param String $psField
	 */
	public function setForeignField($psField='')
	{
	    if (empty($psField)) return false;
	    
		$this->_sForeignField = $psField;
	}
	
	
	/**
	 * Set the name of the foreign field "label"
	 *
	 * @param String $psField
	 */
	public function setForeignLabelField($psField='')
	{
	    if (empty($psField)) return false;
	    
		$this->_sForeignLabelField = $psField;
	}
	
	
	/**
	 * return true if the field is a foreign key
	 */
	public function isKey()
	{
		return $this->foreign;
	}
	
	
	public function __toString()
	{
		return $this->value;
	}
        
        
       
}

