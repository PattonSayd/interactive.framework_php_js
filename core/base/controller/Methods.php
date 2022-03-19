<?php 

namespace core\base\controller;

trait Methods{

# -------------------- CLEAR STR ------------------------------------------

    protected function clearStr($str)
    {
        if(is_array($str)){
            foreach($str as $key => $value){
                $str[$key] = $this->clearStr($value);
                return $str;
            }
        }else {
            return trim(strip_tags($str));
        }
    }

# -------------------- CLEAR NUM ------------------------------------------

    protected function clearNum($num)
    {
        return (!empty($num) && preg_match('/\d/', $num)) ? preg_replace('/[^\d.]/', '', $num) * 1 : 0; 
    }

# -------------------- IS POST ------------------------------------------------

    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

# -------------------- IS AJAX ------------------------------------------------

    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

# -------------------- REDIRECT -----------------------------------------------

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

# -------------------- GET STYLES ---------------------------------------------

    protected function getStyles()
    {
        if($this->styles){

            foreach ($this->styles as $style) {

                echo '<link rel="stylesheet" href="' . $style . '">';
                
            }
        }
    }

# -------------------- GET SCRIPTS --------------------------------------------

    protected function getScripts()
    {
        if($this->scripts){

            foreach ($this->scripts as $script) {

                echo '<script src="' . $script . '" type="text/javascript"></script>';
                
            }
        }
    }
    
# -------------------- WRITE LOG ----------------------------------------------

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault')
    {
        $dataTime = new \DateTime();
         
        $str = $event . ': ' . $dataTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";

        file_put_contents('log/' . $file, $str, FILE_APPEND);
    }
}
