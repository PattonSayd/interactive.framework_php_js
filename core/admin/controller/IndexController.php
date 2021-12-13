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
                'where'           => ['id' => '1, 2, 3', 'game' => 'chess', 'name' => 'phil',  'color'=> ['white', 'black']],
                'operand'         => ['=', '<>', '%LIKE', 'NOT IN'],
                'condition'       => ['AND', 'AND', 'OR'],
                'limit' => '1',

                // 'join'            =>  [
             		
                //  	   [
                //  	      'table'			 => 'like',
                //  	      'fields' 			 => ['id as j_id', 'name as j_name'],
                //  	      'type'  			 => 'left',
                //  	      'where'            => ['name' => 'Yellow'],
                //  	      'operand'     	 => ['='],
                //  	      'condition'	     => ['OR'],
                //  	      'on'			     => ['id', 'parent_id'],
                 	      
                //  	  ],
                    
                // ]
            ]);       
        
            exit($res['id'] . $res['surname']);
        // $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/show';

        // $this->redirect($redirect);
    }
}


?>