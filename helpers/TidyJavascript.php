<?php
namespace P\lib\framework\helpers;

class P_Helpers_TidyJavascript
{
    public static $nTab;
    public static $nInParenthesis;

    const NL = "\r\n";
    const TL = "\t";
	
    public static function tidyJavascript($psJS)
    {
        $asCode = array();
        
        $psJS = str_replace(array("\n", "\r", "\t"), "", $psJS);
        
        $i = 0;
        $nWhiteSpace = 0;
        while(isset($psJS{$i}))
        {
            $sChar = (string) trim($psJS{$i});
            
            if ($sChar == '0')
            {
                $nWhiteSpace    = 0;
                $asCode[]       = $sChar;
            }
            elseif (!empty($sChar))
            {
                $nWhiteSpace  = 0;
                $asCode[]     = $sChar;
            }
            else
            {
                if ($nWhiteSpace == 0)
                {
                    $asCode[] = ' ';
                }
                
                $nWhiteSpace++;
            }
            
            $i++;
        }
        
        $sOutput = '';
        
        $i = 0;
        foreach($asCode as $sChar)
        {
            if ($i == 0)
                $sOutput .= self::getTab();
            
            switch ($sChar) {
            	case '{':
            	case '}':
                	$sOutput .= self::displayBrace($sChar, $asCode, $i);
                	break;
            	
            	
            	case '(':
            	case ')':
                	$sOutput .= self::displayParenthesis($sChar);
                	break;
            	
            	
            	case NL:
                	$sOutput .= self::getTab().$sChar;
                	break;
            	
            	case ',':
                	$sOutput .= self::displayComa($sChar);
                	break;
                	
            	case '+':
                	$sOutput .= ' '.$sChar.' ';
                	break;
                	
            	case '[':
            	    self::$nInParenthesis++;
                	$sOutput .= ' '.$sChar.' ';
                	break;
                	
            	case ']':
            	    self::$nInParenthesis--;
                	$sOutput .= ' '.$sChar.' ';
                	break;
            	
            	
            	default:
            	    //if (!preg_match('/[\(\)\[\]-_a-zA-z]/i', $sChar))
            	     //   $sOutput .= $this->getTab();
            	        
            	    $sOutput .= $sChar;    
            	break;
            }
            
            $i++;
        }
        
       return $sOutput;
    }
    
    
    public static function displayBrace($sChar, $pasChar, $pnIndice)
    {
        $sOutput  = self::NL;

        if (self::$nInParenthesis > 0)
            self::$nInParenthesis--;
        
        if ($sChar == '{')
        {
            $sOutput .= self::getTab();
            
            $sOutput .= $sChar;
            
            $sOutput .= self::NL;
            
            self::$nTab++;
            
            $sOutput .= self::getTab();
        }
        else
        {
            self::$nTab--;
            $sOutput .= self::getTab();
            
            $sOutput .= $sChar;
            
            if ($pasChar[($pnIndice + 1)] != ',')
            {
                $sOutput .= self::NL;
                $sOutput .= self::getTab();
            }
        }
        
        return $sOutput;
    }
    
    
    public static function displayParenthesis($psChar)
    {
        if ($psChar == '(')
            self::$nInParenthesis++;
        else
            self::$nInParenthesis--;
            
        return $psChar;
    }
    

    public static function displayComa()
    {
        $sOutput = '';
        
        if (self::$nInParenthesis > 0)
        {
            $sOutput .= ', ';
        }
        else
        {
            $sOutput .= ','.self::NL.self::getTab();
        }
        
        return $sOutput;
    }
    
    
    public static function getTab()
    {
        $sOutput = '';
        
        for ($i=1; $i<= self::$m_nTab; $i++)
        {
            $sOutput .= self::LT;
        }
        
        return $sOutput;
    }
}