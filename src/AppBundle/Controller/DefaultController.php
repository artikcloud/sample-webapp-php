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

        $this->get('session')->set('state', Helper::generateRandomString());

        return $this->render('default/index.html.twig', [
            'client_id' => $this->container->getParameter('client_id'),
            'redirect_uri' => $this->container->getParameter('redirect_uri'),
            'response_type' => $this->container->getParameter('response_type'),
            'env' => $this->container->getParameter('env'),
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

    public function sendMessage() 
    {

        $session = $this->get('session');
        $token = $session->get('token');

        ArtikCloud\Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);
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
        $message->setSdid($this->container->getParameter('device_id'));
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

    public function getMessage() 
    {

        $device_id = $this->container->getParameter('device_id');
        $session = $this->get('session');
        $token = $session->get('token');

        ArtikCloud\Configuration::getDefaultConfiguration()->setAccessToken($token['access_token']);
        
        $messages_api = new Api\MessagesApi();
        $count = 1;
        $sdids = $device_id;

        try {
             $response = $messages_api->getLastNormalizedMessages($count, $sdids);
             return new Response($response);
        } catch (ArtikCloud\ApiException $e) {
             return new Response(json_encode($e->getResponseBody()), $e->getCode());
        } catch (\Exception $e) {
             return new Response($e->getMessage(), $e->getCode());
        }
       
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
