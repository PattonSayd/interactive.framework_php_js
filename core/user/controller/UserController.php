<?php 

namespace core\user\controller;

use core\base\controller\Controller;
use core\base\model\UserModel;

abstract class UserController extends Controller
{

    protected $model;

    protected $table;

    protected function inputData()
    {
        $this->init(); 

        if(!$this->model) 
            $this->model = UserModel::instance();
    } 
    
    protected function outputData()
    {
        if(!$this->content){
            $args = func_get_arg(0);
            $parameters = !empty($args) ? $args : [];

            // if (!$this->template) $this->template = TEMPLATE . 'show';

            $this->content = $this->render($this->template, $parameters);
        }

        $this->header = $this->render(TEMPLATE . 'include/header', $parameters);
        $this->footer = $this->render(TEMPLATE . 'include/footer', $parameters);

        return $this->render(TEMPLATE . 'layouts/default');
    }

    protected function getImage($img = '', $tag = false)
    {
        if(!$img && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . DEFAULT_IMAGE_DIRECTORY)){

            $dir = scandir($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . DEFAULT_IMAGE_DIRECTORY);

            $images = preg_grep('/' . $this->getController() . '\./i', $dir) ?: preg_grep('/default\./i', $dir);

            $images && $img = DEFAULT_IMAGE_DIRECTORY . '/' . array_shift($images);
            
        }

        if($img){

            $path = PATH . UPLOAD_DIR . $img;
            
            if(!$tag) return $path;
                           
            echo '<img src="' . $path . '" alt="image" title="image" />';
        }
        
        return '';
    }

    protected function alias($alias = '', $query_string = '') 
    {
        $str = '';

        if($query_string){

            if(is_array($query_string)){
 
                foreach($query_string as $key => $item){

                    $str .= (!$str ? '?' : '&');

                    if(is_array($item)){

                        $key .= '[]';

                        foreach($item as $value)
                            $str .= $key . '=' . $value;
                        
                    }else{
                        $str .= $key . '=' .$item;
                    }
                    
                }
                
            }else{

                if(strpos($query_string, '?') === false)
                    $str = '?' . $str;

                $str .= $query_string;
                
            }
            
        }

        if(is_array($alias)){

            $alias_string = '';

            foreach($alias as $key => $item){

                if(!is_numeric($key) && $item){

                    $alias_string .= $key . '/' . $item . '/';

                }elseif($item){


                    $alias_string .= $item . '/';
                }
            }

            $alias = trim($alias_string, '/');
        }

        if(!$alias || $alias === '/') return PATH . $str;

        if(preg_match('/^\s*https?:\/\//i', $alias)) return $alias . $str;

        return preg_replace('/\{2,}/', '/', PATH . $alias . END_SLASH . $str);
    }
    
}