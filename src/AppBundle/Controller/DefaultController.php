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

        self::log("Redirect Uri");
        
        $code = $request->query->get('code');
        $state = $request->query->get('state');

        self::log("Redirect Uri with Code:".$code." and state:".$state);

        if($state) {
            
            //TODO: 
        }

        if($code) {

            //TODO: 
        }

        $response = json_decode(Helper::exchangeCode($code), true);

        self::log($response);


        $session = $this->get('session');
        $session->set('token', $response);
        

        self::log("calling user info with access token");
        $userResponse = json_decode($this->getUserFullName($response['access_token']), true);
        self::log($userResponse);
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
             self::log("Got Response:".json_encode($response));
             return new Response($response);

        } catch (ArtikCloud\ApiException $e) {

             //$session->remove('token');

             $logger->error(json_encode($e->getResponseBody()));

             return new Response(json_encode($e->getResponseBody()), $e->getCode());

        } catch (\Exception $e) {
            // echo 'Exception while calling message api', $e->getMessage(), PHP_EOL;
             
             $logger->error($e->getMessage());
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }

    /**
     * @Route("/message/get", name="message-get")
     */
    public function getMessage() {

        //echo "$_SERVER[REQUEST_URI]";

        $logger = $this->get('logger');

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
             $logger->info($response);

             return new Response($response);


        } catch (ArtikCloud\ApiException $e) {
             

             // $logger->error($e->getMessage());
             // $logger->error($e->getCode());
             // $logger->error(print_r($e->getResponseHeaders()));
             // $logger->error(print_r($e->getResponseBody()));

             $session->remove('token');

             $logger->error(json_encode($e->getResponseBody()));

             return new Response(json_encode($e->getResponseBody()), $e->getCode());

        } catch (\Exception $e) {
            // echo 'Exception while calling message api', $e->getMessage(), PHP_EOL;
             
             $logger->error($e->getMessage());
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }

    private function getUserFullName($userToken) {

        echo "calling getUserFullName >>>>>";

        $logger = $this->get('logger');
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

            $logger = $this->get('logger');
            $logger->$logMode($data);

         } catch(\Exception $e) {

            $logger->warning("There was an error trying to log data: " . print_r($data));

         }
         
    }
}
