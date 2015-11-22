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
     * This method relies on the settings in application config `AES_KEY256` which is generated at install to 
     * a unique key for your application. You can use a different key by passing it in at `$key256`.
     * 
     *
     * @param  string $input Value to encrypt using AES256.
     * @param null|string $key256 An options key used to perform the decryption with instead of the `AES_KEY256`.
     *
     * @return string The encrypted value of $input.
    */
    public function encrypt($input, $key256){
    
        if($key256 === null){
            $key256 = \App::instance()->config('AES_KEY256');
        }//if

        $key256 = pack('H*', $key256);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size,MCRYPT_DEV_URANDOM);

        $cipherText = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key256,$input,MCRYPT_MODE_CBC,$iv);

        return trim(base64_encode($iv.$cipherText));

    }//encrypt



    /**
     * Decrypt with AES256.
     * This method relies on the settings in application config `AES_KEY256` which is generated at install to 
     * a unique key for your application. You can use a different key by passing it in at `$key256`.
     *
     *
     * @param  string $crypt Value to decrypt using AES256.
     * @param null|string $key256 An options key used to perform the decryption with instead of the `AES_KEY256`.
     *
     * @return string The decrypted value of $crypt.
    */
    public function decrypt($crypt, $key256 = null){

        if($key256 === null){
            $key256 = \App::instance()->config('AES_KEY256');
        }//if

        $key256 = pack('H*',$key256);

        $cipherText = base64_decode($crypt);

        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_CBC);
        $iv = substr($cipherText,0,$iv_size);

        $cipherText = substr($cipherText,$iv_size);

        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key256,$cipherText,MCRYPT_MODE_CBC,$iv));

    }//decrypt



    /**
     * Hash with sha512.
     * This method relies on the application configuration values `SHA512_SALT_LEAD` and `SHA512_SALT_TAIL` which 
     * are generated at install to unique values. 
     *
     * You can use your own if you pass them in manually in `$sha512SaltLead` and `$sha512SaltTail`. 
     *
     *
     * @param  string $s Value to hash using SHA512.
     * @param null|string $sha512SaltLead The lead salt to use in the hash.
     * @param null|string $sha512SaltTail The tail salt to use in the hash.
     *
     * @return string The hashed value of $s.
     */
    public function hash($s,$sha512SaltLead = null,$sha512SaltTail = null){

        if($sha512SaltLead === null){
            $sha512SaltLead = \App::instance()->config('SHA512_SALT_LEAD');
            $sha512SaltTail = \App::instance()->config('SHA512_SALT_TAIL');
        }//if

        return hash('sha512', $sha512SaltLead . $s . $sha512SaltTail);

    }//hash




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



}//Crypt
?>
