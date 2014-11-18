<?php

namespace P\lib\framework\core\system\traits;

trait GetShopId
{
    public function getIdShop($id_shop=0)
    {
        if ($id_shop > 0)
            return $id_shop;
        
        return \P\override\Context::getShopId();
    }
}