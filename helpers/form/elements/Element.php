<?php

namespace P\lib\framework\helpers\form\elements;
use P\lib\framework\core\system\dal as dal;
use P\lib\framework\core\system\traits as traits;

abstract class Element
{
    use traits\formTemplating;
    
    public 	$_field;
    protected   $_schemaField;
    protected 	$_params;
    protected 	$_label;
    protected 	$_errorMessage;
    protected 	$_errorStatus;
    protected 	$_error;
    protected 	$_asJS;
    protected 	$_asJSPath 	= array();
    public 	$simple 	= false;
    
    public    	$required 	= false;

    public function __construct(dal\schema\Field $poField)
    {
        $this->_field = $poField;
        $this->_schemaField = $poField;
        $this->_buildParamsArray();
        
        $this->initializeFormTemplating();
    }


    abstract function getField();


    public function setLabel($psLabel)
    {
        $this->_label = $psLabel;
    }
    

    public function __toString()
    {
        if ($this instanceof Hidden) return $this->getField();
        
        if ($this->required)
        {
            $this->_label = \P\tag('strong', $this->_label.' *');
            $this->_params['required'] = '';
        }
        
        $this->addThemeVar('id', $this->getId());
        $this->addThemeVar('name', $this->_params['name']);

        $oField = $this->getField();

        // Template
        $this->addThemeVar('label', $this->_label);
        $this->addThemeVar('error', $this->_getErrorMessage());
        $this->addThemeVar('description', $this->_getDescription());
        $this->addThemeVar('customData', $this->_getCustomData());
        
        
        if (is_object($oField))
            $this->addThemeVar('field', $oField->__toString());
        else
            $this->addThemeVar('field', $oField);
        
        if ($this->simple)
            return $oField;
        
        $this->theme->element = $this->_themeValues;
        
        return $this->display('element.tpl.php');
    }


    public function setName($psName)
    {
        if (empty($psName)) throw new \ErrorException('Name must not be empty');

        $this->setParam('name', $psName);
    }

    
    public function getId()
    {
        if (!isset($this->_params['id'])) $this->setId(uniqid('id'));

        return $this->_params['id'];
    }


    public function setId($psId)
    {
        if (empty($psId)) throw new \ErrorException('Id must not be empty');

        $this->setParam('id', $psId);
    }


    public function setValue($psValue)
    {
        $this->setParam('value', $psValue);
        $this->_field->value = $psValue;
    }


    public function setParam($psName, $psValue)
    {
        if (empty($psName))  throw new \ErrorException('Name must not be empty');

        $this->_params[$psName] = $psValue;
    }


    public function addClass($psClass)
    {
        if (empty($psClass)) throw new \ErrorException('$psClass must not be empty');
        
        $this->_buildClassArray();

        if (!in_array($psClass, $this->_params['class']))
            $this->_params['class'][] = $psClass;
    }


    public function removeClass($psClass)
    {
        if (empty($psClass)) throw new \ErrorException('$psClass must not be empty');

        $this->_buildClassArray();

        if (in_array($psClass, $this->_params['class']))
        {
            foreach ($this->_params['class'] as $nKey => $sValue)
            {
                if ($sValue == $psClass)
                {
                    unset($this->_params['class'][$nKey]);
                }
            }
        }

        if (!in_array($psClass, $this->_params['class']))
            $this->_params['class'][] = $psClass;
    }


    public function getDataField()
    {
        return $this->_field;
    }


    protected function _buildClassArray()
    {
        //\P\lib\framework\core\system\Debug::dump($this->_params);
        if (!isset($this->_params['class']))
            $this->_params['class'] = array();

        
        //\P\lib\framework\core\system\Debug::dump($this->_params);
        if (!empty($this->_params['class']) && !is_array($this->_params['class']))
            $this->_params['class'] = explode(' ', $this->_params['class']);
        
        //\P\lib\framework\core\system\Debug::dump($this->_params);
    }


    protected function _buildParamsArray()
    {
        $this->_params 			= array();

        $this->setValue($this->_field->value);
        $this->setName($this->_field->getName());
        $this->setId($this->_field->getName());
        $this->required                 = $this->_field->required;
        $this->_label                   = $this->_field->label;
        $this->_params['placeholder']   = $this->_getPlaceHolder();
        $this->_params['class']         = 'input-xlarge';
        $this->_placeholder             = $this->_getPlaceHolder();
        

        $asOptions = \P\lib\framework\helpers\Params::copyArray($this->_field->options, '_');

        if (is_array($asOptions))
            $this->_params = array_merge($this->_params, $asOptions);
    }


    protected function _getDescription()
    {
        return '<p class="help-block">'.$this->_field->description.'</p>'."\n";
    }

    protected function _getCustomData()
    {
        return $this->_field->customData;
    }
    
    protected function _getPlaceHolder()
    {
        $sOutput = '';
        if (!empty($this->_field->placeholder))
            $sOutput = $this->_field->placeholder;

        return $sOutput;
    }


    public function setErrorMessage($psMessage)
    {
        $this->_errorMessage = $psMessage;
    }


    protected function _getErrorMessage()
    {
        if (!empty($this->_errorMessage))
            return '<small style="color: red;">'.$this->_errorMessage.'</small><br />'."\n";
    }


    protected function _getClassError()
    {
        if ($this->_error)
            return ' error';
    }


    public function setErrorStatus($pbStatus)
    {
        $this->_errorStatus = $pbStatus;
    }


    public function getValue()
    {
        if (isset($this->_params['value']))
            return $this->_params['value'];

        return '';
    }


    public function getJS()
    {
        if (is_array($this->_asJS))
            return implode("\n", $this->_asJS);

        return '';
    }


    public function getJSPath()
    {
        $sJS = '';

        $this->_asJSPath = array_unique($this->_asJSPath);

        return $this->_asJSPath;
    }


    public function addJS($psJS)
    {
        $this->_asJS[] = $psJS;
    }


    public function addJSPath($psJSPath)
    {
        $this->_asJSPath[] = $psJSPath;
    }
}