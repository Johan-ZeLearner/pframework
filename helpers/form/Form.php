<?php
namespace P\lib\framework\helpers\form;
use P\lib\framework\core\system as system;
use P\lib\framework\core\utils as utils;
use P\lib\framework\core\system\traits as traits;


/**
 *
 * This class handle forms of all kind
 * It accepts Dal_Field Object and Form_Field Object as well.
 * All data can be get as Form or Dal field
 *
 * @author johan
 *
 */
class Form
{
    use traits\formTemplating;
    
	protected   $_params;
	protected   $_fields;
	protected   $_oDal;
	protected   $_validationClass;
	protected   $_bAjax = false;
	protected   $_ajaxId;
	protected   $_id;
        public      $crud = false;
        public      $name;
        public      $title;
        
        private $_model;
        
	/**
	 * Constructor of the class.
	 * It set the main Dal of the form for auto populating field values
	 * By default, populate is true but you can specify an empty form instance
	 *
	 * @param Boolean 				$pbPopulate
	 */
	public function __construct($poModel='', $pbPopulate = true)
	{
            $this->setMethod('POST');
            $this->setAction(\P\Url());
            $this->setParam('_class', system\Settings::getParam('form', 'class', 'form-horizontal'));
            $sFormId = $this->getId();
            
            $this->initializeFormTemplating();
            
            if ($poModel instanceof \P\lib\framework\core\system\abstractClasses\Model)
                $this->_model = $poModel;
            
            // launch the potential childs initialization - for inheritance instance only
            $this->initialize();
            
            if (utils\Http::isPosted() && !empty($this->_fields))
                $this->populateFromPost();
            

            // @TODO : trouver solution au JS qui "oublie" certains champs
            
//            utils\Debug::e($this->_fields);
	}
        

	/**
	 * For inheritance only
	 */
	public function initialize() {}
        
        
        public function setTitle($psTitle)
        {
            $this->title = $psTitle;
        }
        
        
        public function getTitle()
        {
            return $this->title;
        }

        
        public function getFields($pbMustBePosted=false)
        {
            
            if (!$pbMustBePosted)
            {
                return $this->_fields;
            }
            else
            {
                $asFiels = array();
                
                foreach($this->_fields as $oFormField)
                {
                    if (!$oFormField instanceof form\elements\Fieldset) // champ normal
                    {
                        if (utils\Http::exist($oFormField->getDataField()->getName()))
                            $asFiels[] = $oFormField;
                    }
                    else
                    {
                        foreach ($oFormField->_fields as $oFormField)
                        {
                            if ($oFormField->getDataField()->recordable)
                            {
                                if (utils\Http::exist($oFormField->getDataField()->getName()))
                                    $asFiels[] = $oFormField;
                            }
                        }
                    }
                }
                
                return $asFiels;
            }
        }
        
	
	/**
	 * Shortcut to set the id Parameter
	 *
	 * @param unknown_type $psId
	 */
	public function setId($psId)
	{
            $this->_params['id'] = $psId;
            return $this;
	}
	
	
	/**
	 * Shortcut to get the Id Parameter
	 */
	public function getId()
	{
            if (!isset($this->_params['id']))
                    $this->_params['id'] = uniqid('form_');

            return $this->_params['id'];
	}

	
	/**
	 * Set a param (couple of key -> value) for the Form
	 * eg: id, class ...
	 *
	 * @param String $psParam
	 * @param String $psValue
	 * @throws ErrorException
	 */
	public function setParam($psParam, $psValue)
	{
            if (empty($psParam)) throw new \ErrorException('Param must not be empty');

            $this->_params[$psParam] = $psValue;
	}


	/**
	 * Set the send Method.
	 * Must be POST or GET
	 *
	 * @param String $psMethod
	 * @throws ErrorException
	 */
	public function setMethod($psMethod='POST')
	{
            if (strtolower($psMethod) != 'post' && strtolower($psMethod) != 'get') throw new \ErrorException('Method must be POST or GET');

            $this->_params['_method'] = 'post';
	}


	/**
	 * Set the Url of the action.
	 * Must be an instance of P_Core_Utils_Url
	 *
	 * @param P_Core_Utils_Url $poUrl
	 */
	public function setAction(\P\lib\framework\core\utils\Url $poUrl)
	{
            $this->_params['_action'] = $poUrl;
	}


