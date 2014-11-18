<?php
namespace P\lib\framework\core\system\abstractClasses;
use P\lib\framework\core\system as system;
use P\lib\framework\core\system\interfaces as interfaces;

abstract class Object 
{
	static $_oInstance;
	static $_bCreated;
	
	/**
	 * prevention of clonage
	 */
	public function __construct()
	{
		$this->init();
		
		return $this;
	}
	
	
	public function init()
	{
		return true;
	}
	
	
	/**
	 * prevention of clonage
	 */
	public function __clone()
	{
		system\Debug::trigger_error('Impossible de cloner cette classe (singleton)', E_USER_ERROR);
	}
}