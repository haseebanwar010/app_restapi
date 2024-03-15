<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class PermissionController extends Controller
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


    public function verify_permission(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('permission_tag') && $request->input('permission_tag')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $user->permissions=json_decode($user->permissions);
                        if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                        {
                            foreach($user->permissions as $key => $perm)
                            {
                                if($perm->permission==$request->input('permission_tag'))
                                {
                                    if($perm->val=="true")
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'You have permission!',
                                            'data' => ''
                                        );
                                        return response()->json($this->response, 200);
                                        die;
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'You do not have permission!',
                                            'data' => ''
                                        );
                                        return response()->json($this->response, 200);
                                        die;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No permission found!',
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
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'error',
                        'message' => 'Permission-Tag is required!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
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
                return response()->json($this->response, 400);
            }
        }
    }
    
    
    
    
    
}
