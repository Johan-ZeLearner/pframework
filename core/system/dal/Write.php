<?php
namespace P\lib\framework\core\system\dal;
use P\lib\framework\core\utils as utils;

class Write extends Select
{
    protected $_sSet                                    = '';
    protected $_asSet                                   = array();
    protected $table                                    = '';
    protected $type                                     = 'insert';
    protected $res;
    
    public function addSet($string)
    {
        $this->_asSet[] = $string;
    }
    
    
    public function addTable($psTable)
    {
        $this->table = $psTable;

        return $this;
    }
    
    
    /**
    * Render the query
    */
    public function __toString()
    {
        $sQuery  = '  ';
        
        if (empty($this->_asWhere))
        {
            $sQuery .= ' INSERT INTO ';
        }
        else
        {
            $sQuery = ' UPDATE ';
            $this->type = 'update';
        }
        
        $sQuery .= $this->getTable().' SET';
        
        $this->_getSet();
        if (!empty($this->_asSet))
        {
            $sQuery .= ' '.$this->_sSet;
        }

        if (!empty($this->_asWhere))
        {
            $this->_getWhere();
            if (!empty($this->_sWhere))
            {
                    $sQuery .= ' WHERE '.$this->_sWhere;
            }
        }
        
        return $sQuery;
    }
    
    
    protected function getTable()
    {
        if (!empty($this->table)) { return $this->table; }
            
        return $this->_model->getTable();
    }
    
    
    protected function _getSet()
    {
        if (!empty($this->_sSet)) { return $this->_sSet; }

        foreach ($this->_asSet as $i => $set)
        {
                $sSeparator = '';
                if ($i > 0)
                {
                    $sSeparator = ', ';
                }
                $this->_sSet .= $sSeparator.$set;
        }

        return $this->_sSet;
    }
    
    
    public function query($lastid=true)
    {
        $this->res = $this->dal->query($this->__toString());
        
        if ($lastid)
        {
            return $this->lastInsertId();
        }
        
        return $this->res;
    }
    
    
    public function lastInsertId()
    {
        if ($this->type == 'update')
        {
            return $this->rowCount();
        }
        
        return $this->dal->lastInsertId();
    }
    
    
    public function rowCount()
    {
        return $this->res->rowCount();
    }
}