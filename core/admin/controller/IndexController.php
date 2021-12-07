<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\Model;

class IndexController extends Controller
{
    public function inputData()
    {
        $db = Model::instance();
        $res = $db->select(
            'users',
            [
                'fields'          => ['id', 'surname'],
                'where'           => ['surname' => 'fox'],
                'limit' => '1',
            ])[0];       
        
            exit($res['id'] . $res['surname']);
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>