<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
use Pusher\Pusher;


class ClassworkController extends Controller
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


    //Create StudyMaterial
    public function create_studyMaterial(Request $request)
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
            
            if($request->input('class_id') && $request->input('class_id')!='')
            {
            }
            else
            {
                $errors['class_id']='Class Id is required';
            }
                           
            if($request->input('batch_id') && $request->input('batch_id')!='')
            {
            }
            else
            {
                $errors['batch_id']='Batch Id is required';
            }
                                          
            if($request->input('content_type') && $request->input('content_type')!='')
            {
            }
            else
            {
                $errors['content_type']='Content Type is required';
            }
                        
            if($request->input('title') && $request->input('title')!='')
            {
            }
            else
            {
                $errors['title']='Title is required';
            }
                                    
            if($request->input('subject_id') && $request->input('subject_id')!='')
            {
            }
            else
            {
                $errors['subject_id']='Subject Id is required';
            }
                    
                                    
            if($request->input('details') && $request->input('details')!='')
            {
            }
            else
            {
                $errors['details']='Detail is required';
            }

                                                            
            if($request->input('date') && $request->input('date')!='')
            {
            }
            else
            {
                $errors['date']='Date is required';
            }
                                                                        
            if($request->input('created_at') && $request->input('created_at')!='')
            {
            }
            else
            {
                $errors['created_at']='Date/Time is required';
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
                    $user->permissions=json_decode($user->permissions);
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="study_material-upload")
                            {
                                if($perm->val=="true")
                                {
                                    $user->permissions[$key]->val=true;
                                }
                                else
                                {
                                    $user->permissions[$key]->val=false;
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'You do not have permission to upload study material!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    
                    $files_names=array();
                    $all_files=array();
                    $data=array();
                    $subject_code=DB::table('subjects')->select('code')->where('id',$request->input('subject_id'))->first()->code;
                    if($request->file('files') && $request->hasFile('files'))
                    {
                        $files=$request->file('files');
                        foreach($files as $file)
                        {
                            $filename = $file->getClientOriginalName();
                            $files_names[]=$filename;
                            $extension = $file->getClientOriginalExtension();
                            // $check=in_array($extension,$allowedfileExtension);
                            $firstname = pathinfo($filename, PATHINFO_FILENAME);
                            $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                            
                            $file->move('../uploads/study_material', $db_fileName);
                            $all_files[]=$db_fileName;
                        }
                    }
                    $data=array(
                            'title' => $request->input('title'),
                            'content_type' => $request->input('content_type'),
                            'school_id' => $user->school_id,
                            'class_id' => $request->input('class_id'),
                            'batch_id' => $request->input('batch_id'),
                            'subject_id' => $request->input('subject_id'),
                            'subject_code' => $subject_code,
                            'files' => implode(',',$all_files),
                            'file_names' => implode(',',$files_names),
                            'details' => $request->input('details'),
                            'uploaded_at' => date('Y-m-d',strtotime($request->input('date'))),
                            'created_at' => date('Y-m-d H:i:s',strtotime($request->input('created_at'))),
                            'uploaded_by' => $user->id
                        );
                        
                    $response=DB::table('study_material')->insert($data);
                    if($response)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'StudyMaterial created successfully!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
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

  
    //Update StudyMaterial
    public function update_studyMaterial(Request $request)
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
            
            if($request->input('class_id') && $request->input('class_id')!='')
            {
            }
            else
            {
                $errors['class_id']='Class Id is required';
            }
                           
            if($request->input('batch_id') && $request->input('batch_id')!='')
            {
            }
            else
            {
                $errors['batch_id']='Batch Id is required';
            }
                                          
            if($request->input('content_type') && $request->input('content_type')!='')
            {
            }
            else
            {
                $errors['content_type']='Content Type is required';
            }
                        
            if($request->input('title') && $request->input('title')!='')
            {
            }
            else
            {
                $errors['title']='Title is required';
            }
                                    
            if($request->input('subject_id') && $request->input('subject_id')!='')
            {
            }
            else
            {
                $errors['subject_id']='Subject Id is required';
            }
                    
                                    
            if($request->input('details') && $request->input('details')!='')
            {
            }
            else
            {
                $errors['details']='Detail is required';
            }

                                                            
            if($request->input('date') && $request->input('date')!='')
            {
            }
            else
            {
                $errors['date']='Date is required';
            }
                                                                        
            if($request->input('study_materialId') && $request->input('study_materialId')!='')
            {
            }
            else
            {
                $errors['study_materialId']='StudyMaterial Id is required';
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
                    $user->permissions=json_decode($user->permissions);
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="study_material-upload")
                            {
                                if($perm->val=="true")
                                {
                                    $user->permissions[$key]->val=true;
                                }
                                else
                                {
                                    $user->permissions[$key]->val=false;
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'You do not have permission to update study material!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    
                    $files_names=array();
                    $all_files=array();
                    $data=array();
                    $subject_code=DB::table('subjects')->select('code')->where('id',$request->input('subject_id'))->first()->code;
                    $studymaterial_detail = DB::table('study_material')->where('id', $request->input('study_materialId'))->first();
                    if($request->file('files') && $request->hasFile('files'))
                    {
                        $files=$request->file('files');
                        foreach($files as $file)
                        {
                            $filename = $file->getClientOriginalName();
                            $files_names[]=$filename;
                            $extension = $file->getClientOriginalExtension();
                            // $check=in_array($extension,$allowedfileExtension);
                            $firstname = pathinfo($filename, PATHINFO_FILENAME);
                            $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                            
                            $file->move('../uploads/study_material', $db_fileName);
                            $all_files[]=$db_fileName;
                        }
                        $all_files=implode(',',$all_files);
                        $files_names=implode(',',$files_names);
                    }
                    else
                    {
                        if($studymaterial_detail)
                        {
                            $all_files=$studymaterial_detail->files;
                            $files_names=$studymaterial_detail->file_names;
                        }
                    }
                    
                    // if($request->input('old_files_name') && $request->input('old_files_name')!='')
                    // {
                    //     $oldfiles=explode(',', $request->input('old_files_name'));
                    //     $dbfiles=explode(',', $studymaterial_detail->files);
                    //     foreach($dbfiles as $key => $db_f)
                    //     {
                    //         foreach($oldfiles as $key => $o_f)
                    //         {
                    //             if()
                    //             {
                                    
                    //             }
                    //         }
                    //     }
                    // }
                    $data=array(
                            'title' => $request->input('title'),
                            'content_type' => $request->input('content_type'),
                            'school_id' => $user->school_id,
                            'class_id' => $request->input('class_id'),
                            'batch_id' => $request->input('batch_id'),
                            'subject_id' => $request->input('subject_id'),
                            'subject_code' => $subject_code,
                            // 'files' => implode(',',$all_files),
                            'files' => $all_files,
                            // 'file_names' => implode(',',$files_names),
                            'file_names' => $files_names,
                            'details' => $request->input('details'),
                            'uploaded_at' => date('Y-m-d',strtotime($request->input('date')))
                            // 'created_at' => date('Y-m-d H:i:s',strtotime($request->input('created_at')))
                            // 'uploaded_by' => $user->id
                        );
                        
                    $response=DB::table('study_material')->where('id', $request->input('study_materialId'))->update($data);
                    if($response)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'StudyMaterial updated successfully!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
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
  
    //Delete StudyMaterial
    public function delete_studyMaterial(Request $request)
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
            
            if($request->input('study_materialId') && $request->input('study_materialId')!='')
            {
            }
            else
            {
                $errors['study_materialId']='StudyMaterial Id is required';
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
                    $user->permissions=json_decode($user->permissions);
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="study_material-upload")
                            {
                                if($perm->val=="true")
                                {
                                    $user->permissions[$key]->val=true;
                                }
                                else
                                {
                                    $user->permissions[$key]->val=false;
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'You do not have permission to delete study material!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    $response=DB::table('study_material')->where('id', $request->input('study_materialId'))->update(['delete_status' => 1]);
                    if($response)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'StudyMaterial deleted successfully!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
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

    

    //Create Assignment/Homework
    public function create_classActivity(Request $request)
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
            
            if($request->input('class_id') && $request->input('class_id')!='')
            {
            }
            else
            {
                $errors['class_id']='Class Id is required';
            }
                           
            if($request->input('batch_ids') && $request->input('batch_ids')!='')
            {
            }
            else
            {
                $errors['batch_ids']='Batch Id is required';
            }
                                          
            if($request->input('content_type') && $request->input('content_type')!='')
            {
            }
            else
            {
                $errors['content_type']='Content Type is required';
            }
                        
            if($request->input('title') && $request->input('title')!='')
            {
            }
            else
            {
                $errors['title']='Title is required';
            }
                                    
            if($request->input('subject_id') && $request->input('subject_id')!='')
            {
            }
            else
            {
                $errors['subject_id']='Subject Id is required';
            }
                                    
            if($request->input('student_ids') && $request->input('student_ids')!='')
            {
            }
            else
            {
                $errors['student_ids']='Student Id is required';
            }
                                    
            if($request->input('details') && $request->input('details')!='')
            {
            }
            else
            {
                $errors['details']='Detail is required';
            }
                                                
            if($request->input('total_marks') && $request->input('total_marks')!='')
            {
            }
            else
            {
                $errors['total_marks']='Total marks is required';
            }
                                                            
            if($request->input('due_date') && $request->input('due_date')!='')
            {
            }
            else
            {
                $errors['due_date']='Due date is required';
            }
                                                            
            if($request->input('descriptions') && $request->input('descriptions')!='')
            {
            }
            else
            {
                $errors['descriptions']='Description is required';
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
                    $files_names=array();
                    $all_files=array();
                    $data=array();
                    $subject_code=DB::table('subjects')->select('code')->where('id',$request->input('subject_id'))->first()->code;
                    if($request->file('files') && $request->hasFile('files'))
                    {
                        $files=$request->file('files');
                        foreach($files as $file)
                        {
                            $filename = $file->getClientOriginalName();
                            $files_names[]=$filename;
                            $extension = $file->getClientOriginalExtension();
                            // $check=in_array($extension,$allowedfileExtension);
                            $firstname = pathinfo($filename, PATHINFO_FILENAME);
                            $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                            
                            $file->move('../uploads/study_material', $db_fileName);
                            $all_files[]=$db_fileName;
                        }
                    }
                    $data=array(
                            'title' => $request->input('title'),
                            'content_type' => $request->input('content_type'),
                            'school_id' => $user->school_id,
                            'class_id' => $request->input('class_id'),
                            'batch_ids' => $request->input('batch_ids'),
                            'subject_id' => $request->input('subject_id'),
                            'student_ids' => implode(',',$request->input('student_ids')),
                            'subject_code' => $subject_code,
                            'files' => implode(',',$all_files),
                            'file_names' => implode(',',$files_names),
                            'details' => $request->input('details'),
                            'published_date' => date('Y-m-d'),
                            'uploaded_by' => $user->id,
                            'deleted_status' => 0,
                            'due_date' => date('Y-m-d',strtotime($request->input('due_date'))),
                            'total_marks' => $request->input('total_marks'),
                            'material_details' => $request->input('descriptions'),
                        );
                        
                    $response=DB::table('assignments')->insert($data);
                    if($response)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => $request->input('content_type').' created successfully!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
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

    
    
    //Create Assignment/Homework - Get Subjects
    public function get_subjects(Request $request)
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
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            
                            $data=DB::table('subjects')->select('id','name','code')->where('school_id', $user->school_id)->where('class_id', $request->input('class_id'))->where('batch_id', $request->input('batch_id'))->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                            
                            if(sizeof($data) > 0)
                            {
                                foreach($data as $key => $d)
                                {
                                    $data[$key]->id=(int) $d->id;
                                }
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Subjects found successfully!',
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
                                    'message' => 'No subject found!',
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
    
    
    //Create Assignment/Homework - Get Students
    public function get_students(Request $request)
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
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $class_id=$request->input('class_id');
                            $batch_id=$request->input('batch_id');
                            
                            $student_rec = DB::select("SELECT s.id, u.name, u.avatar FROM sh_students_$user->school_id s left JOIN sh_users u ON u.id = s.id WHERE s.class_id='$class_id' AND s.batch_id='$batch_id' AND s.academic_year_id='$academic_year->id' AND u.deleted_at='0' AND u.status='0' ");
                            
                            if(sizeof($student_rec) > 0)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Students found successfully!',
                                    'data' => $student_rec
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No students found!',
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


    //Get Comments Of Assignment / Homework
    public function get_comments_classactivity(Request $request)
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
                        $submitted_record=DB::table('submit_material')->select('id as submit_material_id')->where('material_id',$request->input('id'))->first();
                        if($submitted_record)
                        {
                            $records=DB::table('comments')
                            ->join('users', 'comments.sender_id', '=', 'users.id', 'left')
                            ->select('users.id', 'users.name', 'users.avatar', 'comments.comment_body', 'comments.files', 'comments.submit_material_id')
                            ->where('comments.submit_material_id',$submitted_record->submit_material_id)
                            ->whereNull('comments.deleted_at')
                            ->where('users.deleted_at', '=', '0')
                            ->get()->all();
                            
                            if(sizeof($records) > 0)
                            {
                                foreach($records as $key => $record)
                                {
                                    if($record->files!='')
                                    {
                                        $records[$key]->files=explode(',', $record->files);
                                    }
                                    else
                                    {
                                        $records[$key]->files=array();
                                    }
                                    
                                    if($record->id==$user->id)
                                    {
                                        $records[$key]->placement='right';
                                    }
                                    else
                                    {
                                        $records[$key]->placement='left';
                                    }
                                }
                                
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Comments fetch successfully!',
                                    'data' => $records
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No comments yet!',
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
                                'message' => 'User first have to submit assignment first!',
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
                        'message' => 'Content ID is required!',
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
                
                 
    //Comments On Assignment / Homework
    public function comments_classactivity(Request $request)
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
                    if($request->input('comment') && $request->input('comment')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        if($user)
                        {
                            $tech_id=0;
                            $class_workTitle='assignment';
                            $assig_detail=DB::table('assignments')->select('uploaded_by','content_type')->where('id',$request->input('id'))->first();
                            if($assig_detail)
                            {
                                $tech_id=$assig_detail->uploaded_by;
                                $class_workTitle=$assig_detail->content_type;
                            }
                            else
                            {
                                $tech_id=0;
                            }
                            
                            $submitted_material=DB::table('submit_material')->select('id')->where('material_id',$request->input('id'))->where('student_id',$user->id)->first();
                            /////////////////////////////////////
                            $data=array();
                            $all_files=array();
                            if($request->file('attachments') && $request->hasFile('attachments'))
                            {
                                $files=$request->file('attachments');
                                foreach($files as $file)
                                {
                                    $filename = $file->getClientOriginalName();
                                    $extension = $file->getClientOriginalExtension();
                                    // $check=in_array($extension,$allowedfileExtension);
                                    $firstname = pathinfo($filename, PATHINFO_FILENAME);
                                    $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                                    
                                    $file->move('../uploads/study_material', $db_fileName);
                                    $all_files[]=$db_fileName;
                                }
                                
                                $data=array(
                                    'sender_id' => $user->id,
                                    'submit_material_id' => $submitted_material->id,
                                    'files' => implode(',',$all_files),
                                    'comment_body' => $request->input('comment')
                                );
                            }
                            else
                            {
                                $data=array(
                                    'sender_id' => $user->id,
                                    'submit_material_id' => $submitted_material->id,
                                    'files' => '',
                                    'comment_body' => $request->input('comment')
                                );
                            }
                            
                            if($data)
                            {
                                $response=DB::table('comments')->insert($data);
                                $data['id']=$request->input('id');
                                if($response)
                                {
                                    //////////////Notification code///////////////
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
                            		
                            		$json_message['data']="New comments on ".$class_workTitle." send by ".$user->name;
                            		$pusher->trigger('mychanal-'.$tech_id, 'my-event', $json_message );
                            		///////////////Notification code//////////////
                            		
                            		
                            		$msg_key="comments";
                            		
                            		$sender_detail='{"sender":"'.$user->name.'"}';
                            		$notification_array=array('msg_key' => $msg_key, 'url' => 'study_material/class_work', 'app_url' => 'https', 'data' => $sender_detail, 'sender_id' => $user->id, 'school_id' => $user->school_id);
                            		
                            		$notificationsData_status=DB::table('notifications')->insert($notification_array);
                            		$notification_id = DB::getPdo()->lastInsertId();
                            		
                            		$notificationDetail_array=array('notification_id' => $notification_id, 'receiver_id' => $tech_id);
                            		
                            		$notificationsDetailData_status=DB::table('notification_details')->insert($notificationDetail_array);
                            		
                            		
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Comment posted successfully!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'error',
                                        'message' => 'Something went wrong, please try again!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 400);
                                }
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'error',
                                    'message' => 'All fields are required!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 400);
                                die;
                            }
                            /////////////////////////////////////
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
                            'message' => 'Description is required!',
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
                        'message' => 'Content ID is required!',
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
    
    
    //submit Assignment / Homework
    public function submit_classactivity(Request $request)
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
                    if($request->input('description') && $request->input('description')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        if($user)
                        {
                            $tech_id=0;
                            $class_workTitle='assignment';
                            $assig_detail=DB::table('assignments')->select('uploaded_by','content_type')->where('id',$request->input('id'))->first();
                            if($assig_detail)
                            {
                                $checkifrecord=DB::table('submit_material')->where('material_id', $request->input('id'))->where('student_id', $user->id)->where('deleted_status', 0)->first();
                                if($checkifrecord)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Assignment was already submitted!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                                
                                $tech_id=$assig_detail->uploaded_by;
                                $class_workTitle=$assig_detail->content_type;
                            }
                            else
                            {
                                $tech_id=0;
                            }
                            
                            /////////////////////////////////////
                            $data=array();
                            $all_files=array();
                            $all_file_name=array();
                            $all_file_url=array();
                            
                            $submission_dates= date("Y-m-d H:i:s", strtotime($request->input('created_at')));
                            
                            if($request->file('attachments') && $request->hasFile('attachments'))
                            {
                                $path='https://uvschools.com/uploads/study_material/';
                                $files=$request->file('attachments');
                                foreach($files as $file)
                                {
                                    $filename = $file->getClientOriginalName();
                                    $extension = $file->getClientOriginalExtension();
                                    // $check=in_array($extension,$allowedfileExtension);
                                    $firstname = pathinfo($filename, PATHINFO_FILENAME);
                                    $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                                    
                                    $file->move('../uploads/study_material', $db_fileName);
                                    $all_files[]=$db_fileName;
                                    $all_file_name[]=$filename;
                                    $all_file_url[]=$path.$db_fileName;
                                }
                                
                                $data=array(
                                    'student_id' => $user->id,
                                    'material_id' => $request->input('id'),
                                    'date' => date('Y-m-d'),
                                    'submitted_files' => implode(',',$all_files),
                                    'file_names' => implode(',',$all_file_name),
                                    'filesurl' => implode(',',$all_file_url),
                                    'storage_type' => 1,
                                    'submitted_details' => $request->input('description'),
                                    'created_at' => $submission_dates
                                );
                            }
                            else
                            {
                                $data=array(
                                    'student_id' => $user->id,
                                    'material_id' => $request->input('id'),
                                    'date' => date('Y-m-d'),
                                    'submitted_files' => '',
                                    'storage_type' => 1,
                                    'submitted_details' => $request->input('description'),
                                    'created_at' => $submission_dates
                                );
                            }
                            
                            if($data)
                            {
                                $response=DB::table('submit_material')->insert($data);
                                $submit_materiaId = DB::getPdo()->lastInsertId();
                                if($response)
                                {
                                    if($data['submitted_files']!='')
                                    {
                                        $data['submitted_files']=explode(',',$data['submitted_files']);
                                        $data['file_names']=explode(',',$data['file_names']);
                                        $data['filesurl']=explode(',',$data['filesurl']);
                                    }
                                    $data['submit_material_id']=$submit_materiaId;
                                    
                                    
                                    //////////////Notification code///////////////
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
                            		$json_message['data']="New assignment submitted by ".$user->name;
                            		$pusher->trigger('mychanal-'.$tech_id, 'my-event', $json_message );
                            		///////////////Notification code//////////////
                            		 
                            		$msg_key="sub_homework";
                            	    if($class_workTitle=='Assignment')
                            	    {
                            	        $msg_key="sub_assignment";
                            	    }
                            	    else
                            	    {
                            	        $msg_key="sub_homework";
                            	    }
                            		
                            		$sender_detail='{"sender":"'.$user->name.'"}';
                            		$notification_array=array('msg_key' => $msg_key, 'url' => 'study_material/class_work', 'app_url' => 'https', 'data' => $sender_detail, 'sender_id' => $user->id, 'school_id' => $user->school_id);
                            		
                            		$notificationsData_status=DB::table('notifications')->insert($notification_array);
                            		$notification_id = DB::getPdo()->lastInsertId();
                            		
                            		$notificationDetail_array=array('notification_id' => $notification_id, 'receiver_id' => $tech_id);
                            		
                            		$notificationsDetailData_status=DB::table('notification_details')->insert($notificationDetail_array);

                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Classwork submitted successfully!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'error',
                                        'message' => 'Something went wrong, please try again!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 400);
                                }
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'error',
                                    'message' => 'All fields are required!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 400);
                                die;
                            }
                            /////////////////////////////////////
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
                            'message' => 'Description is required!',
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
                        'message' => 'Content ID is required!',
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
    
    
    //Detail
    public function detail_classactivity(Request $request)
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
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $school_id = $user->school_id;
                        $student_id = $user->id;

                        $response=DB::table('assignments')
                        ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                        ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                        ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                        ->select('assignments.*', 'classes.name as class_name', 'subjects.name as subject_name', 'users.name as teacher_name', 'users.id as teacher_id', 'users.avatar')
                        ->where('assignments.id',$request->input('id'))
                        ->where('assignments.school_id',$school_id)
                        ->where('assignments.deleted_status',0)
                        ->first();

                        $re=DB::table('submit_material')->where('material_id',$response->id)->where('student_id',$student_id)->where('deleted_status',0)->get()->all();
                        $submission=array();
                        $submission=(object) $submission;
                        if(sizeof($re) > 0 )
                        {
                            $submission=$re[0];
                            
                            $submission->id=(int) $submission->id;
                            $submission->student_id=(int) $submission->student_id;
                            $submission->material_id=(int) $submission->material_id;
                            $submission->storage_type=(int) $submission->storage_type;
                            $submission->deleted_status=(int) $submission->deleted_status;
                            
                            if($submission->submitted_files!='')
                            {
                                $submission->submitted_files = explode(",", $submission->submitted_files);
                            }
                            else
                            {
                                $submission->submitted_files=array();
                            }
                            
                            
                            if($submission->file_names!='')
                            {
                                $submission->file_names = explode(",", $submission->file_names);
                            }
                            else
                            {
                                $submission->file_names=array();
                            }
                            
                            
                            if($submission->filesurl!='')
                            {
                                $submission->filesurl = explode(",", $submission->filesurl);
                            }
                            else
                            {
                                $submission->filesurl=array();
                            }
                            
                            
                            if($submission->thumbnail_links!='')
                            {
                                $submission->thumbnail_links = explode(",", $submission->thumbnail_links);
                            }
                            else
                            {
                                $submission->thumbnail_links=array();
                            }
                            
                            
                            
                            
                            $response->status = "Submitted";
                            if($re[0]->obtained_marks == "")
                            {
                                $response->obtained_marks = "Waiting";
                            }
                            else
                            {
                                $response->obtained_marks = $re[0]->obtained_marks;
                            } 
                        }
                        else
                        {
                            $response->status = "Due";
                            $response->obtained_marks = "Submit Your Assignment First";
                        }
                            
                        
                        if($response)
                        {
                            $response->id=(int) $response->id;
                            $response->school_id=(int) $response->school_id;
                            $response->class_id=(int) $response->class_id;
                            $response->subject_id=(int) $response->subject_id;
                            $response->storage_type=(int) $response->storage_type;
                            $response->uploaded_by=(int) $response->uploaded_by;
                            $response->deleted_status=(int) $response->deleted_status;
                            $response->total_marks=(int) $response->total_marks;
                            $response->teacher_id=(int) $response->teacher_id;
                            
                            if($response->files!='')
                            {
                                $response->files = explode(",", $response->files);
                            }
                            else
                            {
                                $response->files=array();
                            }
                            
                            
                            if($response->filesurl!='')
                            {
                                $response->filesurl = explode(",", $response->filesurl);
                            }
                            else
                            {
                                $response->filesurl=array();
                            }
                            
                            
                            if($response->file_names!='')
                            {
                                $response->file_names = explode(",", $response->file_names);
                            }
                            else
                            {
                                $response->file_names=array();
                            }
                            
                            
                            if($response->thumbnail_links!='')
                            {
                                $response->thumbnail_links = explode(",", $response->thumbnail_links);
                            }
                            else
                            {
                                $response->thumbnail_links=array();
                            }
                            
                            
                            
                            
                            // $response->filesurl = explode(",", $response->filesurl);
                            // $response->file_names = explode(",", $response->file_names);
                            // $response->thumbnail_links = explode(",", $response->thumbnail_links);
                            
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => $response->content_type.' fetch successfully!',
                                'data' => $response,
                                'submission' => $submission
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No detail found!',
                                'data' => '',
                                'submission' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        ////////////////////////////////////////////////////////////////////////
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
                        'message' => 'Content ID is required!',
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
    
    
    //Teachers record
    public function teacher_classActivity(Request $request)
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
                                ////////////////////////////////////////////////////////////////////////
                                $school_id = $user->school_id;
                                $student_id = $user->id;
                                $current_date = date('Y-m-d');
                                
                                $response=DB::table('assignments')
                                ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                                ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                                ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                                ->select('assignments.*', 'classes.name as class_name', 'subjects.name as subject_name', 'users.name as teacher_name', 'users.id as teacher_id', 'users.avatar')
                                ->whereIn('assignments.class_id',[$request->input('class_id')])
                                ->whereIn('assignments.batch_ids',[$request->input('batch_id')])
                                ->where('assignments.school_id',$school_id)
                                ->where('assignments.deleted_status',0)
                                ->orderBy('assignments.created_at','desc');
                                if($request->input('date') && $request->input('date')!='')
                                {
                                    if($request->input('subject_id') && $request->input('subject_id')!='')
                                    {
                                        if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                        {
                                            $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                        }
                                        else
                                        {
                                            $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                        }
                                    }
                                    else
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
                                    
                                }
                                else
                                {
                                    if($request->input('subject_id') && $request->input('subject_id')!='')
                                    {
                                        if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                        {
                                            $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','<=',$current_date)->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                        }
                                        else
                                        {
                                            $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','<=',$current_date)->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
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
                                    
                                }
                                
                                
                                
                                
                                
                               
                                foreach ($response as $i => $res)
                                {
                                    $re=DB::table('submit_material')->where('material_id',$res->id)->where('student_id',$student_id)->where('deleted_status',0)->get()->all();
                                    
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
                                    
                                    if($response[$i]->files !='')
                                    {
                                        $response[$i]->files = explode(",", $response[$i]->files);
                                    }
                                    else
                                    {
                                        $response[$i]->files = array();
                                    }
                                    
                                                                
                                    if($response[$i]->filesurl !='')
                                    {
                                        $response[$i]->filesurl = explode(",", $response[$i]->filesurl);
                                    }
                                    else
                                    {
                                        $response[$i]->filesurl = array();
                                    }
                                    
                                                                
                                    if($response[$i]->file_names !='')
                                    {
                                        $response[$i]->file_names = explode(",", $response[$i]->file_names);
                                    }
                                    else
                                    {
                                        $response[$i]->file_names = array();
                                    }
                                    
                                                                
                                    if($response[$i]->thumbnail_links !='')
                                    {
                                        $response[$i]->thumbnail_links = explode(",", $response[$i]->thumbnail_links);
                                    }
                                    else
                                    {
                                        $response[$i]->thumbnail_links = array();
                                    }
                                    
                                    
                                    // $response[$i]->filesurl = explode(",", $response[$i]->filesurl);
                                    // $response[$i]->file_names = explode(",", $response[$i]->file_names);
                                    // $response[$i]->thumbnail_links = explode(",", $response[$i]->thumbnail_links);
                                }
                                if(sizeof($response) > 0)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
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
                                        'status' => 'success',
                                        'message' => 'No '.$request->input('content_type').' found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                ////////////////////////////////////////////////////////////////////////
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
    
       
    //Student record dates fetch if these dates have record or not
    public function student_classactivity_dates(Request $request)
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
                        ////////////////////////////////////////////////////////////////////////
                        $school_id = $user->school_id;
                        $student_id = $user->id;
                        $current_date = date('Y-m-d');
                    
                        
                        $response=DB::table('assignments')
                        ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                        ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                        ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                        ->select('assignments.published_date as dates')
                        // ->whereIn('assignments.student_ids',[$student_id])
                        ->whereRaw('FIND_IN_SET("'.$student_id.'", sh_assignments.student_ids)')
                        ->where('assignments.school_id',$school_id)
                        ->where('assignments.deleted_status',0)
                        ->orderBy('assignments.created_at','desc')->get()->all();
                        
                        
                        
                        // if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                        // {
                        //     $response=$response->where('assignments.content_type','Assignment')->get()->all();
                        // }
                        // else
                        // {
                        //     $response=$response->where('assignments.content_type','Homework')->get()->all();
                        // }
                        
                        if(sizeof($response) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'ClassWork fetch successfully!',
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
                                'message' => 'No classWork found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        ////////////////////////////////////////////////////////////////////////
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
    
           
    //Student record
    public function student_class_activity_subjects(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('date') && $request->input('date')!='')
                {
                    $search_date=$request->input('date');
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
                        $school_id = $user->school_id;
                        $student_id = $user->id;
                        $current_date = date('Y-m-d');
                        $data=array();
                        
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $student_rec = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, sh_batches.name as batch_name FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                        if(sizeof($student_rec) > 0)
                        {
                            $student_rec=$student_rec[0];
                            if($student_rec->subject_group_id != "" && $student_rec->subject_group_id != null && $student_rec->subject_group_id != 0)
                            {
                                $subject_ids=DB::table('subject_groups')->select('subjects')->where('id',$student_rec->subject_group_id)->whereNull('deleted_at')->first()->subjects;
                                $subject_ids = array_map( 'trim', explode( ",",$subject_ids ) );
                                
                                foreach($subject_ids as $key => $sub_id)
                                {
                                    $rec=DB::table('subjects')->where('id',$sub_id)->where('academic_year_id',$academic_year->id)->whereNull('deleted_at')->first();
                                    $data[$key]['id']=(int) $rec->id;
                                    $data[$key]['name']=ucwords(strtolower($rec->name));
                                    
                                }
                                
                                
                                if($data && sizeof($data) > 0)
                                {
                                    $response=DB::table('assignments')
                                    ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                                    ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                                    ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                                    ->select('assignments.subject_id', 'assignments.published_date as dates')
                                    // ->whereIn('assignments.student_ids',[$student_id])
                                    ->whereRaw('FIND_IN_SET("'.$student_id.'", sh_assignments.student_ids)')
                                    ->where('assignments.school_id',$school_id)
                                    ->where('assignments.deleted_status',0)
                                    ->whereDate('assignments.due_date', '>=', $search_date)
                                    ->orderBy('assignments.created_at','desc')->get()->all();
                                    
                                    
                                    if(sizeof($response) > 0)
                                    {
                                        foreach($data as $pkey => $da)
                                        {
                                            foreach($response as $ckey => $res)
                                            {
                                                $response[$ckey]->subject_id=(int) $res->subject_id;
                                                
                                                if($res->subject_id==$da['id'])
                                                {
                                                    $data[$pkey]['flag']=1;
                                                    $data[$pkey]['date']=$res->dates;
                                                    
                                                    break;
                                                }
                                                else
                                                {
                                                    $data[$pkey]['flag']=0;
                                                    $data[$pkey]['date']='';
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        foreach($data as $key => $dat)
                                        {
                                            $data[$key]['flag']=0;
                                        }
                                    }
                                    
                                    if(sizeof($data) > 0)
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Subjects fetch successfully',
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
                                            'message' => 'No subject found!',
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
                                        'message' => 'No subjects found!',
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
                                    'message' => 'Subject group is not assigned!',
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
                        die;
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Date is required!',
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
                    'status' => 'success',
                    'message' => 'Invalid user!',
                    'data' => ''
                );
                return response()->json($this->response, 200);
                die;
            }
        }
    }
    
               
    //Student record
    public function student_class_activity(Request $request)
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
                        ////////////////////////////////////////////////////////////////////////
                        $school_id = $user->school_id;
                        $student_id = $user->id;
                        $current_date = date('Y-m-d');
                        
                        // DB::enableQueryLog();
                        
                        $response=DB::table('assignments')
                        ->join('classes', 'assignments.class_id', '=', 'classes.id', 'left')
                        ->join('subjects', 'assignments.subject_id', '=', 'subjects.id', 'left')
                        ->join('users', 'assignments.uploaded_by', '=', 'users.id', 'left')
                        ->select('assignments.*', 'classes.name as class_name', 'subjects.name as subject_name', 'users.name as teacher_name', 'users.id as teacher_id', 'users.avatar')
                        // ->whereIn('assignments.student_ids',[$student_id])
                        ->whereRaw('FIND_IN_SET("'.$student_id.'", sh_assignments.student_ids)')
                        ->where('assignments.school_id',$school_id)
                        ->where('assignments.deleted_status',0)
                        ->orderBy('assignments.created_at','desc');
                        if($request->input('date') && $request->input('date')!='')
                        {
                            if($request->input('subject_id') && $request->input('subject_id')!='')
                            {
                                if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                {
                                    // $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                    
                                    $response=$response->where('assignments.content_type','Assignment')->whereDate('assignments.due_date','>=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                }
                                else
                                {
                                    // $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                    
                                    $response=$response->where('assignments.content_type','Homework')->whereDate('assignments.due_date','>=',$request->input('date'))->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                }
                            }
                            else
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
                            
                        }
                        else
                        {
                            if($request->input('subject_id') && $request->input('subject_id')!='')
                            {
                                if($request->input('content_type')=='Assignment' || $request->input('content_type')=='assignment')
                                {
                                    $response=$response->where('assignments.content_type','Assignment')->where('assignments.published_date','<=',$current_date)->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
                                }
                                else
                                {
                                    $response=$response->where('assignments.content_type','Homework')->where('assignments.published_date','<=',$current_date)->where('assignments.subject_id','=',$request->input('subject_id'))->get()->all();
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
                            
                        }
                        
                        
                        
                        
                        
                        
                        foreach ($response as $i => $res)
                        {
                            $response[$i]->id=(int) $res->id;
                            $response[$i]->school_id=(int) $res->school_id;
                            $response[$i]->class_id=(int) $res->class_id;
                            $response[$i]->subject_id=(int) $res->subject_id;
                            $response[$i]->storage_type=(int) $res->storage_type;
                            $response[$i]->uploaded_by=(int) $res->uploaded_by;
                            $response[$i]->deleted_status=(int) $res->deleted_status;
                            $response[$i]->teacher_id=(int) $res->teacher_id;
                            $response[$i]->total_marks=(int) $res->total_marks;
                            // $response[$i]->created_at=date('Y-m-d H:i:s',strtotime($res->created_at));
                            
                            $re=DB::table('submit_material')->where('material_id',$res->id)->where('student_id',$student_id)->where('deleted_status',0)->get()->all();
                            
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
                            
                            if($response[$i]->files !='')
                            {
                                $response[$i]->files = explode(",", $response[$i]->files);
                            }
                            else
                            {
                                $response[$i]->files = array();
                            }
                            
                                                        
                            if($response[$i]->filesurl !='')
                            {
                                $response[$i]->filesurl = explode(",", $response[$i]->filesurl);
                            }
                            else
                            {
                                $response[$i]->filesurl = array();
                            }
                            
                                                        
                            if($response[$i]->file_names !='')
                            {
                                $response[$i]->file_names = explode(",", $response[$i]->file_names);
                            }
                            else
                            {
                                $response[$i]->file_names = array();
                            }
                            
                                                        
                            if($response[$i]->thumbnail_links !='')
                            {
                                $response[$i]->thumbnail_links = explode(",", $response[$i]->thumbnail_links);
                            }
                            else
                            {
                                $response[$i]->thumbnail_links = array();
                            }
                            
                            
                            // $response[$i]->filesurl = explode(",", $response[$i]->filesurl);
                            // $response[$i]->file_names = explode(",", $response[$i]->file_names);
                            // $response[$i]->thumbnail_links = explode(",", $response[$i]->thumbnail_links);
                        }
                        if(sizeof($response) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
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
                                'status' => 'success',
                                'message' => 'No '.$request->input('content_type').' found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        ////////////////////////////////////////////////////////////////////////
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
    
    
    
    
    
}
