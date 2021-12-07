<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DBException;
use mysqli;

class Model
{	
	use Singleton;
	
	protected $db;

#   ------------------ CONNECT ------------------------------------------------

	protected function __construct()
	{
		$this->db = @new mysqli(HOST, USER, PASS, DB_NAME);

		if($this->db->connect_error)
		{
			throw new DBException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . ' ' . $this->db->connect_error);
		}
		$this->db->query("SET NAMES UTF8");
	}

#   ------------------ QUERY FUNC ----------------------------------------------

	final public function queryFunc($query, $crud = 'r', $return_id = false)
	{
		$result = $this->db->query($query);

		if($this->db->affected_rows === -1)	
		{
			throw new DBException('Ошибка в SLQ запросе: ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
		}

		/**  @param c /CREATE(INSERT), @param r /SELECT(READ), @param u /UPDATE, @param d /DELETE */
		
		switch ($crud) {

			case 'r':
				if ($result->num_rows) {
					$res = [];

					for ($i=0; $i < $result->num_rows; $i++) {
						$res[] = $result->fetch_assoc();
					}
					// while ($row = $result->fetch_assoc()) {
					// 	$res[] = $row;
					// }
					return $res;
				}
				return false;			
				break;

			case 'c':
				if($return_id){
					return $this->db->insert_id;
				}
				return true;
				break;

			default:
				return true;
				break;
		}
	}

#   -------------------- SELECT --------------------------------------------------------

	final public function select($table, $set = [])
	{
		$fields = $this->createFields($set, $table);
		$order = $this->createOrder($set, $table);

		 
		$where = $this->createWhere($set, $table);

		if (!$where) $new_where = true;
			else $new_where = false;
		
		$join_arr = $this->createJoin($set, $table, $new_where);

		$fields .= $join_arr['fields'];
		$where .= $join_arr['where'];
		$join = $join_arr['join'];

		$fields = rtrim($fields, ', ');


		$limit = !empty($set['limit']) ? 'LIMIT ' .  $set['limit'] : '';

		$query = "SELECT $fields FROM $table $join $where $order $limit";

 		return $this->queryFunc($query);
	}

#   -------------------- CREATE FIELDS ---------------------------------------------------

    protected function createFields($set, $table = false)
    {
		$set['fields'] = is_array($set['fields']) &&  !empty($set['fields'])  ? $set['fields'] : ['*'];

		$table = $table ? $table . '.' : '';

		$fields = ''; 

		foreach($set['fields'] as $field){

			$fields .= $table . $field . ', '; # users.name,
		}

		return $fields;
    }

#   -------------------- CREATE ORDER ---------------------------------------------------

    protected function createOrder($set, $table = false)
    {
		$table = $table ? $table . '.' : '';

		$order_by = ''; 

		if (is_array($set['order']) &&  !empty($set['order'])){

			$set['order_direction'] = is_array($set['order_direction']) &&  !empty($set['order_direction'])  ? $set['order_direction'] : ['ASC'];

			$order_by = 'ORDER BY '; 

			$direct_count = 0;
			
			foreach($set['order'] as $order){

				if ($set['order_direction'][$direct_count]) {
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

		# ORDER BY table.id ASC, table.name DESC;
		
		return $order_by;
    }

#   -------------------- CREATE WHERE ---------------------------------------------------

    protected function createWhere($set, $table = false, $instruction = 'WHERE')
    {
		$table = $table ? $table . '.' : '';

		$where = ''; 

		if (is_array($set['where']) &&  !empty($set['where'])){

			$set['operand'] = is_array($set['operand']) &&  !empty($set['operand'])  ? $set['operand'] : ['='];
			$set['condition'] = is_array($set['condition']) &&  !empty($set['condition'])  ? $set['condition'] : ['AND'];

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

		# =, <>, IN (SELECT * FROM table), NOT, LIKE;
    }

#   ------------------ CREATE JOIN -----------------------------------------------------

    protected function createJoin($set, $table = false, $new_where = false)
    {
        $fields = '';
        $join = '';
        $where = '';

        if ($set['join']) {

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
}

	/** $table   - Таблица базы данных
	 *  $set  - array
	 *  'fields'          => ['id', 'name'],
	 *  'no_concat'       => false/true Если True не присоединять имя таблицы к полям и where
	 *  'where'           => ['id' => '1, 2, 3, 4', 'fio' => 'DeviJones', 'name' => 'Patton', 'surname' => 'Sayd', color=>['red', 'green', 'blue'],
	 *  'operand'         => ['=', '<>', 'IN', '%LIKE%', ''NOT IN],
	 *  'condition'       => ['OR', AND'],
	 *  'order'           => ['fio', 'name'],
	 *  'order_direction' => ['ASC', 'DESC'],
	 *  'limit'           => '1'
	 * 
	 * 
	 *  'join'            =>  [
	 * 		[
		* 	   'join_table2' =>  [
		* 	      'table'			 => 'join_table1',
		* 	      'fields' 			 => ['id as j_id', 'name as j_name'],
		* 	      'type'  			 => 'left',
		* 	      'where'            => ['name' => 'Yellow'],
		* 	      'operand'     	 => ['='],
		* 	      'condition'	     => ['OR'],
		* 	      'on'			     => ['id', 'parent_id'],
		* 	      'group_condition'  => 'AND'
		* 	  ],
		*
		* 	   'join_table2' => [
		* 	        'fields'         => ['id as j2_id', 'name as j2_name'],
		* 	        'type'           => 'left',
		* 	        'where'          => ['name' => 'Yellow'],
		* 	        'operand'        => ['<>'],
		* 	        'condition'      => ['and'],
		* 	        'on' => [
		* 	               'table'  =>'test',
		* 	               'fields' => ['id', 'parent_id']
		* 	                			]
		* 		],	
	 *		]
	 */

