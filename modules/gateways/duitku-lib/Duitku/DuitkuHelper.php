<?php

class Duitku_Helper {

    public static function metode_aes($string, $serverkey, $action = "encrypt"){
        $ciphertext_b64 = "";
        $plaintext = mb_convert_encoding($string, "UTF-8");

        $iv = hex2bin("F27D5C9927726BCEFE7510B1BDD3D137");

        if ($action == 'encrypt') {
            $ciphertext_b64 = base64_encode(openssl_encrypt($plaintext,"AES-128-CBC", $serverkey,OPENSSL_RAW_DATA));
            $output = $ciphertext_b64;
        } else if ($action == 'decrypt') {
            $plaintext = openssl_decrypt(base64_decode($string),"AES-128-CBC", $serverkey,OPENSSL_RAW_DATA);
            $output = $plaintext;
        }
        
        return $output;
    }

    public static function metode_hash($string, $serverkey){
        $output = hash("sha256", $string . $serverkey);
        
        return $output;
    }
}