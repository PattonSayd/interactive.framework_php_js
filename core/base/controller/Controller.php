<?php 

namespace core\base\controller;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;


abstract class Controller
{
    protected $header;
    protected $content;
    protected $footer;
    
    protected $page;
    protected $errors;
    
    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    protected $template;
    protected $styles;
    protected $scripts;

    protected $userID;
    protected $data;

    public function route()
    {
        $controller = str_replace('/', '\\', $this->controller);

        try {
            $object = new \ReflectionMethod($controller, 'request');
            
            $arqs = [                   
                    'parametrs' => $this->parameters,        // []
                    'inputMethod' => $this->inputMethod,    //inputData
                    'outputMethod' => $this->outputMethod,  //outputData
                ];

            $object->invoke(new $controller, $arqs);

        } catch (\ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }

    }


    public function request($arqs)
    {        
        $this->parameters = $arqs['parametrs'];

        $inputData = $arqs['inputMethod'];     // 'inputData'
        $outputData = $arqs['outputMethod'];  // 'outputData'

        $vars = $this->$inputData();   // 'inputData'

        if(method_exists($this, $outputData)){
            $page = $this->$outputData($vars);   // 'outputData'

            if($page){
                $this->page = $page;
            }
        }
        elseif($vars){
            $this->page = $vars;
        }

        // if ($this->errors()){
        //     $this->writeLog();
        // }

        $this->getPage();
    }


    protected function render($path = '', $parameters = [])
    {
        extract($parameters); // если не массив, @ - отключаем warnings
        
        if(!$path) {
            $class = new \ReflectionClass($this);  # ex. core\user\controller\IndexController

            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\'); #core/user/controller/

            $route = Settings::get('routes');

            if($space === $route['user']['path']){
                $template = TEMPLATE;
            }else{ 
                $template = ADMIN_TEMPLATE;
            }
                                                                       
            $path = $template . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0]; # [0] => index
        }

        ob_start();
        
        if (!@include_once $path . '.php') {
            throw new RouteException('Template no exists ' . $path);
        }

        return ob_get_clean();

        $this->getPage();
    }


    protected function getPage(){
        
        if(is_array($this->page)){
            foreach($this->page as $block){
                echo $block;
            }
        }else{
            echo $this->page;
        }
    }
  

}


?>

