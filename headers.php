<?php

namespace BlackPearl;

use \Exception;

class Headers {

    protected $_headers = [];
    
    protected $_cookies = [];
    
    protected $_langs = [];
    
    protected $_accepts = [];
    
    public $host;
    
    public $userAgent;

    public function __construct(array $options = null) {
        
        $options = is_array($options) ? $options : [];
        
        if (isset($options['userAgent'])) $this->userAgent = $options['userAgent'];
        if (isset($options['host'])) $this->userAgent = $options['host'];
        
        if (isset($options['langs'])) {
            if (is_array($options['langs'])) {
                $this->_langs = $options['langs'];
            } else {
                $this->_langs = explode(',',$options['langs']);
            }
        } else {
            if (class_exists('\Locale')) {
                $this->_langs[] = Locale::ACTUAL_LOCAL;
            } else {
                $this->_langs = ['en;q=0.5'];
            }
        }
        
        if (isset($options['acepts'])) {
            if (is_array($options['acepts'])) {
                $this->_accepts = $options['acepts'];
            } else {
                $this->_accepts = explode(',',$options['acepts']);
            }
        } else {
            $this->_accepts = [
                'text/html',
                'application/xhtml+xml',
                'application/xml;q=0.9',
                '*/*;q=0.8'
            ];
        }
       
    }

    public function setHeader ($header, $value) {
        $this->_headers[$header] = $value;
    }
    
    public function getHeader ($header) {
        if (isset($this->_headers[$header])) {
            return $this->_headers[$header];
        } else {
            return null;
        }
    }
    
    public function removeHeader($name) {
        unset($this->_headers[$name]);
    }
    
    public function addLang($lang) {
        $this->langs[] = $lang;
    }
    
    public function getHeaders() {
    
        $res = [];
    
        if ($this->host)
            $this->setHeader('Host', $this->host);
    
        if ($this->userAgent)
            $this->setHeader('User-Agent', $this->userAgent);
   
        if ($this->_accepts)
            $this->setHeader('Accept', implode(',', $this->_accepts)); 
   
        if ($this->_langs)
            $this->setHeader('Accept-Language', implode(',', $this->_langs)); 
    
        $this->setHeader('Accept-Encoding','gzip, deflate');
        $this->setHeader('Connection', 'keep-alive');
    
        foreach ($this->_headers as $key => $val) {
            if (!preg_match('#[\w][\w\-]+#', $key)) {
                throw new Exception ('Invalid header ' . $key);
            }
            
            $key = str_replace("\n", '\n', $key);
            $val = str_replace("\n", '\n', $val);
            
            $res[$key] = $val;
            
        }
        
        return $res;
        
    }
    
    public function render ($asArray = false) {
    
        $vals = $this->getHeaders();
        $res = [];
        
        foreach ($vals as $key => $val) {
            $res[] = "$key: $val";
        }
        
        if ($asArray) {
            return $res;
        } else {
            return implode("\n", $res);
        }
        
    }
    
    public function __set($header, $value) {
        $this->setHeader($header, $value);
    }
    
    public function __get($header) {
        return $this->getHeader($header);
    }
    
    public function __isset ($header) {
        return isset($this->_headers[$header]);
    }
    
    public function __toString () {
        return $this->render();
    }
    
}