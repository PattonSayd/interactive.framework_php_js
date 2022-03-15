<?php  


namespace core\admin\controller;

use core\base\settings\Settings;

class DeleteController extends AdminController
{
    protected function inputData()
    {
        if(!$this->userId) $this->parent_inputData(); # parent::inputData() не вызываем из-за плагина 
        
        $this->createTableData();

        if(!empty($this->parameters[$this->table])){

            $id = is_numeric($this->parameters[$this->table]) ?
                $this->clearNum($this->parameters[$this->table]) : $this->clearStr($this->parameters[$this->table]);

            if($id) {

                $this->data = $this->model->select($this->table, [
                    'where' => [$this->columns['primary_key'] => $id],
                ]);

                if($this->data) {

                    $this->data = $this->data[0];

                    if(count($this->parameters) > 1){
                        $this->checkDeleteFile();
                    }

                    $settings = $this->settings ? $this->settings : Settings::instance();

                    $files = $settings::get('fileTemplates');

                    if($files) {

                        foreach ($files as $file) {
                            
                            foreach ($settings::get('templates')[$file] as $item) {

                                if (!empty($this->data[$item])) {

                                    $fileData = json_decode($this->data[$item], true) ?: $this->data[$item];
 
                                    if (is_array($fileData)) {

                                        foreach ($fileData as $f) {
                                            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $f);
                                        }
                                    } else {
                                        @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $fileData);
                                    }
                                }
                            }
                        }
                    }

                    if(!empty($this->data['menu_position'])){

                        $where = [];

                        if(!empty($this->date['parent_id'])){

                            $pos = $this->model->select($this->table, [
                                'fields' => ['COUNT(*) as count'],
                                'where' => ['parent_id' => $this->data['parent_id']],
                                'no_concat' => true
                            ])[0]['count'];

                            $where = ['where' => 'parent_id'];

                        }else{

                            $pos = $this->model->select($this->table, [
                                'fields' => ['COUNT(*) as count'],
                                'no_concat' => true
                            ])[0]['count'];
                        }

                        $this->model->updateMenuPosition($this->table, 'menu_position', [$this->columns['primary_key'] => $id], $pos, $where);
                    }

                    if($this->model->delete($this->table, ['where' => [$this->columns['primary_key'] => $id]])){

                        $tables = $this->model->getTables();

                        if(in_array('old_alias', $tables)){

                            $this->model->delete('old_alias', [
                                'where' => ['table_name' => $this->table, 'table_id' => $id]
                            ]);
                        }
                    }

                    $manyToMany = $settings::get('manyToMany');

                    if($manyToMany){

                        foreach($manyToMany as $pivot_table => $tables){

                            $main_table_key = array_search($this->table, $tables);

                            if($main_table_key !== false){

                                $this->model->delete($pivot_table, [
                                    'where' => [$tables[$main_table_key] . '_' . $this->columns['primary_key'] => $id]
                                ]);
                            }
                        }
                    }

                    $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-success">
                                                    <span><i class="gn-icon gn-success-color icon-checkmark-circle"></i></span>
                                                    <span class="gn-msg gn-success-color"><b>Well done! </b> '. $this->messages['deleteSuccess'] .'</span> 
                                                    <span class="gn-btn-close">
                                                    <span class="gn-close gn-success-color-hover"><i class="gn-close-icon gn-success-color icon-cross"></i></span>
                                                    </span>
                                                </div>';

                    $this->redirect($this->adminPath . 'show/' . $this->table);
                }
            }
        }

        $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-error">
                                            <span><i class="gn-icon gn-error-color icon-blocked"></i></span>
                                            <span class="gn-msg gn-error-color"><b>Oh snap! </b> '. $this->messages['deleteFail'] .'</span> 
                                            <span class="gn-btn-close">
                                            <span class="gn-close gn-error-color-hover"><i class="gn-close-icon gn-error-color icon-cross"></i></span>
                                            </span>
                                        </div>'; 

        $this->redirect();
    }

    protected function checkDeleteFile()
    {
        unset($this->parameters[$this->table]);

        $updateFlag = false;

        foreach($this->parameters as $row => $item){

            $item = base64_decode($item);

            if(!empty($this->data[$row])){

                $data = json_decode($this->data[$row], true);

                if($data){

                    foreach($data as $key => $value){

                        if($item === $value){

                            $updateFlag = true;

                            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $value); 
                            
                            unset($data[$key]);

                            $this->data[$row] = $data ? json_encode($data) : 'NULL';

                            break;
                        }
                    }

                }elseif($this->data[$row] === $item){ # обезопасить от get вирусов

                    $updateFlag = true;

                    @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $item);
                    
                    $this->data[$row] = 'NULL';
                }
            }
        }

        if($updateFlag){
            $this->model->edit($this->table, [
                'fields' => $this->data
            ]);

            $_SESSION['res']['answer'] =  '<div class="gn-item gn-before gn-primary">
                                                <span><i class="gn-icon gn-primary-color icon-rotate-cw3"></i></span>
                                                <span class="gn-msg gn-primary-color"><b>Well done! </b> '. $this->messages['deleteSuccess'] .'</span> 
                                                <span class="gn-btn-close">
                                                <span class="gn-close gn-primary-color-hover"><i class="gn-close-icon gn-primary-color icon-cross"></i></span>
                                                </span>
                                            </div>';

        }else{
            $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-error">
                                                <span><i class="gn-icon gn-error-color icon-blocked"></i></span>
                                                <span class="gn-msg gn-error-color"><b>Oh snap! </b> '. $this->messages['editFail'] .'</span> 
                                                <span class="gn-btn-close">
                                                <span class="gn-close gn-error-color-hover"><i class="gn-close-icon gn-error-color icon-cross"></i></span>
                                                </span>
                                            </div>';;
        }
        
        $this->redirect();
    }
}

