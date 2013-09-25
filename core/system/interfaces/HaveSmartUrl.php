<?php
namespace P\lib\framework\core\system\interfaces;

interface HaveSmartUrl
{
	function getRewriteRules();
	
	function getRewritePath(\P\lib\framework\core\utils\Url $poUrl);
}