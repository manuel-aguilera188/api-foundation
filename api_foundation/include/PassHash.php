<?php
 /*
    PASSWORD ENCRYPTION UTILITY
        *Use this utility for password hashing and comparing
        *Crypt function returns a string using Unix DES algorithm.
        *Salt is a string used for the crypt function to base the hashing on.
        *Blowfish is one of many types for hashing, using a salt that can be "$2a$", "$2x$" or "$2y$", it also uses a two digit cost parameter like $11, $10, $12, etc,
         and 22 characters from the alphabet "./0-9A-Za-z".

    */
class PassHash {
 
    // blowfish
    private static $algo = '$2a';
    // cost parameter
    private static $cost = '$10';
 
    // method to generate a unique combination for a salt, this returns 22 characters!
    public static function unique_salt() {
        return substr(sha1(mt_rand()), 0, 22);
    }
 
    // Function used to generate a new hash the full salt is composed of 29 characters!
    public static function hash($password) {
        return crypt($password, self::$algo . self::$cost . '$' . self::unique_salt());
    }
 
    // Function to compare a hash with a password
    public static function check_password($hash, $password) {
        //Remove the salt from the Hash!
        $full_salt = substr($hash, 0, 29);
        //Create a new hash with the full recovered salt
        $new_hash = crypt($password, $full_salt);
        //Compare both hashes to be the same.
        return ($hash == $new_hash);
    }
 
}
 
?>