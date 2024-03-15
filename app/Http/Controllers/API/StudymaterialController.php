<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class StudymaterialController extends Controller
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

    //Parent side api for all childs
    public function parent_studymaterial(Request $request)
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
                    $students=DB::table('student_guardians')->select('student_id')->where('guardian_id',$user->id)->whereNull('deleted_at')->get()->all();
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $student_record=array();
                    
                    if(sizeof($students) > 0)
                    {
                        foreach($students as $stu)
                        {
                            $student_rec = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$stu->student_id' ");

                            $student_rec=$student_rec[0];
                            if($student_rec->subject_group_id != "" && $student_rec->subject_group_id != null && $student_rec->subject_group_id != 0)
                            {
                                $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$student_rec->subject_group_id)->whereNull('deleted_at')->first()->subjects;
                                $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );
    
                                foreach($subject_ids as $key => $ids)
                                {
                                    $subject_ids[$key]=(int)$ids;
                                }
                                $current_date = date("Y-m-d");
                                // $current_date = '2021-03-05';
                               
                            //   DB::enableQueryLog();
                                $data=DB::table('study_material')
                                ->join('classes', 'study_material.class_id', '=', 'classes.id')
                                ->join('batches', 'study_material.batch_id', '=', 'batches.id','left')
                                ->join('subjects', 'study_material.subject_code', '=', 'subjects.code', 'left')
                                ->join('users', 'study_material.uploaded_by', '=', 'users.id', 'left')
                                ->selectRaw('sh_users.id as student_id,sh_users.name,sh_users.avatar,sh_study_material.*,sh_classes.name as class_name,sh_batches.name as batch_name,sh_subjects.name as subject_name,sh_users.name,DATE_FORMAT("sh_study_material.uploaded_at","%d/%m/%Y") as uploaded_time')
                                ->where('study_material.delete_status', 0)
                                ->where('study_material.school_id', $user->school_id)
                                ->where('study_material.uploaded_at', $current_date)
                                ->where('classes.academic_year_id', $academic_year->id)
                                ->where('study_material.class_id', $student_rec->class_id)
                                ->whereIn('study_material.batch_id',[$student_rec->batch_id])
                                ->whereIn('study_material.subject_id',$subject_ids)
                                ->groupBy('study_material.id')->orderBy('study_material.uploaded_at','desc')
                                ->get()->all();
                                
    
                                if(sizeof($data) > 0)
                                {
                                    foreach($data as $key => $rec)
                                    {
                                        $data[$key]->files=explode(',',$rec->files);
                                        $data[$key]->filesurl=explode(',',$rec->filesurl);
                                        $data[$key]->file_names=explode(',',$rec->file_names);
                                        $data[$key]->fileids=explode(',',$rec->fileids);
                                        $data[$key]->thumbnail_links=explode(',',$rec->thumbnail_links);
                                        $data[$key]->icon_links=explode(',',$rec->icon_links);
                                    }
                                    $student_record[]=$data;
                                }
                                
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Subject groups are not assigned!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                        }
                        
                        if($student_record && sizeof($student_record) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'StudyMaterial fetch successfully!',
                                'data' => $student_record
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No StudyMaterial found!',
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
                            'message' => 'No childs found!',
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
  
    
  
    //Teacher side API
    public function teacher_studymaterial(Request $request)
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
                                ->whereIn('role_id', [2,3,4])
                                ->where('deleted_at', '=', '0')
                                ->first();
            
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $current_date = date("Y-m-d");
                           
                            $data=DB::table('study_material')
                            ->join('classes', 'study_material.class_id', '=', 'classes.id')
                            ->join('batches', 'study_material.batch_id', '=', 'batches.id','left')
                            // ->join('subjects', 'study_material.subject_code', '=', 'subjects.code', 'left')
                            ->join('subjects', 'study_material.subject_id', '=', 'subjects.id', 'left')
                            ->join('users', 'study_material.uploaded_by', '=', 'users.id', 'left')
                            ->selectRaw('sh_study_material.*,sh_classes.name as class_name,sh_batches.name as batch_name,sh_subjects.name as subject_name, DATE_FORMAT("sh_study_material.uploaded_at","%d/%m/%Y") as uploaded_time, sh_users.id as creator_id, sh_users.name as creator_name, sh_users.avatar as creator_avatar')
                            ->where('study_material.delete_status', 0)
                            ->where('study_material.school_id', $user->school_id)
                            ->where('classes.academic_year_id', $academic_year->id)
                            ->where('study_material.class_id', $request->input('class_id'))
                            ->whereIn('study_material.batch_id',[$request->input('batch_id')])
                            ->groupBy('study_material.id')->orderBy('study_material.uploaded_at','desc');
                            if($request->input('date') && $request->input('date')!='')
                            {
                                $data=$data->where('study_material.uploaded_at', date("Y-m-d",strtotime($request->input('date'))))->get()->all();
                            }
                            else
                            {
                                $data=$data->where('study_material.uploaded_at', $current_date)->get()->all();
                            }
        
                            if($data && sizeof($data) > 0)
                            {
                                foreach($data as $key => $rec)
                                {
                                    if($rec->files!='')
                                    {
                                        $data[$key]->files=explode(',',$rec->files);
                                    }
                                    else
                                    {
                                        $data[$key]->files=array();
                                    }
                                    
                                    if($rec->filesurl!='')
                                    {
                                        $data[$key]->filesurl=explode(',',$rec->filesurl);
                                    }
                                    else
                                    {
                                        $data[$key]->filesurl=array();
                                    }
                                    
                                    if($rec->file_names!='')
                                    {
                                        $data[$key]->file_names=explode(',',$rec->file_names);
                                    }
                                    else
                                    {
                                        $data[$key]->file_names=array();
                                    }
                                    
                                    if($rec->fileids!='')
                                    {
                                        $data[$key]->fileids=explode(',',$rec->fileids);
                                    }
                                    else
                                    {
                                        $data[$key]->fileids=array();
                                    }
                                    
                                    if($rec->thumbnail_links!='')
                                    {
                                        $data[$key]->thumbnail_links=explode(',',$rec->thumbnail_links);
                                    }
                                    else
                                    {
                                        $data[$key]->thumbnail_links=array();
                                    }
                                    
                                    if($rec->icon_links!='')
                                    {
                                        $data[$key]->icon_links=explode(',',$rec->icon_links);
                                    }
                                    else
                                    {
                                        $data[$key]->icon_links=array();
                                    }
                                }
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Study Material fetch successfully!',
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
                                    'message' => 'No studyMaterial found!',
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
            }
        }
    }
      
    //Student side Count API
    public function student_studymaterialDates(Request $request)
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $student = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                    if(sizeof($student) > 0)
                    {
                        $student=$student[0];
                        if($student->subject_group_id != "" && $student->subject_group_id != null && $student->subject_group_id != 0)
                        {
                            
                            $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$student->subject_group_id)->whereNull('deleted_at')->first()->subjects;

                            // $subject_ids=explode(',',$subject_ids); 
                            $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );

                            foreach($subject_ids as $key => $ids)
                            {
                                $subject_ids[$key]=(int)$ids;
                                // $subject_ids[$key]=$ids;
                                
                            }
                            
                            // echo '<pre>';
                            // print_r($subject_ids);
                            // die;
                            $current_date = date("Y-m-d");
                            // $current_date = '2021-03-05';
                            // DB::enableQueryLog();

