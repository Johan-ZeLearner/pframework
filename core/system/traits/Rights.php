<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\system as system;

trait Rights
{
	abstract protected function _checkRights();
	
	protected function getCalledFunction()
	{
		$asCallStack = \debug_backtrace();
		
		return $asCallStack[2]['function'];
	}
}