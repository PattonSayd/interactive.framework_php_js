<?php 

namespace core\admin\controller;

use core\base\settings\Settings;

class SitemapController extends AdminController
{
    protected $xml;
    
    protected function inputData()
    {
        if(!$this->userId) $this->parent_inputData();

        if(!file_exists('sitemap.xml')) return false;

        $this->xml = simplexml_load_file('sitemap.xml');

        
    }
}

?>