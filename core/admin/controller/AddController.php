<?php 

namespace core\admin\controller;

class AddController extends AdminController
{
    protected function inputData()
    {
        if(!$this->userId) $this->parentInputData(); # parent::inputData() не вызываем из-за плагина 

        $this->createTableData();

        $this->createForeignData();

        $this->createMenuPosition(); 
        
        $this->createRadio();

        $this->createBlock(); #createOutputData

    }
}