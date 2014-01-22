<?php

namespace P\lib\framework\core\system\traits;

trait GetStoreId
{
    public function getIdStore($id_store=0)
    {
        if ($id_store > 0)
            return $id_store;
        
        return \P\override\Context::getStoreId();
    }
}