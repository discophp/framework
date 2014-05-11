<?php

namespace Disco;

/**
 *      This file contains the class BaseCrypt
*/


/**
 *
 *      BaseCrypt class.
 *      Provides easy wrapper around mcryp php module
 *
*/
class BaseCrypt {



    /**
     *      Encrypt with AES256
     *      
     *
     *      @param string $input value to encrypt
     *      @return string
    */
    public function encrypt($input){
    
        $key256 = pack('H*',$_SERVER["AES_KEY256"]);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size,MCRYPT_DEV_URANDOM);

        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key256,$input,MCRYPT_MODE_CBC,$iv);

        return base64_encode($iv.$cipherText);

    }//AES128



    /**
     *      Decrypt with AES256
     *
     *
     *      @param string $crypt value to decrypt
     *      @return string
    */
    public function decrypt($crypt){

        $key256 = pack('H*',$_SERVER["AES_KEY256"]);

        $cipherText = base64_decode($crypt);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = substr($cipherText,0,$iv_size);

        $cipherText = substr($cipherText,$iv_size);

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key256,$cipherText,MCRYPT_MODE_CBC,$iv);
    }//end AES128



    /**
     *      hash with sha512
     *
     *
     *      @param string $s value to hash
     *      @return string
     */
    public function hash($s){
        return hash('sha512',$_SERVER['SHA512_SALT_LEAD'].$s.$_SERVER['SHA512_SALT_TAIL']);
    }//pwHash



}//BaseCrypt


?>
