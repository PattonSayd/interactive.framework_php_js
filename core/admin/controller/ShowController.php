<?php 

namespace core\admin\controller;

class ShowController extends AdminController
{
     protected function inputData()
    {
        $this->parentInputData(); # parent::inputData() не вызываем из-за плагина 

        $this->createTableData();

        $this->createData(['fields' => ['surname', 'gallery_image']]);

        exit();
    }
}


?>