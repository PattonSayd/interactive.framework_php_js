<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\Model;

class IndexController extends Controller
{
    public function inputData()
    {
        $db = Model::instance();
        $res = $db->insert(
            'users',
            [
                'fields'=> ['name' => 'patton', 'surname' => 'Sayd'],
                'except' => ['name'],
                'files' => [
                    'images' => 'main.jpg',
                    'gallery_images' => ["red''.jpg", 'green.jpg', 'black.jpg' ]
                ]
            ]
        );       
        
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>