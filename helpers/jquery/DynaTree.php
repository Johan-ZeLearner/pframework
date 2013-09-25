<?php
namespace P\lib\framework\helpers\jquery;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class DynaTree extends Treeview
{
    protected   $_asSelectedValues;
    public      $select 	= true;
    public      $deselect 	= true;
    public      $toggle	 	= true;
	
	public function __construct()
	{
            helpers\JSManager::jQueryEnable(false);
            helpers\JSManager::addFile('jquery.dynatree.js', 'resources/js/dynatree/');
            helpers\JSManager::addFile('jquery.cookie.js', 'resources/js/');
            helpers\CSSManager::addFile('ui.dynatree.css', 'resources/js/dynatree/skin-vista/');
	}
	
	
	public function setSelectedIds($pasSelectedValues)
	{
            $this->_asSelectedValues = $pasSelectedValues;
	}
	
	
	public function addSelectValue($psValue)
	{
            $this->_asSelectedValues[] = $psValue;
	}
	
	
	public function getTree()
	{
            if ($this->_renderFromSelection)
            {
                $asOutput = $this->_renderBranchFromSelection($this->_oSelectionObject->getData(), $this->getId());
            }
            else
            {
                $asOutput = $this->_renderBranch($this->_asData, $this->getId());
            }

            $sId = uniqid();

            $this->setParam('children', utils\Tokenizer::tokenize('treeData_'.$sId));

            $sJs = '
                var treeData_'.$sId.' = '.json_encode($asOutput).';
            ';

            helpers\JSManager::addInstructions($sJs);

            $this->_getJavascript();

            $sOutput = '';

            $sOutput .= \P\tag('a', 'Tout cocher', array('id' => 'btnSelectAll_'.$this->getId(), 'class' => 'dyna_action'));
            $sOutput .= \P\tag('a', 'Tout décocher', array('id' => 'btnDeselectAll_'.$this->getId(), 'class' => 'dyna_action'));
            $sOutput .= \P\tag('br', '', '', true);

            if ($this->onClick)
            {
                    $sOutput .= \P\tag('div', '', array('id' => $this->getId(), 'style' => 'display:none;'));

                    $sInput = $sOutput;

                    $sOutput  = \P\tag('div', 'Cliquez pour voir la selection', array('class' => 'multipleSelectHandle', 'id' => 'handle_'.$this->getId()));
            $sOutput .= $sInput;

            $sJS = '
                jQuery(".dyna_action").hide();

                jQuery("#'.'handle_'.$this->getId().'").click(
                    function()
                    {
                        jQuery("#'.$this->getId().'").toggle();
                        jQuery(".dyna_action").toggle();
                    }
                )
            ';

            helpers\JSManager::addInstructions($sJS);
            }
            else
            {
                $sOutput .= tag('div', '', array('id' => $this->getId(), 'class' => $this->getClass()));
            }

            return $sOutput;
	}
	
	

	protected function _getJavascript()
	{
		$sJS = '
			jQuery("#'.$this->getId().'").dynatree('.$this->_getParam().');
		';
		
		helpers\JSManager::addInstructions($sJS);
		
		if ($this->select)
		{
                    $sJS = '

                        $("#btnSelectAll_'.$this->getId().'").click(function(){
                            $("#'.$this->getId().'").dynatree("getRoot").visit(function(node){
                                    node.select(true);
                            });
                            
                            return false;
                        });
                    ';

                    helpers\JSManager::addInstructions($sJS);
		}
		
		if ($this->deselect)
		{
                    $sJS = '
                        $("#btnDeselectAll_'.$this->getId().'").click(function(){
                                $("#'.$this->getId().'").dynatree("getRoot").visit(function(node){
                                        node.select(false);
                                });
                                return false;
                        });
                    ';

                    helpers\JSManager::addInstructions($sJS);
		}
	}
	
	
	protected function _renderBranchFromSelection($pasData)
	{
		//dump($this->_asSelectedValues);
		$asOutput = array();
		
		foreach ($pasData as $asData)
		{
                    $asLine = array();

                    $asLine['title'] 	= $asData['label'];
                    $asLine['key'] 		= $asData['key'];

                    if (isset($asData['child']) && is_array($asData['child']) && !empty($asData['child']))
                    {
                        $asLine['children'] =  $this->_renderBranchFromSelection($asData['child']);
                    }

                    //dump($this->_asSelectedValues);

                    if (in_array($asData['key'], $this->_asSelectedValues))
                    {
                        $asLine['select'] = true;
                        //dump("selected ?");
                    }

                    $asOutput[] = $asLine;
		}
		
		return $asOutput;
	}
	
}