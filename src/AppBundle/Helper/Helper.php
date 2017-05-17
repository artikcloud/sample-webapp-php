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

    private static function exchangeCode($code) {

        $logger = $this->get('logger');

        $curl = curl_init();

   // Observation:  redirect_uri is not enforced here - assumed to be the same
   // RFC6749  —  Same redirect_uri must be used
   // ********************************************
   
   // 10.6.  Authorization Code Redirection URI Manipulation
   //      Once at the authorization server, the victim is prompted with a

        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' =>  $this->container->getParameter('client_id'),
            'client_secret'=> $this->container->getParameter('client_secret'),
            'redirect_uri' => $this->container->getParameter('redirect_uri'),
            'code' => $code
        );

        //TODO:  pass clientid / secret in Auth Header instead
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://accounts.artik.cloud/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($data),
          CURLOPT_HTTPHEADER => array(
            "Content-Type: application/x-www-form-urlencoded"
          ),
        ));


        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $logger->info($response);
        $logger->warning($err);

        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }

        return $response;

    } 

}
