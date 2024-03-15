<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
use URL; 
 

class MessagesController extends Controller
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



    //create conversation for messages   
    public function create_conversation(Request $request)
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
            
            if($request->input('role_id') && $request->input('role_id')!='')
            {
            }
            else
            {
                $errors['role_id']='Role Id is required';
            }
                           
            if($request->input('user_id') && $request->input('user_id')!='')
            {
            }
            else
            {
                $errors['user_id']='User Id is required';
            }
                                          
            if($request->input('subject') && $request->input('subject')!='')
            {
            }
            else
            {
                $errors['subject']='Subject is required';
            }
                        
            if($request->input('message') && $request->input('message')!='')
            {
            }
            else
            {
                $errors['message']='Message is required';
            }
            
            if(sizeof($errors) > 0)
            {
                $this->response=array
                (
                    'status' => 'error',
                    'message' => 'Fields are required!',
                    'data' => $errors
                );
                return response()->json($this->response, 400);
            }
            else
            {
                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                if($user)
                {
                    $create_conversation=DB::table('conversations')
                    ->insert(['subject'=>$request->input('subject'), 'creator_id'=>$user->id]);
                    if($create_conversation)
                    {
                        $conversation_id = DB::getPdo()->lastInsertId();
                        
                        $all_participents='';
                        
                        $all_users=$request->input('user_id');
                        if(is_array($all_users))
                        {
                            foreach($all_users as $key => $s_user)
                            {
                                if(sizeof($all_users) == ($key+1))
                                {
                                    $all_users[$key+1]=$user->id;
                                }
                            }
                            
                            foreach($all_users as $s_user)
                            {
                                $all_participents=DB::table('participants')
                                ->insert(['conversation_id'=>$conversation_id, 'user_id'=>$s_user]);
                            }
                        }
                        else
                        {
                            $all_participents=DB::table('participants')
                            ->insert(['conversation_id'=>$conversation_id, 'user_id'=>$all_users]);
                        }
                        
                        //////////////////////////////////////
                        // $allowedfileExtension=['pdf','jpg','png','docx'];
                        $all_files=array();
                        $message_data=array();
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
                                
                                $file->move('../uploads/attachment', $db_fileName);
                                $all_files[]=$db_fileName;
                            }
                            
                            $message_data=array(
                                'conversation_id' => $conversation_id,
                                'message_body' => $request->input('message'),
                                'attachments' => implode(',',$all_files),
                                'sender_id' => $user->id
                            );
                        }
                        else
                        {
                            $message_data=array(
                                'conversation_id' => $conversation_id,
                                'message_body' => $request->input('message'),
                                'attachments' => implode(',',$all_files),
                                'sender_id' => $user->id
                            );
                        }
                        
                        ///////////////////////////////////
                        $response=DB::table('messages')->insert($message_data);
                        if($response)
                        {
                            $message_data['attachments']=explode(',',$message_data['attachments']);
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Conversation created successfully!',
                                'data' => $message_data
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
                        ///////////////////////////////////
                        //////////////////////////////////////
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'error',
                            'message' => 'Something went wrong, please try again!',
                            'data' => ''
                        );
                        return response()->json($this->response, 400);
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
    
public function unique_array($my_array, $key) { 
    $result2 = array(); 
    $result = array(); 
    $i = 0; 
    $key_array = array(); 
    
    foreach($my_array as $val) { 
        if (!in_array($val->$key, $key_array)) { 
            $key_array[$i] = $val->$key; 
            // $result[$i] = $val; 
            array_push($result,$val);
        } 
        $i++; 
    } 
    return $result; 
}

    
    //fetch roles related users
    public function get_roleDetails(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('role_id') && $request->input('role_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        if($user->role_id==3)//student //just play for the roles 3 or 4
                        {
                            if($request->input('role_id')==3)
                            {
                                $student_rec = DB::select("SELECT s.class_id, s.batch_id FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                                if(sizeof($student_rec) > 0)
                                {
                                    $student_rec=$student_rec[0];
                                    $data=DB::select("SELECT s.id, s.name, s.avatar FROM sh_students_$user->school_id s WHERE s.class_id='$student_rec->class_id' AND s.batch_id='$student_rec->batch_id' AND s.deleted_at='0' AND s.id !='$user->id' ");
                                    
                                    if($data)
                                    {
                                        foreach($data as $key => $d)
                                        {
                                            $data[$key]->id=(int) $d->id;
                                        }
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Students fetch successfully!',
                                            'data' => $data
                                        );
                                        return response()->json($this->response, 200);
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'No Students found!',
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
                            else if($request->input('role_id')==4)
                            {
                                $student_rec = DB::select("SELECT s.class_id, s.batch_id FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$user->id' ");
                                if(sizeof($student_rec) > 0)
                                {
                                    $student_rec=$student_rec[0];
                                    
                                    $subject_teachers=DB::table('assign_subjects')
                                    ->join('users', 'assign_subjects.teacher_id', '=', 'users.id', 'left')
                                    ->select('users.id as id', 'users.name', 'users.avatar')
                                    ->where('assign_subjects.class_id',$student_rec->class_id)
                                    ->where('assign_subjects.batch_id',$student_rec->batch_id)
                                    ->whereNull('assign_subjects.deleted_at')
                                    ->where('users.deleted_at', '=', '0')
                                    ->get()->all(); 
                                    
                                    $section_teacher=DB::table('batches')
                                    ->join('users', 'batches.teacher_id', '=', 'users.id')
                                    ->select('users.id as id', 'users.name', 'users.avatar')
                                    ->where('batches.class_id',$student_rec->class_id)
                                    ->where('batches.id',$student_rec->batch_id)
                                    ->whereNull('batches.deleted_at')
                                    ->where('users.deleted_at', '=', '0')
                                    ->get()->all(); 
                                    
                                    
                                    $data=array_merge($subject_teachers,$section_teacher);
                                    $data=array_unique($data, SORT_REGULAR);

                                    $data = array_values($data);
                                    
                                    
                                    if(sizeof($data) > 0)
                                    {
                                        foreach($data as $key => $d)
                                        {
                                            $data[$key]->id=(int) $d->id;
                                        }
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Teachers fetch successfully!',
                                            'data' => $data
                                        );
                                        return response()->json($this->response, 200);
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'No teachers found!',
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
                            
                        }
                        else if($user->role_id==2)//parent //just play for the role 1 or 4
                        {
                            if($request->input('role_id')==1)
                            {
                                $admin_rec=DB::table('users')->select('id', 'name', 'avatar')->where('role_id',$request->input('role_id'))->where('school_id',$user->school_id)->where('deleted_at', '=', '0')->first();
                                if($admin_rec)
                                {
                                    $admin_rec->id=(int) $admin_rec->id;
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Admin fetch successfully!',
                                        'data' => $admin_rec
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'No Admin found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                            }
                            else if($request->input('role_id')==4)
                            {
                                $childs=DB::table('student_guardians')->join('users','student_guardians.student_id', '=', 'users.id', 'left')->join('students_'.$user->school_id.' as s_view','users.id', '=', 's_view.id', 'right')->select('student_guardians.student_id','users.name as student_name','users.avatar as student_avatar', 's_view.class_id', 's_view.batch_id')->where('student_guardians.guardian_id',$user->id)->whereNull('student_guardians.deleted_at')->get()->all(); 
                                
                                $teachers_record=array();
                                if(sizeof($childs) > 0)
                                {
                                    foreach($childs as $key => $child)
                                    {
                                        $subject_teachers=DB::table('assign_subjects')
                                        ->join('users', 'assign_subjects.teacher_id', '=', 'users.id', 'left')
                                        ->select('users.id as id', 'users.name', 'users.avatar')
                                        ->where('assign_subjects.class_id',$child->class_id)
                                        ->where('assign_subjects.batch_id',$child->batch_id)
                                        ->whereNull('assign_subjects.deleted_at')
                                        ->where('users.deleted_at', '=', '0')
                                        ->get()->all(); 
                                        
                                        $section_teacher=DB::table('batches')
                                        ->join('users', 'batches.teacher_id', '=', 'users.id')
                                        ->select('users.id as id', 'users.name', 'users.avatar')
                                        ->where('batches.class_id',$child->class_id)
                                        ->where('batches.id',$child->batch_id)
                                        ->whereNull('batches.deleted_at')
                                        ->where('users.deleted_at', '=', '0')
                                        ->get()->all(); 
                                        
                                        
                                        $data=array_merge($subject_teachers,$section_teacher);
                                        $data=array_unique($data, SORT_REGULAR);
                                        
                                        if(sizeof($data) > 0)
                                        {
                                            foreach($data as $i_key => $d)
                                            {
                                                $data[$i_key]->id=(int) $d->id;
                                                $data[$i_key]->student_id=(int) $childs[$key]->student_id;
                                                $data[$i_key]->student_name=$childs[$key]->student_name;
                                            }
                                        }
                                        
                                        $teachers_record=array_merge($teachers_record,$data);
                                        $teachers_record=array_unique($teachers_record, SORT_REGULAR);
                                        // $teachers_record[]=$data;
                                        // array_push($teachers_record, $data);
                                    }
                                    // $teachers_record= (array) $teachers_record;
                                    
                                    // $teachers_record  = json_encode($teachers_record);
                                    // $teachers_record = json_decode($teachers_record, true);
                                    // $teachers_record=array_unique($teachers_record, SORT_REGULAR);
                                    
                                    $teachers_record=$this->unique_array($teachers_record, "id");
                                    
                                    // $teachers_record=$this->unique_array($teachers_record, "id");
                                    
                                    if(sizeof($teachers_record) > 0)
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Teachers fetch successfully!',
                                            'data' => $teachers_record
                                        );
                                        return response()->json($this->response, 200);
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'No teacher found!',
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
                                        'message' => 'No childs found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                                
                                
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
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Role Id is required!',
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
                
    //fetch roles
    public function get_roles(Request $request)
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
                    if($user->role_id==3)//student
                    {
                        $roles=DB::table('roles')->select('id as role_id','name')->whereIn('id',[3,4])->get()->all();
                        if(sizeof($roles) > 0)
                        {
                            foreach($roles as $key => $ro)
                            {
                                $roles[$key]->role_id=(int) $ro->role_id;
                                
                                if($ro->name=='employee')
                                {
                                    $roles[$key]->name='teacher';
                                }
                                $roles[$key]->name=ucfirst($ro->name);
                            }
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Roles fetch successfully!',
                                'data' => $roles
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Roles not found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                        }
                        
                    }
                    else if($user->role_id==2)//parent
                    {
                        $roles=DB::table('roles')->select('id as role_id','name')->whereIn('id',[1,4])->get()->all();
                        if(sizeof($roles) > 0)
                        {
                            foreach($roles as $key => $ro)
                            {
                                $roles[$key]->role_id=(int) $ro->role_id;
                                
                                if($ro->name=='employee')
                                {
                                    $roles[$key]->name='teacher';
                                }
                                $roles[$key]->name=ucfirst($ro->name);
                            }
                            
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Roles fetch successfully!',
                                'data' => $roles
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Roles not found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
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
            
            
    public function send_message(Request $request)
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
            
            if($request->input('conversation_id') && $request->input('conversation_id')!='')
            {
            }
            else
            {
                $errors['conversation_id']='Conversation Id is required';
            }
                        
            if($request->input('message') && $request->input('message')!='')
            {
            }
            else
            {
                $errors['message']='Message is required';
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
                    // $allowedfileExtension=['pdf','jpg','png','docx'];
                    $all_files=array();
                    $data=array();
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
                            
                            $file->move('../uploads/attachment', $db_fileName);
                            $all_files[]=$db_fileName;
                        }
                        
                        $data=array(
                            'conversation_id' => $request->input('conversation_id'),
                            'message_body' => $request->input('message'),
                            'attachments' => implode(',',$all_files),
                            'sender_id' => $user->id
                        );
                    }
                    else
                    {
                        $data=array(
                            'conversation_id' => $request->input('conversation_id'),
                            'message_body' => $request->input('message'),
                            'attachments' => '',
                            'sender_id' => $user->id
                        );
                    }
                    /////////////////////
                    $response=DB::table('messages')->insert($data);
                    if($response)
                    {
                        if($data['attachments']!='')
                        {
                            $data['attachments']=explode(',',$data['attachments']);
                        }
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Message sent successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Something went wrong, please try again!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    /////////////////////
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

    //for inbox details
    public function inbox_conversation_detail(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('conversation_id') && $request->input('conversation_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $data=DB::table('messages')
                        ->join('users', 'messages.sender_id', '=', 'users.id', 'left')
                        ->select('messages.sender_id', 'users.name as sender_name', 'users.avatar', 'messages.message_body as message', 'messages.attachments', 'messages.created_at', DB::raw('(CASE WHEN sh_messages.sender_id = "'.$user->id.'" THEN "right" ELSE "left" END) AS placement'))
                        ->where('messages.conversation_id',$request->input('conversation_id'))
                        ->where('users.deleted_at','=','0')
                        ->get()->all();
                        
                        if(sizeof($data) > 0)
                        {
                            foreach($data as $key => $d)
                            {
                                $data[$key]->sender_id=(int) $d->sender_id;
                                if($d->attachments!='')
                                {
                                    $data[$key]->attachments=explode(',', $d->attachments);
                                }
                                else
                                {
                                    $data[$key]->attachments=array();
                                }
                            }
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Conversation fetch successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No messages found!',
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
                        'message' => 'Conversation Id is required!',
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


    //For Inbox
    public function inbox_conversations(Request $request)
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
                    $all_inbox=DB::table('participants')
                    ->join('conversations', 'participants.conversation_id', '=', 'conversations.id', 'left')
                    ->join('users', 'conversations.creator_id', '=', 'users.id', 'left')
                    ->join('messages', 'participants.conversation_id', '=', 'messages.conversation_id')
                    ->select('participants.conversation_id', 'conversations.subject', 'conversations.creator_id', 'users.name as creator_name', 'users.avatar as creator_avatar', DB::raw('(select sh_messages.message_body as last_message from sh_messages where sh_messages.conversation_id=sh_conversations.id order by sh_messages.created_at desc limit 1 ) as last_message'), 'conversations.created_at')
                    ->where('participants.user_id',$user->id)
                    ->where('participants.delete_status', '=', '0')
                    ->whereNull('conversations.deleted_at')
                    ->groupBy('messages.conversation_id')
                    ->orderBy('messages.created_at','desc')
                    ->get()->all();
                    
                    if(sizeof($all_inbox) > 0)
                    {
                        foreach($all_inbox as $key => $inbox)
                        {
                            $participants=DB::table('participants')
                            ->join('users', 'participants.user_id', '=', 'users.id')
                            ->select('users.id as user_id', 'users.name', 'users.avatar')
                            ->where('participants.conversation_id', $inbox->conversation_id)
                            // ->where('participants.user_id', '!=', $user->id)
                            ->where('participants.user_id', '!=', $inbox->creator_id)
                            ->where('participants.delete_status', '=', '0')
                            ->get()->all();
                            
                            if(sizeof($participants) > 0)
                            {
                                $all_inbox[$key]->participants=$participants;
                            }
                            else
                            {
                                $all_inbox[$key]->participants=array();
                            }
                        }
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Conversations fetch successfully!',
                            'data' => $all_inbox
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No Conversation found!',
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
    
    
    //For Sent
    public function sent_conversations(Request $request)
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
                    $sents=DB::table('conversations')
                    ->join('participants', 'conversations.id', '=', 'participants.conversation_id', 'left')
                    ->join('messages', 'conversations.id', '=', 'messages.conversation_id', 'left')
                    ->select('conversations.id as conversation_id', 'conversations.subject', 'conversations.creator_id', 'conversations.created_at', DB::raw('(select sh_messages.message_body as last_message from sh_messages where sh_messages.conversation_id=sh_conversations.id order by sh_messages.created_at desc limit 1 ) as last_message'))
                    ->where('conversations.creator_id', $user->id)
                    ->where('participants.user_id', '!=', $user->id)
                    ->whereNull('conversations.deleted_at')
                    ->groupBy('conversations.id')
                    ->orderBy('conversations.created_at','desc')
                    ->get()->all();


                    if(sizeof($sents) > 0)
                    {
                        foreach($sents as $key => $sent)
                        {
                            $sents[$key]->conversation_id=(int) $sent->conversation_id;
                            $sents[$key]->creator_id=(int) $sent->creator_id;
                            
                            $participants=DB::table('participants')
                            ->join('users', 'participants.user_id', '=', 'users.id')
                            ->select('users.id as user_id', 'users.name', 'users.avatar')
                            ->where('participants.conversation_id', $sent->conversation_id)
                            ->where('participants.user_id', '!=', $user->id)
                            ->where('participants.user_id', '!=', $sent->creator_id)
                            ->where('participants.delete_status', '=', '0')
                            ->get()->all();
                            
                            if(sizeof($participants) > 0)
                            {
                                // foreach($participants as $key => $part)
                                // {
                                //     $participants[$key]->user_id=(int) $part->user_id;
                                // }
                                $sents[$key]->participants=$participants;
                            }
                            else
                            {
                                $sents[$key]->participants=array();
                            }
                        }
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Conversations fetch successfully!',
                            'data' => $sents
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No Conversation found!',
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
