<?php
namespace P\lib\framework\core\system\dal\schema;
use P\lib\framework\core\system\dal as dal;


class schemaBuilder
{
    private $dbSchema;
    
    /**
     * Init the construction of the DB scheme with the real 
     * database map + overriding of the $poDBSchema provided
     * 
     * @param P\lib\framework\core\system\dal\schema\Table  $poTable
     */
    public function __construct($poTable)
    {
        $sQuery = 'SHOW columns FROM '.$poTable->getName();
        
        $oQuery = dal\Dal::$oDb->query($sQuery);
        
        $asResults = $oQuery->fetchall();
        
        foreach($asResults as $asColumn)
        {
//            \P\lib\framework\core\system\Debug::dump($asColumn);
            /*
            $poTable->addColumn(array(
                    'name' 			=> 	$asColumn['Field'],
                    'type' 			=> 	$this->_getType($asColumn['Type']),
                    'size' 			=> 	$this->_getType($asColumn['Type'], true),
                    'attribute'                 => 	$this->_getUnsigned($asColumn['Type']),
                    'key'			=>	$this->_getKey($asColumn),
                    'ai'			=>      $this->_getAutoIncrement($asColumn['Extra'])
                
            ));
             * */
            
        }
    }
    
    
    
    private function _getType($psType, $pbValue=false)
    {
        preg_match('/([a-z]+)(\([0-9]+\))/i', $psType, $asReq);
        
        if (!$pbValue && isset($asReq[1])) return $asReq[1];
        if ($pbValue && isset($asReq[1])) return $asReq[2];
        
        throw new \ErrorException($psType.'( pbValue : '.(int) $pbValue.' is unknown');
    }
    
    
    private function _getUnsigned($psType)
    {
        $asReq = '';
        preg_match('/(unsigned)/i', $psType, $asReq);
         
        if (isset($asReq[1])) return 'unsigned';
         
        return '';
    }
    
    private function _getKey($asColumn)
    {
        if (isset($asColumn['key']) && !empty($asColumn['key']))
        {    
            switch($asColumn['key'])
            {
                case 'PRI':
                    return Table::PRIMARY;
                    
                default :
                    throw new \Exception('Key "'.$asColumn['key'].'" is unknown');
                    
            }
        }
    }
    
    
    private function _getAutoIncrement($psExtra)
    {
        if ($psExtra == 'auto_increment')
            return true;
        
        return false;
    }
}
?>
