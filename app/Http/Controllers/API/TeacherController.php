<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class TeacherController extends Controller
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

    

    //Get Employee details - Nationality
    public function get_empcountries(Request $request)
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
                    $data= DB::table('countries')->get()->all();
                    
                    if(sizeof($data) > 0)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Countries fetch successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No countries found!',
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
      
    //Get Employee details - Department
    public function get_empdepartment(Request $request)
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
                    $data= DB::table('departments')->select('id','name','code','type')->where('id', $user->department_id)->where('deleted_at', '=', '0')->get()->all();
                    
                    if(sizeof($data) > 0)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Departments fetch successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No department found!',
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
         
    //Get Employee details - Designation
    public function get_empdesignation(Request $request)
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
                    $department= DB::table('departments')->select('id','name','code','type')->where('id', $user->department_id)->where('deleted_at', '=', '0')->first();
                    if($department)
                    {
                        $data=DB::table('role_categories')->select('id','category as name')->where('department_id', $user->department_id)->where('deleted_at', '=', '0')->get()->all();
                        if(sizeof($data) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Designation fetch successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No designation found!',
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
                            'message' => 'No department found!',
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
   
            
    //update Employee image
    public function update_employeeimg(Request $request)
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
                    $user->permissions=json_decode($user->permissions);
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="profile-edit")
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
                                        'message' => 'You do not have permission to edit profile!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    
                    if($request->file('avatar') && $request->hasFile('avatar'))
                    {
                        $file=$request->file('avatar');
                        $filename = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        // $check=in_array($extension,$allowedfileExtension);
                        $firstname = pathinfo($filename, PATHINFO_FILENAME);
                        // $db_fileName = $firstname.'_'.time().'.'.$file->getClientOriginalExtension(); 
                        $db_fileName = $firstname.'.'.$file->getClientOriginalExtension(); 
                        
                        $file->move('../uploads/user', $db_fileName);
                        $updatedimage=$db_fileName;
                            
                        $data=DB::table('users')
                            ->where('id', $user->id)
                            ->update(['avatar' => $updatedimage]);
                            
                        if($data)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Profile picture updated successfully!',
                                'data' => $db_fileName
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No image found!',
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
               
    //update Employee details
    public function update_employeedetails(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('name') && $request->input('name')!='')
                {   
                    if($request->input('job_title') && $request->input('job_title')!='')
                    {   
                        if($request->input('dob') && $request->input('dob')!='')
                        {   
                            if($request->input('qualification') && $request->input('qualification')!='')
                            {   
                                if($request->input('office_phone') && $request->input('office_phone')!='')
                                {   
                                    if($request->input('address') && $request->input('api_token')!='')
                                    {   
                                        if($request->input('city') && $request->input('city')!='')
                                        {
                                            $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                                            if($user)
                                            {
                                                $user->permissions=json_decode($user->permissions);
                    
                                                if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                                                {
                                                    foreach($user->permissions as $key => $perm)
                                                    {
                                                        if($perm->permission=="profile-edit")
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
                                                                    'message' => 'You do not have permission to edit profile!',
                                                                    'data' => ''
                                                                );
                                                                return response()->json($this->response, 200);
                                                                die;
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                $data=DB::table('users')
                                                    ->where('id', $user->id)
                                                    ->update(['name' => $request->input('name'), 'job_title' => $request->input('job_title'), 'dob' => $request->input('dob'), 'qualification' => $request->input('qualification'), 'office_phone' => $request->input('office_phone'), 'address' => $request->input('address'), 'city' => $request->input('city'), 'gender' => $request->input('gender'), 'nationality' => $request->input('nationality'), 'passport_number' => $request->input('passport_number'), 'fax' => $request->input('fax'), 'country' => $request->input('country'), 'marital_status' => $request->input('marital_status')]);
                                                
                                                if($data)
                                                {
                                                    $this->response=array
                                                    (
                                                        'status' => 'success',
                                                        'message' => 'Profile updated successfully!',
                                                        'data' => ''
                                                    );
                                                    return response()->json($this->response, 200);
                                                }
                                                else
                                                {
                                                    $this->response=array
                                                    (
                                                        'status' => 'success',
                                                        'message' => 'No updation found!',
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
                                                'message' => 'City is required!',
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
                                            'message' => 'Address is required!',
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
                                        'message' => 'Office Phone is required!',
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
                                    'message' => 'Qualification is required!',
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
                                'message' => 'DOB is required!',
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
                            'message' => 'Designation is required!',
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
                        'message' => 'Name is required!',
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
   
   
   
    //Get Employee details
    public function get_employeeimage(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $user = DB::table('users')->select('*')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
    
                if($user)
                {
                    $user->permissions=json_decode($user->permissions);
                    
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="profile-index")
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
                                        'message' => 'You do not have permission to view profile!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Avatar fetch successfully!',
                        'data' => $user
                    );
                    return response()->json($this->response, 200);
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
   
   
    //Get Employee details
    public function get_employeedetails(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $user = DB::table('users')->select('*')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
    
                if($user)
                {
                    $user->permissions=json_decode($user->permissions);
                    
                    if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                    {
                        foreach($user->permissions as $key => $perm)
                        {
                            if($perm->permission=="profile-index")
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
                                        'message' => 'You do not have permission to view profile!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                            }
                        }
                    }
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'User found successfully!',
                        'data' => $user
                    );
                    return response()->json($this->response, 200);

                    
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


    public function get_classes(Request $request)
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $class_teachers= DB::table('batches')
                                    ->join('classes', 'batches.class_id', '=', 'classes.id', 'left')
                                    ->select('batches.class_id as id', 'classes.name')
                                    ->where('batches.school_id',$user->school_id)
                                    ->where('batches.teacher_id',$user->id)
                                    ->where('batches.academic_year_id',$academic_year->id)
                                    ->where('classes.academic_year_id',$academic_year->id)
                                    ->whereNull('classes.deleted_at')
                                    ->whereNull('batches.deleted_at')
                                    ->get()->all();
                                    
                    $subject_teachers=  DB::table('assign_subjects')
                                        ->join('classes', 'assign_subjects.class_id', '=', 'classes.id', 'left')
                                        ->select('assign_subjects.class_id as id', 'classes.name')
                                        ->where('assign_subjects.school_id',$user->school_id)
                                        ->whereNull('assign_subjects.deleted_at')
                                        ->where('classes.academic_year_id',$academic_year->id)
                                        ->whereNull('classes.deleted_at')
                                        ->where(function ($match) use ($user){
                                            $match->where('assign_subjects.teacher_id', $user->id)
                                            ->orWhere('assign_subjects.assistant_id', $user->id);
                                        })->get()->all();
                                        
                    $data=array_merge($class_teachers,$subject_teachers);
                    $data=array_unique($data, SORT_REGULAR);
                    
                    $data=array_values($data);
                    
                    if(sizeof($data) > 0)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Classes found successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No classes found!',
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


    public function get_batches(Request $request)
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
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
        
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        
                        $section_teacher=DB::table('batches')
                                        ->select('id', 'name')
                                        ->where('school_id', $user->school_id)
                                        ->where('class_id', $request->input('class_id'))
                                        ->where('teacher_id', $user->id)
                                        ->where('academic_year_id', $academic_year->id)
                                        ->whereNull('deleted_at')
                                        ->get()->all();
                        
                        //teacher_type 1 means Batch/Section Teacher
                        //teacher_type 2 means Subject Teacher
                        if(sizeof($section_teacher) > 0)
                        {
                            foreach($section_teacher as $key => $sec_tech)
                            {
                                $section_teacher[$key]->teacher_type=1;
                            }
                        }
                        
                        
                        $subject_teacher=DB::table('assign_subjects')
                                        ->join('batches', 'assign_subjects.batch_id', '=', 'batches.id', 'left')
                                        ->select('assign_subjects.batch_id as id', 'batches.name')
                                        ->where('assign_subjects.school_id', $user->school_id)
                                        ->where('assign_subjects.class_id', $request->input('class_id'))
                                        ->where(function ($match) use ($user){
                                            $match->where('assign_subjects.teacher_id', $user->id)
                                            ->orWhere('assign_subjects.assistant_id', $user->id);
                                        })->whereNull('assign_subjects.deleted_at')
                                        ->where('batches.academic_year_id', $academic_year->id)
                                        ->whereNull('batches.deleted_at')
                                        ->get()->all();
                                        
                        //teacher_type 1 means Batch/Section Teacher
                        //teacher_type 2 means Subject Teacher
                        if(sizeof($subject_teacher) > 0)
                        {
                            foreach($subject_teacher as $key => $sub_tech)
                            {
                                $subject_teacher[$key]->teacher_type=2;
                            }
                        }
                        
                                        
                        $data=array_merge($subject_teacher,$section_teacher);
                        $data=array_unique($data, SORT_REGULAR);
                        $a_data=array();
                        
                        foreach ($data as $value)
                        {
                            $a_data[$value->id] = $value;
                        }
                        
                        $data=array_values($a_data);
                        
                        
                        if(sizeof($data) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Sections found successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No section found!',
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
                        'message' => 'Class Id is required!',
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

}
