<?php
namespace P\lib\framework\core\utils;

class Scraper{

    public $html;
    
    public function __construct(){
        // set proxies -- you can add your own here or use the setProxies method
        $this->_proxies = array();
    }
    
    public function scrape($url)
    {
        $this->_url = $url;
        $dom = new \DOMDocument();
        $proxy = $this->_pickProxy();

        $sFile = '../temp/'.md5($url).'.txt';
        
        if (false || !is_file($sFile))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_REFERER, "");
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_6) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.151 Safari/535.19");
            if($proxy){
                    curl_setopt($ch, CURLOPT_PROXY, $proxy); 
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            }
            $body = curl_exec($ch);
            curl_close($ch);

            $this->_curl_result = $body;

            file_put_contents($sFile, $body);

        }
        else
        {
            //Debug::dump('Cache used');
            $body = file_get_contents($sFile);
        }
        
        $this->html = $body;
        
    	@$dom->loadHTML($body);
    	$this->_dom = $dom;

    	//$this->_parseDOM();	
    }
    
    public function setProxies($proxies)
    {
        $this->_proxies = $proxies;
    }
    
    private function _pickProxy()
    {
        if(count($this->_proxies) > 0)
            return $this->_proxies[rand(0, count($this->_proxies) - 1)];
        else return false;
    }
    
    public function setKeyword($keyword)
    {
        $this->_keyword = $keyword;
    }
    
    private function _parseDOM()
    {
        $xpath = new \DOMXPath($this->_dom);
        $title = $xpath->query("//head/title");
        $meta_desc = $xpath->query("//head/meta[@name='description']/@content");
        $meta_kw = $xpath->query("//head/meta[@name='keywords']/@content");
        $h1 = $xpath->query("//h1");
        $h2 = $xpath->query("//h2");
        $h3 = $xpath->query("//h3");
	$h4 = $xpath->query("//h4");
	$h5 = $xpath->query("//h5");
	$h6 = $xpath->query("//h6");
        $img = $xpath->query("//img");
        $img_alt = $xpath->query("//img[@alt!='']/@alt");
        $strong = $xpath->query("//strong | //b");
        $body = $xpath->query("//body");
        $sStore = $xpath->query("//div[@class='name']"); //  meta[@name='description']
                              
        if($sStore->length > 0)
        {
            for($i=0; $i < $sStore->length; $i++)
                $this->_store[] = $sStore->item($i)->nodeValue;
        }
        
        Debug::dump($this->_store);
        
        die();
        
        
        if($title->length > 0)
            $this->_title = $title->item(0)->nodeValue;
            
        if($meta_desc->length > 0)
            $this->_meta_desc = $meta_desc->item(0)->nodeValue;
            
        if($meta_kw->length > 0)
            $this->_meta_kw = $meta_kw->item(0)->nodeValue;
            
        if($h1->length > 0)
        {
            for($i=0; $i < $h1->length; $i++)
                $this->_h1[] = $h1->item($i)->nodeValue;
        }
        
        if($h2->length > 0)
        {
            for($i=0; $i < $h2->length; $i++)
                $this->_h2[] = $h2->item($i)->nodeValue;
        }
        
        if($h3->length > 0)
        {
            for($i=0; $i < $h3->length; $i++)
                $this->_h3[] = $h3->item($i)->nodeValue;
        }

	if($h4->length > 0)
        {
            for($i=0; $i < $h4->length; $i++)
                $this->_h4[] = $h4->item($i)->nodeValue;
        }

	if($h5->length > 0)
        {
            for($i=0; $i < $h5->length; $i++)
                $this->_h5[] = $h5->item($i)->nodeValue;
        }

	if($h6->length > 0)
        {
            for($i=0; $i < $h6->length; $i++)
                $this->_h6[] = $h6->item($i)->nodeValue;
        }
        
        if($img_alt->length > 0)
        {
            for($i=0; $i < $img_alt->length; $i++)
                $this->_img_alt[] = $img_alt->item($i)->nodeValue;
        }

        $this->_img_alt_pct = ($img_alt->length / $img->length)*100;
        
        if($strong->length > 0)
        {
            for($i=0; $i < $strong->length; $i++)
                $this->_strong[] = $strong->item($i)->nodeValue;
        }
        
    }
    
}