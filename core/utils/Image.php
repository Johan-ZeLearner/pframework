<?php
namespace P\lib\framework\core\utils;

class Image
{
    private $filepath;
    private $imagick;
    static $outputPath = '';
    private $fail = false;
    
    
    public function __construct($psFilename, $psOutputPath='')
    {
        try
        {
            $this->imagick      = new \Imagick($psFilename);
        }
        catch(\Exception $e)
        {
//            die($e->getMessage());
            $this->fail = true;
            return false;
        }
        
        $this->filepath     = $psFilename;
        self::$outputPath   = $psOutputPath;
        
        Debug::$log = false;
    }

    
    public function __toString() 
    {
        return 'chemin : '.$this->filepath;
    }


    public function liquidResize($pnWidth, $pnHeight, $psFinalPath)
    {
        if (!is_object($this->imagick)) return 'http://placehold.it/'.$pnWidth.'x'.$pnHeight;
       
        $this->_resize($pnWidth, $pnHeight, false);
        $this->imagick->liquidrescaleimage($pnWidth, $pnHeight, 3, 25);
        
        return $this->save($psFinalPath);
    }
    
    
    public function getDimensions()
    {
        return $this->imagick->getimagegeometry();
    }
    
    
    public function thumbnailResize($pnWidth, $pnHeight, $psFinalPath)
    {
        if (!is_object($this->imagick)) return 'http://placehold.it/'.$pnWidth.'x'.$pnHeight;
       
        if ($this->fail) {return false;}
        
        $this->_resize($pnWidth, $pnHeight, false, true);
        
        Debug::log('----------------------------------------------------------------------');
        Debug::log('thumbnail de '.$this->filepath);
        
        $asGeo = $this->imagick->getimagegeometry();
        
        Debug::log('Thumb de '.$asGeo['width'].'x'.$asGeo['height'].' vers '.$pnWidth.'x'.$pnHeight);
        
        if ($pnHeight < $asGeo['height'])
        {
            $nX = 0;
            $nY = (int) (($asGeo['height'] - $pnHeight) / 2);
        }
        elseif ($pnWidth < $asGeo['width'])
        {
            $nX = (int) (($asGeo['width'] - $pnWidth) / 2);
            $nY = 0;
        }
        elseif($pnWidth == $asGeo['width'] && $pnHeight == $asGeo['height'])
        {
            $nX = 0;
            $nY = 0;
        }
        else
        {
            Debug::log('thumb : echec du resize');
            $nX = 0;
            $nY = 0;
            //return 'http://placehold.it/'.$pnWidth.'x'.$pnHeight;
        }
        
        Debug::log('X : '.$nX.' -- Y : '.$nY);
        //Debug::log('APRES : '.($asGeo['width'] - ($nX * 2)).'x'.($asGeo['height'] - ($nY * 2)));
        
        $this->imagick->cropImage($pnWidth, $pnHeight, $nX, $nY);
        
        return $this->save($psFinalPath);
    }
    
    public function resize($pnWidth, $pnHeight, $psFinalPath, $pbFill=false)
    {
        $this->_resize($pnWidth, $pnHeight, true, $pbFill);
        
        return $this->save($psFinalPath);
    }
    
    
    public function resizeFill($pnWidth, $pnHeight, $psFinalPath)
    {
        if (is_file($psFinalPath)) { return $this->getPublicPath($psFinalPath); }
        if ($this->fail) {return false;}
        if (!is_object($this->imagick)) {return false;}
        
        $asGeo          = $this->imagick->getimagegeometry();
        
        $bBiggerWidth   = $this->_isLarger($pnWidth, $asGeo['width']);
        $bBiggerHeight  = $this->_isLarger($pnHeight, $asGeo['height']);
        
        if ($bBiggerHeight || $bBiggerWidth)
        {
            $this->_resizeInBox($pnWidth, $pnHeight);
            
           // $this->imagick->adaptiveResizeImage($pnWidth, $pnHeight, true);
            
            $asGeo = $this->imagick->getimagegeometry();
        
            $nBorderRL = 0;
            if ($asGeo['width'] < $pnWidth)
                $nBorderRL = ($pnWidth - $asGeo['width']) / 2;
            
            $nBorderTB = 0;
            if ($asGeo['height'] < $pnHeight)
                $nBorderTB = ($pnHeight - $asGeo['height']) / 2;
        }
        else
        {
            $nBorderRL = ($pnWidth - $asGeo['width']) / 2;
            $nBorderTB = ($pnHeight - $asGeo['height']) / 2;
        }
        

        // on place l'image au centre d'un canvas blanc
        $this->imagick->borderImage(new \ImagickPixel("white"), $nBorderRL, $nBorderTB);
        
        return $this->save($psFinalPath);
    }
    
    
    
