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

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */

    public function indexAction(Request $request)
    {

        $this->get('session')->set('state', $this->generateRandomString());
        return $this->render('default/index.html.twig', [
            'client_id' => $this->container->getParameter('client_id'),
            'redirect_uri' => $this->container->getParameter('redirect_uri'),
            'state' => $this->get('session')->get('state')
        ]);
    }
    /**
     * @Route("/callback/artikcloud", name="callback-artikcloud")
     */

    public function oauth2RedirectURI(Request $request)
    {

        
        $code = $request->query->get('code');
        $state = $request->query->get('state');
        if (strcmp($state, $this->get('session')->get('state')) !== 0) {
           echo 'state parameter must match';
           $this->redirectToRoute('homepage');
        }
        $response = json_decode($this->exchangeCode($code), true);
        $session = $this->get('session');
        $session->set('token', $response);
        
        $userResponse = json_decode($this->getUserFullName($response['access_token']), true);
        $session->set('user', $userResponse);
        return $this->redirectToRoute('homepage'); 
    }
    /**
     * @Route("/message/send", name="message-send")
     */

    public function sendMessage(Request $request) 
    {

        $this->initARTIKCloudConfiguration();
        $params = $this->jsonParamsToArray($request);
        $messages_api = new Api\MessagesApi();
        $data = [
            "activity" => 12,
            "description" => "my simple description",
            "heartRate" => 67,
            "state" => 22,
            "stepCount" => 56
        ];
        $message = new \ArtikCloud\Model\Message();
        $message->setData($data);
        $message->setSdid($params['deviceId']);
        $response = "";
        try {
             $response = $messages_api->sendMessage($message);
             return new Response($response);
        } catch (ArtikCloud\ApiException $e) {
             return new Response(json_encode($e->getResponseBody()), $e->getCode());
        } catch (\Exception $e) {
             
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }
    /**
     * @Route("/message/get", name="message-get")
     */

    public function getMessage(Request $request) 
    {

        $this->initARTIKCloudConfiguration();
        $params = $this->jsonParamsToArray($request);
        $messages_api = new Api\MessagesApi();
        $count = 1;
        $sdids = $params['deviceId'];
        try {
             $response = $messages_api->getLastNormalizedMessages($count, $sdids);
             return new Response($response);
        } catch (ArtikCloud\ApiException $e) {
             return new Response(json_encode($e->getResponseBody()), $e->getCode());
        } catch (\Exception $e) {
             
             return new Response($e->getMessage(), $e->getCode());
        }
       
    }
     /**
     * @Route("/user/devices", name="list-devices")
     */

    public function listUserDevices(Request $request) {
        $this->initARTIKCloudConfiguration();
        $api_instance = new ArtikCloud\Api\UsersApi();
        $user_id = $this->getUserId();
        $offset = null; // int | Offset for pagination.
        $count = null; // int | Desired count of items in the result set
        $include_properties = false; // bool | Optional. Boolean (true/false) - If false, only return the user's device types. If true, also return device types shared by other users.
        try {
            $result = $api_instance->getUserDevices($user_id, $offset, $count, $include_properties);
        } catch (ArtikCloud\ApiException $e) {
             return new Response(json_encode($e->getResponseBody()), $e->getCode());
        } catch (Exception $e) {
            echo 'Exception when calling UsersApi->getUserDevices: ', $e->getMessage(), PHP_EOL;
            return new Response($e->getMessage(), $e->getCode());
        }
        return new Response($result);
    }
    /**
    * @Route("/user/create-device", name="create-device")
    */

    public function createUserDevice(Request $request) {
        $this->initARTIKCloudConfiguration();
        $params = $this->jsonParamsToArray($request);
        $api_instance = new ArtikCloud\Api\DevicesApi();
        $new_device_parameters = [
            "uid" => $this->getUserId(),
            "dtid" => $this->container->getParameter('device_type_id'),
            "name" => $params['deviceName']
        ];
        $device = new \ArtikCloud\Model\Device($new_device_parameters);
        try {
            $result = $api_instance->addDevice($device);
        } catch (\Exception $e) {
            echo 'Exception when calling DevicesApi->addDevice: ', $e->getMessage(), PHP_EOL;
            return new Response($e, $e->getCode());
        }
        return new Response($result);
    }

    private function exchangeCode($code) 
    {

        $curl = curl_init();
   
        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' =>  $this->container->getParameter('client_id'),
            'client_secret'=> $this->container->getParameter('client_secret'),
            'redirect_uri' => $this->container->getParameter('redirect_uri'),
            'code' => $code
        );
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
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }
        return $response;
    } 

    private function getUserFullName($userToken) 
    {

        ArtikCloud\Configuration::getDefaultConfiguration()->setAccessToken($userToken);
        $users_api = new Api\UsersApi();
        $response = $users_api->getSelf();
        return $response;
    }

    private function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
          
        return $randomString;
    }

    private function initARTIKCloudConfiguration() {
        $session = $this->get('session');
        $token = $session->get('token');
        ArtikCloud\Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);
    }

    private function getUserId() {
        
        $session = $this->get('session');
        $user = $session->get('user');
        return $user['data']['id'];
        
    }

    private function jsonParamsToArray(Request $request) {
      $params = array();
      $content = $request->getContent();
      
      if (!empty($content))
      {

          return json_decode($content, true); // 2nd param to get as array
      }
      return [];
    }

    private function log($data, $logMode='info') 
    {

         try {
            $logger = $this->get('logger');
            $logger->$logMode($data);
         } catch(\Exception $e) {
            $logger->warning("Error trying to log data: " . print_r($data));
         }
         
    }
}
