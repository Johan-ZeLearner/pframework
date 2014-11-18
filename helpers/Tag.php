<?php
namespace P\lib\framework\helpers;

class Tag
{
	protected $_content;
	
	public function __construct($psTagName, $psContent='', $pasArgs='', $pbSimpleTag=false)
	{
		$this->_content  = '';
		$this->_content .= '<'.$psTagName.Params::serialize($pasArgs);
		if (!$pbSimpleTag)
		    $this->_content .= '>';
		    
		$this->_content .= $psContent;
		
		if ($pbSimpleTag)
			$this->_content .= ' />'."\n";
		else
			$this->_content .= '</'.$psTagName.'>'."\n";
			
	}
	
	
	public function __toString()
	{
		return $this->_content;
	}
}
