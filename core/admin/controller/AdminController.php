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

    protected $alias;

    protected $messages;
    protected $settings;

    protected $translate;
    protected $blocks = [];

    protected $templates;
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

        if(!$this->templates)
            $this->templates = Settings::get('templates');

        if (!$this->formTemplates)
            $this->formTemplates = PATH . Settings::get('formTemplates');

        if (!$this->messages)
            $this->messages = include $_SERVER['DOCUMENT_ROOT'] . PATH . Settings::get('messages') . 'infoMessages.php';
        
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

# -------------------- CHECK POST ------------------------------------------------

    protected function checkPost($settings = false)
    {
        if ($this->isPost()) {

            $this->clearPostFields($settings);

            $this->table = $this->clearStr($_POST['table']);

            unset($_POST['table']);

            if($this->table) {
                $this->createTableData($settings);

                $this->editData();  // ADD and EDIT METHOD                 
            }
        }
    }

# -------------------- CLEAR POST FIELDS -----------------------------------------

    protected function clearPostFields($settings, &$arr = [])
    {
        if (!$arr) $arr = &$_POST;

        if (!$settings) $settings =  Settings::instance();

        $id = isset($_POST[isset($this->columns['id_row'])]) ?  $_POST[$this->columns['id_row']] : false; # edit

        $validate =  Settings::get('validation');
        
        if(!$this->translate) $this->translate = $settings::get('translate');

        foreach ($arr as $key => $value) {

            if(is_array($value)) {
                $this->clearPostFields($settings, $value);
            }else{
                if(is_numeric($value))
                    $arr[$key] = $this->clearNum($value);
                
                if ($validate) {
                    if (!empty($validate[$key])) {
                        if ($this->translate[$key])
                            $answer = $this->translate[$key][0];
                        else 
                            $answer = $key;

                        if (!empty($validate[$key]['crypt'])) {
                            if ($id) {
                                if (empty($value)) {
                                    unset($arr[$key]);
                                    continue;
                                }

                                $arr[$key] = md5($value);
                            }
                        }

                        if (!empty($validate[$key]['empty']))
                            $this->emptyFields($value, $answer, $arr);

                        if (!empty($validate[$key]['trim']))
                            $arr[$key] = trim($value);

                        if (!empty($validate[$key]['int']))
                            $arr[$key] = $this->clearNum($value);

                        if (!empty($validate[$key]['count']))
                            $arr[$key] = $this->countChar($value, $validate[$key]['count'], $answer, $arr);
                    }
                }
            }
        }
             
        return true;
    }

# -------------------- EMPTY FIELDS ----------------------------------------------

    protected function emptyFields($value, $answer, $arr = [])
    {
        if(empty($value)) {
            $_SESSION['res']['answer'] = '<div class="alert alert-warning alert-styled-left alert-dismissible">
                                             <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                             <span class="font-weight-semibold">Warning!</span> '.$this->messages['empty'].'>
                                         </div>';
            $this->addSessionData($arr);
        }
    }

# -------------------- ADD SESSION DATA ------------------------------------------

    protected function addSessionData($arr = [])
    {
        if (!$arr)
            $arr = $_POST;

        foreach ($arr as $key => $value) {
            $_SESSION['res'][$key] = $value;
        }

        $this->redirect();
    }

# -------------------- COUNT CHAR --------------------------------------------------

    protected function countChar($value, $counter, $answer, $arr)
    {
        if (mb_strlen($value) > $counter) {

            $str_res = mb_str_replace('$1', '<span class="font-weight-bold">' . $answer. '</span>', $this->messages['count']);
            $str_res = mb_str_replace('$2', $counter, $str_res);

            $_SESSION['res']['answer'] = '<div class="alert alert-warning alert-styled-left alert-dismissible">
                                             <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                             <span class="font-weight-semibold">Warning!</span> '.$str_res .'
                                         </div>';

            $this->addSessionData($arr);
        }

        return $value;
    }

