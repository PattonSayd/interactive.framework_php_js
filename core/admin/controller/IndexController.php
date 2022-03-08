<?php 

namespace core\admin\controller;

use core\base\settings\Settings;

class IndexController extends AdminController
{
    public function inputData()
    {
        
        $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        $this->redirect($redirect);
    }
}


?>