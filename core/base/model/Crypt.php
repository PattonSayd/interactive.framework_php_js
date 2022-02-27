<?php

namespace core\base\model;

use core\base\controller\Singleton;

class Crypt
{
    use Singleton;

    private $crypt_method = 'AES-128-CBC'; // метод Шифрования
    private $hash_algorithm = 'sha256'; // алгоритм хештрования
    private $hash_length = 32; // длинны хэша для алгаритма 256 (32symb)   
    
    public function encrypt($str)
    {
        $ivlen = openssl_cipher_iv_length($this->crypt_method); // получения длины

        $iv = openssl_random_pseudo_bytes($ivlen); // сгенирировать псевдослучайную последовательность

        $ciphel = openssl_encrypt($str, $this->crypt_method, CRYPT_KEY, OPENSSL_RAW_DATA, $iv);

        $hmac = hash_hmac($this->hash_algorithm, $ciphel, CRYPT_KEY, true);

        $encode = $this->cryptCombine($ciphel, $iv, $hmac);

        $r = $this->decrypt($encode);

        // $ciphel_comb = '112233445566778899';
        // $iv_comb = 'abcdefghijklmnop';
        // $hmac_comb = '00000000000000000000000000000000';
        
    }

    public function decrypt($str)
    {
        $ivlen = openssl_cipher_iv_length($this->crypt_method);

        $crypt_data = $this->cryptUnCombine($str, $ivlen);

        $original_plaintext = openssl_decrypt($crypt_data['str'], $this->crypt_method, CRYPT_KEY, OPENSSL_RAW_DATA, $crypt_data['iv']);

        $calcmac = hash_hmac($this->hash_algorithm, $crypt_data['str'], CRYPT_KEY, true);

        if(hash_equals($crypt_data['hmac'], $calcmac)) 
            return $original_plaintext;

        return false;
    }

    public function cryptCombine($str, $iv, $hmac)
    {
        $new_str = '';

        $strlen = strlen($str);

        $counter = (int)ceil(strlen(CRYPT_KEY) / ($strlen + $this->hash_length));

        $progress = 1;

        if($counter >= $strlen) $counter = 1;

        for($i = 0; $i < $strlen; $i++) {

            if($counter < $strlen){
            
                if($counter === $i){

                    $new_str .= substr($iv, $progress - 1, 1);
                    $progress++;
                    $counter += $progress;  
                }

            }else{

                break;
            }

            $new_str .= substr($str, $i, 1);
        }

        $new_str .= substr($str, $i);
        $new_str .= substr($iv, $progress -1);

        $new_str_half = (int)ceil(strlen($new_str) / 2);

        $new_str = substr($new_str, 0, $new_str_half) . $hmac . substr($new_str, $new_str_half);

        return base64_encode($new_str);

    }


    protected function cryptUnCombine($str, $ivlen)
    {
        $crypt_data = [];

        $str = base64_decode($str);

        $hash_position = (int)ceil(strlen($str) / 2 - $this->hash_length / 2);

        $crypt_data['hmac'] = substr($str, $hash_position, $this->hash_length);

        $str = str_replace($crypt_data['hmac'], '', $str);

        $counter = (int)ceil(strlen(CRYPT_KEY) / (strlen($str) - $ivlen + $this->hash_length)); 

        $progress = 2;

        $crypt_data['str'] = '';
        $crypt_data['iv'] = '';
        
        for($i=0; $i < strlen($str); $i++) { 
            
            if($ivlen + strlen($crypt_data['str']) < strlen($str) ){
                
                if($i === $counter){

                    $crypt_data['iv'] .= substr($str, $counter, 1); 
                    $progress++;
                    $counter += $progress; 

                }else{
                    $crypt_data['str'] .= substr($str, $i, 1);
                }

            }else{
                $crypt_data_len = strlen($crypt_data['str']);

                $crypt_data['str'] .= substr($str, $i, strlen($str) - $ivlen - $crypt_data_len);
                $crypt_data['iv'] .= substr($str, $i + (strlen($str) - $ivlen - $crypt_data_len));

                break;
            }
        }

        return $crypt_data;
    }
}