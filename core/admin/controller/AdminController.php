<?php 
namespace core\admin\controller;

use core\admin\model\AdminModel;
use core\base\controller\Controller;
use core\base\exception\RouteException;
use core\base\settings\Settings;
use libraries\FileEdit;

abstract class AdminController extends Controller
{
    protected $model;

    protected $table;
    protected $columns;
    protected $foreignData;

    protected $adminPath;
  
    protected $menu;
    protected $title;

    protected $alias;
    protected $fileArray;

    protected $messages;
    protected $settings;

    protected $translate;
    protected $blocks = [];

    protected $templates;
    protected $formTemplatesPath;
    protected $noDelete;

# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {        
        if(!MS_MODE){

            if(preg_match('/msie|trident.+?rv\s*:/', $_SERVER['HTTP_USER_AGENT'])){

                exit('Вы используете устаревшую версию браузера. Пожалуйста, обновитесь до актуальной версии.'); // EX. Ссылка на скачку браузера
            }
        }
        
        $this->init(true);
                  
        $this->title = 'GN engine';

        if(!$this->model) 
            $this->model = AdminModel::instance();

        if(!$this->menu)
            $this->menu = Settings::get('projectTable');

        if(!$this->adminPath)
            $this->adminPath = PATH . Settings::get('routes')['admin']['alias'] . '/';

        if(!$this->templates)
            $this->templates = Settings::get('templates');

        if (!$this->formTemplatesPath)
            $this->formTemplatesPath = PATH . Settings::get('formTemplatesPath');

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

            // if (!$this->template) $this->template = ADMIN_TEMPLATE . 'show';

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

    protected function parent_inputData()
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

