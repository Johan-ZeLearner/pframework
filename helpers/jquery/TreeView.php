<?php
namespace P\lib\framework\helpers\jquery;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Treeview
{
	protected $_asParam = array();
	protected $_asData = array();
	protected $_oSelectionObject;
	protected $_id;
	protected $_class;
	protected $_renderFromSelection = false;
	public $onClick = false;
	
	public function __construct()
	{
		helpers\JSManager::jQueryEnable(false);
		helpers\JSManager::addFile('treeview.js', 'resources/js/treeview/');
	}
	
	
	public function setParam($psParam, $psValue)
	{
            if (empty($psParam)) throw new \ErrorException('psParam must not be truncate');

            $this->_asParam[$psParam] = $psValue;
	}
	
	
	public function getId()
	{
            if (!empty($this->_id)) return $this->_id;

            $this->setId(uniqid('tree'));

            return $this->_id;
	}
	
	
	public function setId($psId)
	{
            $this->_id = $psId;
	}
	
	public function getClass()
	{
            if (empty($this->_class)) return '';

            return $this->_class;
	}
	
	
	public function setClass($psClass)
	{
            $this->_class = $psClass;
	}
	
	public function load($pasData)
	{
            if ($pasData instanceof system\SelectionObject)
            {
                $this->_renderFromSelection = true;
                $this->_oSelectionObject	= $pasData;
            }
            else
            {
                $this->_asData = $pasData;
            }
	}
	
	
	public function add($psData, $psParent='root')
	{
            if ($psParent != 'root')
            {
                if (isset($this->_asData[$psParent]))
                    $this->_asData[$psParent][] = $psData;
            }
            else
            {
                $this->_asData[] = $psData;
            }
	}
	
	
	public function getTree()
	{
            if ($this->_renderFromSelection)
            {
                $sOutput = $this->_renderBranchFromSelection($this->_oSelectionObject->getData(), $this->getId());
            }
            else
            {
                $sOutput = $this->_renderBranch($this->_asData, $this->getId());
            }

            $this->_getJavascript();

            return $sOutput;
	}
	
	
	protected function _getJavascript()
	{
            $sJS = '

                jQuery("#'.$this->getId().'").treeview('.$this->_getParam().');

            ';

            helpers\JSManager::addInstructions($sJS);
	}
	
	
	protected function _renderBranch($pasData)
	{
            $sOutput = '';

            foreach ($pasData as $asData)
            {
                if (is_array($asData))
                    $sOutput .=  \P\tag('li',  $this->_renderBranch($asData));
                else
                    $sOutput .= \P\tag('li', $asData);
            }

            return \P\tag('ul', $sOutput);
	}
	
	
	protected function _renderBranchFromSelection($pasData)
	{
            $sOutput = '';

            foreach ($pasData as $asData)
            {
                if (isset($asData['child']) && is_array($asData['child']) && !empty($asData['child']))
                    $sOutput .=  \P\tag('li',  $this->_renderBranchFromSelection($asData['child']));
                else
                    $sOutput .= \P\tag('li', $asData['label'], array('id' => $this->getId().'_'.$asData['key']));
            }

            return \P\tag('ul', $sOutput);
	}
	
	
	protected function _getParam()
	{
		if (empty($this->_asParam)) return '';
		
		return Tokenizer::replace(json_encode($this->_asParam));
	}
	
}