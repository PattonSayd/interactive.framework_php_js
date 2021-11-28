<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\Model;

class IndexController extends Controller
{
    public function inputData()
    {
        $db = Model::instance();
        $res = $db->select('users',[
            'fields'          => ['name', 'surname'],
            'where'           => ['id' => '1, 2, 3, 4', 'fio' => 'DeviJones', 'name' => 'Patton', 'surname' => 'Sayd', 'color' => ['red', 'grey', 'yellow']],
	        'operand'         => ['IN', 'LIKE', '<>', '=', 'NOT IN'],
	        'condition'       => ['AND'],
            'order'           => ['name', 'old', 'surname'],
            'order_direction' => ['desk', 'ask']
        ]);
        exit;
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>