# -------------------- EDIT DATA -------------------------------------------------

    protected function editData($returnID = false)
    {
        $id = false;
        $where = false;
        $method = 'add';

        if(!empty($_POST['return_id'])) $returnID = true;        

        if (isset($_POST[$this->columns['id_row']])) {

            $id = is_numeric($_POST[$this->columns['id_row']]) ? $this->clearNum($_POST[$this->columns['id_row']]) : $this->clearStr($_POST[$this->columns['id_row']]);  // ЕСЛИ id В mysql СОДЕРЖЕТСЯ СТРОКА

            if($id) {
                $where = [$this->columns['id_row'] => $id];
                $method = 'edit';
            }
        }

        foreach ($this->columns as $key => $value) {

            if ($key === 'id_row')
                continue;

            if ($value['Type'] === 'date' || $value['Type'] === 'datetime') {
                       
                if (!isset($_POST[$key])) $_POST[$key] = 'NOW()';
            }
        }        

        // $this->createFile();

        // if($id && method_exists($this, 'checkFiles'))
        //     $this->checkFiles($id);

        $this->createAlias($id);

        // $this->updateMenuPosition($id);

        $except = $this->checkExceptFields(); 

        $res_id = $this->model->$method($this->table, [
            'files' => $this->fileArray,
            'where' => $where,
            'return_id' => true,
            'except' => $except,
        ]);

        if (!$id && $method === 'add') {
            $_POST[$this->columns['id_row']] = $res_id;

            $answerSuccess = $this->messages['addSuccess'];
            $answerFail = $this->messages['addFail'];
        } else {
            $answerSuccess = $this->messages['editSuccess'];
            $answerFail = $this->messages['editFail'];
        }

        // $this->checkManyToMAny();

        $this->extension(get_defined_vars());

        $result = $this->checkAlias($_POST[$this->columns['id_row']]);

        if ($res_id) {

            $_SESSION['res']['answer'] = '<div class="alert alert-success alert-styled-left alert-arrow-left alert-dismissible alert-setInterval">
                                             <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                             <span class="font-weight-semibold">Well done!</span> '.$answerSuccess .'
                                         </div>';

            if (!$returnID) $this->redirect();

            return $_POST[$this->columns['id_row']];

        } else {
            $_SESSION['res']['answer'] = '<div class="alert alert-danger alert-styled-left alert-dismissible">
                                             <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                             <span class="font-weight-semibold">Oh snap!</span> '.$answerFail .'
                                         </div>';

            if (!$returnID) $this->redirect();
        }
    }

# -------------------- CHECK EXCEPT FIELDS ---------------------------------------

    protected function checkExceptFields($arr = [])
    {
        if (!$arr) $arr = $_POST;

        $except = [];

        if ($arr) {
            foreach ($arr as $key => $value)

                if (!isset($this->columns[$key]))
                    $except[] = $key;
        }

        return $except; 
    }

# -------------------- CREATE ALIAS ----------------------------------------------

    protected function createAlias($id = false)
    {
        if (isset($this->columns['alias'])) {

            if (!isset($_POST['alias'])) {

                if ($_POST['name']) {
                    $alias_str = $this->clearStr($_POST['name']);
                } else {
                    foreach ($_POST as $key => $value) {

                        if (strpos($key, 'name') !== false && $value) {
                            $alias_str = $this->clearStr($value);
                            break;
                        }
                    }
                }
            } else {
                $alias_str = $_POST['alias'] = $this->clearStr($_POST['alisa']);
            }

            $textModify = new \libraries\TextModify();
            $alias = $textModify->translit($alias_str);

            $where['alias'] = $alias;
            $operand[] = '=';

            if ($id) {
                $where[$this->columns['id_row']] = $id;
                $operand[] = '<>';
            }

            $res_alias = $this->model->select($this->table, [
                'fields' => ['alias'],
                'where' => $where,
                'operand' => $operand,
                'limit' => 1,
            ]);

            if (!$res_alias) {
                $_POST['alias'] = $alias;
                
            } else {  // для редактирования
                $this->alias = $alias;
                $_POST['alias'] = '';
            }

            if ($_POST['alias'] && $id) {
                method_exists($this, 'checkOldAlias') && $this->checkOldAlias($id);
            }
        }
    }

# -------------------- CHECK ALIAS -----------------------------------------------

    protected function checkAlias($id)
    {
        if ($id) {

            if ($this->alias) {
                $this->alias .= '-' . $id;

                $this->model->edit($this->table, [
                    'fields' => ['alias' => $this->alias],
                    'where' => [$this->columns['id_row'] => $id]
                ]);

                return true;
            }
        }

        return false;
    }

}