    private function _resizeInBox($pnWidth, $pnHeight)
    {
        $asGeo = $this->imagick->getimagegeometry();
        
        $bBiggerWidth   = $this->_isLarger($pnWidth, $asGeo['width']);
        $bBiggerHeight  = $this->_isLarger($pnHeight, $asGeo['height']);
        
        $nRatioWidth    = $asGeo['width'] / $pnWidth;
        $nRatioHeight   = $asGeo['height'] / $pnHeight;
        
        //image trop grande
        if ($bBiggerWidth && $bBiggerHeight)
        {
            if ($nRatioHeight > $nRatioWidth)
            {
                $nFinalHeight = $pnHeight;
                $nFinalWidth = $asGeo['width'] / ($nRatioHeight);
            }
            else
            {
                $nFinalWidth = $pnWidth;
                $nFinalHeight = $asGeo['height'] / $nRatioWidth;
            }
        }
        // hauteur plus grande seulement
        elseif($bBiggerHeight)
        {
            $nFinalHeight = $pnHeight;
            $nFinalWidth = $asGeo['width'] / ($nRatioHeight);
        }
        // largeur plus grande seulement
        elseif($bBiggerWidth)
        {
            $nFinalWidth = $pnWidth;
            $nFinalHeight = $asGeo['height'] / $nRatioWidth;
        }
        // image trop petite
        else
        {
            return false;
        }
        
        $this->imagick->resizeimage($nFinalWidth, $nFinalHeight,\Imagick::FILTER_LANCZOS, 1);
    }
    