	/**
	 * Add a field to the Form
	 *
	 * @param P_Core_Helpers_Form_Field $poField
	 */
	public function addField($poField, $pnPosition=0)
	{
            if ($poField instanceof \P\lib\framework\core\system\dal\schema\Field)
            {
                    $poField = FormBuilder::buildField($poField, $this);
            }
            elseif (is_array($poField))
            {
                    return $this->addFieldArray($poField, $pnPosition);
            }
            elseif (!$poField instanceof elements\Element)
            {
                    throw new \ErrorException('$poField must be an instance of element\Element');
            }

            if ($pnPosition > 0 && count($this->_fields) > $pnPosition)
            {
                    $this->_refactorFieldsOrder($poField, $pnPosition);
            }
            else
            {
                    $this->_fields[] = $poField;
            }
	}
        
        
        public function addFieldArray($pasField, $pnPosition)
        {
            if (!isset($pasField['name']))
                throw new \ErrorException('Field must contains the "name" parameter');
            
            $oField = new system\dal\schema\Field($pasField['name']);
            
            if (isset($pasField['type']))
                $oField->setType($pasField['type']);
            
            if (isset($pasField['input_type']))
                $oField->setInputType($pasField['input_type']);
            
            if (isset($pasField['length']))
                $oField->setLength($pasField['length']);
            
            if (isset($pasField['required']) && is_bool($pasField['required']))
                $oField->required = $pasField['required'];
            
            if (isset($pasField['pattern']))
                $oField->pattern = $pasField['pattern'];
            
            if (isset($pasField['placeholder']))
                $oField->setPlaceHolder($pasField['placeholder']);
            
            if (isset($pasField['help']))
                $oField->description = $pasField['help'];
            
            if (isset($pasField['label']))
                $oField->label = $pasField['label'];
            
            if (isset($pasField['value']))
                $oField->value = $pasField['value'];
            
            if (isset($pasField['foreign_table']))
                $oField->setForeignTable($pasField['foreign_table']);
            
            if (isset($pasField['foreign_field']))
                $oField->setForeignField($pasField['foreign_field']);
            
            if (isset($pasField['foreign_field_label']))
                $oField->setForeignLabelField($pasField['foreign_field_label']);
            
            if (isset($pasField['custom'])) // 2 possibilities for custom data
                $oField->addCustomData($pasField['custom']);
            elseif (isset($pasField['customData'])) //
                $oField->addCustomData($pasField['customData']);
//            else
//                utils\Debug::e ('pas de custom pour '.$pasField['name']);
            
//            if ($pasField['name'] == 'active')
//                utils\Debug::e ($oField);
            
            $this->addField($oField);
        }


	/**
	 * Special method for adding a fieldset field
	 *
	 * @param P_Helpers_Form_Fields_Fieldset $poField
	 * @param Integer $pnPosition
	 */
	public function addFieldset($poField, $pnPosition)
	{
	    if (!($poField instanceof elements\Fieldset))
	        return false;


            if ($pnPosition > 0 && count($this->_fields) > $pnPosition)
            {
                $this->_refactorFieldsOrder($poField, $pnPosition);
            }
            else
            {
                $this->_fields[] = $poField;
            }
	}


	/**
	 * If the Dal is set, the method try to save all recordable fields automatically
	 *
	 * @param Integer $pnPK
	 * @param Boolean $pbDebug
	 * @throws ErrorException
	 *
	 * @return Boolean
	 */
	public function save($pnPK=0, $pbDebug=false)
	{
            return $this->_model->saveForm($this, $pnPK, $pbDebug);
	}


	/**
	 * Set the template vars and renders it
	 *
	 * @return String $sOutput
	 */
	public function getForm()
	{
            $this->addThemeVar('params', \P\lib\framework\helpers\Params::serialize($this->_params, '_'));
            
            
            $oSubmit            = new \stdClass();
            $oSubmit->value     = 'valider';
            $oSubmit->class     = 'btn btn-success';      
            $oSubmit->id        = ''; 
            
            if ($this->_bAjax)
            {
                $oSubmit->id        = $this->_ajaxId;
            }

            $this->addThemeVar('submit', $oSubmit);
            $this->addThemeVar('fields', $this->_fields);
            $this->addThemeVar('title', $this->getTitle());

            $this->theme->current_form = $this->_themeValues;
            
            return $this->display('form.tpl.php');
	}


	/**
	 *
	 * Internal shortcut to $this->getForm()
	 */
	public function __toString()
	{
            return $this->getForm();
	}


	/**
	 * Populate the form with the content of the current Model's Table Scheme
	 *
	 */
	private function _populate()
	{
            return $this->_model->populateForm($this);
	}


	/**
	 * If called, this method launch the specified controller's
	 * getSelection Method for population the Select Field Family Instance
	 *
	 * @param P_Core_System_Dal_Field $poDalField
	 * @param P_Helpers_Form_Fields_Field $poFormField
	 * @throws ErrorException
	 */
	public function populateSelect(system\dal\schema\Field $poDalField, elements\Element $poFormField)
	{
	    $sForeignTable   	= $poDalField->getForeignTable();
	    
	    if (empty($sForeignTable)) return $poFormField;
	    
	    $sClassName 		= system\PathFinder::tableToController($sForeignTable);
	    $oClass    			= system\ClassManager::getInstance($sClassName);
	    
	    if (strlen($sClassName) < 6) throw new \ErrorException($sForeignTable.' -- '.$sClassName.' -- There is a probleme with the foreign table values. Run WLP.');

	    if (!is_object($oClass)) return false;
            
	    $oClass->populateForeignField($poDalField, $poFormField);

	    return $poFormField;
	}


