<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class ParentsController extends Controller
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


    public function get_childs(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
    
                if($user)
                {
                    
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $students=DB::table('student_guardians')->join('users','student_guardians.student_id', '=', 'users.id', 'left')->select('student_guardians.student_id','users.name','users.avatar','users.rollno as roll_number')->where('student_guardians.guardian_id',$user->id)->whereNull('student_guardians.deleted_at')->where('users.deleted_at', '=', '0')->where('users.status', '=', '0')->get()->all();
                    
                    if(sizeof($students) > 0)
                    {
                        foreach($students as $key => $stu)
                        {
                            // $studssent = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name, sh_classes.name as class_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id left JOIN sh_classes ON s.class_id = sh_classes.id  WHERE s.id='$stu->student_id' ");
                            $stu_rec = DB::select("SELECT sh_batches.name as batch_name, sh_classes.id as class_id, sh_classes.name as class_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id left JOIN sh_classes ON s.class_id = sh_classes.id  WHERE s.id='$stu->student_id' ");
                            
                            if(sizeof($stu_rec) > 0)
                            {
                                $s_id=$stu->student_id;
                                $random_Usertoken=$this->generate_string($permitted_chars, 20);
                                
                                $stu_api_tokens=DB::table('users')->select('api_token')->where('id', $s_id)->first();
                                if($stu_api_tokens)
                                {
                                    $students[$key]->api_token=$stu_api_tokens->api_token;
                                }
                                else
                                {
                                    $students[$key]->api_token="";
                                }
        
                                // $result=DB::table('users')->where(function ($match) use ($s_id){
                                //     $match->where('users.id', $s_id);
                                // })->update(array(
                                //   'users.api_token' => $random_Usertoken
                                // ));
                                // $students[$key]->api_token=$random_Usertoken;
                                
                                
                                $students[$key]->batch_name=$stu_rec[0]->batch_name;
                                $students[$key]->class_id=$stu_rec[0]->class_id;
                                $students[$key]->class_name=$stu_rec[0]->class_name;
                            }
                            else
                            {
                                unset($students[$key]);
                            }
                        }
                        
                        $students=array_values($students);
                        
                       
                    
                        
                        
                        foreach($students as $key => $std)
                        {
                            $fee_types=DB::table('fee_types')->select('id as fee_type_id','name as fee_type_name','description','amount as total_amount','due_date')->where('school_id',$user->school_id)->where('class_id',$std->class_id)->where('academic_year_id',$academic_year->id)->whereNull('deleted_at')->get()->all();
                    
                    
                        
                            //Only Enabled Fees
                            $fee_type_status=DB::table('feetype_status')->select('*')->where('student_id',$std->student_id)->where('status','1')->get()->all();
                            if(sizeof($fee_type_status) > 0 && sizeof($fee_types) > 0)
                            {
                                foreach($fee_types as $mkey => $mfeetype)
                                {
                                    foreach($fee_type_status as $fee_t_status)
                                    {
                                        if($mfeetype->fee_type_id==$fee_t_status->feetype_id)
                                        {
                                            unset($fee_types[$mkey]);
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if(sizeof($fee_types) > 0)
                            {
                                $fee_types=array_values($fee_types);
                                
                                $students[$key]->fee_counter=sizeof($fee_types);
                                
                                foreach($fee_types as $fee_type)
                                {
                                    $collections=DB::table('fee_collection')->where('feetype_id',$fee_type->fee_type_id)->where('student_id',$std->student_id)->whereNull('deleted_at')->get()->all();
                                    if(sizeof($collections) > 0)
                                    {
                                        foreach($collections as $c_key => $col)
                                        {
                                            $fee_status=$col->status;
                                        }
                                        if($fee_status==0)
                                        {
                                            $students[$key]->fee_status=0;
                                        }
                                        else if($fee_status==1)
                                        {
                                            $students[$key]->fee_status=1;
                                        }
                                        else if($fee_status==2)
                                        {
                                            $students[$key]->fee_status=2;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $students[$key]->fee_counter=0;
                                $students[$key]->fee_status=1;
                            }
                            
                            
                            if($students[$key]->fee_counter > 0)
                            {
                                if(isset($students[$key]->fee_status))
                                {
                                }
                                else
                                {
                                    $students[$key]->fee_status=0;
                                }
                            }
                            
                            
                        }
                        
                        foreach($students as $std){
                            $std->student_id = (int)$std->student_id;
                            $std->fee_status = (int)$std->fee_status;
                        }
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Childs fetch successfully!',
                            'data' => $students
                        );
                        return response()->json($this->response, 200);
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
    
    
    public function generate_string($input, $strength = 16)
    {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }
    
    //Parents fetch student home work and assignment

    public function students_classactivities(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('content_type') && $request->input('content_type')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $students=DB::table('student_guardians')->select('student_id')->where('guardian_id',$user->id)->whereNull('deleted_at')->get()->all();
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $student_record=array();
                        
                        if(sizeof($students) > 0)
                        { 
                            foreach($students as $key => $ids)
                            {
                                $students[$key]=(int)$ids->student_id;
                            }
                            ////////////////////////////////////////////////////////////////////////
                            $school_id = $user->school_id;
                            // $student_id = $user->id;
                            $current_date = date('Y-m-d');
                            
                            DB::enableQueryLog();
                            $response=DB::table('assignments')
                            ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                            ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                            ->select('users.name', 'users.avatar', 'assignments.*', 'classes.name as class_name', 'subjects.name as subject_name', 'users.name as teacher_name', 'users.id as teacher_id', 'users.avatar')
                            // ->whereIn('assignments.student_ids',[$student_id])
                            ->whereIn('assignments.student_ids',$students)
                            ->where('assignments.school_id',$school_id)
                            ->where('assignments.deleted_status',0)
                            ->orderBy('assignments.created_at','desc');
                            if($request->input('date') && $request->input('date')!='')
                            {
                                if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                {
                                    $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','=',$request->input('date'))->get()->all();
                                }
                                else
                                {
                                    $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','=',$request->input('date'))->get()->all();
                                }
                                
                            }
                            else
                            {
                                if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                {
                                    $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','<=',$current_date)->get()->all();
                                }
                                else
                                {
                                    $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','<=',$current_date)->get()->all();
                                }
                                
                            }
                            

                            //from here to manage status of assignments and homework
                            foreach ($response as $i => $res)
                            {
                                $response[$i]->id=(int) $res->id;
                                $response[$i]->school_id=(int) $res->school_id;
                                $response[$i]->subject_id=(int) $res->subject_id;
                                
                                $re=DB::table('submit_material')->where('material_id',$res->id)->whereIn('student_id',$students)->where('deleted_status',0)->get()->all();
                                
                                if(sizeof($re) > 0 )
                                {
                                    $res->status = "Submitted";
                                    if($re[0]->obtained_marks == "")
                                    {
                                        $res->obtained_marks = "Waiting";
                                    }
                                    else
                                    {
                                        $res->obtained_marks = $re[0]->obtained_marks;
                                    } 
                                }
                                else
                                {
                                    $res->status = "Due";
                                    $res->obtained_marks = "Submit Your Assignment First";
                                }
                                
                                $response[$i]->files = explode(",", $response[$i]->files);
                                $response[$i]->filesurl = explode(",", $response[$i]->filesurl);
                                $response[$i]->file_names = explode(",", $response[$i]->file_names);
                                $response[$i]->thumbnail_links = explode(",", $response[$i]->thumbnail_links);
                            }
                            
                            if(sizeof($response) > 0)
                            {
                                $this->response=array
                                (
                                    'status' => 'error',
                                    'message' => $request->input('content_type').' fetch successfully!',
                                    'data' => $response
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'error',
                                    'message' => 'No '.$request->input('content_type').' found!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 400);
                                die;
                            }
                            ////////////////////////////////////////////////////////////////////////
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
                        'message' => 'Content type is required!',
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
    
    
    
    //Create StudyMaterial
    public function update_parentpic(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            
            $errors=array();
            if($request->input('api_token') && $request->input('api_token')!='')
            {
            }
            else
            {
                $errors['api_token']='Api Token is required';
            }
            
            if(sizeof($errors) > 0)
            {
                $this->response=array
                (
                    'status' => 'success',
                    'message' => 'Fields are required!',
                    'data' => $errors
                );
                return response()->json($this->response, 200);
            }
            else
            {
                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                if($user)
                {
                    if($request->file('avatar') && $request->hasFile('avatar'))
                    {
                        $file=$request->file('avatar');
                        $filename = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $firstname = pathinfo($filename, PATHINFO_FILENAME);
                        $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                        
                        $file->move('../uploads/user', $db_fileName);
                        
                        $data=array(
                            'avatar' => $db_fileName
                            );
                            
                        $response=DB::table('users')->where('id',$user->id)->update($data);
                        
                        if($response)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Avatar updated successfully!',
                                'data' => $data
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
                            'message' => 'Avatar is required!',
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
        }
    }

    
    
    
   
}
