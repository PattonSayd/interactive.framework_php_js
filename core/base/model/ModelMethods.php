<?php

namespace core\base\model;

abstract class ModelMethods
{    
    protected $sql_func = ['RAND()', 'NOW()'];
    protected $table_rows;
    protected $union = [];

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

    protected function createFields($set, $table = false, $join = false)
    {
        if(array_key_exists('fields', $set) && $set['fields'] === null) return '';

        $concat_table = '';
        $pseudo_table = $table;

        if(!isset($set['no_concat'])){

            $arr = $this->createPseudonymForTable($table);

            $concat_table = $arr['pseudo'] . '.';
            $pseudo_table = $arr['pseudo'];
        }
        
		$fields = '';
        $join_structure = false;

        if(($join || isset($set['join_structure']) && $set['join_structure']) && $table){
            $join_structure = true;

            $this->getColumns($table); # вызывается из admin/AdmonModel or user/UserModel
        
            if(isset($this->table_rows[$table]['multi_primary_key'])) $set['fields'] = [];
        }

        if(!isset($set['fields']) || !is_array($set['fields']) || !$set['fields']){

            if(!$join){
                $fields = $concat_table . '*,';
            }else{  
                foreach($this->table_rows[$pseudo_table] as $key => $item){
                    if($key !== 'primary_key' && $key !== 'multi_primary_key'){
                        $fields .= $concat_table . $key . ' as TABLE' . $pseudo_table . 'TABLE_' . $key . ',';
                    }
                }
            }
        }else{
            $id_field = false;

            foreach($set['fields'] as $field) {
                
                if($join_structure && !$id_field && $this->table_rows[$pseudo_table] === $field) $id_field = true;

                if($field || $field === null){

                    if($field === null){
                        
                        $fields .= "NULL,";
                        continue;
                    }

                    if($join && $join_structure){

                        if(preg_match('/^(.+)?\s+as\s+(.+)/i', $field, $matches))
                            $fields .= $concat_table . $matches[1] . ' as TABLE' . $pseudo_table . 'TABLE_' . $matches[2] . ',';
                        else
                            $fields .= $concat_table . $field . ' as TABLE' . $pseudo_table . 'TABLE_' . $field . ','; 
                        
 
                    }else{
                        $fields .= $concat_table . $field . ', ';
                    }
                    
                }
            }

            if(!$id_field && $join_structure){

                if($join)
                    $fields .= $concat_table . $this->table_rows[$pseudo_table]['primary_key'] . ' as TABLE' . $pseudo_table . 'TABLE_' . $this->table_rows[$pseudo_table]['primary_key'] . ',';
                else 
                    $fields .= $concat_table . $this->table_rows[$pseudo_table]['primary_key'] . ',';
            }
        }

        return $fields;
    }

/*
|--------------------------------------------------------------------------
|                   CREATE WHERE
|--------------------------------------------------------------------------
|   
|   'table'           => 'table'
|   'where'           => ['id' => '1, 2, 3', 'game' => 'chess', 'name' => 'phil',  'color'=>['black', 'white']],
|   'operand'         => ['<>', '=', '%LIKE', 'NOT IN'],  default: '=',
|	'condition'       => ['OR', 'AND']    default: 'AND'
|   
|   "WHERE table.id <> '1, 2, 3' OR table.game = 'chess' AND table.name LIKE '%phil' AND table.color NOT IN ('black', 'white') "
*/

