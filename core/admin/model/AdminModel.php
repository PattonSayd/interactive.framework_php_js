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

        return $this->queryFunc($query);
    }
}