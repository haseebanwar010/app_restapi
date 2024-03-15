<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
use DateTime;


class NotificationController extends Controller
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

    //parent + child
    public function get_allnotifications(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                if($user)
                {
                    $data='';
                    $student_ids=DB::table('student_guardians')->select('student_id')->where('guardian_id',$user->id)->whereNull('deleted_at')->get()->all();
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    if(sizeof($student_ids) > 0)
                    {
                        foreach($student_ids as $key => $ids)
                        {
                            $student_ids[$key]=(int)$ids->student_id;
                        }
                        $student_ids[]=$user->id;
                        
                        $data=DB::table('notifications')
                        ->join('notification_details', 'notification_details.notification_id', '=', 'notifications.id', 'right')
                        ->join('users', 'notifications.sender_id', '=', 'users.id', 'right')
                        ->select('notification_details.id','notifications.msg_key', 'notifications.data', 'notifications.created_at as dateTime', 'notification_details.is_read as is_read', 'users.id as senderid', 'users.name as sender_name','users.avatar as sender_img', 'notifications.app_url as notifyUrl', 'notifications.app_id', 'users.email', 'notification_details.receiver_id')
                        ->whereIn('notification_details.receiver_id', $student_ids)
                        ->where('notification_details.is_read', 0)
                        ->where('notifications.sender_id', '!=', $student_ids)
                        ->whereNull('notification_details.deleted_at')
                        ->orderBy('notifications.created_at', 'desc')->get()->all();
                    }
                    else
                    {
                        $data=DB::table('notifications')
                        ->join('notification_details', 'notification_details.notification_id', '=', 'notifications.id', 'right')
                        ->join('users', 'notifications.sender_id', '=', 'users.id', 'right')
                        ->select('notification_details.id','notifications.msg_key', 'notifications.data', 'notifications.created_at as dateTime', 'notification_details.is_read as is_read', 'users.id as senderid', 'users.name as sender_name','users.avatar as sender_img', 'notifications.app_url as notifyUrl', 'notifications.app_id', 'users.email')
                        ->where('notification_details.receiver_id', $user->id)
                        ->where('notification_details.is_read', 0)
                        ->where('notifications.sender_id', '!=', $user->id)
                        ->whereNull('notification_details.deleted_at')
                        ->orderBy('notifications.created_at', 'desc')->get()->all();
                    }
                    
                    
                    
                    

                    if($data && sizeof($data) > 0)
                    {
                        foreach($data as $rec)
                        {
                            if($rec->receiver_id && $rec->receiver_id!='')
                            {
                                $receiver_rec=DB::table('users')->where('id', $rec->receiver_id)->where('deleted_at', '=', '0')->first();
                                if($receiver_rec)
                                {
                                    $rec->receiver_name=$receiver_rec->name;
                                    $rec->receiver_img=$receiver_rec->avatar;
                                }
                            }
                            $rec->data=json_decode($rec->data);
                        }
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Notification fetch successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No notifications found!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Invalid user!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }
            }
            else
            {
                $this->response=array
                (
                    'status' => 'error',
                    'message' => 'Invalid user!',
                    'data' => ''
                );
            }
        }
    }
    
    
    //single_student
    public function get_notifications(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $api_token=$request->input('api_token');
                $user = DB::table('users')
                        ->where(function ($match) use ($api_token){
                            $match->where('api_token', $api_token)
                            ->orWhere('id', $api_token);
                        })
                        ->whereIn('role_id', [2,3,4])
                        ->where('deleted_at', '=', '0')
                        ->first();
                if($user)
                {
                    $data=DB::table('notifications')
                    ->join('notification_details', 'notification_details.notification_id', '=', 'notifications.id', 'right')
                    ->join('users', 'notifications.sender_id', '=', 'users.id', 'right')
                    ->select('notification_details.id','notifications.msg_key', 'notifications.data', 'notifications.created_at as dateTime', 'notification_details.is_read as is_read', 'users.id as senderid', 'users.name as sender','users.avatar as user_img', 'notifications.app_url as notifyUrl', 'notifications.app_id', 'users.email')
                    ->where('notification_details.receiver_id', $user->id)
                    ->where('notification_details.is_read', 0)
                    ->where('notifications.sender_id', '!=', $user->id)
                    ->whereNull('notification_details.deleted_at')
                    ->orderBy('notifications.created_at', 'desc')->get()->all();

                    if($data)
                    {
                        foreach($data as $rec)
                        {
                            $rec->id = (int) $rec->id;
                            // $rec->dateTime = new DateTime(date('Y-m-d h:i:s',strtotime($rec->dateTime)));
                            $rec->dateTime = date('Y-m-d H:i:s',strtotime($rec->dateTime));
                            // $rec->dateTime = $rec->dateTime->format('Y-m-d\TH:i:s.v');
                            $rec->is_read = (int) $rec->is_read;
                            $rec->app_id = (int) $rec->app_id;
                            $rec->data=json_decode($rec->data);
                        }
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Notification fetch successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No notifications found!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Invalid user!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }
            }
            else
            {
                $this->response=array
                (
                    'status' => 'error',
                    'message' => 'Invalid user!',
                    'data' => ''
                );
            }
        }
    }
        
    //notification_exist
    public function get_notificationStatus(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $api_token=$request->input('api_token');
                $user = DB::table('users')
                        ->where(function ($match) use ($api_token){
                            $match->where('api_token', $api_token)
                            ->orWhere('id', $api_token);
                        })
                        ->whereIn('role_id', [2,3,4])
                        ->where('deleted_at', '=', '0')
                        ->first();
                if($user)
                {
                    $data=DB::table('notifications')
                    ->join('notification_details', 'notification_details.notification_id', '=', 'notifications.id', 'right')
                    ->join('users', 'notifications.sender_id', '=', 'users.id', 'right')
                    ->select('notification_details.id','notifications.msg_key', 'notifications.data', 'notifications.created_at as dateTime', 'notification_details.is_read as is_read', 'users.id as senderid', 'users.name as sender','users.avatar as user_img', 'notifications.app_url as notifyUrl', 'notifications.app_id', 'users.email')
                    ->where('notification_details.receiver_id', $user->id)
                    ->where('notification_details.is_read', 0)
                    ->where('notifications.sender_id', '!=', $user->id)
                    ->whereNull('notification_details.deleted_at')
                    ->orderBy('notifications.created_at', 'desc')->get()->all();

                    if(sizeof($data) > 0)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Notifications exist!',
                            'data' => 'true'
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No notification found!',
                            'data' => 'false'
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Invalid user!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }
            }
            else
            {
                $this->response=array
                (
                    'status' => 'error',
                    'message' => 'Invalid user!',
                    'data' => ''
                );
            }
        }
    }
    
        
    public function notification_status(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('notification_id') && $request->input('notification_id')!='')
                {
                    
                    $api_token=$request->input('api_token');
                    $user = DB::table('users')
                            ->where(function ($match) use ($api_token){
                                $match->where('api_token', $api_token)
                                ->orWhere('id', $api_token);
                            })
                            ->whereIn('role_id', [2,3,4])
                            ->where('deleted_at', '=', '0')
                            ->first();
                    if($user)
                    {
                        $result=DB::table('notification_details')->where('id', $request->input('notification_id'))->where('receiver_id', $user->id)->update(array('is_read' => 1));
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Notification read successfully!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No changes found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Invalid user!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Notification Id is required',
                        'data' => ''
                    );
                }
            }
            else
            {
                $this->response=array
                (
                    'status' => 'success',
                    'message' => 'API Token is required!',
                    'data' => ''
                );
            }
        }
    }
    
    
    
    
    
}
