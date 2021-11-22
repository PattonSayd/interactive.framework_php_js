<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\ModelMethods;

class IndexController extends Controller
{
    public function inputData()
    {
        $db = ModelMethods::instance();
        $res = $db->queryFunc("SELECT * FROM users1");
        exit;
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>