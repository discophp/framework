<?php

class BaseCrypt {

    //encrypt AES128
    public function encrypt($input){
    
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        
        $key128 = $_SERVER["AES_KEY128"];
        $iv = $_SERVER["AES_IV"];

        if (mcrypt_generic_init($cipher, $key128, $iv) != -1) {
            $cipherText = mcrypt_generic($cipher,$input);
            mcrypt_generic_deinit($cipher);
        }//if

        return bin2hex($cipherText);
    }//AES128

    //decrypt AES128
    public function decrypt($crypt){
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

        $key128 = $_SERVER["AES_KEY128"];
        $iv = $_SERVER["AES_IV"];

        if (mcrypt_generic_init($cipher, $key128, $iv) != -1) {
            $cipherText = mcrypt_generic($cipher,$crypt);
            mcrypt_generic_deinit($cipher);
        }//end if

        return bin2hex($cipherText);
    }//end AES128


    public function hash($pw){
        return hash('sha512',$_SERVER['SHA512_SALT_LEAD'].$pw.$_SERVER['SHA512_SALT_TAIL']);
    }//pwHash

}//BaseCrypt


?>
