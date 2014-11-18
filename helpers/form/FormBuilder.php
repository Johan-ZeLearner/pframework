<?php
namespace P\lib\framework\helpers\form;
use P\lib\framework\core\system\dal as dal;
use P\lib\framework\core\utils as utils;
/*
 * Design Pattern Factory
 */
class FormBuilder
{
    public static function buildField(dal\schema\Field $poField, Form $poForm)
    {
        $sType = $poField->getType();
        
//        utils\Debug::e($sType);
        
        switch ($sType)
        {
            case 'int':
            case 'smallint':
            case 'mediumint':
            case 'float':
            case 'decimal':
            case 'decimal(20,6) unsigned':
            case 'varchar':
            case 'string':
                    return new elements\Text($poField);

            case 'text':
            case 'mediumtext':
                    return new elements\Textarea($poField);

            case 'hidden':
                    return new elements\Hidden($poField);

            case 'password':
                    return new elements\Password($poField);

            case 'tinyint':
            case 'bool':
            case 'boolean':
            case 'checkbox':
                    return new elements\Checkbox($poField);

            case 'select':
            case 'foreign':
                    if (isset($poField->options['multiple']) && $poField->options['multiple'])
                        $oField = new  elements\SelectMultiple($poField);
                    else
                        $oField = new elements\Select($poField);

                    return $oField;

            case 'tree':
                    $oField = new  elements\Tree($poField);
                    return $oField;
                
            case 'autocomplete':
                    $oField =  new elements\Autocomplete($poField);
                    return $oField;

            case 'tag':
            case 'tags':
                    $oField =  new elements\Tags($poField);
                    return $oField;

            case 'html':
                    $oField = new elements\Html($poField);
                    return $oField;

            case 'color':
                    $oField = new elements\Color($poField);
                    return $oField;

            case 'date':
            case 'datetime':
                    return new elements\Date($poField);

            case 'time':
                    return new elements\Time($poField);

            case 'hour':
                    return new elements\Hour($poField);

            case 'ghost':
                    return new elements\Ghost($poField);

            case 'radio':
                    $oField = new elements\Radio($poField);

                    $poForm->_populateSelect($poField, $oField);
                    
                    return $oField;

            default:
                utils\Debug::dump($poField);
                throw new \ErrorException('Unknown field type ('.$sType.')');
        }
    }
    
    
    public static function getField($paoFields, $psName, $pbFormField)
    {
        foreach ($paoFields as $nOrder => $oField)
        {
            if (is_object($oField))
            {
                if (!($oField instanceof elements\Fieldset))
                {
                    if ( $oField->_field->getName() == $psName)
                    {
                        if ($pbFormField)
                                return $oField;
                        else
                                return $oField->_field;
                    }
                }
            }
        }


        // si on arrive ici c'est qu'on n'a pas trouvé le champ
        // Fouillons les fieldset
        foreach ($paoFields as $nOrder => $oField)
        {
            if ($oField instanceof elements\Fieldset)
            {
                foreach ($oField->_fields as $nOrder2 => $oFieldChild)
                {
                    if ( $oFieldChild->_field->getName() == $psName)
                    {
                        if ($pbFormField)
                            return $oFieldChild;
                        else
                            return $oFieldChild->_field;
                    }
                }
            }
        }

        throw new \ErrorException(__CLASS__.' :: champ '.$psName.' introuvable');
        
        return false;
    }
}
?>