    private function _isLarger($pnWanted, $pnOriginal)
    {
        return ($pnOriginal > $pnWanted);
    }
    
    
    private function _resize($pnWidth, $pnHeight, $pbAdapt=true, $pbFill=false)
    {
        Debug::log('----------------------------------------------------------------------');
        
        if (!is_object($this->imagick)) return 'http://placehold.it/'.$pnWidth.'x'.$pnHeight;
        
        $asGeo = $this->imagick->getimagegeometry();
        
        if ($pnHeight == 0)
        {
            $pnHeight = $pnWidth * ($asGeo['height'] / $asGeo['width']);
        }
        
        if ($pnWidth == 0)
        {
            $pnWidth = $pnHeight * ($asGeo['width'] / $asGeo['height']);
        }

        
        if ($pbAdapt && $pnWidth <= $asGeo['width'] && $pnHeight <= $asGeo['height'])
        {
            // let Imagick do the resizing into the specifiedbox
            $this->imagick->adaptiveResizeImage($pnWidth, $pnHeight, true);
            return true;
        }
        
        Debug::log('demande de resize de '.$asGeo['width'].'x'.$asGeo['height'].' vers '.$pnWidth.' x '.$pnHeight);
        
        $nRatioWidth         = $pnWidth / $asGeo['width'];
        $nRatioHeight        = $pnHeight / $asGeo['height'];
        
        Debug::log($this->filepath);
        Debug::log('ratio Width : '.$nRatioWidth);
        Debug::log('ratio height : '.$nRatioHeight);
        
        if ($pnWidth > $asGeo['width'] && $pnHeight > $asGeo['height'])
        {
            Debug::log('image trop petite');
            
            if ($pbFill)
            {
                Debug::log('On complete de blanc');
                 
                $nBorderRL = ($pnWidth - $asGeo['width']) / 2;
                $nBorderTB = ($pnHeight - $asGeo['height']) / 2;

                // on place l'image au centre d'un canvas blanc
                $this->imagick->borderImage(new \ImagickPixel("white"), $nBorderRL, $nBorderTB);

                return true;
            }
            elseif($pbAdapt)
            {
                Debug::log('on ne redimenssionne pas');
                return true; 
                $nTempWidth     = $pnWidth;
                $nTempHeight    = $pnHeight;
            }
        }
        elseif($pnWidth > $asGeo['width'])
        {
            Debug::log('largeur trop petite');
            
            $nTempWidth     = $pnWidth;
            $nTempHeight    = $pnWidth * $asGeo['height'] / $asGeo['width'];
        }
        elseif($pnHeight > $asGeo['height'])
        {
            Debug::log('hauteur trop petite');
            
            $nTempWidth     = $asGeo['width'] * $pnHeight / $asGeo['height'];
            $nTempHeight    = $pnHeight;
        }
        elseif ($nRatioWidth < $nRatioHeight && ($asGeo['height'] > $asGeo['width'])) // portrait
        {
            Debug::log('largeur = ratio le plus petit (width < height)');
            
            $nTempWidth     = $asGeo['width'] * $pnHeight / $asGeo['height'];
            $nTempHeight    = $pnHeight;
            /*
            $nTempWidth = $pnWidth;
            $nTempHeight = (int) ($asGeo['height'] * $pnHeight) / $asGeo['width'];
             */
        }
        elseif ($nRatioWidth < $nRatioHeight && ($asGeo['width'] > $asGeo['height'])) // paysage
        {
            Debug::log('largeur = ratio le plus petit (width > height)');
            
            $nTempWidth     = $asGeo['width'] * $pnHeight / $asGeo['height'];
            $nTempHeight    = $pnHeight; // * $nRatioHeight;
            /*
            $nTempWidth = $pnWidth;
            $nTempHeight = (int) ($asGeo['height'] * $pnHeight) / $asGeo['width'];
             */
        }
        elseif ($nRatioWidth > $nRatioHeight && ($asGeo['width'] < $asGeo['height']))
        {
            Debug::log('hauteur = ratio le plus petit (width < height)');
            $nTempWidth     = $pnWidth;
            $nTempHeight    = $asGeo['height'] * $pnWidth / $asGeo['width'];
            /*
            $nTempHeight = $pnHeight;
            $nTempWidth = (int) ($asGeo['width'] * $pnWidth) / $asGeo['height'];
             */
        }
        elseif ($nRatioWidth > $nRatioHeight && ($asGeo['width'] > $asGeo['height']))
        {
            Debug::log('hauteur = ratio le plus petit (width > height)');
            $nTempWidth     = $pnWidth;
            $nTempHeight    = $asGeo['height'] * $pnWidth / $asGeo['width'];
            /*
            $nTempHeight = $pnHeight;
            $nTempWidth = (int) ($asGeo['width'] * $pnWidth) / $asGeo['height'];
             */
        }
        elseif ($nRatioHeight == $nRatioWidth) 
        {
            $nTempWidth     = $pnWidth;
            $nTempHeight    = $pnHeight;
        }
        else 
        {
            Debug::log('ECHEC resize : cas inconnu');
            return '';
        }
        
        Debug::log('AVANT : ('.$asGeo['width'].'x'.$asGeo['height'].') Ratio : '.($asGeo['width'] / $asGeo['height']));
        Debug::log('APRES : ('.$nTempWidth.'x'.$nTempHeight.') Ratio : '.($nTempWidth / $nTempHeight));
        
        $this->imagick->resizeimage($nTempWidth, $nTempHeight,\Imagick::FILTER_LANCZOS, 1);
    }
    
    
    public function save($psOutput)
    {
        $this->imagick->writeImage($psOutput);
        
        chmod($psOutput, 0777);
        
        return $this->getPublicPath($psOutput);
    }
    
    
    public static function getPublicPath($finalPath)
    {
        $sRoot = \P\lib\framework\core\system\PathFinder::getRootDir('public');
        return str_replace($sRoot, '/'.self::$outputPath, $finalPath);
    }
}
?>
