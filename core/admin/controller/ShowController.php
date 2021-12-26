<?php 

namespace core\admin\controller;

use core\base\settings\Settings;

class ShowController extends AdminController
{
     protected function inputData()
    {
        $this->parentInputData(); # parent::inputData() не вызываем из-за плагина 

        $this->createTableData();

        $this->createData();  

        return $this->extension(get_defined_vars());

    }

# -------------------- CREATE DATA -----------------------------------------------
    
protected function createData($arr = [])
{
    $fields = [];
    $order = [];    
    $order_direction = [];

    if (!$this->columns['id_row'])
        return $this->data = [];
 
    $fields[] = $this->columns['id_row'] . ' as id';

    if ($this->columns['name'])
        $fields['name'] = 'name';

    if ($this->columns['image'])
        $fields['image'] = 'image';

    if (count($fields) < 3)
        foreach ($this->columns as $key => $value) {
            if (!$fields['name'] && strpos($key, 'name') !== false) {
                $fields['name'] = $key . ' as name';
            }
            if (!$fields['image'] && strpos($key, 'image') === 0) {
                $fields['image'] = $key . ' as image';
            }
        }

    /** fields ************************/

    if (isset($arr['fields'])) {
        if (is_array($arr['fields'])) {
            $fields = Settings::instance()->arrayMergeRecursive($fields, $arr['fields']);
        } else {
            $fields[] = $arr['fields'];
        }
    }
    
    /** parent_id *********************/

    if ($this->columns['parent_id']) {
        if (!in_array('parent_id', $fields))
            $fields[] = 'parent_id';
        $order[] = 'parent_id';
    }

    /** menu position *****************/ 

    if ($this->columns['menu_position']) {
        $order[] = 'menu_position';
    } elseif ($this->columns['date']) {
        if ($order)
            $order_direction = ['ASC', 'DESC'];
        else
            $order_direction[] = 'DESK';

        $order[] = 'date';
    }

    /** order *************************/

    if (isset($arr['order'])) {
        if (is_array($arr['order'])) {
            $order = Settings::instance()->arrayMergeRecursive($order, $arr['order']);
        } else {
            $order[] = $arr['order'];
        }
    }

    /** order direction ***************/

    if (!empty($arr['order_direction'])) {
        if (is_array($arr['order_direction'])) {
            $order_direction = Settings::instance()->arrayMergeRecursive($order_direction, $arr['order_direction']);
        } else {
            $order_direction[] = $arr['order_direction'];
        }
    }

    $this->data = $this->model->select($this->table, [
        'fields' => $fields,
        'order' => $order,
        'order_direction' => $order_direction
    ]);
}
}


?>