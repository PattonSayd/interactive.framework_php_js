<?php

namespace core\admin\model;

use core\base\controller\Singleton;
use core\base\model\Model;

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
    
}