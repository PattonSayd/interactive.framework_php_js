<?php

namespace libraries;


class FileEdit
{
    protected $images = [];
    protected $directory;
    protected $unique_file = true;

    
    public function addFile($directory = '')
    {
        $directory = trim($directory, ' /');

        $directory .= '/';
        
        $directory && !preg_match('/\/$/', $directory) && $directory .= '/';
        
        $this->setDirectory($directory);

        foreach($_FILES as $key => $file){

            if(is_array($file['name'])){
                 
                $file_array = [];
                
                foreach($file['name'] as $i => $value) { 

                    if(!empty($file['name'][$i])){

                        $file_array['name'] = $file['name'][$i];
                        $file_array['type'] = $file['type'][$i];
                        $file_array['tmp_name'] = $file['tmp_name'][$i];
                        $file_array['error'] = $file['error'][$i];
                        $file_array['size'] = $file['size'][$i];

                        $res_name = $this->createFile($file_array);

                        if($res_name) $this->images[$key][$i] = $directory . $res_name;
                        
                    }
                    
                }
                
            }else{
                if($file['name']){

                    $res_name = $this->createFile($file);

                    if ($res_name) $this->images[$key] = $directory . $res_name;
                }
            } 
        }

        return $this->getFiles();
    }


    public function getFiles()
    {
        return $this->images;
    }


    protected function createFile($file)
    {
        $filename_array = explode('.', $file['name']); // 0: название  1: росширение
        $ext = $filename_array[count($filename_array) - 1];  // росширение

        unset($filename_array[count($filename_array) - 1]);

        $filename = implode('.', $filename_array); // соединяем, если названиe файла с точкой

        $filename = (new TextModify())->translit($filename);

        $filename = $this->checkFile($filename, $ext);

        $filename_full = $this->directory . $filename;

        if($this->uploadFile($file['tmp_name'], $filename_full))
            return $filename;

        return false;
    }   

    protected function uploadFile($temp_name, $filename_full)
    {
        if(move_uploaded_file($temp_name, $filename_full))
            return true;

        return false;
    }

    protected function checkFile($filename, $ext, $filename_last = '')
    {
        if(!file_exists($this->directory . $filename . $filename_last . '.' . $ext) || !$this->unique_file){

            return $filename . $filename_last . '.' . $ext;
        }

        return $this->checkFile($filename, $ext, '_' . hash('crc32', time() . mt_rand(1, 1000))) ;
    }

    public function setUniqueFile($value)
    {
        $this->unique_file = $value ? true : false;
    }
    
    public function setDirectory($directory)
    {
        $this->directory = $_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR . $directory;

        if(!file_exists($this->directory)) mkdir($this->directory, 0777, true);
    }


}