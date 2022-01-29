<?php

namespace libraries;


class FileEdit
{
    protected $imgArray = [];
    protected $directory;

    
    public function addFile($directory = false)
    {
        if(!$directory)
            $this->directory = $_SERVER['DOCUMENT_ROOT'] . PATH . UPLOAD_DIR;
        else
            $this->directory = $directory;

        foreach($_FILES as $key => $file){

            if(is_array($file['name'])){
                 
                $file_arr = [];
                
                foreach($file['name'] as $i => $value) { 

                    if(!empty($file['name'][$i])){

                        $file_arr['name'] = $file['name'][$i];
                        $file_arr['type'] = $file['type'][$i];
                        $file_arr['tmp_name'] = $file['tmp_name'][$i];
                        $file_arr['error'] = $file['error'][$i];
                        $file_arr['size'] = $file['size'][$i];

                        $res_name = $this->createFile($file_arr);

                        if($res_name)
                            $this->imgArray[$key][] = $res_name;
                        
                    }
                    
                }
                
            }else{
                if($file['name']){

                    $res_name = $this->createFile($file);

                    if ($res_name)
                        $this->imgArray[$key] = $res_name;
                }
            } 
        }

        return $this->getFiles();
    }


    public function getFiles()
    {
        return $this->imgArray;
    }


    protected function createFile($file)
    {
        $fileNameArr = explode('.', $file['name']); // 0: название  1: росширение
        $ext = $fileNameArr[count($fileNameArr) - 1];  // росширение

        unset($fileNameArr[count($fileNameArr) - 1]);

        $fileName = implode('.', $fileNameArr); // соединяем, если названиe файла с точкой

        $fileName = (new TextModify())->translit($fileName);

        $fileName = $this->checkFile($fileName, $ext);

        $fileFullName = $this->directory . $fileName;

        if($this->uploadFile($file['tmp_name'], $fileFullName))
            return $fileName;

        return false;
    }   

    protected function uploadFile($tmpName, $fileFullName)
    {
        if(move_uploaded_file($tmpName, $fileFullName))
            return true;

        return false;
    }

    protected function checkFile($fileName, $ext, $fileLastName = '')
    {
        if(!file_exists($this->directory . $fileName . $fileLastName . '.' . $ext)){

            return $fileName . $fileLastName . '.' . $ext;
        }

        return $this->checkFile($fileName, $ext, '_' . hash('crc32', time() . mt_rand(1, 1000))) ;

    }
}