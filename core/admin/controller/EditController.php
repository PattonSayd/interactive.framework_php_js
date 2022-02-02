<?php 

namespace core\admin\controller;

class EditController extends AdminController
{
    protected $action = 'edit';

# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {
        if(!$this->userId) $this->parent_inputData();
              
    }

}