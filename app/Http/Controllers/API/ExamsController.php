<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
 

class ExamsController extends Controller
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

    //Teacher - Create Exam - Add Activity
    public function delete_examActivity(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('activity_id') && $request->input('activity_id')!='')
                {
                    $api_token=$request->input('api_token');
                    $user = DB::table('users')
                            ->where(function ($match) use ($api_token){
                                $match->where('api_token', $api_token)
                                ->orWhere('id', $api_token);
                            })
                            ->where('deleted_at', '=', '0')
                            ->first();
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        
                        $result=DB::table('exam_activities')->where('id',$request->input('activity_id'))->update(['deleted_at' => date('Y-m-d h:i:s')]);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Activity deleted successfully!',
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
                                'message' => 'Something went wrong, please try again!',
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
                        'status' => 'success',
                        'message' => 'Activity Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    //Teacher - Create Exam - Add Activity
    public function update_examActivity(Request $request)
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
                        if($request->input('activity_name') && $request->input('activity_name')!='')
                        {
                            if($request->input('subject_ids') && $request->input('subject_ids')!='')
                            {
                                if($request->input('activity_id') && $request->input('activity_id')!='')
                                {
                                    $api_token=$request->input('api_token');
                                    $user = DB::table('users')
                                            ->where(function ($match) use ($api_token){
                                                $match->where('api_token', $api_token)
                                                ->orWhere('id', $api_token);
                                            })
                                            ->where('deleted_at', '=', '0')
                                            ->first();
                                    if($user)
                                    {
                                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                        
                                        $data=array(
                                            'activity_name' => $request->input('activity_name'),
                                            'class_id' => $request->input('class_id'),
                                            'batch_id' => $request->input('batch_id'),
                                            'subject_ids' => implode(',', $request->input('subject_ids')),
                                            'school_id' => $user->school_id,
                                            'academic_year_id' => $academic_year->id
                                        );
                                        $result=DB::table('exam_activities')->where('id', $request->input('activity_id'))->update($data);
                                        if($result)
                                        {
                                            $this->response=array
                                            (
                                                'status' => 'success',
                                                'message' => 'Activity updated successfully!',
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
                                                'message' => 'Changes not found!',
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
                                        'status' => 'success',
                                        'message' => 'Activity Id is required!',
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
                                    'message' => 'Subject Id is required!',
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
                                'message' => 'Activity Name is required!',
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    //Teacher - Create Exam - Add Activity
    public function create_examActivity(Request $request)
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
                        if($request->input('activity_name') && $request->input('activity_name')!='')
                        {
                            if($request->input('subject_ids') && $request->input('subject_ids')!='')
                            {
                                $api_token=$request->input('api_token');
                                $user = DB::table('users')
                                        ->where(function ($match) use ($api_token){
                                            $match->where('api_token', $api_token)
                                            ->orWhere('id', $api_token);
                                        })
                                        ->where('deleted_at', '=', '0')
                                        ->first();
                                if($user)
                                {
                                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                    
                                    $data=array(
                                        'activity_name' => $request->input('activity_name'),
                                        'class_id' => $request->input('class_id'),
                                        'batch_id' => $request->input('batch_id'),
                                        'subject_ids' => implode(',', $request->input('subject_ids')),
                                        'school_id' => $user->school_id,
                                        'academic_year_id' => $academic_year->id
                                    );
                                    $result=DB::table('exam_activities')->insert($data);
                                    if($result)
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Activity created successfully!',
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
                                            'message' => 'Please input correct values!',
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
                                    'status' => 'success',
                                    'message' => 'Subject Id is required!',
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
                                'message' => 'Activity Name is required!',
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    //Teacher - Create Exam - Add Activity
    public function get_booksActivity(Request $request)
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
                                ->where('deleted_at', '=', '0')
                                ->first();
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            
                            $is_batchTeacher=DB::table('batches')->select('*')->where('teacher_id', $user->id)->where('class_id', $request->input('class_id'))->where('id', $request->input('batch_id'))->where('academic_year_id', $academic_year->id)->first();
                            
                            if($is_batchTeacher)
                            {
                                $subjects=DB::table('batches')
                                ->join('subjects', 'batches.id', '=', 'subjects.batch_id', 'left')
                                ->select('subjects.id as id', 'subjects.name', 'batches.id as batch_id')
                                ->where('batches.class_id', $request->input('class_id'))
                                ->where('batches.academic_year_id', $academic_year->id)
                                ->whereNull('batches.deleted_at')
                                ->where('subjects.class_id', $request->input('class_id'))
                                ->where('subjects.batch_id', $request->input('batch_id'))
                                ->where('subjects.academic_year_id', $academic_year->id)
                                ->whereNull('subjects.deleted_at')
                                ->get()->all();
                                
                                if(sizeof($subjects) > 0)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Subjects fetch successfully!',
                                        'data' => $subjects
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Subjects not found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                            else
                            {
                                $subjects=DB::table('assign_subjects')
                                ->join('subjects', 'assign_subjects.subject_id', '=', 'subjects.id', 'left')
                                ->select('assign_subjects.subject_id as id', 'subjects.name')
                                ->where(function ($match) use ($user){
                                        $match->where('assign_subjects.teacher_id', $user->id)
                                        ->orWhere('assign_subjects.assistant_id', $user->id);
                                })
                                ->where('assign_subjects.class_id', $request->input('class_id'))
                                ->where('assign_subjects.batch_id', $request->input('batch_id'))
                                ->whereNull('assign_subjects.deleted_at')
                                ->where('subjects.class_id', $request->input('class_id'))
                                ->where('subjects.batch_id', $request->input('batch_id'))
                                ->where('subjects.academic_year_id', $academic_year->id)
                                ->whereNull('subjects.deleted_at')
                                ->get()->all();
                                
                                if(sizeof($subjects) > 0)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Subjects fetch successfully!',
                                        'data' => $subjects
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Subjects not found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    //Teacher - Create Exam - Add Exam
    public function create_exam(Request $request)
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
                        if($request->input('title') && $request->input('title')!='')
                        {
                            if($request->input('start_date') && $request->input('start_date')!='')
                            {
                                if($request->input('end_date') && $request->input('end_date')!='')
                                {
                                    $api_token=$request->input('api_token');
                                    $user = DB::table('users')
                                            ->where(function ($match) use ($api_token){
                                                $match->where('api_token', $api_token)
                                                ->orWhere('id', $api_token);
                                            })
                                            ->where('deleted_at', '=', '0')
                                            ->first();
                                    if($user)
                                    {
                                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                        $data=array(
                                            'title' => $request->input('title'),
                                            'start_date' => date("Y-m-d", strtotime($request->input('start_date'))),
                                            'end_date' => date("Y-m-d", strtotime($request->input('end_date'))),
                                            'school_id' => $user->school_id,
                                            'academic_year_id' => $academic_year->id
                                        );
                                        $result=DB::table('exams')->insert($data);
                                        if($result)
                                        {
                                            $this->response=array
                                            (
                                                'status' => 'success',
                                                'message' => 'Exam created successfully!',
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
                                                'message' => 'Please input correct values!',
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
                                        'status' => 'success',
                                        'message' => 'End date is required!',
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
                                    'message' => 'Start date is required!',
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
                                'message' => 'Title is required!',
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    //Teacher - Create Exam - Add Exam
    public function update_exam(Request $request)
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
                        if($request->input('title') && $request->input('title')!='')
                        {
                            if($request->input('start_date') && $request->input('start_date')!='')
                            {
                                if($request->input('end_date') && $request->input('end_date')!='')
                                {
                                    if($request->input('exam_id') && $request->input('exam_id')!='')
                                    {
                                        $api_token=$request->input('api_token');
                                        $user = DB::table('users')
                                                ->where(function ($match) use ($api_token){
                                                    $match->where('api_token', $api_token)
                                                    ->orWhere('id', $api_token);
                                                })
                                                ->where('deleted_at', '=', '0')
                                                ->first();
                                        if($user)
                                        {
                                            $data=array(
                                                'title' => $request->input('title'),
                                                'start_date' => date("Y-m-d", strtotime($request->input('start_date'))),
                                                'end_date' => date("Y-m-d", strtotime($request->input('end_date')))
                                            );
                                            $result=DB::table('exams')->where('id',$request->input('exam_id'))->update($data);
                                            if($result) 
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Exam updated successfully!',
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
                                                    'message' => 'Please input correct values!',
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
                                            'status' => 'success',
                                            'message' => 'Exam Id is required!',
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
                                        'message' => 'End date is required!',
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
                                    'message' => 'Start date is required!',
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
                                'message' => 'Title is required!',
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    //Teacher - Create Exam - Delete Exam
    public function delete_exam(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('exam_id') && $request->input('exam_id')!='')
                {
                    $api_token=$request->input('api_token');
                    $user = DB::table('users')
                            ->where(function ($match) use ($api_token){
                                $match->where('api_token', $api_token)
                                ->orWhere('id', $api_token);
                            })
                            ->where('deleted_at', '=', '0')
                            ->first();
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();

                        $result=DB::table('exams')->where('id',$request->input('exam_id'))->update(['deleted_at' => date('Y-m-d h:i:s')]);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Exam deleted successfully!',
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
                                'message' => 'Please try again!',
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
                        'status' => 'success',
                        'message' => 'Exam Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    //Teacher - Homepage of exams list
    public function teacher_exams(Request $request)
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
                                ->where('deleted_at', '=', '0')
                                ->first();
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            
                            $subject_groups=DB::table('subject_groups')->select('*')->where('class_id',$request->input('class_id'))->where('batch_id',$request->input('batch_id'))->where('academic_year_id',$academic_year->id)->whereNull('deleted_at')->first();
                            
                            // $student = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");

                                if($subject_groups)
                                {
                                    $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$subject_groups->id)->whereNull('deleted_at')->first()->subjects; 
                                    $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );
                                    foreach($subject_ids as $key => $ids)
                                    {
                                        $subject_ids[$key]=(int)$ids;
                                    }
                                    
                                    // DB::enableQueryLog();
                                    $response=DB::table('exams')
                                        ->join('exam_details', 'exams.id', '=', 'exam_details.exam_id', 'left')
                                        // ->select('exams.id as exam_id','exams.title as exam_name', 'exam_details.id as examdetail_id')
                                        ->select('exams.id as exam_id','exams.title as exam_name')
                                        ->where('exams.school_id',$user->school_id)
                                        ->where('exams.academic_year_id',$academic_year->id)
                                        ->whereNull('exams.deleted_at')
                                        ->where('exam_details.class_id',$request->input('class_id'))
                                        ->where('exam_details.batch_id',$request->input('batch_id'))
                                        ->whereIN('exam_details.subject_id',$subject_ids)
                                        ->whereNull('exam_details.deleted_at')
                                        ->where('exam_details.academic_year_id',$academic_year->id)
                                        ->groupBy('exams.id')
                                        ->orderBy('exams.id','asc')
                                        ->get()->all();
                                    
                                    //convert to array
                                    // $response = json_decode(json_encode($response, true), true);
                                    
                                    //Getch some specific column
                                    // $uniquePids = array_unique(array_map(function ($i) { return $i['exam_id']; }, $response));
                                    
                                    // $response=array_values($response);
                                    // $response = array_unique($response,SORT_REGULAR);
                                    if($response)
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Exams fetch successfully',
                                            'data' => $response
                                        );
                                        return response()->json($this->response, 200);
                                        die;
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'No exams found!',
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
                                        'message' => 'Subject Group is not assigned!',
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
                            'status' => 'success',
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    public function student_exams(Request $request)
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
                        ->where('deleted_at', '=', '0')
                        ->first();
                if($user)
                {
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $student = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                    
                    if(sizeof($student) > 0)
                    {
                        $student=$student[0];
                        if($student->subject_group_id != "" && $student->subject_group_id != null && $student->subject_group_id != 0)
                        {
                            $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$student->subject_group_id)->whereNull('deleted_at')->first()->subjects; 
                            $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );
                            foreach($subject_ids as $key => $ids)
                            {
                                $subject_ids[$key]=(int)$ids;
                            }
                            
                            // DB::enableQueryLog();
                            $response=DB::table('exams')
                                ->join('exam_details', 'exams.id', '=', 'exam_details.exam_id', 'left')
                                // ->select('exams.id as exam_id','exams.title as exam_name', 'exam_details.id as examdetail_id')
                                ->select('exams.id as exam_id','exams.title as exam_name')
                                ->where('exams.school_id',$user->school_id)
                                ->where('exams.academic_year_id',$academic_year->id)
                                ->whereNull('exams.deleted_at')
                                ->where('exam_details.class_id',$student->class_id)
                                ->where('exam_details.batch_id',$student->batch_id)
                                ->whereIN('exam_details.subject_id',$subject_ids)
                                ->whereNull('exam_details.deleted_at')
                                ->where('exam_details.academic_year_id',$academic_year->id)
                                ->groupBy('exams.id')
                                ->orderBy('exams.id','asc')
                                ->get()->all();
                            
                            //convert to array
                            // $response = json_decode(json_encode($response, true), true);
                            
                            //Getch some specific column
                            // $uniquePids = array_unique(array_map(function ($i) { return $i['exam_id']; }, $response));
                            
                            // $response=array_values($response);
                            // $response = array_unique($response,SORT_REGULAR);
                            if($response)
                            {
                                foreach($response as $key => $res)
                                {
                                    $response[$key]->exam_id=(int) $res->exam_id;
                                }
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Exams fetch successfully',
                                    'data' => $response
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No exams found!',
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
                                'message' => 'Subject Group is not assigned!',
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
                            'message' => 'Class & Batch is not assigned!',
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    public function student_marksheet(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('exam_id') && $request->input('exam_id')!='')
                {
                    $api_token=$request->input('api_token');
                    $user = DB::table('users')
                            ->where(function ($match) use ($api_token){
                                $match->where('api_token', $api_token)
                                ->orWhere('id', $api_token);
                            })
                            ->where('deleted_at', '=', '0')
                            ->first();
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $student_rec = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id FROM sh_students_$user->school_id s WHERE s.id='$user->id' ");
                        if(sizeof($student_rec) > 0)
                        {
                            $student_rec=$student_rec[0];
                            
                            $passsing_variation=DB::table('passing_rules')->select('*')->where('class_id',$student_rec->class_id)->where('batch_id',$student_rec->batch_id)->where('school_id',$user->school_id)->where('exam_id', $request->input('exam_id'))->whereNull('deleted_at')->first();
                            
                            // echo '<pre>';
                            // print_r($passsing_variation);
                            
                            if($passsing_variation)
                            {
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Passing rules are not defined!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 200);
                                die;
                            }

                            $passsing_value=$passsing_variation;
                            // $passsing_value=DB::table('passing_rules')->select('*')->where('class_id',$student_rec->class_id)->where('batch_id',$student_rec->batch_id)->where('school_id',$user->school_id)->where('exam_id', $request->input('exam_id'))->whereNull('deleted_at')->first();
                            if($passsing_value)
                            {
                                $passsing_value=(int) $passsing_value->minimum_percentage;
                            }
                            else
                            {
                                $passsing_value='0.00';
                            }
                            
                            
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $response=DB::table('exams')
                            ->join('exam_details', 'exams.id', '=', 'exam_details.exam_id', 'left')
                            ->join('marksheets', 'exam_details.id', '=', 'marksheets.exam_detail_id', 'left')
                            ->join('subjects', 'exam_details.subject_id', '=', 'subjects.id', 'left')
                            // ->select('exams.id as exam_id','exams.title as exam_name', 'exam_details.id as examdetail_id', 'subjects.name', 'exam_details.total_marks', 'marksheets.obtained_marks')
                            ->select('exams.id as exam_id','exams.title as exam_name', 'subjects.name as subject_name', 'exam_details.total_marks', 'marksheets.obtained_marks', 'marksheets.activities', 'marksheets.total_obtained_marks', 'marksheets.total_grade', 'exam_details.passing_marks', 'marksheets.remarks', 'exam_details.total_exam_marks')
                            ->whereNull('exams.deleted_at')
                            ->whereNull('exam_details.deleted_at')
                            ->whereNull('marksheets.deleted_at')
                            ->whereNull('subjects.deleted_at')
                            ->where('exams.id',$request->input('exam_id'))
                            ->where('marksheets.student_id',$user->id)
                            ->where('exams.academic_year_id',$academic_year->id)
                            ->where('exam_details.academic_year_id',$academic_year->id)
                            ->where('exam_details.published','yes')
                            ->where('subjects.academic_year_id',$academic_year->id)
                            ->get()->all();
                            
                           
                            
                            if($response)
                            {
                                $total_marks=0;
                                $obtained_marks=0;
                                $overall_status='Pass';
                                $pass_cpunter=0;
                                
                                $grades=DB::table('grades')->where('school_id',$user->school_id)->whereNull('deleted_at')->get()->all();
                                
                                foreach($response as $key => $res)
                                {
                                    $response[$key]->exam_id=(int) $res->exam_id;
                                    $response[$key]->total_marks=(int) $res->total_marks;
                                    $response[$key]->obtained_marks=(int) $res->obtained_marks;
                                    $response[$key]->total_obtained_marks=(int) $res->total_obtained_marks;
                                    $response[$key]->passing_marks=(int) $res->passing_marks;
                                    $response[$key]->total_exam_marks=(int) $res->total_exam_marks;
                                    
                                    $total_marks=$total_marks+$res->total_marks;
                                    $obtained_marks=$obtained_marks+$res->obtained_marks;
                                    $status='Fail';
                                    if($res->obtained_marks > $res->passing_marks)
                                    {
                                        $status='Pass';
                                        $pass_cpunter=$pass_cpunter+1;
                                    }
                                    $response[$key]->status=$status;
                                    
                                    // $json_activities=json_decode($res->activities);
                                    
                                    if(empty($res->activities))
                                    {
                                        $response[$key]->activities=[];
                                    }
                                    else
                                    {
                                        $json_activities=json_decode($res->activities);
                                        
                                        
                                    
                                        if($json_activities!='' && $json_activities!=null) 
                                        {
                                            $fetched_activities=json_decode($res->activities);
                                            
                                           
                                            //for khalid easyness
                                            foreach($fetched_activities as $zkey => $feth_act)
                                            {
                                                $fetched_activities[$zkey]->id=(int) $feth_act->id;
                                                $fetched_activities[$zkey]->class_id=(int) $feth_act->class_id;
                                                $fetched_activities[$zkey]->batch_id=(int) $feth_act->batch_id;
                                                $fetched_activities[$zkey]->school_id=(int) $feth_act->school_id;
                                                $fetched_activities[$zkey]->academic_year_id=(int) $feth_act->academic_year_id;
                                                
                                                $activity_arrayss=(array)$feth_act;
                                                
                                                // echo '<pre>';
                                                // print_r($activity_arrayss);
                                                // die;
                                                if(isset($activity_arrayss[$zkey]->obtained_marks))
                                                {
                                                    $fetched_activities[$zkey]->obtain_marks=$activity_arrayss[$zkey]->obtained_marks;
                                                }
                                                else
                                                {
                                                    $fetched_activities[$zkey]->obtain_marks=0;
                                                }
                                                
                                            }
                                            
                                            $response[$key]->activities=$fetched_activities;
                                            
                                            if(sizeof($fetched_activities) > 0)
                                            {
                                                foreach($fetched_activities as $fet_key => $fetch_act)
                                                {
                                                    $activity_array=(array)$fetch_act;

                                                    // $response[$key]->obtained_marks=$response[$key]->obtained_marks+$activity_array[0]->obtained_marks;
                                                    // $response[$key]->obtained_marks=$response[$key]->obtained_marks+$activity_array[$fet_key]->obtained_marks;
                                                    if(isset($activity_array[$fet_key]->obtained_marks))
                                                    {
                                                        $obtained_marks=$obtained_marks+$activity_array[$fet_key]->obtained_marks;
                                                    }
                                                    else
                                                    {
                                                        $obtained_marks=$obtained_marks+0;
                                                    }
                                                    
                                                    // $obtained_marks=$obtained_marks+$activity_array[0]->obtained_marks;
                                                
                                                    //commented because already added in the database
                                                    // $response[$key]->total_obtained_marks=$response[$key]->total_obtained_marks+$activity_array[$fet_key]->obtained_marks;
                                                    // $response[$key]->total_obtained_marks=$response[$key]->total_obtained_marks+$activity_array[0]->obtained_marks;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $response[$key]->activities=[];
                                        }
                                    }
                                    
                                    
                                    if($response[$key]->total_obtained_marks >= $response[$key]->passing_marks)
                                    {
                                        $response[$key]->status='Pass';
                                    }
                                    else
                                    {
                                        $response[$key]->status='Fail';
                                    }
                                    

                                    foreach($grades as $sub_key => $grade)
                                    {
                                        $subject_percent=round(($response[$key]->obtained_marks/$response[$key]->total_marks)*100,2);
                                        
                                        if($grade->percent_from <= $subject_percent && $grade->percent_upto >= $subject_percent)
                                        {
                                            $response[$key]->grade=$grade->name;
                                            break;
                                        }
                                        else
                                        {
                                            $response[$key]->grade="";
                                        }
                                    }
                                    
                                }
                                
                                
                                // $obtained_percent=($obtained_marks/$total_marks)*100;
                                $obtained_percent=round(($obtained_marks/$total_marks)*100,2);
                                
                                
                                
                                if($passsing_variation->operator=='OR')
                                {
                                    if($passsing_variation->minimum_percentage <= $obtained_percent || $passsing_variation->minimum_subjects <= $pass_cpunter)
                                    {
                                        $overall_status='Pass';
                                    }
                                    else
                                    {
                                        $overall_status='Fail';
                                    }
                                }
                                else if($passsing_variation->operator=='AND')
                                {
                                    if($passsing_variation->minimum_percentage <= $obtained_percent && $passsing_variation->minimum_subjects <= $pass_cpunter)
                                    {
                                        $overall_status='Pass';
                                    }
                                    else
                                    {
                                        $overall_status='Fail';
                                    }
                                }
                                
                                
                                if(is_int($passsing_value))
                                {
                                    $passsing_value=round($passsing_value,2);
                                    $passsing_value=number_format((float)$passsing_value, 2, '.', '');
                                }
                                
                                $obtained_percent=number_format((float)$obtained_percent, 2, '.', '');
                                
                                $position_name='';
                                $position=DB::table('remarks_and_positions')->select('position')->where('exam_id', $request->input('exam_id'))->where('student_id',$user->id)->whereNull('deleted_at')->first();
                                if($position)
                                {
                                    $position_name=$position->position;
                                }
                                
                                if(sizeof($grades) > 0)
                                {
                                    foreach($grades as $k => $gra)
                                    {
                                        $grades[$k]->id=(int) $gra->id;
                                        $grades[$k]->school_id=(int) $gra->school_id;
                                    }
                                    
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Mark sheet fetch successfully!',
                                        'data' => $response,
                                        'position' => $position_name,
                                        'id' => (int) $user->id,
                                        'exam_id' => (int) $request->input('exam_id'),
                                        'class_id' => (int) $student_rec->class_id,
                                        'batch_id' => (int) $student_rec->batch_id,
                                        'academic_year_id' => (int) $academic_year->id,
                                        'school_id' => (int) $user->school_id,
                                        'total_marks' => $total_marks,
                                        'obtained_marks' => $obtained_marks,
                                        'overall_status' => $overall_status,
                                        'passing_percentage' => $passsing_value,
                                        'obtained_percentage' => $obtained_percent,
                                        // 'user_detail' => $user,
                                        'grading_detail' => $grades
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Mark sheet fetch successfully!',
                                        'data' => $response,
                                        'position' => $position_name,
                                        'id' => $user->id,
                                        'exam_id' => (int) $request->input('exam_id'),
                                        'class_id' => $student_rec->class_id,
                                        'batch_id' => $student_rec->batch_id,
                                        'academic_year_id' => $academic_year->id,
                                        'school_id' => $user->school_id,
                                        'total_marks' => $total_marks,
                                        'obtained_marks' => $obtained_marks,
                                        'overall_status' => $overall_status,
                                        'passing_percentage' => $passsing_value,
                                        'obtained_percentage' => $obtained_percent,
                                        // 'user_detail' => $user,
                                        'grading_detail' => $grades
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
                                    'message' => 'No result card found!',
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
                                'message' => 'Class/Batch not assigned!',
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
                        'message' => 'Please select exam!',
                        'data' => ''
                    );
                    return response()->json($this->response, 400);
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
                return response()->json($this->response, 400);
                die;
            }
        }
    }
    
    
    
    
    
}
