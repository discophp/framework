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
     * - $app->config['AES_KEY256']
     * 
     *
     * @param  string $input Value to encrypt using AES256.
     * @return string The encrypted value of $input.
    */
    public function encrypt($input){
    
        $key256 = pack('H*',\App::instance()->config["AES_KEY256"]);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size,MCRYPT_DEV_URANDOM);

        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key256,$input,MCRYPT_MODE_CBC,$iv);

        return trim(base64_encode($iv.$cipherText));

    }//AES128



    /**
     * Decrypt with AES256.
     * This method relies on the settings in [.config.php] 
     * - \App::$app->config['AES_KEY256']
     *
     *
     * @param  string $crypt Value to decrypt using AES256.
     * @return string The decrypted value of $crypt.
    */
    public function decrypt($crypt){

        $key256 = pack('H*',\App::instance()->config["AES_KEY256"]);

        $cipherText = base64_decode($crypt);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = substr($cipherText,0,$iv_size);

        $cipherText = substr($cipherText,$iv_size);

        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key256,$cipherText,MCRYPT_MODE_CBC,$iv));
    }//end AES128



    /**
     * Hash with sha512.
     * This method relies on the settings in [.config.php] 
     * - \App::$app->config['SHA512_SALT_LEAD']
     * - \App::$app->config['SHA512_SALT_TAIL']
     *
     *
     * @param  string $s Value to hash using SHA512.
     * @return string The hashed value of $s.
     */
    public function hash($s){
        return hash('sha512',\App::instance()->config['SHA512_SALT_LEAD'].$s.\App::instance()->config['SHA512_SALT_TAIL']);
    }//pwHash




    /**
     * Perform a timing safe compare.
     *
     *
     * @param string $safe
     * @param string $user
     *
     * @return boolean
    */
    public function timingSafeCompare($safe, $user) {
    
        // Prevent issues if string length is 0
        $safe .= chr(0);
        $user .= chr(0);
        $safeLen = strlen($safe);
        $userLen = strlen($user);
    
        // Set the result to the difference between the lengths
        $result = $safeLen - $userLen;
    
        // Note that we ALWAYS iterate over the user-supplied length
        // This is to prevent leaking length information
        for ($i = 0; $i < $userLen; $i++) {
            // Using % here is a trick to prevent notices
            // It's safe, since if the lengths are different
            // $result is already non-0
            $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
        }//for
    
        // They are only identical
        //strings if $result is
        //exactly 0...
        return $result === 0;
    
    }//timingSafeCompare



}//BaseCrypt
?>
