<?php
namespace P\lib\framework\core\utils;

use P\lib\framework\core\system\PathFinder;

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
     * @param String  $psString
     * @param Integer $pnStart
     * @param Integer $pnEnd  : ''
     * @param Boolean $pbDots : false
     */
    static function substr($psString, $pnStart, $pnEnd = '', $pbDots = false)
    {
        $nLength = $pnEnd - $pnStart;

        $sDots = ''; // by default dot is truncate

        // check if we need the dots
        if ($pbDots && ($pnStart < 0 || ($nLength < strlen($psString))))
        {
            $sDots = '...';
        }

        // we take care of any eventual utf-8 encoded character wich may be more than 1 bit long.
        return utf8_encode(substr(utf8_decode($psString), $pnStart, $pnEnd)) . $sDots;
    }


    public static function escapeChar($char, $string)
    {
        if (is_array($string))
        {
            return self::espaceArray($char, $string);
        }

        return str_replace($char, '\\' . trim($char), $string);
    }


    public function espaceArray($char, $array)
    {
        foreach ($array as $key => $value)
        {
            $array[$key] = self::escapeChar($char, $value);
        }

        return $array;
    }


    public static function lastSlice($psString, $psSeparator)
    {
        $asSlices = explode($psSeparator, $psString);

        return $asSlices[(count($asSlices) - 1)];
    }


    public static function rewrite($psString)
    {
        $translit = array(
            '.' => '',
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ç' => 'C',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ï' => 'I',
            'Î' => 'I',
            'Ì' => 'I',
            'Ñ' => 'N',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'Õ' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ç' => 'c',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'õ' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y'
        );
        $sString  = strtr($psString, $translit);
        $sString  = preg_replace('#[^a-zA-Z0-9\-\._]#', '-', $sString);
        $sString  = str_replace(array(
            '------',
            "-----",
            '----',
            '---',
            '--'
        ), '-', $sString);
        $sString  = strtolower($sString);

        $len = strlen($sString) - 1;

        if ($sString{$len} == '-')
        {
            $sString = substr($sString, 1, -1);
        }

        return $sString;
    }


    public static function stripAccents($string)
    {
        $search  = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");

        $string = Encoding::toUTF8($string);

        return str_replace($search, $replace, $string);
    }


    public static function getRandomString($length = 0)
    {
        $code = md5(uniqid(rand(), true));
        if ($length != 0)
        {
            return substr($code, 0, $length);
        }
        else
        {
            return $code;
        }
    }


    public static function getExtension($psFileName)
    {
        return strtolower(substr(strrchr($psFileName, '.'), 1));
    }


    public static function verifyEmail($psEmail)
    {
        if (filter_var($psEmail, FILTER_VALIDATE_EMAIL))
        {
            return true;
        }

        return false;
    }


    public static function emptyString($psString, $psReplace = 'vide', $psClass = '')
    {
        if (!empty($psString))
        {
            return $psString;
        }

        if (!empty($psClass))
        {
            $psReplace = \P\tag('span', $psReplace, array('class' => $psClass));
        }

        return $psReplace;
    }


    public static function sanitize($psString)
    {
        $sString = trim(preg_replace('/\s+/', ' ', $psString));

        return strip_tags(str_replace(array(
            "0x0A",
            "0x0D",
            "\n",
            "\r",
            "\r\n",
            "\n\r",
            ';',
            '|'
        ), array(
            '',
            '',
            '',
            '',
            '',
            '',
            '-',
            '-'
        ), $sString));
    }


    public static function removeNewLine($psString)
    {
        $sString = trim(preg_replace('/\s+/', ' ', $psString));

        return str_replace(array(
            "0x0A",
            "0x0D",
            "\n",
            "\r",
            "\r\n",
            "\n\r"
        ), array(
            '',
            '',
            '',
            '',
            '',
            ''
        ), $sString);
    }


    public static function upperCase($psString)
    {
        $psString = Encoding::toLatin1($psString);
        $psString = self::stripAccents($psString);
        $psString = strtoupper($psString);

        return Encoding::toUTF8($psString);
    }


    public static function bool($bool)
    {
        return ($bool) ? 'Oui' : 'Non';
    }


    public static function surround($needle, $surround)
    {
        if (is_array($needle))
        {
            $output = array();
            foreach ($needle as $key => $item)
            {
                $output[$key] = $surround . $item . $surround;
            }

            return $output;
        }

        return $surround . $needle . $surround;
    }


    /**
     * @param $filepath
     *
     * @return string
     */
    public static function getFileNameFromPath($filepath)
    {
        return basename($filepath);
    }


    public static function xmlToArray($xml, $options = array())
    {
        $defaults       = array(
            'namespaceSeparator' => ':',
            //you may want this to be something other than a colon
            'attributePrefix'    => '@',
            //to distinguish between attributes and nodes with the same name
            'alwaysArray'        => array(),
            //array of xml tag names which should always become arrays
            'autoArray'          => true,
            //only create arrays for tags which appear more than once
            'textContent'        => '$',
            //key used for the text content of elements
            'autoText'           => true,
            //skip textContent key if node has no attributes or child nodes
            'keySearch'          => false,
            //optional search and replace on tag and attribute names
            'keyReplace'         => false
            //replace values for above search values (as passed to str_replace())
        );
        $options        = array_merge($defaults, $options);
        $namespaces     = $xml->getDocNamespaces();
        $namespaces[''] = null; //add base (empty) namespace

        //get attributes from all namespaces
        $attributesArray = array();
        foreach ($namespaces as $prefix => $namespace)
        {
            foreach ($xml->attributes($namespace) as $attributeName => $attribute)
            {
                //replace characters in attribute name
                if ($options['keySearch'])
                {
                    $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
                }
                $attributeKey                   = $options['attributePrefix'] . ($prefix ? $prefix . $options['namespaceSeparator'] : '') . $attributeName;
                $attributesArray[$attributeKey] = (string)$attribute;
            }
        }

        //get child nodes from all namespaces
        $tagsArray = array();
        foreach ($namespaces as $prefix => $namespace)
        {
            foreach ($xml->children($namespace) as $childXml)
            {
                //recurse into child nodes
                $childArray = self::xmlToArray($childXml, $options);
                list($childTagName, $childProperties) = each($childArray);

                //replace characters in tag name
                if ($options['keySearch'])
                {
                    $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
                }
                //add namespace prefix, if any
                if ($prefix)
                {
                    $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
                }

                if (!isset($tagsArray[$childTagName]))
                {
                    //only entry with this key
                    //test if tags of this type should always be arrays, no matter the element count
                    $tagsArray[$childTagName] = in_array($childTagName, $options['alwaysArray']) || !$options['autoArray'] ? array($childProperties) : $childProperties;
                }
                elseif (is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)
                )
                {
                    //key already exists and is integer indexed array
                    $tagsArray[$childTagName][] = $childProperties;
                }
                else
                {
                    //key exists so convert to integer indexed array with previous value in position 0
                    $tagsArray[$childTagName] = array(
                        $tagsArray[$childTagName],
                        $childProperties
                    );
                }
            }
        }

        //get text content of node
        $textContentArray = array();
        $plainText        = trim((string)$xml);
        if ($plainText !== '')
        {
            $textContentArray[$options['textContent']] = $plainText;
        }

        //stick it all together
        $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '') ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

        //return node as array
        return array(
            $xml->getName() => $propertiesArray
        );
    }


    /**
     * @param string $xml
     *
     * @return mixed
     */
    public static function xmlToJson($xml)
    {
        $xml = simplexml_load_string($xml);
        return json_decode(json_encode(self::xmlToArray($xml)));
    }


    public static function plural($string, $value)
    {
        if ($value > 1)
        {
            return $string.'s';
        }

        return $string;
    }
}

