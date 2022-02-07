<?php

namespace core\base\controller;

class Route
{
    use Singleton, Methods;

    public static function routeDirection()
    {
        if(self::instance()->isAjax()){

            exit((new Ajax())->route());
        }

        RouteController::instance()->route();
    }
}