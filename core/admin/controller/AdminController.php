<?php 
namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\exceptions\RouteException;
use core\base\model\Model;
use core\base\settings\Settings;

abstract class AdminController extends Controller
{
    protected $model;

    protected $table;
    protected $data;
    protected $columns;
  
    protected $menu;
    protected $title;


# ------------------ INPUT DATA ----------------------------------------------------------------

    protected function inputData()
    {        
        $this->init(true);

        $this->title = 'VG engine';

        if(!$this->model) 
            $this->model = Model::instance();
        
        $this->sendNoCacheHeaders();
    }
    
# ------------------ SEND NO CACHE HEADERS -----------------------------------------------------

    protected function sendNoCacheHeaders()
    {
        header("Last-Modified: " .gmdate("D, d M Y H:i:s"). " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0, pre-check=0"); //browser explorer
    }

# ------------------ PARENT INPUT DATA ---------------------------------------------------------

    protected function parentInputData()
    {
        self::inputData(); # $this
    }

# ----------------- CREATE TABLE DATA ----------------------------------------------------------

    protected function createTableData($settings = false)
    {
        if(!$this->table){

            if($this->parameters){
                $this->table = array_keys($this->parameters)[0];
                
            }else {
                if(!$settings)
                    $settings = Settings::instance();

                $this->table = $settings::get('defaultTable');     
            }
        }

        $this->columns = $this->model->showColumns($this->table);

        if(!$this->columns)
            throw new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
        
    }
    
}