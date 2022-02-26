<?php

namespace core\admin\controller;

class AjaxController extends AdminController
{

    public function ajax()
    {
        if(isset($this->data['ajax'])){

            $this->parent_inputData();

            switch ($this->data['ajax']) {

                case 'sitemap':
                    
                    return (new CreatesitemapController())->inputData($this->data['links_counter'], false);

                    break;

                case 'editData':
    
                    $_POST['return_id'] = true;

                    $this->checkPost();

                    return json_encode(['success' => 1]);
                    
                    break;
            }
        }

        return json_encode(['success' => '0', 'message' => 'NO AJAX DATA']);
    }
}
