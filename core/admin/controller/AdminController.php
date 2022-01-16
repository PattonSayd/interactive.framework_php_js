<?php 
namespace core\admin\controller;

use core\admin\model\AdminModel;
use core\base\controller\Controller;
use core\base\exception\RouteException;
use core\base\settings\Settings;

abstract class AdminController extends Controller
{
    protected $model;

    protected $table;
    protected $data;
    protected $columns;
    protected $foreignData;

    protected $adminPath;
  
    protected $menu;
    protected $title;

    protected $translate;
    protected $blocks = [];

    protected $templateArr;
    protected $formTemplates;
    protected $noDelete;


# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {        
        $this->init(true);
                  
        $this->title = 'VG engine';

        if(!$this->model) 
            $this->model = AdminModel::instance();

        if(!$this->menu)
            $this->menu = Settings::get('projectTable');

        if(!$this->adminPath)
            $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';

        if(!$this->templateArr)
            $this->templateArr = Settings::get('templateArr');

        if (!$this->formTemplates)
            $this->formTemplates = PATH . Settings::get('formTemplates');

        // if (!$this->messages)
        //     $this->messages = include $_SERVER['DOCUMENT_ROOT'] . PATH . Settings::get('messages') . 'informationMessages.php';
        
        $this->sendNoCacheHeaders();
    }

# ------------------- OUTPUT DATA -----------------------------------------------

    protected function outputData()
    {
        if(!$this->content){
            
            $args = func_get_arg(0);
            $parameters = !empty($args) ? $args : [];

            // if (!$this->template)
            //     $this->template = ADMIN_TEMPLATE . 'show';

            $this->content = $this->render($this->template, $parameters);
        }

        $this->header = $this->render(ADMIN_TEMPLATE . 'include/header');
        $this->footer = $this->render(ADMIN_TEMPLATE . 'include/footer');

        return $this->render(ADMIN_TEMPLATE . 'layouts/default');
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

        $this->columns = $this->model->getColumns($this->table);

        if(!$this->columns)
            throw new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
        
    }

# -------------------- EXTENSION -------------------------------------------------

    protected function extension($args = [], $settings = false)
    {
        $filename = explode('_', $this->table);
        $className = '';

        foreach ($filename as $item) {
            $className .= ucfirst($item);
        }

        if (!$settings){
            $path = Settings::get('extension');
        }elseif(is_object($settings)){
            $path = $settings::get('extension');
        }else{
            $path = $settings;
        }

        $class = $path . $className . 'Extension';
                                 
        if(is_readable($_SERVER['DOCUMENT_ROOT'] . PATH . $class . '.php')){
             
            $class = str_replace('/', '\\', $class);

            $ext = $class::instance();

            foreach ($this as $name => $value) {
                $ext->$name = &$this->$name; 
            }

            return $ext->extension($args);

        }else{
            $file = $_SERVER['DOCUMENT_ROOT'] . PATH . $path . $this->table .  '.php';

            extract($args);

            if(is_readable($file))
                return include $file;
        }

        return false;
    }

# -------------------- CREATE BLOCK ----------------------------------------

    protected function createBlock($settings = false)           // vg-rows['id]
    {                                                           // vg-img['name]
        if (!$settings)                                         // vg-content[]
            $settings = Settings::instance();

        $blocks = $settings::get('block');

        $this->translate = $settings::get('translate');

        if(!$blocks || !is_array($blocks)){

            foreach ($this->columns as $name => $value) {
                if($name === 'id_row')
                    continue;

                if(empty($this->translate[$name]))
                    $this->translate[$name][] = $name; // [] по умолчанию вставляется 0

                $this->blocks[0][] = $name;
            }
            return;
        }  

        $default = array_keys($blocks)[0];

        foreach ($this->columns as $name => $value) {
            if ($name === 'id_row')
                continue;

            $insert = false;

            foreach ($blocks as $block => $value) {
                if(!array_key_exists($block, $this->blocks))
                    $this->blocks[$block] = [];

                if(in_array($name, $value)){
                    $this->blocks[$block][] = $name;
                    $insert = true;
                    break;
                }  
            }
            if(!$insert)
                $this->blocks[$default][] = $name;

            if(empty($this->translate[$name]))
                $this->translate[$name][] = $name;
        }
        return;
    }

# -------------------- CREATE FOREİGN DATA ---------------------------------------

    protected function createForeignData($settings = false)
    {
        if (!$settings) $settings =  Settings::instance();

        $root = $settings::get('root');

        $keys = $this->model->getForeignKeys($this->table);

        if ($keys) {
            foreach ($keys as $item) {
                $this->createForeignProperty($item, $root);
            }
        } elseif (!empty($this->columns['parent_id'])) {
    
            $arr['COLUMN_NAME'] = 'parent_id';
            $arr['REFERENCED_COLUMN_NAME'] = $this->columns['id_row'];
            $arr['REFERENCED_TABLE_NAME'] = $this->table;

            $this->createForeignProperty($arr, $root);
        }
    }

