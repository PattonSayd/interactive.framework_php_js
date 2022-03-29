<?php 

namespace core\admin\controller;

use core\base\settings\Settings;

class SearchController extends AdminController
{
    protected function inputData()
    {
        if(!$this->userId) $this->parent_inputData(); # parent::inputData() не вызываем из-за плагина 

        $text = $this->clearStr($_GET['search']);

        $table = $_GET['search_table'];

        $this->data = $this->model->search($text, $table);

        $this->createData(['fields' => 'visible']);  

        return $this->extension();

    }

# -------------------- CREATE DATA -----------------------------------------------
    
    protected function createData($arr = [])
    {
        $fields = [];
  
        foreach($this->data as $key => $item) {

            $columns = $this->model->getColumns($item['table_name']);

            if(!$columns['primary_key']) return $this->data = [];

            if(!empty($columns['content']))
                $fields['content'] = 'content';

            if(!empty($columns['created_at']))
                $fields['created_at'] = 'created_at';

            if(count($fields) < 2){

                foreach ($columns as $row => $value) {

                    if(empty($fields['content']) && strpos($row, 'content') !== false)
                        $fields['content'] = $row . ' as name';
                }
            }

            if (isset($arr['fields'])) {

                if (is_array($arr['fields'])) {

                    foreach ($arr['fields'] as $field){

                        if(in_array($field, array_keys($columns)))
                            $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']); 
                    }
                }else{
                    if(in_array($arr['fields'], array_keys($columns)))
                        $fields[] = $arr['fields'];
                }
            }

            if(!empty($fields)){

                $res = $this->model->select($item['table_name'], [
                    'fields' => $fields,
                    'where' => ['id' => $item['id']]
                ]);

                foreach ($res as $data) {

                    $this->data[$key] = Settings::instance()->arrayMergeRecursive($this->data[$key], $data);
                    
                    $fields = [];
                }
                
            }

            

        }

        
        
        

        
    }
}


?>