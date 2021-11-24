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


}