// and then you can get query log

                            $student->batch_id=(int)$student->batch_id;
                           
                            $data=DB::table('study_material')
                            // ->join('classes', 'study_material.class_id', '=', 'classes.id')
                            // ->join('batches', 'study_material.batch_id', '=', 'batches.id','left')
                            // ->join('subjects', 'study_material.subject_code', '=', 'subjects.code', 'left')
                            // ->join('users', 'study_material.uploaded_by', '=', 'users.id', 'left')
                            // ->selectRaw('sh_study_material.uploaded_at as date')
                            ->select('study_material.uploaded_at as date')
                            ->where('study_material.delete_status', 0)
                            ->where('study_material.school_id', $user->school_id)
                            // ->where('classes.academic_year_id', $academic_year->id)
                            ->where('study_material.class_id', $student->class_id)
                            // ->whereIn('study_material.batch_id',[$student->batch_id])
                            ->whereRaw('FIND_IN_SET(?,sh_study_material.batch_id)', [$student->batch_id]);
                            // ->whereIn('study_material.subject_id',$subject_ids)
                            // ->groupBy('study_material.id')
                            
                            if(sizeof($subject_ids) > 0)
                            {
                                $data->where(function ($match) use ($subject_ids){
                                    foreach($subject_ids as $sbjids)
                                    {
                                        
                                        $match->orWhereRaw('FIND_IN_SET(?,sh_study_material.subject_id)', [$sbjids]);
                                        // ->whereIn('study_material.subject_id',$subject_ids)
                                    }
                                });
                            }
                            
                            $data=$data->orderBy('study_material.uploaded_at','desc')->get()->all();
                            
                            
                            
                            // if($request->input('date') && $request->input('date')!='')
                            // {
                            //     $data=$data->where('study_material.uploaded_at', date("Y-m-d",strtotime($request->input('date'))))->get()->all();
                            // }
                            // else
                            // {
                            //     $data=$data->where('study_material.uploaded_at', $current_date)->get()->all();
                            // }
                            
                            // echo '<pre>';
                            // print_r(DB::getQueryLog());
                            // echo '<pre>';
                            // print_r($data);
                            // die;
                            // echo json_encode('die');
                            // die;

                            if($data && sizeof($data) > 0)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Study Material fetch successfully!',
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
                                    'message' => 'No study material found!',
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
                                'message' => 'Subject groups are not assigned!',
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
                    'message' => 'Invalid user!',
                    'data' => ''
                );
            }
        }
    }
          
    //Student side API
    public function student_studymaterial(Request $request)
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $student = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                    if(sizeof($student) > 0)
                    {
                        $student=$student[0];
                        if($student->subject_group_id != "" && $student->subject_group_id != null && $student->subject_group_id != 0)
                        {
                            
                            $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$student->subject_group_id)->whereNull('deleted_at')->first()->subjects;

                            // $subject_ids=explode(',',$subject_ids); 
                            $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );

                            foreach($subject_ids as $key => $ids)
                            {
                                $subject_ids[$key]=$ids;
                                
                            }
                            
                            // echo '<pre>';
                            // var_dump($subject_ids);
                            // die;
                            $current_date = date("Y-m-d");
                            // $current_date = '2021-03-05';
                            
                            $student->batch_id=(int) $student->batch_id;
                            
                            DB::enableQueryLog();
                           
                            $data=DB::table('study_material')
                            ->join('classes', 'study_material.class_id', '=', 'classes.id')
                            ->join('batches', 'study_material.batch_id', '=', 'batches.id','left')
                            // ->join('subjects', 'study_material.subject_code', '=', 'subjects.code', 'left')
                            ->join('users', 'study_material.uploaded_by', '=', 'users.id', 'left')
                            // ->selectRaw('sh_study_material.*,sh_classes.name as class_name,sh_batches.name as batch_name,sh_subjects.name as subject_name, DATE_FORMAT("sh_study_material.uploaded_at","%d/%m/%Y") as uploaded_time, sh_users.id as creator_id, sh_users.name as creator_name, sh_users.avatar as creator_avatar')
                            ->selectRaw('sh_study_material.*,sh_classes.name as class_name,sh_batches.name as batch_name, DATE_FORMAT("sh_study_material.uploaded_at","%d/%m/%Y") as uploaded_time, sh_users.id as creator_id, sh_users.name as creator_name, sh_users.avatar as creator_avatar')
                            ->where('study_material.delete_status', 0)
                            ->where('study_material.school_id', $user->school_id)
                            ->where('classes.academic_year_id', $academic_year->id)
                            ->where('study_material.class_id', $student->class_id)
                            // ->whereIn('study_material.batch_id',[$student->batch_id])
                            ->whereRaw('FIND_IN_SET(?,sh_study_material.batch_id)', [$student->batch_id])
                            
                            ->groupBy('study_material.id')->orderBy('study_material.created_at','desc');
                            
                        //     ->where(function ($match) use ($api_token){
                        //     $match->where('api_token', $api_token)
                        //     ->orWhere('id', $api_token);
                        // })
                            if(sizeof($subject_ids) > 0)
                            {
                                $data->where(function ($match) use ($subject_ids){
                                    foreach($subject_ids as $sbjids)
                                    {
                                        
                                        $match->orWhereRaw('FIND_IN_SET(?,sh_study_material.subject_id)', [$sbjids]);
                                        // ->whereIn('study_material.subject_id',$subject_ids)
                                    }
                                });
                            }
                            
                            
                            if($request->input('date') && $request->input('date')!='')
                            {
                                $data=$data->where('study_material.uploaded_at', date("Y-m-d",strtotime($request->input('date'))))->get()->all();
                            }
                            else
                            {
                                $data=$data->where('study_material.uploaded_at', $current_date)->get()->all();
                            }
                            
                            // echo '<pre>';
                            // print_r(DB::getQueryLog());
                            // die;
                            

                            foreach($data as $key => $rec)
                            {
                                $data[$key]->id=(int) $rec->id;
                                $data[$key]->school_id=(int) $rec->school_id;
                                $data[$key]->class_id=(int) $rec->class_id;
                                $data[$key]->storage_type=(int) $rec->storage_type;
                                $data[$key]->delete_status=(int) $rec->delete_status;
                                $data[$key]->uploaded_by=(int) $rec->uploaded_by;
                                $data[$key]->creator_id=(int) $rec->creator_id;
                                
                                if($rec->subject_id!='')
                                {
                                    $allIdssSubjects=explode(',', $rec->subject_id);
                                    
                                    
                                    if(sizeof($allIdssSubjects) > 0)
                                    {
                                        $name=array();
                                        foreach($allIdssSubjects as $childKey => $allSbj)
                                        {
                                            $foundSubject=DB::table('subjects')->select('name')->where('id', $allSbj)->whereNull('deleted_at')->first();
                                            if($foundSubject)
                                            {
                                                $name[]=$foundSubject->name;
                                            }
                                            
                                        }
                                        $name=array_unique($name);
                                    //     echo '<pre>';
                                    // print_r($name);
                                    // die;
                                        $data[$key]->subject_name=implode(' ', $name);
                                    }
                                }
                                
                                if($rec->batch_id!='')
                                {
                                    $all_batchesids=explode(',', $rec->batch_id);
                                    if(sizeof($all_batchesids) > 0)
                                    {
                                        $name=array();
                                        foreach($all_batchesids as $childKey => $allbtchid)
                                        {
                                            $foundSubject=DB::table('batches')->select('name')->where('id', $allbtchid)->whereNull('deleted_at')->first();
                                            if($foundSubject)
                                            {
                                                $name[]=$foundSubject->name;
                                            }
                                        }
                                        $data[$key]->batch_name=implode(', ', $name);
                                    }
                                }
                                
                                if($rec->files!='')
                                {
                                    $data[$key]->files=explode(',',$rec->files);
                                }
                                else
                                {
                                    $data[$key]->files=array();
                                }
                                
                                if($rec->filesurl!='')
                                {
                                    $data[$key]->filesurl=explode(',',$rec->filesurl);
                                }
                                else
                                {
                                    $data[$key]->filesurl=array();
                                }
                                
                                if($rec->file_names!='')
                                {
                                    $data[$key]->file_names=explode(',',$rec->file_names);
                                }
                                else
                                {
                                    $data[$key]->file_names=array();
                                }
                                
                                if($rec->fileids!='')
                                {
                                    $data[$key]->fileids=explode(',',$rec->fileids);
                                }
                                else
                                {
                                    $data[$key]->fileids=array();
                                }
                                
                                if($rec->thumbnail_links!='')
                                {
                                    $data[$key]->thumbnail_links=explode(',',$rec->thumbnail_links);
                                }
                                else
                                {
                                    $data[$key]->thumbnail_links=array();
                                }
                                
                                if($rec->icon_links!='')
                                {
                                    $data[$key]->icon_links=explode(',',$rec->icon_links);
                                }
                                else
                                {
                                    $data[$key]->icon_links=array();
                                }
                            }
                            
                            // echo '<pre>';
                            // print_r($data);
                            // die;

                            if($data && sizeof($data) > 0)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Study Material fetch successfully!',
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
                                    'message' => 'No study material found!',
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
                                'message' => 'Subject groups are not assigned!',
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
                    'message' => 'Invalid user!',
                    'data' => ''
                );
            }
        }
    }
    
    
    //Detail page API
    public function studyMaterial_detail(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('id') && $request->input('id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();

                        $data=DB::table('study_material')
                        ->join('classes', 'study_material.class_id', '=', 'classes.id')
                        ->join('batches', 'study_material.batch_id', '=', 'batches.id','left')
                        ->join('subjects', 'study_material.subject_code', '=', 'subjects.code', 'left')
                        ->join('users', 'study_material.uploaded_by', '=', 'users.id', 'left')
                        ->selectRaw('sh_study_material.*,sh_classes.name as class_name,sh_batches.name as batch_name,sh_subjects.name as subject_name ,DATE_FORMAT("sh_study_material.uploaded_at","%d/%m/%Y") as uploaded_time, sh_users.id as creator_id, sh_users.name as creator_name, sh_users.avatar as creator_avatar')
                        ->where('study_material.id', $request->input('id'))
                        ->where('study_material.delete_status', 0)
                        ->where('study_material.school_id', $user->school_id)
                        ->where('classes.academic_year_id', $academic_year->id)
                        ->groupBy('study_material.id')->orderBy('study_material.uploaded_at','desc')
                        ->get()->all();

                        if($data && sizeof($data) > 0)
                        {
                            foreach($data as $key => $rec)
                            {
                                $data[$key]->id=(int) $rec->id;
                                $data[$key]->school_id=(int) $rec->school_id;
                                $data[$key]->class_id=(int) $rec->class_id;
                                $data[$key]->storage_type=(int) $rec->storage_type;
                                $data[$key]->delete_status=(int) $rec->delete_status;
                                $data[$key]->uploaded_by=(int) $rec->uploaded_by;
                                $data[$key]->creator_id=(int) $rec->creator_id;
                                
                                if($rec->files!='')
                                {
                                    $data[$key]->files=explode(',',$rec->files);
                                }
                                else
                                {
                                    $data[$key]->files=array();
                                }
                                
                                if($rec->filesurl!='')
                                {
                                    $data[$key]->filesurl=explode(',',$rec->filesurl);
                                }
                                else
                                {
                                    $data[$key]->filesurl=array();
                                }
                                
                                if($rec->file_names!='')
                                {
                                    $data[$key]->file_names=explode(',',$rec->file_names);
                                }
                                else
                                {
                                    $data[$key]->file_names=array();
                                }
                                
                                if($rec->fileids!='')
                                {
                                    $data[$key]->fileids=explode(',',$rec->fileids);
                                }
                                else
                                {
                                    $data[$key]->fileids=array();
                                }
                                
                                if($rec->thumbnail_links!='')
                                {
                                    $data[$key]->thumbnail_links=explode(',',$rec->thumbnail_links);
                                }
                                else
                                {
                                    $data[$key]->thumbnail_links=array();
                                }
                                
                                if($rec->icon_links!='')
                                {
                                    $data[$key]->icon_links=explode(',',$rec->icon_links);
                                }
                                else
                                {
                                    $data[$key]->icon_links=array();
                                }
                            }
                            
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'StudyMaterial fetch successfully!',
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
                                'message' => 'No StudyMaterial found!',
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
                        'message' => 'Study Material id is required!',
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
            }
        }
    }
    
    
    
    
    
}
