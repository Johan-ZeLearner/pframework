<?php
namespace P\lib\framework\core\system;

class Assert
{
    public static function assertInteger($value)
    {
        if (!is_integer($value))
        {
            throw new \ErrorException($value.' doit être de type  Integer');
        }
    }
    
    
    public static function assertNumeric($value)
    {
        assert('is_numeric($value)', $value.' doit être de type numérique');
    }
    
    
    public static function assertAlphaNum($value)
    {
        assert('is_numeric($value) || is_string($value)', $value.' doit être de type alphanum');
    }
    
    
    public static function assertArray($value)
    {
        assert('is_array($value)', $value.' doit être de type Tableau associatif');
    }
    
    
    public static function assertObject($value)
    {
        assert('is_object($value)', $value.' doit être de type Objet');
    }
    
    
    public static function assertBool($value)
    {
        assert('is_bool($value)', $value.' doit être de type Booleen');
    }
}