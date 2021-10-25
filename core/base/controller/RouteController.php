<?php

namespace core\base\controller;

class RouteController
{
    static private $_instance;
    
    static public function getInstance()
    {
        if(self::$_instance instanceof self)
            return self::$_instance;

        return self::$_instance = new self();
    }

    private function __construct(){}
    private function clone(){}
}