    protected function createWhere($set, $table = false, $instruction = 'WHERE')
    {
        $table = ($table && (!isset($set['no_concat']) || !$set['no_concat'])) ? $this->createPseudonymForTable($table)['pseudo'] . '.' : '';

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
				
				if (isset($set['operand'][$operand_count])) {
					$operand = $set['operand'][$operand_count];
					$operand_count++;

				}else{
					$operand = $set['operand'][$operand_count - 1];
				}

				if (isset($set['condition'][$condition_count])) {
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


/* -----------------------
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
        $table = ($table && (!isset($set['no_concat']) || !$set['no_concat'])) ? $this->createPseudonymForTable($table)['pseudo'] . '.' : '';

        $order_by = ''; 

        if (isset($set['order']) && $set['order']){

            $set['order'] = (array)$set['order'];

            $set['order_direction'] = isset($set['order_direction']) && $set['order_direction'] ? (array)$set['order_direction'] : ['ASC'];

            $order_by = 'ORDER BY '; 

            $direct_count = 0;
            
            foreach($set['order'] as $order){

                if (!empty($set['order_direction'][$direct_count])) {
                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;

                }else{																								#	0++     1++   2-- (1)
                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]); # 'order_direction' => ['ASC', 'DESC', (DESC)],
                }

                if(in_array($order, $this->sql_func)) 
                    $order_by .= $order . ',';
                elseif(is_int($order)) 
                    $order_by .= $order . ' ' . $order_direction . ',';
                else 
                    $order_by .= $table . $order . ' ' . $order_direction . ',';
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
                    if (!$value['table']) continue;
                    else $key = $value['table'];
                }

                $concat_table = $this->createPseudonymForTable($key)['pseudo'];

                if ($join) $join .= ' ';

                if (isset($value['on']) && $value['on']) {
                    
                    if(isset($value['on']['fields']) && 
                       is_array($value['on']['fields']) && 
                       count($value['on']['fields']) === 2) $join_fields = $value['on']['fields'];
                    elseif(count($value['on']) === 2) $join_fields = $value['on'];
                    else continue;

                    if (empty($value['type'])) $join .= ' LEFT JOIN ';
                    else $join .= trim(strtoupper($value['type'])) . ' JOIN ';

                    $join .= $key . ' ON ';

                    if (!empty($value['on']['table'])) $join_temp_table = $value['on']['table'];
                    else $join_temp_table = $join_table;

                    $join .= $this->createPseudonymForTable($join_temp_table)['pseudo'];

                    $join .= '.' . $join_fields[0] . ' = ' . $concat_table . '.' . $join_fields[1];

                    $join_table = $key;

                    if ($new_where) {
                        if (!empty($value['where'])) $new_where = false;
                        $group_condition = 'WHERE';
                    } else {
                        $group_condition = $value['group_condition'] ? strtoupper($value['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($value, $key, $set['join_structure']); # $set['join_structure'] 
                    $where .= $this->createWhere($value, $key, $group_condition);
                }
            }
        }

        return compact('fields', 'join', 'where');
    }

/*
|--------------------------------------------------------------------------
|                   CREATE ADD  
|--------------------------------------------------------------------------
|   
|  'except' => ['field']
|   
|  table (field, field2) VALUE ('field', 'field2')
*/

    protected function createAdd ($fields, $files, $except)
    {
        $add = [];
        $add['fields'] = '(';
        $add['value'] = false;

        $array_type = array_keys($fields)[0];

        if(is_int($array_type)){

            $check_fields = false;
            $count_fields = 0;

            foreach($fields as $key => $items){

                $add['value'] .= '(';

                if(!$count_fields)
                    $count_fields = count($fields[$key]);

                $a = 0; 

                foreach($items as $row =>$value){
                    
                    if($except && in_array($row, $except)) continue;

                    if(!$check_fields) $add['fields'] .= $row . ',';

                    if(in_array($value, $this->sql_func))
                        $add['value'] .= $value . ',';

                    elseif($value == 'NULL' || $value === NULL || $value === '')
                        $add['value'] .= "NULL" . ',';

                    else
                        $add['value'] .= "'" . addslashes($value) . "',";
                    
                    $a++;

                    if($a === $count_fields) break; # ОБЕЗОПАСИТЬ ЛИШНЕЕ ВХОЖДЕНИЕ В VALUE
                }

                if($a < $count_fields){
                    for(; $a < $count_fields; $a++){
                        $add['value'] .= "NULL" . ',';
                    }
                }
                
                $add['value'] = rtrim($add['value'], ',') . '),';

                if(!$check_fields){
                    $check_fields = true;
                    $add['fields'] = rtrim($add['fields'], ',') . ')';
                }
            }

            $add['value'] = rtrim($add['value'], ',');
            
        }else{
            
            $add['value'] = '(';
            
            if($fields){

                foreach($fields as $row => $value){

                    if($except && in_array($row, $except))
                        continue;

                    $add['fields'] .= $row . ',';

                    if(in_array($value, $this->sql_func)){
                        $add['value'] .= $value . ',';

                    }elseif($value == 'NULL' || $value === NULL || $value === ''){
                        $add['value'] .= "NULL" . ',';

                    }else{
                         $add['value'] .= "'" . addslashes($value) . "',";
                    }

                }
            }

            if($files){

                foreach($files as $row => $file){
                    
                    $add['fields'] .= $row . ',';

                    if(is_array($file))
                        $add['value'] .= "'" . addslashes(json_encode($file)) . "',";
                    else
                        $add['value'] .= "'" . addslashes($file) . "',";
                }
            }

            foreach($add as $key => $arr){ 
                $add[$key] = rtrim($arr, ',') . ')';
            }
            
        }
        return $add;
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

                elseif($value == 'NULL' || $value === NULL || $value === '')
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

/*
|--------------------------------------------------------------------------
|                   JOIN STRUCTURE  
|--------------------------------------------------------------------------
|   SELECT
|       table.id, table.field2,
|       table_table2.t_id as TABLEtable_table2TABLE_t_id,
|       table_table2.t2_id as TABLEtable_table2TABLE_t2_id, 
|       table2.field as TABLEtableTABLE_field,
|       table2.filed2 as TABLEtableTABLE_field2
*/

    protected function joinStructure($data, $table)
    {
        $join = [];
        
        $id = $this->table_rows[$this->createPseudonymForTable($table)['pseudo']]['primary_key'];

        foreach ($data as $items) {

            if($items) {

                if(!isset($join[$items[$id]])) $join[$items[$id]] = [];

                foreach($items as $key => $value){

                    if(preg_match('/TABLE(.+)?TABLE/u', $key, $matches)){
                        $table_name = $matches[1];

                        if(!isset($this->table_rows[$table_name]['multi_primary_key'])){
                            $join_id = $items[$matches[0] . '_' . $this->table_rows[$table_name]['primary_key']];
 
                        }else{
                            $join_id = '';

                            foreach($this->table_rows[$table_name]['multi_primary_key'] as $multi){
                                $join_id .= $items[$matches[0] . '_' . $multi];

                            }
                        }

                        $row = preg_replace('/TABLE(.+)TABLE_/u', '', $key);

                        if($join_id && !isset($join[$items[$id]]['join'][$table_name][$join_id][$row]))
                            $join[$items[$id]]['join'][$table_name][$join_id][$row] = $value;
                        

                        continue;
                    }

                    $join[$items[$id]][$key] = $value;
                }
            }
        }

        return $join;
    }

/*
|--------------------------------------------------------------------------
|                   CREATE PSEUDONYM FOR TABLE        
|--------------------------------------------------------------------------
|   
*/

    protected function createPseudonymForTable($table)
    {
        $arr = [];

        if(preg_match('/\s+/i', $table)){

            $table = preg_replace('/\s{2,}/i', ' ', $table);

            $table_name = explode(' ', $table);

            $arr['table'] = trim($table_name[0]);
            $arr['pseudo'] = trim($table_name[1]);

        }else{
            $arr['pseudo'] = $arr['table'] = $table;
        }

        return $arr;
    }
    
}