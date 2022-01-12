<?php

namespace core\base\model;

abstract class ModelMethods
{    
    protected $sql_func = ['RAND()', 'NOW()'];
    protected $tableRows;

/*
|--------------------------------------------------------------------------
|                   CREATE FIELDS
|--------------------------------------------------------------------------
|
|  'table' => 'table'
|  'fields' => ['id', 'name']      default: *
|
|   SELECT table.id, table.name FROM table
*/

    protected function createFields($set, $table = false)
    {
		$set['fields'] = is_array($set['fields']) &&  !empty($set['fields']) ? $set['fields'] : ['*'];

		$table = ($table && !isset($set['no_concat'])) ? $table . '.' : '';

		$fields = ''; 

		foreach($set['fields'] as $field){
            if(!empty($field))
			    $fields .= $table . $field . ', ';  
		}

		return $fields;
    }

/*
|--------------------------------------------------------------------------
|                   CREATE WHERE
|--------------------------------------------------------------------------
|   
|   'table'           => 'table'
|   'where'           => ['id' => '1, 2, 3', 'game' => 'chess', 'name' => 'phil',  'color'=>['red', 'green']],
|   'operand'         => ['<>', '=', '%LIKE', 'NOT IN'],  default: '=',
|	'condition'       => ['OR', 'AND']    default: 'AND'
|   
|   "WHERE table.id <> '1, 2, 3' OR table.game = 'chess' AND table.name LIKE '%phil' AND table.color NOT IN ('red', 'green') "
*/