	/**
	 * This method re-order the fields as specified
	 *
	 * @param $poField
	 * @param Integer $pnPosition
	 */
	private function _refactorFieldsOrder($poField, $pnPosition)
	{
            $aoTempFields = array();

            $i = 0;
            foreach ($this->_fields as $oField)
            {
                    if ($pnPosition == $i)
                            $aoTempFields[] = $poField;

                    $aoTempFields[] = $oField;
                    $i++;
            }

            $this->_fields = $aoTempFields;
	}


	/**
	 * Check if the values within the form are correct
	 *
	 * @return Boolean
	 */
	public function isValid()
	{
          //  utils\Debug::e($_POST);
		// on rempli les valeurs de _scheme
	    foreach ($this->_fields as $oFormField)
	    {
	        if (!$oFormField instanceof elements\Fieldset)
	        {
                    $oDataField = $oFormField->getDataField();
                    $sValue = utils\Http::getParam($oDataField->getName());

                    $oDataField->value = $sValue;
                    $oFormField->setValue($sValue);
	        }
	        else
	        {
                    foreach ($oFormField->_fields as $oFormField2)
                    {   
                        $sValue = utils\Http::getParam($oFormField2->_field->getName());
                        $oFormField2->_field->value = $sValue;
                    }
	        }
	    }

            return true;
	}


	/**
	 *
	 * Return either the Form field or the Dal Field
	 *
	 * @param String $psName
	 * @param Boolean $pbFormField
	 * @throws ErrorException
	 * @return P_Helpers_Form_Fields_Field
	 */
	public function getField($psName, $pbFormField=false)
	{
            return FormBuilder::getField($this->_fields, $psName, $pbFormField);
	}
	
	
	/**
	 * Public method used to directly have a Form field from a Dal Field
	 *
	 * @param P_Core_System_Dal_Field $poDalField
	 */
	public function buildField(system\dal\Field $poDalField)
	{
		return FormBuilder::buildField($poDalField);
	}
	
	
	/**
	 * Set the Ajax id of the submit button and enable the form ajax-ready options
	 *
	 * @param String $psAjaxId
	 */
	public function isAjax($psAjaxId='')
	{
            $this->_bAjax = true;

            $this->_ajaxId = uniqid('ajax_');
            if (!empty($psAjaxId))
                $this->_ajaxId = $psAjaxId;
	}
	
	
	/**
	 * Return the javascript code generated by the fields of the form
	 * It is active only if isAjax() is set to true
	 *
	 */
	public function getAjax()
	{
            $sJS = '';

            foreach ($this->_fields as $oField)
            {
                    $sJS .= $oField->getJS();
            }

            return $sJS;
	}
	
	
	/**
	 * Return the javascript  code generated by the fields of the form
	 * It is active only if isAjax() is set to true
	 *
	 */
	public function getAjaxPath()
	{
            $asJS = array();

            foreach ($this->_fields as $oField)
            {
                $asTempJS = $oField->getJSPath();

                if (is_array($asTempJS))
                    $asJS = array_merge($oField->getJSPath(), $asJS);
            }
            
            $asJS = array_unique($asJS);

            $sJSPath = '';

            foreach ($asJS as $sJS)
            {
                $sJSPath .= tag('script', '', array('type' => 'text/javascript', 'src' => HOST_URL.'/'.$sJS));
            }

            return $sJSPath;
	}
	
	
	/**
	 * Populate the FORM (not the Dal scheme) with the available POST data
	 */
	protected function populateFromPost()
	{
            if (!$this->_fields)
                throw new \ErrorException('erreur');
            
            foreach ($this->_fields as $oField)
            {
                if ($oField instanceof elements\Fieldset)
                {
                    foreach ($oField->_fields as $nOrder => $oFieldChild)
                    {
                        $oFieldChild->setValue(utils\Http::getParam($oFieldChild->_field->getName()));
                    }
                }
               else
               {
//                    utils\Debug::e($oField->_field->getName());
//                    utils\Debug::e(utils\Http::getParam($oField->_field->getName()));
                   
                   $oField->setValue(utils\Http::getParam($oField->_field->getName()));
               }
            }
            
//            utils\Debug::e('OK from post');
//            
//            utils\Debug::e($this->_fields);
	}
        
        
}