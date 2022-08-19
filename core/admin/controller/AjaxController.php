<?php

namespace core\admin\controller;

use core\base\model\AuthModel;
use libraries\FileEdit;

class AjaxController extends AdminController
{
    public function ajax()
    {
         if(isset($this->ajax_data['ajax'])){

            $this->parent_inputData();

            foreach($this->ajax_data as $key => $v) $this->ajax_data[$key] = $this->clearStr($v);

            switch ($this->ajax_data['ajax']) {

                case 'sitemap':
                    
                    return (new CreatesitemapController())->inputData($this->ajax_data['links_counter'], false);
                    break;

                case 'editData':
    
                    $_POST['return_id'] = true;

                    $this->checkPost();

                    return json_encode(['success' => 1]);
                    break;
                
                case 'change_parent':

                    return $this->changeParent();
                    break;
                
                case 'search':

                    return $this->search();
                    break;

                case 'change_password':

                    return $this->changePassword();
                    break;
                
                case 'bloks':

                    $this->model->sortingColumns($this->clearStr($this->ajax_data['table']), $this->ajax_data['sortable']);
                    // return $this->redirect();
                    break;

                case 'wysiwyg':

                    $fileEdit = new FileEdit();

                    $fileEdit->setUniqueFile(false);

                    $file = $fileEdit->addFile($this->clearStr($this->ajax_data['table']) . '/content_file/');
                    
                    return ['location' => PATH . UPLOAD_DIR . $file[key($file)]];
                    break;
            }
        }

        return json_encode(['success' => '0', 'message' => 'NO AJAX DATA']);
    }

    protected function search()
    {
        $data = $this->clearStr($this->ajax_data['data']);
        $table = $this->clearStr($this->ajax_data['table']);

        return $this->model->search($data, $table, 20);
    }

    protected function changeParent()
    {
        return $this->model->select($this->ajax_data['table'],[
            'fields' => ['COUNT(*) as count'],
            'where' => ['parent_id' => $this->ajax_data['parent_id']],
            'no_concat' => true
        ])[0]['count'] + $this->ajax_data['iterations'];
    } 
    
    protected function changePassword()
    {
        $model = AuthModel::instance();

        $user_data = $this->model->select($model->getAdminTable(), [
            'fields' => ['id', 'name'],
            'where' => ['id' => $this->userId[0]['id'], 'password' => md5($_POST['current'])]
        ]);

        if(!$user_data) return json_encode(['error' => 'Incorrect password']);

        $this->model->edit($model->getAdminTable(),[
            'fields' => ['password' => md5($_POST['confirm'])],
            'where' => ['id' => $this->userId[0]['id'], 'password' => md5($_POST['current'])]
        ]);

        return json_encode(['success' => 1]);        
    }
    
}
