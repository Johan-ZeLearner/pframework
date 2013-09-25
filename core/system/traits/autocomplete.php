<?php
namespace P\lib\framework\core\system\traits;
use P\lib\framework\core\utils as utils;
use \P\lib\framework\core\system\dal as dal;

trait autocomplete 
{
    public function autocomplete()
    {
        throw new Exception('You must implement autocomplete::autocomplete()');
    }
    
    
    protected function _autocomplete($psTable, $psFieldMatch, $psFieldId, $psFieldName)
    {
        $sTerm = trim(utils\Http::getParam('term'));
        
        $asTerm = explode(' ', $sTerm);
        
        $oSelect = new dal\Select();
        $oSelect->addTable($psTable);
        $oSelect->addFields(array($psFieldId, $psFieldName));
        $oSelect->where('id_lang = 2');
        
        foreach ($asTerm as $sTerm)
            $oSelect->andWhere($psFieldMatch.' LIKE "%'.$sTerm.'%"');
        
        $oResults = $oSelect->fetchAll();
        
        $asData = array();
        while ($oRecord = $oResults->readNext())
        {
            $asLine = array(
                'id' => $oRecord->$psFieldId,
                'label' => $oRecord->$psFieldName
            );

            $asData[] = $asLine;
        }

         return json_encode($asData);
    }
    
}

?>
