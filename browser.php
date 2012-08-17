<?php

namespace BlackPearl;

class Browser {

    const REQUEST_METHOD_CURL = 'curl';

    public $userAgent = "PHP/BlackPearl 0.1 (PHP v.{phpversion()} - on {PHP_OS})";

    public $timeOut;
    
    private $_reqMethod;
    
    private $_sessions;
    
    private $_history;
    
    private $_curlOptions;
    
    private $_html;
    
    private $_DOM;
    
    public function __construct($options) {
    
        $this->_reqMethod = 
            isset($options['requestMethod']) ? $options['requestMethod'] : static::REQUEST_METHOD_CURL; 
    
    }
    
    private function _requestByCurl ($url, $options) {
    
        $curl = curl_init();
        
        if ($this->_curlOptions) {
        
            foreach ($this->_curlOptions as $option -> $value) {
                curl_setopt($curl, $option, $value);
            }
            
        }
    
    }
    
    private function _request($url, $options) {
        
        switch ($this->_reqMethod) {
            case (static::REQUEST_METHOD_CURL):
                return $this->_requestByCurl($url, $options);
            default:
                throw new Exception ('Invalid request method');
        }
        
    }
    
    public function visit() {
    
    }
    
}