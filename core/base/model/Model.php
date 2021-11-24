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
		$fields = $this->createFields($table, $set);
		$order = $this->createOrder($table, $set);

		 
		$where = $this->createWhere($table, $set);
		
		$join_arr = $this->createJoin($table, $set);

		$fields .= $join_arr['fields'];
		$where .= $join_arr['where'];
		$join = $join_arr['join'];

		$fields = rtrim($fields, ', ');


		$limit = !empty($set['limit']) ? 'LIMIT ' .  $set['limit'] : '';

		$query = "SELECT $fields FROM $table $join $where $order $limit";

		return $this->queryFunc($query);
	}

#   -------------------- CREATE FIELDS ---------------------------------------------------

    protected function createFields($table = false, $set)
    {
		$set['fields'] = is_array($set['fields']) &&  !empty($set['fields'])  ? $set['fields'] : ['*'];

		$table = $table ? $table . '.' : '';

		$fields = ''; 

		foreach($set['fields'] as $field){

			$fields .= $table . $field . ','; # users.name,
		}

		return $fields;
    }

#   -------------------- CREATE ORDER ---------------------------------------------------

    protected function createOrder($table = false, $set)
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

				}else{
					$order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
				}
				
				$order_by .= $table . $order . ' ' . $order_direction . ',';
			}

			$order_by = rtrim($order_by, ',');
		}

		# ORDER BY table.id ASC, table.name DESC;
		
		return $order_by;
    }
}

	/** $table   - Таблица базы данных
	 *  $set  - array
	 *  'fields'          => ['id', 'name'],
	 *  'no_concat'       => false/true Если True не присоединять имя таблицы к полям и where
	 *  'where'           => ['fio' => 'DeviJones', 'name' => 'Patton', 'surname' => 'Sayd'],
	 *  'operand'         => ['=', '<>'],
	 *  'condition'       => ['AND'],
	 *  'order'           => ['fio', 'name'],
	 *  'order_direction' => ['ASC', 'DESC'],
	 *  'limit'           => '1'
	 * 
	 * 
	 *  'join'            =>  [
	 * 	      [
	 * 	      'table'			 => 'join_table1',
	 * 	      'fields' 			 => ['id as j_id', 'name as j_name'],
	 * 	      'type'  			 => 'left',
	 * 	      'where'            => ['name' => 'Yellow'],
	 * 	      'operand'     	 => ['='],
	 * 	      'condition'	     => ['OR'],
	 * 	      'on'			     => ['id', 'parent_id'],
	 * 	      'group_condition'  => 'AND'
	 * 	       									],
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
	 * 											],	
	 *			]
	 */

