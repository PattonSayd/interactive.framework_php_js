<?php

namespace core\base\controller;

use core\base\controller\Controller;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;
 
class RouteController extends Controller
{
    static private $_instance;

    protected $routes;
        
    static public function instance()
    {
        if(self::$_instance instanceof self)
            return self::$_instance;

        return self::$_instance = new self;
        
    }

    private function __construct(){

        $url = $_SERVER['REQUEST_URI'];

        if(!empty($_SERVER['QUERY_STRING'])){
#                                                   page=1&order=row    
            $url = substr($url, 0, strpos($url, $_SERVER['QUERY_STRING']) - 1);
        }
#                          /index.php                                         1  
        $path = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], 'index.php')); #   '/'
        
        if($path === PATH){
                     
            if (strrpos($url, '/') === strlen($url) - 1 && strrpos($url,  '/') !== strlen(PATH) - 1) {
                $this->redirect(rtrim($url, '/'), 301);
            }

            $this->routes = Settings::get('routes');
           
            if (!$this->routes)
                    throw new RouteException('Отсутствуют маршруты в базовых настройках', 1);
#                                      /unknown/denied     1
            $explode_url = explode('/', substr($url, strlen(PATH))); #  0:unknown  1:denied   
#                    admin             admin === admin
            if ($explode_url[0] && $explode_url[0] ===  $this->routes['admin']['alias']){
                                          
                array_shift($explode_url);  # delete -> 0:admin

                if (!empty($explode_url[0]) && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $explode_url[0])){

                    $plugin = array_shift($explode_url); #  shop

                    $pluginSetting = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings'); # core/base/settings/ShopSetting

                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSetting . '.php')) { # red.info/core/base/settings/ShopSetting.php

                        $pluginSetting = str_replace('/', '\\', $pluginSetting);

                        $this->routes = $pluginSetting::get('routes');  
                    };

                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';

                    $dir = str_replace('//', '/', $dir); # если в путь $this->routes['plugins']['dir'] занести слэш /alias/
                    
                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir; # core/plugins/shop/alias
                    
                    $hrUrl = $this->routes['plugins']['hrUrl'];

                    $route = 'plugins';

                }else{
                    $this->controller = $this->routes['admin']['path']; # core/admin/controllers/

                    $hrUrl = $this->routes['admin']['hrUrl'];

                    $route = 'admin';
                }
            }else{  
                $hrUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }
            
            $this->createRoute($route, $explode_url);
            
            if (isset($explode_url[1])){
                $count = count($explode_url);
                $key = '';

                if (!$hrUrl){
                    $i = 1;
                }else{
                    $this->parameters['alisa'] = $explode_url[1];
                    $i = 2;
                }

                for ( ; $i < $count; $i++){
                    if (!$key){
                        $key = $explode_url[$i];
                        $this->parameters[$key] = '';

                    }else{
                        $this->parameters[$key] = $explode_url[$i];
                        $key = '';
                    }
                }
            }     
        }   
        else{
            throw new RouteException('Не корректная директория сайта', 1);
        }
     }

     private function createRoute($var, $explode_url)
     {
            $route = [];

            if (!empty($explode_url[0])) {
                
                if (!empty($this->routes[$var]['routes'][$explode_url[0]])){

                    $route = explode('/', $this->routes[$var]['routes'][$explode_url[0]]);
                    
                    $this->controller .= ucfirst($route[0] . 'Controller');
                }else{
                    $this->controller .= ucfirst($explode_url[0] . 'Controller');
                }
            }else{
                $this->controller .=  $this->routes['default']['controller']; # controller => IndexController
            }
#                                                                          inputMethod => inputData
            $this->inputMethod = isset($route[1]) ? $route[1] : $this->routes['default']['inputMethod'];
#                                                                          outputMethod => outputData
            $this->outputMethod = isset($route[2]) ? $route[2] : $this->routes['default']['outputMethod'];
    }
    private function clone(){}
}

