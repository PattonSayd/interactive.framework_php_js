<?php 

if(!function_exists('mb_str_replace')){

    function mb_str_replace($needle, $text, $message)
    {
        return implode($text, explode($needle, $message));
    }
}