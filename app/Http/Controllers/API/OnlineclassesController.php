<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
use Carbon\Carbon;


class OnlineclassesController extends Controller
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

    //single student APIs
    public function get_online_classes(Request $request)
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
                        ->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        
                if($user)
                {
                    $student_rec = DB::select("SELECT s.class_id, sh_classes.name as class_name, s.batch_id, sh_batches.name as batch_name, sh_users.name as student_name FROM sh_students_$user->school_id s left JOIN sh_users ON s.id = sh_users.id left JOIN sh_classes ON s.class_id = sh_classes.id left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE s.id='$user->id' ");
                    
                    if(sizeof($student_rec) > 0)
                    {
                        $student_rec=$student_rec[0];
                        $data=array();
                        if($request->input('date') && $request->input('date')!='')
                        {
                            $data=DB::table('online_classes')
                            ->join('users', 'online_classes.started_by', '=', 'users.id')
                            ->join('classes', 'online_classes.class_id', '=', 'classes.id', 'left')
                            ->join('batches', 'online_classes.batch_id', '=', 'batches.id', 'left')
                            ->select('online_classes.id', 'online_classes.class_name', 'online_classes.status', 'users.name as teacher_name', 'classes.name as origional_className', 'batches.name as batch_name' )
                            ->where('online_classes.class_id',$student_rec->class_id)
                            ->where('online_classes.batch_id',$student_rec->batch_id)
                            ->where('online_classes.status','ongoing')
                            ->where(function ($match) use ($user){
                                    $match->whereDate('online_classes.created_at', '=', Carbon::today()->toDateString())
                                    ->orWhereDate('online_classes.updated_at', '=', Carbon::today()->toDateString());
                            })->whereNull('online_classes.deleted_at')->get()->all();
                        }
                        else 
                        {
                            $data=DB::table('online_classes')
                            ->join('classes', 'online_classes.class_id', '=', 'classes.id', 'left')
                            ->join('batches', 'online_classes.batch_id', '=', 'batches.id', 'left')
                            ->join('users', 'online_classes.started_by', '=', 'users.id')
                            ->select('online_classes.id', 'online_classes.class_name', 'online_classes.status', 'users.name as teacher_name', 'classes.name as origional_className', 'batches.name as batch_name')
                            ->where('online_classes.class_id',$student_rec->class_id)
                            ->where('online_classes.batch_id',$student_rec->batch_id)
                            ->where('online_classes.status','ongoing')
                            ->whereNull('online_classes.deleted_at')->get()->all();
                        }
                        
                        
                        if($data)
                        {
                            foreach($data as $d){
                                $d->id  = (int) $d->id;
                            }
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'online class found successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No online class found!',
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
                            'message' => 'Class/Batch not found!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                    }
                    
                    
                    
                    // $class = $this->db->select('class_name,u.name as teacher')->from('sh_online_classes o')->join('sh_users u','u.id = o.started_by')->where('o.class_id', $student->class_id)->where('o.batch_id', $student->batch_id)->where('o.status', 'ongoing')->where('o.deleted_at is null')->get()->row();
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
                    'message' => 'Invalid user!',
                    'data' => ''
                );
                return response()->json($this->response, 400);
            }
        }
    }
    
    //Teacher side online classes
    public function get_teacherOnlineclasses(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('class_id') && $request->input('class_id')!='')
                {
                    if($request->input('batch_id') && $request->input('batch_id')!='')
                    {
                        $api_token=$request->input('api_token');
                        $user = DB::table('users')
                                ->where(function ($match) use ($api_token){
                                    $match->where('api_token', $api_token)
                                    ->orWhere('id', $api_token);
                                })
                                ->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                                
                        if($user)
                        {
                            $data=array();
                            if($request->input('date') && $request->input('date')!='')
                            {
                                $data=DB::table('online_classes')
                                ->join('users', 'online_classes.started_by', '=', 'users.id')
                                ->join('classes', 'online_classes.class_id', '=', 'classes.id', 'left')
                                ->join('batches', 'online_classes.batch_id', '=', 'batches.id', 'left')
                                ->select('online_classes.id', 'online_classes.class_name', 'online_classes.status', 'users.name as teacher_name', 'classes.name as origional_className', 'batches.name as batch_name')
                                ->where('online_classes.class_id',$request->input('class_id'))
                                ->where('online_classes.batch_id',$request->input('batch_id'))
                                ->where('online_classes.status','ongoing')
                                ->where(function ($match) use ($user){
                                        $match->whereDate('online_classes.created_at', '=', Carbon::today()->toDateString())
                                        ->orWhereDate('online_classes.updated_at', '=', Carbon::today()->toDateString());
                                })->whereNull('online_classes.deleted_at')->get()->all();
                            }
                            else
                            {
                                $data=DB::table('online_classes')
                                ->join('users', 'online_classes.started_by', '=', 'users.id')
                                ->join('classes', 'online_classes.class_id', '=', 'classes.id', 'left')
                                ->join('batches', 'online_classes.batch_id', '=', 'batches.id', 'left')
                                ->select('online_classes.id', 'online_classes.class_name', 'online_classes.status', 'users.name as teacher_name', 'classes.name as origional_className', 'batches.name as batch_name')
                                ->where('online_classes.class_id',$request->input('class_id'))
                                ->where('online_classes.batch_id',$request->input('batch_id'))
                                ->where('online_classes.status','ongoing')
                                ->whereNull('online_classes.deleted_at')->get()->all();
                            }
                            
                            if($data)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'online class found successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No online class found!',
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
                            'status' => 'success',
                            'message' => 'Batch Id is required!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Class Id is required!',
                        'data' => $data
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
    
        
    //Teacher side Create online classes
    public function create_onlineclass(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('class_id') && $request->input('class_id')!='')
                {
                    if($request->input('batch_id') && $request->input('batch_id')!='')
                    {
                        if($request->input('class_name') && $request->input('class_name')!='')
                        {
                            $api_token=$request->input('api_token');
                            $user = DB::table('users')
                                    ->where(function ($match) use ($api_token){
                                        $match->where('api_token', $api_token)
                                        ->orWhere('id', $api_token);
                                    })
                                    ->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                                    
                            if($user)
                            {
                                $result=DB::table('online_classes')
                                ->where('class_id', $request->input('class_id'))
                                ->where('batch_id', $request->input('batch_id'))
                                ->update([
                                    'class_name' => $request->input('class_name'),
                                    'started_by' => $user->id,
                                    'status' => 'ongoing'
                                    ]);
                                
                                if($result)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'online class created successfully!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Same class can not be created again!',
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
                                'status' => 'success',
                                'message' => 'Class name is required!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Batch Id is required!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Class Id is required!',
                        'data' => $data
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
    
            
    //Teacher side End online classes
    public function end_onlineclass(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('class_id') && $request->input('class_id')!='')
                {
                    if($request->input('batch_id') && $request->input('batch_id')!='')
                    {
                        $api_token=$request->input('api_token');
                        $user = DB::table('users')
                                ->where(function ($match) use ($api_token){
                                    $match->where('api_token', $api_token)
                                    ->orWhere('id', $api_token);
                                })
                                ->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                                
                        if($user)
                        {
                            $result=DB::table('online_classes')
                            ->where('class_id', $request->input('class_id'))
                            ->where('batch_id', $request->input('batch_id'))
                            ->update(['status' => 'done']);
                            
                            if($result)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'online class finished successfully!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Class is already finished!',
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
                            'status' => 'success',
                            'message' => 'Batch Id is required!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Class Id is required!',
                        'data' => $data
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
