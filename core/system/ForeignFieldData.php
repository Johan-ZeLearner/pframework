<?php
namespace P\lib\framework\core\system;

class ForeignFieldData
{
    public static function populate($poController, $poDalField, $poFormField)
    {
        $sForeignField  = $poDalField->getForeignLabelField();

        $asSelection    = self::getSelection($poController, $sForeignField)->getData();

//        \P\lib\framework\core\utils\Debug::e($asSelection);
        
        foreach ($asSelection as $nKey => $asItem)
        {
            $sLabel 	= $asItem['label'];
            $nKey 	= $asItem['key'];
            $asChild 	= $asItem['child'];

            if (is_array($asChild) && !empty($asChild))
            {
                $asOptiongroup = array();
                foreach ($asChild as $nKey2 => $asItem)
                {
                        $asOptiongroup[$asItem['key']] = $asItem['label'];
                }

                $poFormField->addOption($sLabel, $asOptiongroup);
            }
            else
            {
                $poFormField->addOption($nKey, $sLabel);
            }
        }

        return $poFormField;
    }
    
    
    public static function getSelection($poController, $psFieldName='')
    {
        if (empty($psFieldName)) $psFieldName = $poController->model->getTable().'_name';

        if ($psFieldName != 'getLabel' && !isset($poController->model->_scheme->$psFieldName)) throw new \ErrorException('FieldName doesn\'t exists');

        $sPrimary 	= $poController->model->getPrimary();
        $sLabel 	= $psFieldName;

        if ($psFieldName == 'getLabel')
            $oDbResponse = $poController->model->select(array($sPrimary));
        else
            $oDbResponse = $poController->model->select(array($sPrimary, $sLabel));

        $asData = array();


        while ($oRecord = $oDbResponse->readNext())
        {
            if ($sLabel == 'getLabel')
                $asRow['label'] = $poController->getLabel($oRecord->$sPrimary);
            else
                $asRow['label'] = $oRecord->$sLabel;

            $asRow['key'] 	= $oRecord->$sPrimary;

            $asRow['child'] = false;

            $asData[] = $asRow;
        }

        return new SelectionObject($asData);
    }
}
?>