# -------------------- CREATE FOREİGN PROPERTY -----------------------------------

    protected function createForeignProperty($arr, $root)
    {
        $where = false;
        $operand = false;

        if (in_array($this->table, $root['tables'])) {
            $this->foreignData[$arr['COLUMN_NAME']][0]['id'] = 'NULL'; 
            $this->foreignData[$arr['COLUMN_NAME']][0]['name'] = $root['name'];
        }

        $orderData = $this->createOrderData($arr['REFERENCED_TABLE_NAME']);

        if ($this->data) {
            if ($arr['REFERENCED_TABLE_NAME'] === $this->table) {
                $where[$this->columns['id_row']] = $this->data[$this->columns['id_row']];
                $operand[] = '<>';
            }
        }

        $foreign = $this->model->select($arr['REFERENCED_TABLE_NAME'], [
            'fields' => [$arr['REFERENCED_COLUMN_NAME'] . ' as id', $orderData['name'], $orderData['parent_id']],
            'where' => $where,
            'operand' => $operand,
            'order' => $orderData['order']
        ]);

        if ($foreign) {
            if (!empty($this->foreignData[$arr['COLUMN_NAME']])) {
                foreach ($foreign as $value) {
                    $this->foreignData[$arr['COLUMN_NAME']][] = $value;
                }
            } else {
                $this->foreignData[$arr['COLUMN_NAME']] = $foreign;
            }
        }
    }

# -------------------- CREATE ORDER DATA -----------------------------------------

    protected function createOrderData($table)
    {
        $columns = $this->model->getColumns($table); # $columns = $this->columns

        if(!$columns)
            throw new RouteException('Отсутствуют поля в таблице ' . $table);

        $name = '';
        $order_name = '';

        if(!empty($columns['name'])) {
            $order_name = $name = 'name';
        }else{
            foreach($columns as $key => $value){
                if(strpos($key, 'name') !== false){
                    $order_name = $key;
                    $name =  $key . ' as name';
                }
            }

            if(!$name)
                $name = $columns['id_row'] . ' as name'; // непринципиално
        }

        $parent_id = '';
        $order = [];

        if(!empty($columns['parent_id']))
            $order[] = $parent_id = 'parent_id';

        if(!empty($columns['menu_position'])) 
            $order[] = 'menu_position';
        else 
            $order[] = $order_name;

        return compact('name', 'parent_id', 'order', 'columns');
    }

# -------------------- CREATE RADIO ----------------------------------------------

    protected function createRadio($settings = false)
    {
        if (!$settings) $settings = Settings::instance();

        $radio = $settings::get('radio');

        if($radio){
            foreach($this->columns as $name => $value){
                if(!empty($radio[$name])){
                    $this->foreignData[$name] = $radio[$name];
                }
            }
        }
    }

# -------------------- CREATE MENU POSITION --------------------------------------

    protected function createMenuPosition($settings = false)
    {
        $where = false;

        if (isset($this->columns['menu_position'])) {

            if (!$settings)
                $settings =  Settings::instance();

            $root = $settings::get('root');

            if (isset($this->columns['parent_id'])) {

                if (in_array($this->table, $root['tables'])) {
                    $where = 'parent_id IS NULL OR parent_id = 0';

                } else {
                    $parent = $this->model->getForeignKeys($this->table, 'parent_id');

                    if ($parent) {

                        $parent = $parent[0];

                        if ($this->table === $parent['REFERENCED_TABLE_NAME']) {
                            $where = 'parent_id IS NULL OR parent_id = 0';
                        } else {
                            $columns = $this->model->getColumns($parent['REFERENCED_TABLE_NAME']);

                            if (isset($columns['parent_id'])) {
                                $order[] = 'parent_id';
                            } else {
                                $order[] = $parent['REFERENCED_COLUMN_NAME'];
                            }

                            $id = $this->model->select($parent['REFERENCED_TABLE_NAME'], [
                                'fields' => [$parent['REFERENCED_COLUMN_NAME']],
                                'order' => $order,
                                'limit' => 1,
                            ])[0][$parent['REFERENCED_COLUMN_NAME']];

                            if($id) $where = ['parent_id' => $id];
                        }
                    } else {
                        $where = 'parent_id IS NULL OR parent_id = 0';
                    }
                }
            }

            $menu_position = $this->model->select($this->table, [
                'fields' => ['COUNT(*) as count'],
                'where' => $where,
                'no_concat' => true
            ])[0]['count'] + (int)!$this->data;

            for ($i = 1; $i <= $menu_position; $i++) {

                $this->foreignData['menu_position'][$i - 1]['id'] = $i;
                $this->foreignData['menu_position'][$i - 1]['name'] = $i;
            }
        }
        return;
    }
}

