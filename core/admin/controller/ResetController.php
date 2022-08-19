<?php 

namespace core\admin\controller;

class ResetController extends AdminController
{
    
    
# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {
        if(!$this->userId) $this->parent_inputData(); # parent::inputData() 

    }
}