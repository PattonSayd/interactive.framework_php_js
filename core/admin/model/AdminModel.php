<?php

namespace core\admin\model;

use core\base\controller\Singleton;
use core\base\exception\RouteException;
use core\base\model\Model;
use core\base\settings\Settings;

class AdminModel extends Model
{
    use Singleton;
    
    public function getForeignKeys($table, $key = false)
    {  
        $db = DB_NAME;
        $where = false; 

        if($key)
            $where = "AND COLUMN_NAME = '$key' LIMIT 1";
            
        //                parent_id     ссылаемая таблица                id
        $query = "SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
         FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = '$db' 
         AND TABLE_NAME = '$table' 
         AND CONSTRAINT_NAME <> 'PRIMERY' 
         AND REFERENCED_TABLE_NAME is not null $where";

        return $this->query($query);
    }

    public function updateMenuPosition($table, $row, $where, $end_pos, $update_rows = []) 
    {
        if($update_rows && isset($update_rows['where'])){
           
            $update_rows['operand'] = isset($update_rows['operand']) ? $update_rows['operand'] : ['='];

            if($where){

                $old_data = $this->select($table, [
                    'fields' => [$update_rows['where'], $row],
                    'where' => $where
                ])[0];
 
                $start_pos = $old_data[$row];

                if($old_data[$update_rows['where']] !== $_POST[$update_rows['where']]){

                    $pos = $this->select($table, [
                        'fields' => ['COUNT(*) as count'],
                        'where' => [$update_rows['where'] => $old_data[$update_rows['where']]],
                        'no_concat' => true
                    ])[0]['count'];

                    if($start_pos != $pos) {

                        $update_where = $this->createWhere([
                            'where' => [$update_rows['where'] => $old_data[$update_rows['where']]],
                            'operand' => $update_rows['operand']
                        ]);

                        $query = "UPDATE $table SET $row = $row - 1 $update_where AND $row <= $pos AND $row > $start_pos"; 

                        $this->query($query, 'u');
                    }

                    $start_pos = $this->select($table, [
                        'fields' => ['COUNT(*) as count'],
                        'where' => [$update_rows['where'] => $_POST[$update_rows['where']]],
                        'no_concat' => true
                    ])[0]['count'] + 1;
                }

            }else{

                $start_pos = $this->select($table, [
                    'fields' => ['COUNT(*) as count'],
                    'where' => [$update_rows['where'] => $_POST[$update_rows['where']]],
                    'no_concat' => true
                ])[0]['count'] + 1;
            }

            if (array_key_exists($update_rows['where'], $_POST)) $where_equal = $_POST[$update_rows['where']];
            elseif (isset($old_data[$update_rows['where']])) $where_equal = $old_data[$update_rows['where']];
            else $where_equal = NULL;

            $db_where = $this->createWhere([
                'where' => [$update_rows['where'] => $where_equal],
                'operand' => $update_rows['operand']
            ]);

        }else{

            if($where){
                $start_pos = $this->select($table, [
                    'fields' => [$row],
                    'where' => $where
                ])[0][$row];

            }else{
                $start_pos = $this->select($table, [
                    'fields' => ['COUNT(*) as count'],
                    'no_concat' => true
                ])[0]['count'] + 1;
            }
        }

        $db_where = isset($db_where) ? $db_where . ' AND' : 'WHERE';

        if($start_pos < $end_pos)
            $query = "UPDATE $table SET $row = $row - 1 $db_where $row <= $end_pos AND $row > $start_pos";

        elseif($start_pos > $end_pos)
            $query = "UPDATE $table SET $row = $row + 1 $db_where $row >= $end_pos AND $row < $start_pos";
            
        else return;

        return $this->query($query, 'u');

    }
    
    public function search($data, $current_table = false, $qty = false)
    {

        $db_tables = $this->getTables();

        $data = addslashes($data);

        $arr = preg_split('/(,|\.)?\s+/', $data, 0, PREG_SPLIT_NO_EMPTY);

        $search_array = [];

        $order = [];
        
        for(;;){

            if(!$arr) break;

            $search_array[] = implode(' ', $arr);

            unset($arr[count($arr) - 1]);   

        }

        $correctCurrentTable = false;

        $projectTables = Settings::get('projectTable');

        if(!$projectTables) throw new RouteException('Ошибка поиска. Нет разделов в админ панели');

        foreach($projectTables as $table => $item){

            if(!in_array($table, $db_tables)) continue;

            $search_rows = [];

            $oreder_rows = ['name'];

            $fields = [];

            $columns = $this->getColumns($table);

            $fields[] = $columns['primary_key'] . ' as id';

            $field_name = isset($columns['name']) ? "CASE WHEN name <> '' THEN name " : '';

            foreach ($columns as $column => $value) {

                if($column !== 'name' && stripos($column, 'name') !== false){

                    if(!$field_name) $field_name = 'CASE ';

                    $field_name .= "WHEN $column <> '' THEN $column ";
                }

                if(isset($value['Type']) && 
                    (stripos($value['Type'], 'char') !== false || 
                    stripos($value['Type'], 'text') !== false)){
                    
                    $search_rows[] = $column; 
                        
                }
                
            }

            if($field_name) $fields[] = $field_name . 'END as name';
            else $fields[] = $columns['primary_key'] . ' as name';

            $fields[] = "('$table') AS table_name";

            $res = $this->createWhereOrder($search_rows, $search_array, $oreder_rows, $table);

            $where = $res['where'];

            !$order && $order = $res['order'];

            if($table === $current_table){

                $correctCurrentTable = true;

                $fields[] = "('current_table') AS current_table";                
            }

            if($where){
                $this->buildUnion($table,[
                    'fields' => $fields,
                    'where' => $where,
                    'no_concat' => true
                ]);
            }
        } 

        $order_direction = null;
        
        if($order){
            $order = ($correctCurrentTable ? 'current_table DESC, ' : '') . '(' . implode('+', $order). ')';
            $order_direction = 'DESC';
        }

        $result = $this->getUnion([
            // 'type' => 'all',
            // 'pagination' => [],
            // 'limit' => 3,
            'order' => $order,
            'order_direction' => $order_direction
        ]);

        $a = 1;

      
        
    }

    protected function createWhereOrder($search_rows, $search_array, $order_rows, $table)
    {
        $where = '';

        $order = [];

        if($search_rows && $search_array){

            $columns = $this->getColumns($table);

            if($columns){

                $where = '(';

                foreach ($search_rows as $row){

                    $where .= '(';

                    foreach($search_array as $item){

                        if(in_array($row, $order_rows)){

                            $str = "($row LIKE '%$item%')";

                            if(!in_array($str, $order)) $order[] = $str;
                        }

                        if(isset($columns[$row])) $where .= "$row LIKE '%$item%' OR ";
                    }

                    $where = preg_replace('/\)?\s*or\s*\(?$/i', '', $where) . ') OR ';
                }

                $where && $where = preg_replace('/\s*or\s*$/i', '', $where) . ')';
            }
        }
        return compact('where', 'order');
    }
}