    protected function createWhere($set, $table = false, $instruction = 'WHERE')
    {
		$table = ($table && !isset($set['no_concat'])) ? $table . '.' : '';

        if(!empty($set['where']) &&  is_string($set['where']))
            return $instruction . ' ' . trim($set['where']);
        
		$where = ''; 

		if (!empty($set['where']) && is_array($set['where'])){

			$set['operand'] = !empty($set['operand']) && is_array($set['operand']) ? $set['operand'] : ['='];
			$set['condition'] = !empty($set['condition']) && is_array($set['condition']) ? $set['condition'] : ['AND'];

			$where = $instruction; 

			$operand_count = 0;
			$condition_count = 0;
			
			foreach($set['where'] as $key => $value){

				$where .= ' '; 
				
				if ($set['operand'][$operand_count]) {
					$operand = $set['operand'][$operand_count];
					$operand_count++;

				}else{
					$operand = $set['operand'][$operand_count - 1];
				}

				if ($set['condition'][$condition_count]) {
					$condition = $set['condition'][$condition_count];
					$condition_count++;

				}else{
					$condition = $set['condition'][$condition_count - 1];
				}

				if ($operand === 'IN' || $operand === 'NOT IN') {
					
                    if (is_string($value) && strpos($value, 'SELECT') === 0) {
                        $in = $value;

                    } else {
                        if (is_array($value)) {
                            $temp_value = $value;

                        } else {
                            $temp_value = explode(',', $value);
                        }
						
                        $in = '';

                        foreach ($temp_value as $v) {
                            $in .= "'" . addslashes(trim($v)) . "', ";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($in, ', ') . ') ' . $condition;

                }elseif (strpos($operand, 'LIKE') !== false) {

                    $like = explode('%', $operand);

                    foreach ($like as $like_key => $like_value) {

                        if (!$like_value) {

                            if (!$like_key) {
                                $value = '%' . $value;
                            } else {
                                $value .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . addslashes($value) . "' " .  $condition;

                }else{

                    if(strpos($value, 'SELECT') === 0) {
                        $where .= $table . $key . " $operand " . '(' . $value . ')' . $condition;

                    }elseif($value === NULL || $value === 'NULL'){

                        if($operand === '=') 
                            $where .= $table . $key . ' IS NULL ' . $condition;
                        else
                            $where .= $table . $key . ' IS NOT NULL ' . $condition;

                    }else{
                        $where .= $table . $key . " $operand " . "'" . addslashes($value)  . "' " . $condition;
                    }
                }
			}

            $where = substr($where, 0, strrpos($where, $condition));	

			// $where = rtrim($where, "$condition ");

		}

		return $where;
    }


/*
|--------------------------------------------------------------------------
|                   CREATE ORDER
|--------------------------------------------------------------------------
|   
|  'table'           => 'table'
|  'order'           => ['name', 'surname']
|  'order_direction' => ['DESC', 'ASC']      default: ASC
|   
|   ORDER BY table.name DESC, table.surname ASC;
*/

    protected function createOrder($set, $table = false)
    {
        $table = ($table && !isset($set['no_concat'])) ? $table . '.' : '';

        $order_by = ''; 

        if (!empty($set['order']) && is_array($set['order'])){

            $set['order_direction'] = !empty($set['order_direction']) && is_array($set['order_direction']) ? $set['order_direction'] : ['ASC'];

            $order_by = 'ORDER BY '; 

            $direct_count = 0;
            
            foreach($set['order'] as $order){

                if (!empty($set['order_direction'][$direct_count])) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;

                }else{																								#	0++     1++   2-- (1)
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]); # 'order_direction' => ['ASC', 'DESC', (DESC)],
                }

                if(is_int($order)) 
                    $order_by .= $order . ' ' . $order_direction . ', ';
                    
                else 
                    $order_by .= $table . $order . ' ' . $order_direction . ', ';
                
            }

            $order_by = rtrim($order_by, ', ');
        }
        
        return $order_by;
    }

    
/*
|--------------------------------------------------------------------------
|                   CREATE JOIN
|--------------------------------------------------------------------------
|  CREATE FIELDS, WHERE, ORDER
|  'join' =>  
|        [
|		  '0' =>  [
|		 	  'table'			 => 'join_table',
|		 	  'fields' 			 => ['id as j_id', 'name as j_name'],
|		 	  'type'  			 => 'left',
|		 	  'where'            => ['color' => 'yellow'],
|		 	  'operand'     	 => ['='],
|		 	  'condition'	     => ['OR'],
|		 	  'on'			     => ['id', 'parent_id'],
|		 	  'group_condition'  => 'AND'
|           ],
|		 ],
|   
|   fields: table.id, table.surname, join_table.id as j_id, join_table.name as j_name
|   where: OR join_table.color = 'yellow'
|   join: LEFT JOIN join_table ON table.id = join_table.parent_id
|   
|   "SELECT table.id, table.surname, join_table.id as j_id, join_table.name as j_name
|    FROM table 
|    LEFT JOIN join_table 
|    ON table.id = join_table.parent_id WHERE table.id = '4'
|    WHERE table.id = '4'
|       OR table.game <> 'chess' 
|       AND table.name LIKE '%phil' 
|       AND table.color NOT IN ('white', 'black') 
|       AND join_table2.name = 'Yellow'

*/

    protected function createJoin($set, $table = false, $new_where = false)
    {
        $fields = '';
        $join = '';
        $where = '';

        if (!empty($set['join'])) {

            $join_table = $table;

            foreach ($set['join'] as $key => $value) {

                if (is_int($key)) {
                    if (!$value['table']) 
                        continue;
                    else 
                        $key = $value['table'];
                }

                if ($join) $join .= ' ';

                if ($value['on'] && $value['on']) {

                    if($value['on']['fields'] && is_array($value['on']['fields']) && count($value['on']['fields']) === 2){
                        $join_fields = $value['on']['fields'];

                    }elseif(count($value['on']) === 2){
                        $join_fields = $value['on'];

                    }else{
                        continue;
                    }

                    if (!$value['type'])
                        $join .= ' LEFT JOIN ';
                    else
                        $join .= trim(strtoupper($value['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';

                    if ($value['on']['table'])
                        $join_temp_table = $value['on']['table'];
                    else
                        $join_temp_table = $join_table;

                    $join .= '.' . $join_fields[0] . ' = ' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if ($new_where) {

                        if ($value['where'])
                            $new_where = false;

                        $group_condition = 'WHERE';

                    } else {
                        $group_condition = $value['group_condition'] ? strtoupper($value['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($value, $key, $set['join_structure']);
                    $where .= $this->createWhere($value, $key, $group_condition);
                }
            }
        }

        return compact('fields', 'join', 'where');
    }

/*
|--------------------------------------------------------------------------
|                   CREATE INSERT  
|--------------------------------------------------------------------------
|   
|  'except' => ['field']
|   
|  table (field, field2) VALUE ('field', 'field2')
*/

    protected function createInsert ($fields, $files, $except)
    {
        $insert = [];
        $insert['fields'] = '(';
        $insert['value'] = false;

        $array_type = array_keys($fields)[0];

        if(is_int($array_type)){

            $check_fields = false;
            $count_fields = 0;

            foreach($fields as $key => $items){

                $insert['value'] .= '(';

                if(!$count_fields)
                    $count_fields = count($fields[$key]);

                $a = 0; 

                foreach($items as $row =>$value){
                    
                    if($except && in_array($row, $except)) continue;

                    if(!$check_fields) $insert['fields'] .= $row . ',';

                    if(in_array($value, $this->sql_func))
                        $insert['value'] .= $value . ',';

                    elseif($value == 'NULL' || $value == NULL)
                        $insert['value'] .= "NULL" . ',';

                    else
                        $insert['value'] .= "'" . addslashes($value) . "',";
                    
                    $a++;

                    if($a === $count_fields) break; # ОБЕЗОПАСИТЬ ЛИШНЕЕ ВХОЖДЕНИЕ В VALUE
                }

                if($a < $count_fields){
                    for(; $a < $count_fields; $a++){
                        $insert['value'] .= "NULL" . ',';
                    }
                }
                
                $insert['value'] = rtrim($insert['value'], ',') . '),';

                if(!$check_fields){
                    $check_fields = true;
                    $insert['fields'] = rtrim($insert['fields'], ',') . ')';
                }
            }

            $insert['value'] = rtrim($insert['value'], ',');
            
        }else{
            
            $insert['value'] = '(';
            
            if($fields){

                foreach($fields as $row => $value){

                    if($except && in_array($row, $except))
                        continue;

                    $insert['fields'] .= $row . ',';

                    if(in_array($value, $this->sql_func)){
                        $insert['value'] .= $value . ',';

                    }elseif($value == 'NULL' || $value == NULL){
                        $insert['value'] .= "NULL" . ',';

                    }else{
                         $insert['value'] .= "'" . addslashes($value) . "',";
                    }

                }
            }

            if($files){

                foreach($files as $row => $file){
                    
                    $insert['fields'] .= $row . ',';

                    if(is_array($file))
                        $insert['value'] .= "'" . addslashes(json_encode($file)) . "',";
                    else
                        $insert['value'] .= "'" . addslashes($file) . "',";
                }
            }

            foreach($insert as $key => $arr){ 
                $insert[$key] = rtrim($arr, ',') . ')';
            }
            
        }
        return $insert;
    }
    
/*
|--------------------------------------------------------------------------
|                   CREATE UPDATE  
|--------------------------------------------------------------------------
|   
|  UPDATE table SET update where
*/

    protected function createUpdate($fields, $files, $except)
    {
        $update = '';

        if($fields){

            foreach ($fields as $row => $value) {
                
                if ($except && in_array($row, $except)) continue;
                
                $update .= $row . '='; 

                if(in_array($value, $this->sql_func))
                    $update .= $value . ',';  

                elseif($value === 'NULL' || $value === NULL || $value === '')
                    $update .= 'NULL' . ',';

                else 
                    $update .= "'" . addslashes($value) . "',";
            }
        }

        if($files){
            foreach ($files as $row => $file) {
                $update .= $row . '=';

                if (is_array($file))
                    $update .= "'" . addslashes(json_encode($file)) . "',";
                else
                    $update .= "'" . addslashes($file) . "',";
            }
        }

        return rtrim($update, ',');
    }
    
}