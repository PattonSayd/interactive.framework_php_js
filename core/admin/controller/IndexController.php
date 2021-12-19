<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\Model;

class IndexController extends Controller
{
    public function inputData()
    {
        $db = Model::instance();
        $res = $db->delete(
            'users',
            [
                'fields'=> ['id', 'name'],
                'where'=> ['id' => 2],
                
            ]
        );              
        
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>