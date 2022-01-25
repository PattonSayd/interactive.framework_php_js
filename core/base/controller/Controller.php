<?php 

namespace core\base\controller;

use core\base\controller\Methods;
use core\base\exception\RouteException;
use core\base\settings\Settings;


abstract class Controller
{
    use Methods;
    
    protected $header;
    protected $content;
    protected $footer;
    
    protected $pageAction;
    protected $page;
    protected $errors;
    
    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    protected $template;
    protected $styles;
    protected $scripts;

    protected $userId;
    // protected $data;

# ----------------- ROUTE -----------------------------------------------------

    public function route()
    {
        $controller = str_replace('/', '\\', $this->controller);

        try {
            $object = new \ReflectionMethod($controller, 'request');  
            
            $arqs = [                   
                    'pageAction' => $this->pageAction, # []
                    'parametrs' => $this->parameters, # []
                    'inputMethod' => $this->inputMethod, # inputData
                    'outputMethod' => $this->outputMethod, # outputData
                ];

            $object->invoke(new $controller, $arqs);

        } catch (\ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }
    }

# ----------------- REQUEST ---------------------------------------------------

    public function request($arqs)
    {        
        $this->pageAction = $arqs['pageAction'];
        
        $this->parameters = $arqs['parametrs'];

        $inputData = $arqs['inputMethod']; # default 'inputData' or url '/page'
        $outputData = $arqs['outputMethod']; # default 'outputData' or url '/page' 

        $data = $this->$inputData(); # fucn 'inputData()' or func 'page()'

        if(method_exists($this, $outputData)){
            $this->page = $this->$outputData($data);

            // if($page) $this->page = $page; 

        }else if($data){
            $this->page = $data;
        }
        /**
         *  if ($this->errors){
         *      $this->writeLog($this->errors);
         *  }
         */

        $this->getPage();
    }

# -------------------- RENDER -------------------------------------------------

    protected function render($path = '', $parameters = [])
    {
        extract($parameters); # если не массив, @ - отключаем warnings
        
        if(!$path) {
            $class = new \ReflectionClass($this); # ex. core\user\controller\IndexController

            $space = str_replace('\\', '/', $class->getNamespaceName() . '\\'); # core/user/controller/

            $route = Settings::get('routes');

            if($space === $route['user']['path']){
                $template = TEMPLATE;  
            }else{ 
                $template = ADMIN_TEMPLATE;
            }
            
            $path = $template . explode('controller', strtolower((new \ReflectionClass($this))->getShortName()))[0]; # [0] => index;
        }

        ob_start();
        
        if (!@include_once $path . '.php') {
            throw new RouteException('Отсутствует шаблон - ' . $path);
        }

        return ob_get_clean();

        $this->getPage();
    }

# -------------------- GET PAGE -----------------------------------------------

    protected function getPage(){
        
        if(is_array($this->page)){
            foreach($this->page as $block){
                echo $block;
            }
        }else{
            echo $this->page;
        }

        exit;
    }

# -------------------- INIT ------------------------------------------------

    protected function init($admin = false)
    {
        if (!$admin) {
            if (USER_CSS_JS['styles']) {
                foreach (USER_CSS_JS['styles'] as $item) {                        
                    $this->styles[] = PATH . TEMPLATE . trim($item, '/');
                }
            }
            if (USER_CSS_JS['scripts']) {
                foreach (USER_CSS_JS['scripts'] as $item) {
                    $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
                }
            }
        } else {
            foreach(ADMIN_CSS_JS as $tag => $items) {
                if (ADMIN_CSS_JS[$tag]) {
                    foreach($items as $key => $paths) {
                        if($key == 'main' || $this->pageAction == $key){
                            foreach ($paths as $path){
                                $this->$tag[] = PATH . ADMIN_TEMPLATE . trim($path, '/');
                            }
                        }
                    }
                }
            }
        }
    }
}


?>

