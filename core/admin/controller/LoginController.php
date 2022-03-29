<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\UserModel;

class LoginController extends Controller
{
    protected $model;
    
    protected function inputData()
    {

        $this->model = UserModel::instance();
        
    }
    
}