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
            'fields' => ['name', 'surname', 'old'],
            'order' => ['name', 'old', 'surname'],
            'order_direction' => ['desk', 'ask']
        ]);
        exit;
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>