<?php

namespace BlackPearl;

class Response {

    private $_data;
    
    private $_info;
    
    public function __construct($data, $content) {
        $this->_data = $data;
        $this->_info = $content;
    }
    
    public function __get ($var) {
    
        switch ($var) {
            case 'status':
                return $this->_info['status'];
        }
    
    }
    
    public function __set($var, $val) {
        throw new Exception ('Read-only object');
    }

}