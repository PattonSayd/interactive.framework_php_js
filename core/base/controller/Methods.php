<?php 

namespace core\base\controller;

trait Methods{

/*
|--------------------------------------------------------------------------
| Очищения строки
|--------------------------------------------------------------------------
*/
    protected function clearStr($str)
    {
        if(is_array($str)){
            foreach($str as $key => $value){
                $str[$key] = trim(strip_tags($value));
                return $str;
            }
        }else {
            return trim(strip_tags($str));
        }
    }

/*
|--------------------------------------------------------------------------
| Очищения чисел
|--------------------------------------------------------------------------
*/
    protected function clearNum($num)
    {
        return (!empty($num) && preg_match('/\d/', $num)) ? preg_replace('/[^\d.]/', '', $num) * 1 : 0; 
    }

/*
|--------------------------------------------------------------------------
| Проверка запроса Post
|--------------------------------------------------------------------------
*/
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

/*
|--------------------------------------------------------------------------
| Проверка запроса Ajax
|--------------------------------------------------------------------------
*/
    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

/*
|--------------------------------------------------------------------------
| Перенаправление
|--------------------------------------------------------------------------
*/
    protected function redirect($http = false, $code = false)
    {
        if($code){
            $codes = ['301' => 'HTTP/1.1 301 Moved Permanently'];

            if($codes[$code]){
                header($codes[$code]);
            }
        }

        if($http){
            $redirect = $http;
        }else{
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;
        }

        header("Location: $redirect");
        exit();
    }

/*
|--------------------------------------------------------------------------
| Получение стилей
|--------------------------------------------------------------------------
*/
    protected function getStyles()
    {
        if($this->styles){

            foreach ($this->styles as $style) {

                echo '<link rel="stylesheet" href="' . $style . '">';
                
            }
        }
    }

/*
|--------------------------------------------------------------------------
| Получение скриптов
|--------------------------------------------------------------------------
*/
    protected function getScripts()
    {
        if($this->scripts){

            foreach ($this->scripts as $script) {

                echo '<script src="' . $script . '"></script>';
                
            }
        }
    }
    
/*
|--------------------------------------------------------------------------
| Запись в лог-файл
|--------------------------------------------------------------------------
*/
    protected function writeLog($message, $file = 'log.txt', $event = 'Fault')
    {
        $dataTime = new \DateTime();
         
        $str = $event . ': ' . $dataTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";

        file_put_contents('log/' . $file, $str, FILE_APPEND);
    }
}
