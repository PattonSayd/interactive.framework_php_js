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

        $this->createBlock(); 

        $this->manyAdd();

    }



    protected function manyAdd()
    {
        $fields = [
            'name' => 'rood', 
            'menu_position' => 5,
            'visible'=> 3

        ];

        $this->model->insert('articles', [
            'fields' => [
                'name' => 'rood', 
                'menu_position' => 5,
                'visible'=> 3
            ],  
            
            'files' => [
                'image' => 'TT.png',
                'gallery_image' => ['R1.png', 'R2.png', 'R3.png'],
            ],  
        ]);
    }

}