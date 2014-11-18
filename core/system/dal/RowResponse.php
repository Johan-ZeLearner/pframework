<?php
namespace P\lib\framework\core\system\dal;

class RowResponse
{
    private $_cursor;
    private $_results;
    
    public function __construct($poResults, $pnCursor)
    {
       $this->setCursor($pnCursor);
//       $this->_results = $poResults;
    }
    
    
    public function raw($psName)
    {
            $sField = 'raw_'.$psName;

            if (isset($this->$sField)) return $this->$sField;

            if (isset($this->$psName)) return $this->$psName;

            throw new \ErrorException($psName.' is not a member of RowResponse');
    }
    
    
    public function setCursor($pnCursor)
    {
        $this->_cursor = $pnCursor;
    }
    
    
    public function toArray()
    {
        $asData = array();
        foreach ($this as $sName => $sValue)
        {
            $asData[$sName] = $sValue;
        }
        
        return $asData;
    }
    
    
}