<?php

# Завершите решение так, чтобы оно разбило
# строку на пары из двух символов. Если строка 
# содержит нечетное количество символов, она
# должна заменить отсутствующий 
# второй символ последней пары подчеркиванием ('_')

function solution($str) {

    if(strlen($str) % 2 != 0){
        $str .= '_';
    }   

    $str = str_split($str);    
	$str_arr = [];    
    $conct = '';
    
    foreach ($str as $key => $v){
     
        if($key % 2 != 0){

        	$conct .= $v;          
          	array_push($str_arr, $conct);
            $conct = '';

            continue;
        }      
        $conct = $v;
    }  

    return $str_arr;
}
solution("abcder532453trf5e");


# Завершите метод / функцию, чтобы он
# преобразовывал слова, разделенные тире / подчеркиванием,
# в верблюжий регистр. Первое слово в выводе
# должно быть с заглавной буквы, только если
# исходное слово было с заглавной буквы


function toCamelCase($str){

        $symbols = ['_', '-'];

        $camCase = "";
        
        foreach($symbols as $symbol){

            if(strpos($str, $symbol)){

                $arr = explode($symbol, $str);
                $shift = array_shift($arr);
                $camCase .= $shift;

                foreach($arr as $name){
                    $camCase .= ucfirst($name);
                } 
            }
        }
        return $camCase;
    
}

toCamelCase("the-stealth-Warrior");


# Итеративно уменьшает массив к единственному значению(narcissistic), используя callback-функцию

function narcissistic(int $value): bool {
    $total = array_reduce(str_split($value), function ($carry, $item) use ($value) {

      $carry += pow($item, strlen($value));

      return $carry;
      
    }, 0);

    return $total === $value;
  }

narcissistic(371);
# or

function narcissistic1(int $value): bool {
    return $value == array_sum(array_map(function($n) use ($value) {

            return pow($n, strlen($value));

        }, str_split($value)));
  }