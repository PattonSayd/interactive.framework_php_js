<?php 

namespace core\user\controller;

use core\base\controller\Controller;
use core\base\model\UserModel;

abstract class UserController extends Controller
{

    protected $model;

    protected $table;

    protected $set;

    protected $catalog;

    protected $information;
    
    protected function inputData()
    {
        $this->init(); 

        if(!$this->model) $this->model = UserModel::instance();

        $this->set = $this->model->select('settings', [
            'order' => ['id'],
            'limit' => 1
        ]);

        $this->set && $this->set = $this->set[0];

        $this->catalog = $this->model->select('catalog', [
            'where' => ['visible' => 1, 'parent_id' => null],
            'order' => ['menu_position']
        ]);

        $this->information = $this->model->select('information', [
            'where' => ['visible' => 1, 'show_top_menu' => 1],
            'order' => ['menu_position']
        ]);
        
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

    protected function link($link = '', $query_string = '') 
    {
        $str = '';

        // $link && $link = strtolower($link);

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

        if(is_array($link)){

            $link_string = '';

            foreach($link as $key => $item){

                if(!is_numeric($key) && $item){

                    $link_string .= $key . '/' . $item . '/';

                }elseif($item){


                    $link_string .= $item . '/';
                }
            }

            $link = trim(strtolower($link_string), '/');
        }

        if(!$link || $link === '/') return PATH . $str;

        if(preg_match('/^\s*https?:\/\//i', $link)) return $link . $str;

        return preg_replace('/\{2,}/', '/', PATH . $link . END_SLASH . $str);
    }
    
}