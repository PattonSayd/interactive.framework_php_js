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


# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {        
        $this->init(true);

        $this->title = 'VG engine';

        if(!$this->model) 
            $this->model = Model::instance();

        if(!$this->menu)
            $this->menu = Settings::get('projectTable');

        if(!$this->adminPath)
            $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';

        if(!$this->templateArr)
            $this->templateArr = Settings::get('templateArr');

        if (!$this->formTemplates)
            $this->formTemplates = PATH . Settings::get('formTemplates');

        if (!$this->messages)
            $this->messages = include $_SERVER['DOCUMENT_ROOT'] . PATH . Settings::get('messages') . 'informationMessages.php';
        
        $this->sendNoCacheHeaders();
    }
    
# -------------------- SEND NO CAHCE HEADERS -------------------------------------

    protected function sendNoCacheHeaders()
    {
        header("Last-Modified: " .gmdate("D, d M Y H:i:s"). " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: max-age=0");
        header("Cache-Control: post-check=0, pre-check=0"); //browser explorer
    }

# -------------------- PARENT INPUT DATA -----------------------------------------

    protected function parentInputData()
    {
        self::inputData(); # $this
    }

# -------------------- CREATE TABELE DATA ----------------------------------------

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
    
# -------------------- CREATE DATA -----------------------------------------------
    
    protected function createData($arr = [])
    {
        $fields = [];
        $order = [];
        $order_direction = [];

        if (!$this->columns['id_row'])
            return $this->data = [];

        $fields[] = $this->columns['id_row'] . ' as id';

        if ($this->columns['name'])
            $fields['name'] = 'name';

        if ($this->columns['image'])
            $fields['image'] = 'image';

        if (count($fields) < 3)
            foreach ($this->columns as $key => $value) {
                if (!$fields['name'] && strpos($key, 'name') !== false) {
                    $fields['name'] = $key . ' as name';
                }
                if (!$fields['image'] && strpos($key, 'image') === 0) {
                    $fields['image'] = $key . ' as image';
                }
            }

        /** fields ************************/

        if ($arr['fields']) {
            if (is_array($arr['fields'])) {
                $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
            } else {
                $fields[] = $arr['fields'];
            }
        }
        
        /** parent_id *********************/

        if ($this->columns['parent_id']) {
            if (!in_array('parent_id', $fields))
                $fields[] = 'parent_id';
            $order[] = 'parent_id';
        }
        /** menu position *****************/ 

        if ($this->columns['menu_position']) {
            $order[] = 'menu_position';
        } elseif ($this->columns['date']) {
            if ($order)
                $order_direction = ['ASC', 'DESC'];
            else
                $order_direction[] = ['DESK'];

            $order[] = 'date';
        }

        /** order *************************/

        if ($arr['order']) {
            if (is_array($arr['order'])) {
                $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
            } else {
                $order[] = $arr['order'];
            }
        }

        /** order direction ***************/

        if ($arr['order_direction']) {
            if (is_array($arr['order_direction'])) {
                $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
            } else {
                $order_direction[] = $arr['order_direction'];
            }
        }

        $this->data = $this->model->select($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $order_direction
        ]);
    }
}