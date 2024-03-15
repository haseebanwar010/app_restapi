<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use Pusher\Pusher;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
use GuzzleHttp;

class PusherNotificationController extends Controller
{

    private $response=array();

    public function __construct(Request $request)
    {
        $token=CommonHelper::token_authentication();
        if($request->hasHeader('Authoraization'))
        {
            if($token==$request->header('Authoraization'))
            {
            }
            else
            {
                $this->response=array
                (
                    'status' => 'error',
                    'message' => 'Invalid authentication token!',
                    'data' => ''
                );
            }
        }
        else
        {
            $this->response=array
            (
                'status' => 'error',
                'message' => 'Only authenticated requests are allowed!',
                'data' => ''
            );
        }
    }
    

    public function notification(Request $request)
    {
        
        
        $options = array(
			'cluster' => env('PUSHER_APP_CLUSTER'),
			'encrypted' => true
		);
        $pusher = new Pusher(
			env('PUSHER_APP_KEY'),
			env('PUSHER_APP_SECRET'),
			env('PUSHER_APP_ID'), 
			$options
		);
		
		$json_message['data']="hello khalid now its work";
// 		$json_message=json_encode($json_message,true);
		
// 		echo '<pre>';
// 		echo getType($json_message);
// 		print_r($json_message);
// 		var_dump($json_message);
// 		die;
		
		
		echo '<pre>';
// 		print_r($pusher->trigger('mychanal-23638', 'my-event', 'Hello Khalid, welcome notification'));
// 		print_r($pusher->trigger('mychanal-7458', 'my-event', '{"message": "hello"}' ));
		print_r($pusher->trigger('mychanal-23638', 'my-event', $json_message ));
		die;

        $data = 'Hello Khalid';
        return response()->$pusher->trigger('mychanal-7458', 'App\\Events\\Notify', $data);
        
        // $this->response=array
        // (
        //     'status' => 'success',
        //     'message' => 'send',
        //     'data' => $result
        // );
        // return response()->json($this->response, 200);
    }
    
    
    public function whatsapp_msgs(Request $request)
    {
        $data =
        [
            'token'=>'c0d57c25868c4e4dad0182639235fc01', //token waping
            'source' => 923074626182,  // your phone
            'destination'=>923154071704, // Receivers phone
            'type'=>'text', //type message
            'body' => [ 
            'text'=>'Hello' // Message
            ]  
        ]; 
        
        
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST','http://waping.es/api/send', 
        ['headers' => ['Content-Type' => 'application/json'], 
        'json' => $data
        ]
        );
        
        echo $response->getStatusCode();
        echo $response->getBody();
        die;
        
        
        // $json = json_encode($data); // Encode data to JSON
        // // URL for request POST /message
        // $url = 'http://waping.es/api/send';
        // // Make a POST request
        // $options = stream_context_create(['http' => [
        //     'method'  => 'POST',
        //     'header'  => 'Content-type: application/json',
        //     'content' => $json
        //     ]
        // ]);
        
        // // Send a request
        // $result = file_get_contents($url, false, $options);
        
        // echo '<pre>';
        // print_r($result);
        // die;
    }
    
}