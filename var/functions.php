<?php
namespace P;
use P\lib\framework\helpers as helpers;
use P\lib\framework\core\utils as utils;

function tag($psTagName, $psContent, $pasArgs=array(), $pbSimple=false)
{
	return new helpers\Tag($psTagName, $psContent, $pasArgs, $pbSimple);
}

function url($psArg01='', $psArg02='', $psArg03='')
{
    return new utils\Url($psArg01, $psArg02, $psArg03);
}

function varDisplay($psVarName)
{
    return lib\framework\themes\ThemeManager::getVar($psVarName);
}

function _l($psString, $psApp)
{
    return $psString;
}


function get($psParam, $psDefault='')
{
    return lib\framework\core\utils\Http::getParam($psParam,$psDefault);
}


function money($psString, $psDevise='&euro;')
{
    return utils\Number::money($psString, $psDevise);
}

function emptyString($psString, $psReplace='')
{
    return utils\String::emptyString($psString, $psReplace);
}