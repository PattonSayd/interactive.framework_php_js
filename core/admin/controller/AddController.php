<?php 

namespace core\admin\controller;

class AddController extends AdminController
{
    protected $action = 'add';
    
# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {
        if(!$this->userId) $this->parentInputData(); # parent::inputData() не вызываем из-за плагина 

        $this->checkPost();

        $this->createTableData();

        $this->createForeignData();

        $this->createMenuPosition(); 
        
        $this->createRadio();

        $this->createBlock(); 

        // $this->data = [
        //     'name' => 'Fair',
        //     'keywords' => 'Worry',
        //     'content' => 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old.',
        //     'image' => 'akat.jpg',
        //     'gallery_image' => json_encode(['ety.jpg', 'pus.jpg']),
        // ];
        

        // $this->manyAdd();

    }



    // protected function manyAdd()
    // {
    //     $fields = [
    //         'name' => 'rood', 
    //         'menu_position' => 5,
    //         'visible'=> 3

    //     ];

    //     $this->model->add('articles', [
    //         'fields' => [
    //             'name' => 'rood', 
    //             'menu_position' => 5,
    //             'visible'=> 3
    //         ],  
            
    //         'files' => [
    //             'image' => 'TT.png',
    //             'gallery_image' => ['R1.png', 'R2.png', 'R3.png'],
    //         ],  
    //     ]);
    // }

}