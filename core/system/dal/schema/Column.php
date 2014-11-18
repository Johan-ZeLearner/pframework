<?php
namespace P\lib\framework\core\system\dal\schema;

class Column
{
	public $name;
	public $type;
	public $size;
	public $defaultValue;
	public $collate;
	public $attribute;
	public $isNull = false;
	public $key;
	public $autoincrement;
	public $comments;
	public $MIME;
	public $transformation;
	public $transformationOptions;
	
	const BEHAVIOUR_CREATE = 'create';
	const BEHAVIOUR_UPDATE = 'update';
	
	private $_isString = false;
	
	public function __construct($pasArgs=array())
	{
		if (!empty($pasArgs))
			$this->_populateFromArray($pasArgs);
		
		return $this;
	}
	
	
	public function setName($psName)
	{
		if (empty($psName) || !preg_match('/[a-zA-Z]+[0-9]*[a-zA-Z0-9]+/', $psName))
			throw new \ErrorException('Column name must be alphanum and not blank');
		
		$this->name = $psName;
		
		return $this;
	}
	
	
	public function setType($psType)
	{
		// @TODO : check accepted types - database dependant
		
		if (empty($psType))
			throw new \ErrorException('Column '.$this->getName().' : type cannot be void');
		
		switch ($psType)
		{
			case 'varchar':
			case 'text':
				$this->_isString = true;
                            break;
		}
		
		
		$this->type = $psType;
		
		return $this;
	}
	
	
	public function setSize($psSize)
	{
		// @TODO : check accepted sizes - depending on Type - database dependant
		
		if (empty($psSize))
			throw new \Exception('Column '.$this->getName().' - size value is truncate or blank (it could be void but you seemed to specify a value).');
		
		$this->size = $psSize;

		return $this;
	}
	
	
	public function setDefaultValue($psDefaultValue)
	{
		if ((string)$psDefaultValue != '0' && empty($psDefaultValue))
			throw new \ErrorException('Column '.$this->getName().' : default value is blank (it could be void but you seemed to specify a value)');

		$this->defaultValue = $psDefaultValue;
		
		return $this;
	}
	
	
	
	public function setCollate($psCollate)
	{
		// @TODO : check accepted encoding
		
		if (empty($psCollate))
			throw new \ErrorException('Column '.$this->getName().' : collate value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->collate = $psCollate;
		
		return $this;
	}
	
	
	public function setAttribute($psAttribute)
	{
		// @TODO : check accepted attributes - database dependant
		
		if (empty($psAttribute))
			throw new \ErrorException('Column '.$this->getName().' : attribute value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->attribute = $psAttribute;
		
		
		return $this;
	}
	
	
	public function setNull($pbNull)
	{
		$this->isNull = (bool) $pbNull;
		
		return $this;
	}
	
	
	public function setkey($pskey)
	{
		// @TODO : check accepted keyes values - database dependant
		
		if (empty($pskey))
			throw new \ErrorException('Column '.$this->getName().' : key value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->key = $pskey;
		
		return $this;
	}
	
	
	public function setAutoincrement($pbAutoincrement=false)
	{
		$this->autoincrement = (bool) $pbAutoincrement;
		
		return $this;
	}
	
	
	public function setComments($psComments)
	{
		if (empty($psComments))
			throw new \ErrorException('Column '.$this->getName().' : comments value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->comments = $psComments;
		
		return $this;
	}
		
		
	
	public function setMIME($psMIME)
	{
		// @TODO : check accepted MIMEtypes values - database dependant
		
		if (empty($psMIME))
			throw new \ErrorException('Column '.$this->getName().' : MIME value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->MIME = $psMIME;
		
		return $this;
	}
		
	
	public function setTransformation($psTransformation)
	{
		// @TODO : check accepted MIMEtypes values - database dependant
		
		if (empty($psTransformation))
			throw new \ErrorException('Column '.$this->getName().' : transformation value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->transformation = $psTransformation;
		
		return $this;
	}
	
	
	public function setTransformationOptions($psTransformationOptions)
	{
		// @TODO : check accepted MIMEtypes values - database dependant
		
		if (empty($psTransformationOptions))
			throw new \ErrorException('Column '.$this->getName().' : transformation value is truncate or blank (it could be void but you seemed to specify a value)');
		
		$this->transformationOptions = $psTransformationOptions;
		
		return $this;
	}
	
	
	public function getName()
	{
		if (empty($this->name))
			return '[undefined]';
		
		return $this->name;
	}
	
	
	public function __toString()
	{
		$sSQL = '';
		
		$sSQL .= '`'.$this->name.'`';
		$sSQL .= ' '.$this->type;
		
		if (!empty($this->size))
			$sSQL .= '('.$this->size.')';
		
		if (!empty($this->attribute))
			$sSQL .= ' '.$this->attribute;
                
		if ($this->isNull)
			$sSQL .= ' NULL';
                else 
                        $sSQL .= ' NOT NULL';
		
		if ($this->defaultValue != '')
			$sSQL .= ' DEFAULT '.($this->_isString) ? '"'.$this->defaultValue.'"' : $this->defaultValue;

		if ($this->autoincrement)
			$sSQL .= ' AUTO_INCREMENT';
		
		return $sSQL;
	}
	
	
	private function _populateFromArray($pasArray)
	{
		foreach ($pasArray as $sParameter => $sValue)
		{
			$this->_registerParameter($sParameter, $sValue);
		}
	}
	
	
	private function _registerParameter($psParameter, $psValue)
	{
		$sParameter = strtolower($psParameter);
		
		switch ($sParameter)
		{
			case 'name':
				return $this->setName($psValue);
				break;
				
			case 'type':
				return $this->setType($psValue);
				break;
				
			case 'size':
				return $this->setSize($psValue);
				break;
				
			case 'default':
			case 'defaultValue':
				return $this->setDefaultValue($psValue);
				break;
				
			case 'collate':
				return $this->setCollate($psValue);
				break;
				
			case 'attribute':
				return $this->setAttribute($psValue);
				break;
				
			case 'null':
			case 'isnull':
				return $this->setNull($psValue);
				break;
				
			case 'key':
				return $this->setkey($psValue);
				break;
				
			case 'autoincrement':
			case 'ai':
				return $this->setAutoincrement($psValue);
				break;
				
			case 'comments':
				return $this->setComments($psValue);
				break;
				
			case 'mime':
			case 'mimetype':
				return $this->setMIME($psValue);
				break;

			case 'transformation':
				return $this->setTransformation($psValue);
				break;
				
			case 'transformationOptions':
				return $this->setTransformationOptions($psValue);
				break;
		}
	}
	
}