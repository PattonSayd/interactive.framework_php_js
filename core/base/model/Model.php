<?php

namespace core\base\model;

use core\base\exception\DBException;
use mysqli;

abstract class Model extends ModelMethods
{		
	protected $db;

/*
|--------------------------------------------------------------------------
|					CONNECT
|--------------------------------------------------------------------------
|		
*/

	protected function connect()
	{
		$this->db = @new mysqli(HOST, USER, PASS, DB_NAME);

		if($this->db->connect_error)
		{
			throw new DbException('Ошибка подключения к базе данных: ' . $this->db->connect_errno . ' ' . $this->db->connect_error);
		}
		$this->db->query("SET NAMES UTF8");
	}

/*
|--------------------------------------------------------------------------
|					QUERU FUNCTION
|--------------------------------------------------------------------------
|
|  c - CREATE(ADD),
|  r - READ(SELECT), 
|  u - UPDATE(EDIT),
|  d - DELETE  
|		
*/

	final public function queryFunc($query, $crud = 'r', $return_id = false)
	{
		$result = $this->db->query($query);

		if($this->db->affected_rows === -1)	
		{
			throw new DBException('Ошибка в SLQ запросе: ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
		}
		
		switch ($crud) {

			case 'r':
				if ($result->num_rows) {
					$res = [];

					for ($i=0; $i < $result->num_rows; $i++) {   	# while ($row = $result->fetch_assoc()) {
						$res[] = $result->fetch_assoc();		 	# $res[] = $row; }
					}
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

/*
|--------------------------------------------------------------------------
|					SELECT
|--------------------------------------------------------------------------
| 
|  string $table      - табоица базы данных
|  array $set         - массив параметров
|  'fields'           => ['id', 'name']
|  'where' 			  => ['id' => '2', 'name' => 'chess']
|  'operand'          => ['=', '<>', 'IN', '%LIKE%', 'NOT IN']
|  'condition'        => ['OR', AND'], 	   default: 'AND'
|  'order'            => ['id', 'name'],
|  'order_direction'  => ['ASC', 'DESC'],  default: 'ASC'
|  'limit'            => '1'
| 
|  "SELECT fields FROM table join where order limit"
*/


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

/*
|--------------------------------------------------------------------------
|					ADD
|--------------------------------------------------------------------------
|   
|  string $table - табоица для добавления данных
|  array $set 	- массив параметров
|  fields 		=> [поле => значение]; если не указан, то обрабатывается $_POST[поле => значение]
|  разрешена передача например NOW() в качестве MySQL функции обычной строкой
|  files 		=> [поле => значение]; можно подать массив вида [поле => [массив значение]]
|  except 		=> ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавления в запрос
|  return_id	=> true|false - возвпащать или нет идентификатор вставленной записи 
|  return mixed 
|   
|  "INSERT INTO table (field, field2) VALUE ('field', 'field2')" 
*/

	final public function add($table, $set = [])
	{
		$set['fields'] = !empty($set['fields']) && (is_array($set['fields'])) ? $set['fields'] : $_POST;

		$set['files'] = !empty($set['files']) && (is_array($set['files'])) ? $set['files'] : false;	

		if (!$set['fields'] && !$set['files']) return false; 

		$set['except'] = !empty($set['except']) && (is_array($set['except'])) ? $set['except'] : false;
		
		$set['return_id'] = $set['return_id'] ? true : false;
		
		$add = $this->createAdd($set['fields'], $set['files'], $set['except']);

		$query = "INSERT INTO $table {$add['fields']} VALUE {$add['value']}";

		return $this->queryFunc($query, $crud = 'c', $set['return_id']);
	}


/*
|--------------------------------------------------------------------------
|					EDIT
|--------------------------------------------------------------------------
|    
|  UPDATE table SET update where
*/

	final public function edit($table, $set = [])
	{
		$set['fields'] = !empty($set['fields']) && (is_array($set['fields'])) ? $set['fields'] : $_POST;

		$set['files'] =  !empty($set['files']) && (is_array($set['files'])) ? $set['files'] : false;	

		$set['except'] = !empty($set['except']) && (is_array($set['except'])) ? $set['except'] : false;

		if (!$set['fields'] && !$set['files']) return false; 

		if(!$set['all_rows']){

			if($set['where']){
				$where = $this->createWhere($set);
			}else {
				$where = false;

				$columns = $this->getColumns($table);

				if(!$columns) return false;
						// id						
				if($columns['id_row'] && $set['fields'][$columns['id_row']]){

					$where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];

					unset($set['fields'][$columns['id_row']]);
				}
			}
		}
			
		$update = $this->createUpdate($set['fields'], $set['files'], $set['except']);

		$query = "UPDATE $table SET $update $where";

		return $this->queryFunc($query, $crud = 'u');
		
	}

/*
|--------------------------------------------------------------------------
|					DELETE
|--------------------------------------------------------------------------
|    
|  DELETE FROM table WHERE field = field
|  
*/

	final public function delete($table, $set = [])
	{
		$table = trim($table);

		$where = $this->createWhere($set, $table);

		$columns = $this->getColumns($table);

		if (!$columns) return false;

		if (is_array($set['fields']) && !empty($set['fields'])) {

			if ($columns['id_row']) {
				$key = array_search($columns['id_row'], $set['fields']);  # Если пришел первичный(id-PRİ) ключ то удаляет его с массива

				if ($key !== false)
					unset($set['fields'][$key]);
			}
			$fields = [];

			foreach ($set['fields'] as $field) {

				$fields[$field] = $columns[$field]['Default'];
			}
			$update = $this->createUpdate($fields, false, false);

			$query = "UPDATE $table SET $update $where";
		}else{
			$join_arr = $this->createJoin($table, $set);

			$join = $join_arr['join'];

			$join_tables = $join_arr['tables'] ? $join_arr['tables'] : '';

			$query = 'DELETE ' . $table . $join_tables . ' FROM ' . $table . ' ' . $join . ' ' . $where;
		}
		return $this->queryFunc($query, $crud = 'u');
	}

/*
|--------------------------------------------------------------------------
|					GET COLUMNS
|--------------------------------------------------------------------------
|   
|   SHOW COLUMNS FROM table
*/
	
	final public function getColumns($table)
	{
			$query = "SHOW COLUMNS FROM $table";

			$res = $this->queryFunc($query);

			$columns = [];

			if($res){

				foreach($res as $row) {
					$columns[$row['Field']] =  $row; // ячейка с бызы данных

					if ($row['Key'] === 'PRI')
						$columns['id_row'] = $row['Field'];
					}
				}
				
		return $columns;
	}
	
}