    protected function createBlock($settings = false)           
    {                                                           
        if (!$settings)                                         
            $settings = Settings::instance();

        $blocks = $settings::get('blocks');

        $this->translate = $settings::get('translate');

        if(!$blocks || !is_array($blocks)){

            foreach ($this->columns as $name => $value) {
                if($name === 'primary_key')
                    continue;

                if(empty($this->translate[$name]))
                    $this->translate[$name][] = $name; // [] по умолчанию вставляется 0

                $this->blocks[0][] = $name;
            }
            return;
        }  

        $default = array_keys($blocks)[0];

        foreach ($this->columns as $name => $value) {
            if ($name === 'primary_key')
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
            $arr['REFERENCED_COLUMN_NAME'] = $this->columns['primary_key'];
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

        $rows = $this->createаForeignRows($arr['REFERENCED_TABLE_NAME']);

        if ($this->data) {
            if ($arr['REFERENCED_TABLE_NAME'] === $this->table) {
                $where[$this->columns['primary_key']] = $this->data[$this->columns['primary_key']];
                $operand[] = '<>';
            }
        }

        $foreign = $this->model->select($arr['REFERENCED_TABLE_NAME'], [
            'fields' => [$arr['REFERENCED_COLUMN_NAME'] . ' as id', $rows['name'], $rows['parent_id']],
            'where' => $where,
            'operand' => $operand,
            'order' => $rows['order']
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

# -------------------- CREATE FOREIGN ROWS ---------------------------------------

    protected function createаForeignRows($table)
    {
        $columns = $this->model->getColumns($table); # $columns = $this->columns

        if(!$columns)
            throw new RouteException('Отсутствуют поля в таблице ' . $table);

        $name = '';
        $row_name = '';

        if(!empty($columns['name'])) {
            $row_name = $name = 'name';
        }else{
            foreach($columns as $key => $value){
                if(strpos($key, 'name') !== false){
                    $row_name = $key;
                    $name =  $key . ' as name';
                }
            }

            if(!$name)
                $name = $columns['primary_key'] . ' as name'; // непринципиално
        }

        $parent_id = '';
        $order = [];

        if (!empty($columns['parent_id'])) $order[] = $parent_id = 'parent_id';

        if (!empty($columns['menu_position'])) $order[] = 'menu_position';
        else $order[] = $row_name;

        return compact('name', 'parent_id', 'order', 'columns');
    }

# -------------------- CREATE MANY TO MANY ---------------------------------------
    
    protected function createManyToMany($settings = false)
    {
        if (!$settings) $settings = $this->settings ?: Settings::instance();

        $manyToMany = $settings::get('manyToMany');
        $blocks = $settings::get('blocks');

        if($manyToMany){

            foreach($manyToMany as $pivot_table => $tables){

                $main_table_key = array_search($this->table, $tables);

                if($main_table_key !== false){

                    $extra_table_key = $main_table_key ? 0 : 1;

                    $checkbox = $settings::get('templates')['checkbox'];
                       
                    if(!$checkbox || !in_array($tables[$extra_table_key], $checkbox)) continue;

                    if(!isset($this->translate[$tables[$extra_table_key]])){

                        if(isset($settings::get('projectTable')[$tables[$extra_table_key]]))
                            $this->translate[$tables[$extra_table_key]] = [$settings::get('projectTable')[$tables[$extra_table_key]]['name']];
                    }

                    $rows = $this->createаForeignRows($tables[$extra_table_key]);

                    $insert = false;
                    
                    if($blocks){

                        foreach($blocks as $key => $item) {
                            
                            if(in_array($tables[$extra_table_key], $item)) {

                                $this->blocks[$key][] = $tables[$extra_table_key];

                                $insert = true;

                                break;
                            }
                        }
                    }

                    if(!$insert) $this->blocks[array_keys($this->blocks)[0]][] = $tables[$extra_table_key]; 

                    $foreign = [];

                    if($this->data){

                        $res = $this->model->select($pivot_table, [
                            'fields' => [$tables[$extra_table_key] . '_' . $rows['columns']['primary_key']],
                            'where' => [$this->table . '_' . $this->columns['primary_key'] => $this->data[$this->columns['primary_key']]],
                        ]);

                        if($res){
                            foreach($res as $item) {

                                $foreign[] = $item[$tables[$extra_table_key] . '_' . $rows['columns']['primary_key']];
                            }
                        }
                    }
                    
                    if(isset($tables['type'])){

                        $data = $this->model->select($tables[$extra_table_key], [
                            'fields' => [$rows['columns']['primary_key'] . ' as id', $rows['name'], $rows['parent_id']],
                            'order' => $rows['order']
                        ]);
                        
                        if($data){

                            $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['name'] = 'Select';

                            foreach($data as $item){
                                
                                if($tables['type'] === 'root' && $rows['parent_id']){

                                    if($item[$rows['parent_id']] === null)
                                        $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['sub'][] = $item;

                                }elseif($tables['type'] === 'child' && $rows['parent_id']) {

                                    if ($item[$rows['parent_id']] !== null)
                                        $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['sub'][] = $item;

                                }else{
                                    $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['sub'][] = $item;
                                }

                                if(in_array($item['id'], $foreign))
                                    $this->data[$tables[$extra_table_key]][$tables[$extra_table_key]][] = $item['id'];
                            }
                        }

                    }elseif($rows['parent_id']){
                        $parent = $tables[$extra_table_key];

                        $keys = $this->model->getForeignKeys($tables[$extra_table_key]);

                        if($keys){
                            
                            foreach($keys as $item) {
                                if($item['COLUMN_NAME'] === 'parent_id'){

                                    $parent = $item['REFERENCED_TABLE_NAME'];
                                    break;  
                                }
                            }
                        }

                        if($parent === $tables[$extra_table_key]){

                            $data = $this->model->select($tables[$extra_table_key], [
                                'fields' => [$rows['columns']['primary_key'] . ' as id', $rows['name'], $rows['parent_id']],
                                'order' => $rows['order']
                            ]);

                            if($data){
                                while(($key = key($data)) !== null){

                                    if(!$data[$key]['parent_id']){

                                        $this->foreignData[$tables[$extra_table_key]][$data[$key]['id']]['name'] = $data[$key]['name'];

                                        unset($data[$key]);
                                        reset($data);
                                        continue;

                                    }else{                      
                                        if(isset($this->foreignData[$tables[$extra_table_key]][$data[$key][$rows['parent_id']]])){

                                            $this->foreignData[$tables[$extra_table_key]][$data[$key][$rows['parent_id']]]['sub'][$data[$key]['id']] = $data[$key];

                                            if(in_array($data[$key]['id'], $foreign))
                                                $this->data[$tables[$extra_table_key]][$data[$key][$rows['parent_id']]][] = $data[$key]['id'];

                                            unset($data[$key]);
                                            reset($data);
                                            continue;

                                        }else{
                                            foreach($this->foreignData[$tables[$extra_table_key]] as $id => $item) {

                                                $parent_id = $data[$key][$rows['parent_id']];
                                                
                                                if(isset($item['sub']) && $item['sub'] && isset($item['sub'][$parent_id])){

                                                    $this->foreignData[$tables[$extra_table_key]][$id]['sub'][$data[$key]['id']] = $data[$key];

                                                    if(in_array($data[$key]['id'], $foreign))
                                                        $this->data[$tables[$extra_table_key]][$id][] = $data[$key]['id'];
                                                    
                                                    unset($data[$key]);
                                                    reset($data);
                                                    continue 2;
                                                }
                                            }
                                        }

                                        next($data);
                                    }
                                }
                            }

                        }else{
                            $parent_rows = $this->createаForeignRows($parent);

                            $data = $this->model->select($parent, [
                                'fields' => [$parent_rows['name']],
                                'join' => [
                                    $tables[$extra_table_key] =>[
                                        'fields' => [$rows['columns']['primary_key'] . ' as id', $rows['name']],
                                        'on' => [$parent_rows['columns']['primary_key'], $rows['parent_id']], 
                                    ]
                                ],
                                'join_structure' => true,
                            ]);

                            foreach($data as $key => $item) {

                                if(isset($item['join'][$tables[$extra_table_key]]) && $item['join'][$tables[$extra_table_key]]){

                                    $this->foreignData[$tables[$extra_table_key]][$key]['name'] = $item['name'];
                                    $this->foreignData[$tables[$extra_table_key]][$key]['sub'] = $item['join'][$tables[$extra_table_key]];

                                    foreach($item['join'][$tables[$extra_table_key]] as $value){

                                        if(in_array($value['id'], $foreign))
                                            $this->data[$tables[$extra_table_key]][$key][] = $value['id'];
                                    }
                                }   
                            }
                        }

                    }else{
                        $data = $this->model->select($tables[$extra_table_key], [
                            'fields' => [$rows['columns']['primary_key'] . ' as id', $rows['name'], $rows['parent_id']],
                            'order' => $rows['order']
                        ]);

                        if($data) {
                            $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['name'] = 'Выбрать';

                            foreach($data as $item) {
                                $this->foreignData[$tables[$extra_table_key]][$tables[$extra_table_key]]['sub'][] = $item;

                                if(in_array($item['id'], $foreign)){
                                    $this->data[$tables[$extra_table_key]][$tables[$extra_table_key]][] = $item['id'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

# -------------------- CHECK MANY TO MANY ------------------------------------------

    protected function checkManyToMany($settings = false)
    {
        if (!$settings) $settings = $this->settings ? $this->settings : Settings::instance();

        $manyToMany = $settings::get('manyToMany');

        if($manyToMany) {
            foreach($manyToMany as $pivot_table => $tables) {

                $main_table_key = array_search($this->table, $tables);

                if($main_table_key !== false) {
                    $extra_table_key = $main_table_key ? 0 : 1; 

                    $checkbox = $settings::get('templates')['checkbox'];

                    if(empty($checkbox) || !in_array($tables[$extra_table_key], $checkbox))
                        continue;

                    $columns = $this->model->getColumns($tables[$extra_table_key]);

                    $main_row = $this->table . '_' . $this->columns['primary_key'];

                    $extra_row = $tables[$extra_table_key] . '_' . $columns['primary_key'];

                    $this->model->delete($pivot_table, [
                        'where' => [$main_row => $_POST[$this->columns['primary_key']]]
                    ]);

                    if(isset($_POST[$tables[$extra_table_key]])) {

                        $fields = [];
                        $i = 0;

                        foreach($_POST[$tables[$extra_table_key]] as $value) {

                            foreach($value as $item) {

                                if($item) {
                                    $fields[$i][$main_row] = $_POST[$this->columns['primary_key']];

                                    $fields[$i][$extra_row] = $item; 

                                    $i++;
                                    
                                }
                            }
                        }

                        if($fields) {
                            $this->model->add($pivot_table, [
                                'fields' => $fields
                            ]);
                        }
                    }
                }
            }
        }
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

        $id = isset($_POST[isset($this->columns['primary_key'])]) ?  $_POST[$this->columns['primary_key']] : false; # edit

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
                            $name = $this->translate[$key][0];
                        else 
                            $name = $key;

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
                            $this->emptyFields($value, $name, $arr);

                        if (!empty($validate[$key]['trim']))
                            $arr[$key] = trim($value);

                        if (!empty($validate[$key]['int']))
                            $arr[$key] = $this->clearNum($value);

                        if (!empty($validate[$key]['count']))
                            $arr[$key] = $this->countChar($value, $validate[$key]['count'], $name, $arr);
                    }
                }
            }
        }
             
        return true;
    }

# -------------------- EMPTY FIELDS ----------------------------------------------

    protected function emptyFields($value, $name, $arr = [])
    {
        if(empty($value)) {
            $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-warning">
                                            <span><i class="gn-icon gn-warning-color icon-exclamation"></i></span>
                                            <span class="gn-msg gn-warning-color"><b>Warning! </b> '.$this->messages['empty'] .'</span> 
                                            <span class="gn-btn-close">
                                            <span class="gn-close gn-warning-color-hover"><i class="gn-close-icon gn-warning-color icon-cross"></i></span>
                                            </span>
                                        </div>';           
            $this->addSessionData($arr);
        }
    }

# -------------------- ADD SESSION DATA ------------------------------------------

    protected function addSessionData($arr = [])
    {
        if (!$arr) $arr = $_POST;

        foreach ($arr as $key => $value) {
            $_SESSION['res'][$key] = $value;
        }

        $this->redirect();
    }

# -------------------- COUNT CHAR --------------------------------------------------

    protected function countChar($value, $count, $name, $arr)
    {
        if (mb_strlen($value) > $count) {

            $message = sprintf($this->messages['count'], '<u>' . mb_strtolower($name) . '</u>', $count);
           
            // $message = mb_str_replace('$1', '<span class="font-weight-bold">' . mb_strtolower($name) . '</span>', $this->messages['count']);
            // $message = mb_str_replace('$2', $count, $message);

            $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-warning">
                                            <span><i class="gn-icon gn-warning-color icon-exclamation"></i></span>
                                            <span class="gn-msg gn-warning-color"><b>Warning! </b> '. $message .'</span> 
                                            <span class="gn-btn-close">
                                            <span class="gn-close gn-warning-color-hover"><i class="gn-close-icon gn-warning-color icon-cross"></i></span>
                                            </span>
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

        if (isset($_POST[$this->columns['primary_key']])) {

            $id = is_numeric($_POST[$this->columns['primary_key']]) ? $this->clearNum($_POST[$this->columns['primary_key']]) : $this->clearStr($_POST[$this->columns['primary_key']]);  // ЕСЛИ id В mysql СОДЕРЖЕТСЯ СТРОКА

            if($id) {
                $where = [$this->columns['primary_key'] => $id];
                $method = 'edit';
            }
        }

        foreach ($this->columns as $key => $value) {

            if ($key === 'primary_key')
                continue;

            if ($value['Type'] === 'date' || $value['Type'] === 'datetime') {
                       
                if (!isset($_POST[$key])) $_POST[$key] = 'NOW()';
            }
        }        

        $this->createFiles($id);

        $this->createAlias($id);

        $this->updateMenuPosition($id);

        $except = $this->checkExceptFields(); 

        $res_id = $this->model->$method($this->table, [
            'files' => $this->fileArray,
            'where' => $where,
            'return_id' => true,
            'except' => $except,
        ]);

        if (!$id && $method === 'add') {
            $_POST[$this->columns['primary_key']] = $res_id;

            $answerSuccess = $this->messages['addSuccess'];
            $answerFail = $this->messages['addFail'];
        } else {
            $answerSuccess = $this->messages['editSuccess'];
            $answerFail = $this->messages['editFail'];
        }

        $this->checkManyToMAny();

        $this->extension(get_defined_vars());

        $this->checkAlias($_POST[$this->columns['primary_key']]);

        if ($res_id) {
            $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-success">
                                            <span><i class="gn-icon gn-success-color icon-checkmark-circle"></i></span>
                                            <span class="gn-msg gn-success-color"><b>Well done! </b> '. $answerSuccess .'</span> 
                                            <span class="gn-btn-close">
                                            <span class="gn-close gn-success-color-hover"><i class="gn-close-icon gn-success-color icon-cross"></i></span>
                                            </span>
                                          </div>';

            if (!$returnID) $this->redirect();

            return $_POST[$this->columns['primary_key']];

        } else {
            $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-error">
                                            <span><i class="gn-icon gn-error-color icon-blocked"></i></span>
                                            <span class="gn-msg gn-error-color"><b>Oh snap! </b> '. $answerFail .'</span> 
                                            <span class="gn-btn-close">
                                            <span class="gn-close gn-error-color-hover"><i class="gn-close-icon gn-error-color icon-cross"></i></span>
                                            </span>
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

    protected function updateMenuPosition($id = 'false')
    {
        if(isset($_POST['menu_position'])){

                $where = false;

                if ($id && $this->columns['primary_key'])
                    $where = [$this->columns['primary_key'] => $id];

                if(array_key_exists('parent_id', $_POST))
                    $this->model->updateMenuPosition($this->table, 'menu_position', $where, $_POST['menu_position'], ['where' => 'parent_id']);
                else
                    $this->model->updateMenuPosition($this->table, 'menu_position', $where, $_POST['menu_position']);
        }

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
                $where[$this->columns['primary_key']] = $id;
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
                    'where' => [$this->columns['primary_key'] => $id]
                ]);

                return true;
            }
        }

        return false;
    }

# -------------------- CHECK OLD ALIAS -------------------------------------------

    protected function checkOldAlias($id)
    {
        $tables = $this->model->getTables();

        if (in_array('old_alias', $tables)) {

            $old_alias = $this->model->select($this->table, [   // получаем текущий ALIAS 
                'fields' => ['alias'],
                'where' => [$this->columns['primary_key'] => $id]
            ])[0]['alias'];

            if ($old_alias && $old_alias !== $_POST['alias']) {

                $this->model->delete('old_alias', [   // удаляем текущеe значение поля OLD_ALIAS,  если в нем существует значение
                    'where' => ['alias' => $old_alias, 'table_name' => $this->table]
                ]);

                $this->model->delete('old_alias', [
                    'where' => ['alias' => $_POST['alias'], 'table_name' => $this->table]
                ]);

                $this->model->add('old_alias', [
                    'fields' => ['alias' => $old_alias, 'table_name' => $this->table, 'table_id' => $id]
                ]);
            }
        }
    }

# -------------------- CREATE FILES -----------------------------------------------

    protected function createFiles($id)
    { 
        $fileEdit = new FileEdit;

        $this->fileArray = $fileEdit->addFile($this->table);

        if($id) $this->fileExistenceCheck($id);

        if(!empty($_POST['js-sorting']) && $this->fileArray){

            foreach($_POST['js-sorting'] as $key => $value){

                if(!empty($value) && !empty($this->fileArray[$key])){

                    $file_array = json_decode($value);

                    if($file_array) $this->fileArray[$key] = $this->sortingFiles($file_array, $this->fileArray[$key]); 
                    
                }
                
            }
            
        } 
    }
    
# -------------------- SORTING FILES -------------------------------------- -----   

    protected function sortingFiles($file_array, $array)
    {
        $res = [];

        foreach ($file_array as $file){

            if(!is_numeric($file))
                $file = substr($file, strlen(PATH . UPLOAD_DIR));
            else
                $file = $array[$file];

            if($file && in_array($file, $array))
                $res[] = $file;
        }

        return $res;        
    }

# -------------------- FILE EXISTENCE CHECH --------------------------------------    

    protected function fileExistenceCheck($id) # обезопасить от перезаписи файлов
    {
        if ($id) {

            $arr_keys = [];

            if(!empty($this->fileArray)) $arr_keys = array_keys($this->fileArray);

            if(!empty($_POST['js-sorting'])) $arr_keys = array_merge($arr_keys, array_keys($_POST['js-sorting']));

            if($arr_keys){

                $arr_keys = array_unique($arr_keys);

                $data = $this->model->select($this->table, [
                    'fields' => $arr_keys,
                    'where' => [$this->columns['primary_key'] => $id]
                ]);
    
                if ($data) {
    
                    $data = $data[0];
    
                    foreach ($data as $key => $item) {

                        if((!empty($this->fileArray[$key]) && is_array($this->fileArray[$key])) || !empty($_POST['js-sorting'][$key])){

                            $fileArr = json_decode($item);
    
                            if ($fileArr) {
    
                                foreach ($fileArr as $file) {
    
                                    $this->fileArray[$key][] = $file;
                                }
                            }
                        }elseif (!empty($this->fileArray[$key])) {
    
                            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $item);
                        }
                    }
                }
            }
        }
    }
}