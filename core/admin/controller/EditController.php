<?php 

namespace core\admin\controller;

use core\base\exception\RouteException;

class EditController extends AdminController
{
    protected $action = 'edit';

# -------------------- INPUT DATA ------------------------------------------------

    protected function inputData()
    {
        $this->actionPage = 'add';

        if(!$this->userId) $this->parent_inputData(); # parent::inputData() не вызываем из-за плагина 

        $this->checkPost();

        $this->createTableData();

        $this->createData();

        $this->createForeignData();

        $this->createMenuPosition(); 
        
        $this->createRadio();

        $this->createBlock(); 

        $this->createManyToMany();

        $this->template = ADMIN_TEMPLATE . 'add';

        return $this->extension();       
    }

    protected function createData() # получение данных из БД
    {
        $id = is_numeric($this->parameters[$this->table]) ?
            $this->clearNum($this->parameters[$this->table]) : 
                $this->clearStr($this->parameters[$this->table]);  // ЕСЛИ id В mysql СОДЕРЖЕТСЯ СТРОКА
        
        if(!$id) throw new RouteException('Некорректный идентификатор - ' . $id . ' при редоктировании таблицы - ' . $this->table);

        $this->data = $this->model->select($this->table, [
            'where' => [$this->columns['primary_key'] => $id]
        ]);
        
        $this->data && $this->data = $this->data[0];
    }

}