<?php
namespace Disco\classes;
/**
 * This file contains the class Crypt. It provides helper wrappers around the \Defuse\Crypto library.
 * It also provides SHA512 hashing and a timing safe comparision function.
*/


/**
 * Crypt class.
*/
class Crypt {



    /**
     * Generate a new key that can be used with the `crypt` and `decrypt` methods of this class.
     *
     * Uses `\Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString()` to generate the key.
     *
     *
     * @return string The key.
    */
    public function generateNewCryptKey(){
        return \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
    }//generateNewCryptKey



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
    public function encrypt($input, $key256 = null){
    
        if($key256 === null){
            $key256 = \App::config('AES_KEY256');
        }//if

        $key256 = \Defuse\Crypto\Key::loadFromAsciiSafeString($key256);

        return \Defuse\Crypto\Crypto::encrypt($input,$key256);

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
            $key256 = \App::config('AES_KEY256');
        }//if

        $key256 = \Defuse\Crypto\Key::loadFromAsciiSafeString($key256);

        return \Defuse\Crypto\Crypto::decrypt($crypt,$key256);

    }//decrypt



    /**
     * Hash with sha512.
     * If no salt is provided the salt stored in `app/config/config.php` with key `SHA512_SALT` will be used as the 
     * salt value.
     *
     *
     * @param  string $value Value to hash using SHA512.
     * @param null|string $salt The salt to use in the hash.
     *
     * @return string The hashed value of $s.
     */
    public function hash($value, $salt = ''){

        if($salt === ''){
            $salt = \App::config('SHA512_SALT');
        }//if

        return hash('sha512', $salt . $value);

    }//hash



    /**
     * Hash with sha512.
     * Generate a new salt and hash the value with it, returning both the salt and hash.
     *
     * 
     * @param string $value The value to hash using sha512. 
     * @param int $saltLength The length of the salt to generate, defaults to 128.
     *
     * @return array An array with two keys, `salt` and `hash`, containing the salt and hash value respectivly.
     *
    */
    public function hashAndGenerateNewSalt($value, $saltLength = 128){
        $salt = \Disco\manage\Manager::genRand($saltLength);
        return Array(
            'salt' => $salt,
            'hash' => $this->hash($value, $salt),
        );
    }//hashAndGenerateNewSalt



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
