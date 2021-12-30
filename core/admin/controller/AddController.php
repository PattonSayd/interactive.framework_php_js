<?php 

namespace core\admin\controller;

class ShowController extends AdminController
{
    protected function inputData()
    {
        if(!$this->userId)
            $this->parentInputData(); # parent::inputData() не вызываем из-за плагина 

        $this->createTableData();

        $this->createOutputData();


    }
}