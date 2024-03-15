<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class BooksController extends Controller
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


    public function get_bookDetail(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('book_id') && $request->input('book_id')!='')
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
                        $data=DB::table('book_shop')->select('*')->where('id',$request->input('book_id'))->whereNull('deleted_at')->get()->all();
                        if($data)
                        {
                            foreach($data as $key => $dd)
                            {
                                $data[$key]->id=(int) $dd->id;
                                $data[$key]->school_id=(int) $dd->school_id;
                                $data[$key]->class_id=(int) $dd->class_id;
                                $data[$key]->link='https://'.$dd->link;
                            }
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Books fetch successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No books found!',
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
                        'message' => 'please select book!',
                        'data' => ''
                    );
                    return response()->json($this->response, 400);
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
    
    

    public function get_books(Request $request)
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
                    $student_rec = DB::select("SELECT s.class_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                    if(sizeof($student_rec) > 0)
                    {
                        $student_rec=$student_rec[0];
                        if($student_rec->class_id != "" && $student_rec->class_id != null && $student_rec->class_id != 0)
                        {
                            DB::enableQueryLog();
                            $data=DB::table('book_shop')->select('id','title','price','picture','link')->where('school_id',$user->school_id)->where('class_id',$student_rec->class_id)->whereNull('deleted_at')->get()->all();
                            if($data)
                            {
                                foreach($data as $key => $dd)
                                {
                                    $data[$key]->id=(int) $dd->id;
                                }
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Books fetch successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No books found!',
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
                                'message' => 'Class/Batch not assigned!',
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
                            'message' => 'Class/Batch not assigned!',
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
                    'message' => 'Invalid user!',
                    'data' => ''
                );
                return response()->json($this->response, 400);
            }
        }
    }
    
    
    
    
    
}
