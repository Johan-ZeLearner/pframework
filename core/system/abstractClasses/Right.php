<?php
namespace P\lib\framework\core\system\abstractClasses;
use P\lib\framework\core\system as system;
use P\lib\framework\core\system\interfaces as interfaces;

class Right
{
	/**
	 * Useless default class .............
	 *
	 * @param String $psAction
	 */
   public static function getRoles($psAction)
   {
       return array('anonymous');
   }
}