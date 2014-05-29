<?php
namespace Disco\classes;
/**
 * This file contains the class Crypt. It provides helper functions to use AES and SHA.
*/


/**
 * Crypt class.
 * Provides easy wrapper around mycrpt_php module.
*/
class Crypt {


    /**
     * Encrypt with AES256.
     * This method relies on the settings in [.config.php] 
     * - $_SERVER['AES_KEY256']
     * 
     *
     * @param  string $input Value to encrypt using AES256.
     * @return string The encrypted value of $input.
    */
    public function encrypt($input){
    
        $key256 = pack('H*',$_SERVER["AES_KEY256"]);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size,MCRYPT_DEV_URANDOM);

        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key256,$input,MCRYPT_MODE_CBC,$iv);

        return base64_encode($iv.$cipherText);

    }//AES128



    /**
     * Decrypt with AES256.
     * This method relies on the settings in [.config.php] 
     * - $_SERVER['AES_KEY256']
     *
     *
     * @param  string $crypt Value to decrypt using AES256.
     * @return string The decrypted value of $crypt.
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
     * Hash with sha512.
     * This method relies on the settings in [.config.php] 
     * - $_SERVER['SHA512_SALT_LEAD']
     * - $_SERVER['SHA512_SALT_TAIL']
     *
     *
     * @param  string $s Value to hash using SHA512.
     * @return string The hashed value of $s.
     */
    public function hash($s){
        return hash('sha512',$_SERVER['SHA512_SALT_LEAD'].$s.$_SERVER['SHA512_SALT_TAIL']);
    }//pwHash

}//BaseCrypt
?>
