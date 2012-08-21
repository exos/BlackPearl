<?php

namespace BlackPearl;

use \Exception;
use \DOMDocument;
use \DOMXPath;

class Browser {

    const REQUEST_METHOD_CURL = 'curl';

    public $userAgent;

    public $timeOut;
    
    private $_headers;
    
    private $_reqMethod;
    
    private $_sessions;
    
    private $_history;
    
    private $_currentUrl;
    
    private $_lasturl;
    
    private $_curlOptions;
    
    private $_content;
    
    private $_DOM;
    
    public function __construct($options = null) {
    
        if (!$options) $options = [];
    
        $this->userAgent =
            isset($options['userAgent']) ? $options['userAgent'] : "BlackPearl-0.1 (compatible; Mozilla/5.0; BlackPearl; PHP v." . phpversion() . " - on " . php_uname("s") . " " . php_uname("r") . " - " . php_uname("m") . ")";
    
        $this->_reqMethod = 
            isset($options['requestMethod']) ? $options['requestMethod'] : static::REQUEST_METHOD_CURL; 
    
        $this->_headers = new Headers([
            'userAgent' => $this->userAgent
        ]);
        
        $uid = uniqid();
        
        $cookiefile = "/tmp/blackpearl_cookies_$uid.txt";
        
        $this->_curlOptions = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR => $cookiefile,
            CURLOPT_COOKIEFILE => $cookiefile,
            CURLOPT_HEADERFUNCTION => [$this, '_processHeader'],
            CURLOPT_ENCODING => 1
        ];
        
    }
    
    private function _processHeader($curl, $headerline) {
        echo "\n HEADER: $headerline\n";
        return strlen($headerline);
    }
    
    private function _refresh ($res) {
        $this->_lasturl = $this->_currentUrl;
        $this->_currentUrl = $res->info->url;
        
        $this->_content = $res->body;
       
        if (preg_match('#^text/html#',$res->info->content_type) ) {
            $this->_DOM = new DOMDocument();
            @$this->_DOM->loadHTML($res->body);
        } else {
            $this->_DOM = null;
        }
    }
    
    private function _requestByCurl ($url, $options = null) {
    
        $curl = curl_init($url);
        $options = is_array($options) ? $options : [];
        
        $headval = $this->_headers->render(true);
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headval); 
        
        if (isset($options['post'])) {
            $postData = [];
            
            foreach ($options['post'] as $key => $val) {
                $postData[] = urlencode($key) . "=" . urlencode($val);
            }
            
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,implode("&",$postData));
        }
        
        if ($this->_curlOptions) {
        
            foreach ($this->_curlOptions as $option => $value) {
                curl_setopt($curl, $option, $value);
            }
            
        }
        
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        return (object) [
            'body' => $content,
            'status' => $status,
            'info' => (object) $info
        ];
    
    }
    
    private function _request($url, $options = null) {
        
        switch ($this->_reqMethod) {
            case (static::REQUEST_METHOD_CURL):
                return $this->_requestByCurl($url, $options);
            default:
                throw new Exception ('Invalid request method');
        }
        
    }
    
    /* DOM INTERACTION */
    
    public function getDocument($asText = false) {
    
        if ($asText) return $this->_content;
        
        return $this->_DOM ? $this->_DOM : $this->_content;
    
    }
    
    public function select ($expresion) {
    
        if (! $this->_DOM) throw new Exception('Current document has not DOM');
        return  (new DOMXPath($this->_DOM))->query($expresion);
    
    }
    
    /* NAVIGATION */
    
    public function getUrl () {
        return $this->_currentUrl;
    }
    
    public function visit($url) {
    
        if ($this->_lasturl) {
            $this->_headers->setHeader('Referer', $this->_lasturl);
        } else {
            $this->_headers->removeHeader('Referer');
        }
        
        $res = $this->_request($url);
        
        $this->_refresh($res);
        
        return $this;
        
    }
    
    public function post($url,array $data) {
    
        $res = $this->_request($url, [
            'post' => $data
        ]);
        
        $this->_refresh($res);
    
    }
    
    public function submit($form) {
    
        $form = $this->select($form)->item(0);
        
        if (!$form) {
            throw new Exception ("Form not found");
        }
        
        //$fields = $form->query('//input || //textarea | //select');
   
        print_r($form->childNodes->item(1));
    
    }
    
}