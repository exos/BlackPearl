<?php

namespace BlackPearl;

use BlackPearl\Exceptions\BadUrlException;
use \Exception;

class Url {

    // From: http://pregcopy.com/exp/12
    const regexp = '/^(?:(?<protocol>[\w]+)\:\/\/(?:(?<user>[^:^@]+)(?::(?<password>[^@]+))?\@)?(?<domain>[^\/]*)(?::(?<port>\d+))?)?(?<path>(?:\/[^\?^\#]*)*)?(?:\?(?<vars>[^\#]*))?(?:\#(?<hash>.*))?$/';

    private $_protocol;
    private $_user;
    private $_pass;
    private $_host;
    private $_port;
    private $_path;
    private $_querystring;
    private $_hash;

    public function __construct ($url) {

        $parts = [];
        
        if (preg_match(static::regexp, $url, $parts)) {
            
            $this->_protocol     = isset($parts['protocol']) ? $parts['protocol'] : null ;
            $this->_user         = isset($parts['user']) ? $parts['user'] : null;
            $this->_pass         = isset($parts['password']) ? $parts['password'] : null;
            $this->_host         = isset($parts['domain']) ? $parts['domain'] : null;
            $this->_port         = isset($parts['port']) ? $parts['port'] : null;
            $this->_path         = isset($parts['path']) ? $parts['path'] : null;
            $this->_querystring  = isset($parts['vars']) ? $parts['vars'] : null;
            $this->_hash         = isset($parts['hash']) ? $parts['hash'] : null;
            
            if (!$this->_host)
                throw new BadUrlException('The url can\t be relative',$this);
            
        } else {
            throw new BadUrlException('Invalid url', $this);
        }
    
    }
    
    public function setUser ($user, $pass = null) {
        
        $user = urlencode($user);
        if ($pass) $pass = urlencode($pass);
        
        $this->_user = $user;
        $this->_pass = $pass;
        
        return $this;
    }
    
    public function updatePort($port) {
        $port = (int) $port;
        
        if (!$port) throw new Exception('Invalid port');
        
        if ($port == 80) $port = '';
        
        $this->_port = $port;
        
        return $this;
        
    }
    
    public function update($url) {
    
        $parts = [];
        
        if (preg_match(static::regexp, $url, $parts)) {
            
            if (isset($parts['protocol']) && $parts['protocol']) {
                $this->_protocol = $parts['protocol'];
            }
            
            if (isset($parts['user']) && $parts['user']) { 
                $this->_user    = $parts['user'];
                $this->_pass    = isset($parts['password']) ? $parts['password'] : null;
            }
            
            if (isset($parts['domain']) && $parts['domain']) {
            
                $this->_host = $parts['domain'];
                $this->_port = isset($parts['port']) ? $parts['port'] : null;
                
                $this->_path         = isset($parts['path']) ? $parts['path'] : null;
                $this->_querystring  = isset($parts['vars']) ? $parts['vars'] : null;
                $this->_hash         = isset($parts['hash']) ? $parts['hash'] : null;
                
            } elseif (isset($parts['path']) && $parts['path']) {
                $this->_path = $parts['path'];
                $this->_querystring  = isset($parts['vars']) ? $parts['vars'] : null;
                $this->_hash         = isset($parts['hash']) ? $parts['hash'] : null;
            } elseif (isset($parts['vars']) && $parts['vars']) {
                $this->_querystring = $parts['vars'];
                $this->_hash         = isset($parts['hash']) ? $parts['hash'] : null;
            } elseif (isset($parts['hash']) && $parts['hash']) {
                $this->_hash = $parts['hash'];
            }
            
            if (!$this->_host)
                throw new BadUrlException('The url can\t be relative',$this);
            
        } else {
            throw new BadUrlException('Invalid url', $this);
        }
        
        return $this;
    
    }
    
    public function getUrl() {
        
        if (!$this->_protocol) $this->_protocol = 'http';
        
        $url = $this->_protocol . '://';
        
        if ($this->_user) {
            $url .= $this->_user;
            if ($this->_pass) $url .= ":{$this->_pass}";
            $url .= '@';
        }
        
        $url .= $this->_host;
        
        if ($this->_port) $url .= ":{$this->_port}";
        
        $url .= $this->getRelative();
        
        return $url;
        
    }
    
    public function getRelative () {
    
        $url = $this->_path;
        
        if ($this->_querystring) $url .= "?{$this->_querystring}";
        
        if ($this->_hash) $url .= "#{$this->_hash}";
        
        return $url;
        
    }

    public function __toString() {
        return $this->getUrl();
    }
    
}