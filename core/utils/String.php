<?php
namespace P\lib\framework\core\utils;
/**
 * Utility static class for basic String manipulation
 *
 * @author johan
 *
 */
class String
{
	/**
	 * UTF-8 ready wrapper of the php standard funtion 'substr'.
	 * It also handle the length of the final string and put an optionnal "..."
	 * if asked.
	 *
	 * @param String $psString
	 * @param Integer $pnStart
	 * @param Integer $pnEnd : ''
	 * @param Boolean $pbDots : false
	 */
	static function substr($psString, $pnStart, $pnEnd='', $pbDots=false)
	{
		$nLength = $pnEnd - $pnStart;
		
		$sDots = '';		// by default dot is empty
		
		// check if we need the dots
		if ($pbDots && ($pnStart < 0 || ($nLength < strlen($psString))))
			$sDots = '...';
			
		// we take care of any eventual utf-8 encoded character wich may be more than 1 bit long.
		return utf8_encode(substr(utf8_decode($psString), $pnStart, $pnEnd)).$sDots;
	}
        
        
        
        public static function escapeChar($char, $string)
        {
            return str_replace($char, '\\'.trim($char), $string);
        }
        
        
        public static function lastSlice($psString, $psSeparator)
        {
            $asSlices = explode($psSeparator, $psString);
            
            return $asSlices[(count($asSlices) - 1)];
        }
        
        
        public static function rewrite($psString)
        {
            $translit = array('.' => '', 'Á'=>'A','À'=>'A','Â'=>'A','Ä'=>'A','Ã'=>'A','Å'=>'A','Ç'=>'C','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Í'=>'I','Ï'=>'I','Î'=>'I','Ì'=>'I','Ñ'=>'N','Ó'=>'O','Ò'=>'O','Ô'=>'O','Ö'=>'O','Õ'=>'O','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','á'=>'a','à'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ñ'=>'n','ó'=>'o','ò'=>'o','ô'=>'o','ö'=>'o','õ'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y');
	$sString = strtr($psString, $translit);
	$sString = preg_replace('#[^a-zA-Z0-9\-\._]#', '-', $sString);
        $sString    = str_replace(array('------', "-----", '----', '---', '--'), '-', $sString);
        $sString    = strtolower($sString);
            
            
            return $sString;
            
            
            $sString = $psString;
            
            $sString    = self::stripAccents($sString);
            $sString    = str_replace(' ', '-', $sString);
            $sString    = str_replace(array(',', '.', '+', '/', '&', ';'), '-', $sString);
            $sString    = str_replace(array('"', "'", '°', '(', ')', '[', ']', '{', '}'), '', $sString);
            $sString    = str_replace(array('------', "-----", '----', '---', '--'), '-', $sString);
            $sString    = strtolower($sString);
            
            return $sString;
        }
        
        
        public static function stripAccents($string)
        {
            $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
            $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
            
            $string = Encoding::toUTF8($string);
            
            return str_replace(
                    $search,
                    $replace,
                    $string
            );
        }
        
        public static function getRandomString($length = 0)
        {	
            $code = md5(uniqid(rand(), true));
            if ($length != 0) return substr($code, 0, $length);
            else return $code;
        }
        
        
        public static function getExtension($psFileName)
        {
            return substr(strrchr($psFileName,'.'),1);
        }
        
        
        public static function verifyEmail($psEmail)
        {
            if(filter_var($psEmail, FILTER_VALIDATE_EMAIL))
            {
                return true;
            }
            
            return false;
        }
        
        
        public static function emptyString($psString, $psReplace='vide')
        {
            if (empty($psString))
                return $psReplace;
            
            return $psString;
        }
        
        
        public static function generatePasswd($numAlpha=6, $numNonAlpha=2)
        {
           $listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
           $listNonAlpha = ',;:!?.$/*-+&@_+;./*&?$-!,';
           return str_shuffle(
              substr(str_shuffle($listAlpha),0,$numAlpha) .
              substr(str_shuffle($listNonAlpha),0,$numNonAlpha)
            );
        }
        
        
//        function generateHashWithSalt($password) 
//        {
//            $max_length         = 8;
//            $intermediateSalt   = md5(uniqid(rand(), true));
//            $salt               = substr($intermediateSalt, 0, $max_length);
//            
//            return hash("sha256", $password . $salt);
//        }
        
        
        public static function sanitize($psString)
        {
            $sString = trim( preg_replace( '/\s+/', ' ', $psString ) );
            
            return strip_tags(str_replace(array("0x0A", "0x0D", "\n", "\r", "\r\n", "\n\r", ';', '|'), array('', '', '', '', '', '', '-', '-'), $sString));
        }
        
        
        public static function upperCase($psString)
        {
            $psString = Encoding::toLatin1($psString);
            $psString = self::stripAccents($psString);
            $psString = strtoupper($psString);
            
            return Encoding::toUTF8($psString);
        }
}

