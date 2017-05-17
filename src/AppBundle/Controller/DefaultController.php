<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use ArtikCloud as ArtikCloud;

use ArtikCloud\Configuration as Configuration;
use ArtikCloud\Api as Api;
use ArtikCloud\ApiException as ApiException;

use AppBundle\Helper\Helper as Helper;


class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */

    public function indexAction(Request $request)
    {
        //replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'client_id' => $this->container->getParameter('client_id'),
            'client_secret' => $this->container->getParameter('client_secret'),
            'redirect_uri' => $this->container->getParameter('redirect_uri'),
            'response_type' => $this->container->getParameter('response_type'),
            'env' => $this->container->getParameter('env'),
            'state' => Helper::generateRandomString()

        ]);
    }


    /**
     * @Route("/callback/artikcloud", name="callback-artikcloud")
     */
    public function oauth2RedirectURI(Request $request)
    {

        
        $code = $request->query->get('code');
        $state = $request->query->get('state');


        if($state) {
            
            //TODO: 
        }

        if($code) {

            //TODO: 
        }

        $response = json_decode(Helper::exchangeCode($code), true);



        $session = $this->get('session');
        $session->set('token', $response);
        

        $userResponse = json_decode($this->getUserFullName($response['access_token']), true);
        //id, name, email, fullName, saIdentity, createdOn, modifiedOn


        $session->set('user', $userResponse);

        return $this->redirectToRoute('homepage'); 

    }

    /**
     * @Route("/message/send", name="message-send")
     */
    public function sendMessage() {

        //Example Activity Tracker
        //Notes: device fields: //activity (int), description (string), heartRate (int), state (int), stepCount (int)

        //TODO: force user to set in configuration file for this sample
        $device_id = "1efff91de88243e5b9c5ef4a5541ed02";

        $session = $this->get('session');
        $token = $session->get('token');


        Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);

        //reference to the messages api
        $messages_api = new Api\MessagesApi();

        $data = [
            "activity" => 12,
            "description" => "my simple description",
            "heartRate" => 67,
            "state" => 22,
            "stepCount" => 56
        ];

        //https://github.com/artikcloud/artikcloud-php/blob/master/docs/Model/Message.md
        $message = new \ArtikCloud\Model\Message();
        $message->setData($data);
        $message->setSdid($device_id);

        $response = "";

        try {

             //TODO/SDK as Bug:  Missing / how to retrieve header information
             //https://github.com/artikcloud/artikcloud-php/blob/master/docs/Api/MessagesApi.md#sendmessage
             $response = $messages_api->sendMessage($message);
             return new Response($response);

        } catch (ArtikCloud\ApiException $e) {

             //$session->remove('token');


             return new Response(json_encode($e->getResponseBody()), $e->getCode());

        } catch (\Exception $e) {
            // echo 'Exception while calling message api', $e->getMessage(), PHP_EOL;
             
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }

    /**
     * @Route("/message/get", name="message-get")
     */
    public function getMessage() {

        //echo "$_SERVER[REQUEST_URI]";


        //Example Activity Tracker
        $device_id = "1efff91de88243e5b9c5ef4a5541ed02";

        $session = $this->get('session');
        $token = $session->get('token');

        //typically used with user access token here (ie: oauth2 authentication)
        //here, we will just set this value with the device token
        Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);

        //reference to the messages api
        $messages_api = new Api\MessagesApi();

        $count = 1;
        $sdids = "1efff91de88243e5b9c5ef4a5541ed02";

        $response = "";

        try {

             //SDK Bug: SDK mutating data into "arrays"
             //"data": { "state": [ 22 ], "description": [ "my simple description" ], "stepCount": [ 56 ], "heartRate": [ 67 ], "activity": [ 12 ] } 

             $response = $messages_api->getLastNormalizedMessages($count, $sdids);

             return new Response($response);


        } catch (ArtikCloud\ApiException $e) {
             


             $session->remove('token');


             return new Response(json_encode($e->getResponseBody()), $e->getCode());

        } catch (\Exception $e) {
            // echo 'Exception while calling message api', $e->getMessage(), PHP_EOL;
             
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }

    private function getUserFullName($userToken) {

        echo "calling getUserFullName >>>>>";

        $session = $this->get('session');
        $token = $session->get('token');

        ///typically used with user access token here (ie: oauth2 authentication)
        //here, we will just set this value with the device token
        Configuration::getDefaultConfiguration()->setAccessToken($userToken);

        //reference to the messages api
        $users_api = new Api\UsersApi();

        $response = $users_api->getSelf();

        echo "Getting user info>>>>";
        echo $response;

        var_dump($response);
        return $response;

    }

    
    private function log($data, $logMode='info') {

         try {


         } catch(\Exception $e) {


         }
         
    }
}
