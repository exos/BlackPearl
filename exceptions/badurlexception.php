<?php

namespace BlackPearl\Exceptions;

use \Exception;

class BadUrlException extends Exception {

    private $_url;

    public function __construct($msg, \BlackPearl\Url $url) {
        $this->_url = $url;
        parent::__construct("$msg with " . $url->getUrl());
    }

    public function getUrl() {
        return $this->_url;
    }

}