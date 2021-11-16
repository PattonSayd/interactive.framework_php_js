<?php

namespace core\user\controller;

use core\base\controller\Controller;

class IndexController extends Controller{

   public function inputData(){

      $lot = "hello";
      
       $todo = $this->render('', compact('lot'));

       
   } public function outputData($data){
        return $data;
   }
}