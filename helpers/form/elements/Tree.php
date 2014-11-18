<?php
namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\utils\Debug as utils;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\system as system;

class Tree extends SelectMultiple
{
    public $_tree;
    public $bLoaded 	= false;
    public $bPersist 	= false;
    public $selectMode 	= 3;

    public function __construct($poField)
    {
        parent::__construct($poField);

        $this->_tree = new P_Helpers_Jquery_DynaTree();
        $this->setTreeParam();
    }


    public function addSelectedValue($psValue)
    {
        if (is_array($psValue))
        {
            foreach ($psValue as $sValue)
            {
                if (!in_array($sValue, $this->_asSelectedValues))
                {
                    $this->_asSelectedValues[] = $sValue;
                    $this->_tree->addSelectValue($sValue);
                }
            }
        }
        else
        {
            if (!in_array($psValue, $this->_asSelectedValues))
            {
                $this->_asSelectedValues[] = $psValue;
                $this->_tree->addSelectValue($psValue);
            }
        }	
    }


    public function getTree()
    {
        $this->getJavascript();
    }


    public function load($pasData)
    {
        $this->_tree->load($pasData);
        $this->bLoaded = true;
    }


    public function getField()
    {
        if ($this->_errorStatus)
        {
            $this->addClass('error');
            $this->_tree->setClass('error');
        }


        if (isset($this->_field->options['behaviour']) && $this->_field->options['behaviour'] == 'onClick')
        {   
            $this->_tree->onClick = true;
        }

        $this->_tree->setSelectedIds($this->_asSelectedValues);

        if (!$this->bLoaded)
        {
            $oController = system\ClassManager::getInstance(system\PathFinder::tableToController($this->_field->getForeignTable()));
            $this->_tree->load($oController->getSelection());
        }


        if (!empty($this->_field->value) && !in_array($this->_field->value, $this->_asSelectedValues))
        {
            $this->addSelectedValue($this->_field->value);
        }

        $sTree 		= $this->_tree->getTree();
        $sOutput 	= $sTree;
        $sValues 	= '';

        foreach ($this->_asSelectedValues as $sValue)
        {
            if ($this->selectMode == 1)
                $sValues .= \P\tag('input', '', array('type' => 'hidden', 'name' => $this->getId(), 'value' => $sValue), true);
            else
                $sValues .= \P\tag('input', '', array('type' => 'hidden', 'name' => $this->getId().'[]', 'value' => $sValue), true);

        }

        $sOutput .= \P\tag('div', $sValues, array('id' => 'values_'.$this->getId()));

        return $sOutput;
    }


    protected function setTreeParam()
    {
        if (isset($this->_field->options['selectMode']))
            $this->selectMode = $this->_field->options['selectMode'];

        if ($this->selectMode == 1)
        {
            $this->_tree->setParam(utils\Tokenizer::tokenize('classNames'), array(utils\Tokenizer::tokenize('checkbox') => 'dynatree-radio'));
        }

        $this->_tree->setParam(utils\Tokenizer::tokenize('checkbox'), true);

        $this->_tree->setParam(utils\Tokenizer::tokenize('selectMode'), utils\Tokenizer::tokenize($this->selectMode));
        $this->_tree->setParam(utils\Tokenizer::tokenize('persist'), $this->bPersist);
        $this->_tree->setParam(utils\Tokenizer::tokenize('imagePath'), 'skin-vista/');

        $sFunction01 = 'function(select, node) {
            // Get a list of all selected nodes, and convert to a key array:
            var selKeys = $.map(node.tree.getSelectedNodes(), function(node){
                return node.data.key;
            });

            var sValues = "";
            nLength = selKeys.length;
            var i;
            for(i=0; i<nLength; i++)
            {
                sValues += "<input type=\"hidden\" name=\"'.$this->getId().'[]\" value=\"" + selKeys[i] + "\" />";
            }

            $("#values_'.$this->getId().'").html(sValues);
        }';

        $sFunction02 = 'function(select, node) {
            // Get a list of all selected nodes, and convert to a key array:
            var selKeys = $.map(node.tree.getSelectedNodes(), function(node){
                return node.data.key;
            });

            var sValues = "";
            nLength = selKeys.length;
            var i;
            for(i=0; i<nLength; i++)
            {
                sValues += "<input type=\"hidden\" name=\"'.$this->getId().'\" value=\"" + selKeys[i] + "\" />";
            }

            $("#values_'.$this->getId().'").html(sValues);
        }';

        if ($this->selectMode == 1)
            $this->_tree->setParam(utils\Tokenizer::tokenize('onSelect'), utils\Tokenizer::tokenize($sFunction02));
        else
            $this->_tree->setParam(utils\Tokenizer::tokenize('onSelect'), utils\Tokenizer::tokenize($sFunction01));
    }
}