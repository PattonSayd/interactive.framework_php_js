<?php 

namespace core\base\controller;

use core\base\controller\Methods;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;


abstract class Controller
{
    use Methods;
    
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
    // protected $data;

# ------------------ Route ------------------------------------------------------------

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

# ------------------ Request ----------------------------------------------------------

    public function request($arqs)
    {        
        $this->parameters = $arqs['parametrs'];

        $inputData = $arqs['inputMethod'];     // default 'inputData' or url '/page'
        $outputData = $arqs['outputMethod'];  // default 'outputData' or url '/page' 

        $data = $this->$inputData();   //  fucn 'inputData()' or func 'page()'

        if(method_exists($this, $outputData)){
            $this->page = $this->$outputData($data);   // 'outputData'

        }else if($data){
            $this->page = $data;
        }

        // if ($this->errors){
        //     $this->writeLog($this->errors);
        // }

        $this->getPage();
    }

# -------------------------------------------------------------------------------------

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

# -------------------------------------------------------------------------------------

    protected function getPage(){
        
        if(is_array($this->page)){
            foreach($this->page as $block){
                echo $block;
            }
        }else{
            echo $this->page;
        }
    }

# -------------------------------------------------------------------------------------

    protected function init($admin = false)
    {
        if (!$admin) {
            if (USER_CSS_JS['styles']) {
                foreach (USER_CSS_JS['styles'] as $item) {   //  delete '/'
                    $this->styles[] = PATH . TEMPLATE . trim($item, '/');
                }
            }
            if (USER_CSS_JS['scripts']) {
                foreach (USER_CSS_JS['scripts'] as $item) {
                    $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
                }
            }
        } else {
            if (ADMIN_CSS_JS['styles']) {
                foreach (ADMIN_CSS_JS['styles'] as $item) {   //  delete '/'
                    $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
                }
            }
            if (ADMIN_CSS_JS['scripts']) {
                foreach (ADMIN_CSS_JS['scripts'] as $item) {
                    $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
                }
            }
        }
    }
}


?>

