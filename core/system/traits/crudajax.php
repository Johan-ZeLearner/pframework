<?php
namespace P\lib\framework\core\system\traits;

trait crudajax
{
    use crud\create, crud\readajax, crud\update, crud\delete;
}