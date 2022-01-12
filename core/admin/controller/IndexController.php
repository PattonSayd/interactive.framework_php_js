<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\Model;
use core\base\settings\Settings;

class IndexController extends Controller
{
    public function inputData()
    {
        
        $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        $this->redirect($redirect);
    }
}


?>