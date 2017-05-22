<?php

namespace AppBundle\Helper;

class Helper
{


	/**
	* Helper static function to generate a "state" code 
	* during oauth2
	* 
	* @param length - length of the random string
	*/

    public static function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
          return $randomString;
    }

    

}
