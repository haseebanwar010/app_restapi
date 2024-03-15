<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class AppointmentController extends Controller
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



    
    public function get_teachers(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('child_id') && $request->input('child_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $child_id=$request->input('child_id');
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $student_rec = DB::select("SELECT s.class_id, s.batch_id FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$child_id' ");
                        if(sizeof($student_rec) > 0)
                        {
                            $student_rec=$student_rec[0];
                            
                            $subject_teachers=DB::table('assign_subjects')
                            ->join('users', 'assign_subjects.teacher_id', '=', 'users.id', 'left')
                            ->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')
                            ->select('users.id as id', 'users.name', 'users.avatar', 'role_categories.category as role_name')
                            ->where('assign_subjects.class_id',$student_rec->class_id)
                            ->where('assign_subjects.batch_id',$student_rec->batch_id)
                            ->whereNull('assign_subjects.deleted_at')
                            ->where('users.deleted_at', '=', '0')
                            ->get()->all(); 
                            
                            $section_teacher=DB::table('batches')
                            ->join('users', 'batches.teacher_id', '=', 'users.id')
                            ->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')
                            ->select('users.id as id', 'users.name', 'users.avatar', 'role_categories.category as role_name')
                            ->where('batches.class_id',$student_rec->class_id)
                            ->where('batches.id',$student_rec->batch_id)
                            ->whereNull('batches.deleted_at')
                            ->where('users.deleted_at', '=', '0')
                            ->get()->all(); 
                            
                            
                            $admin_rec=DB::table('users')->select('users.id', 'users.name', 'users.avatar', 'roles.name as role_name')->join('roles', 'users.role_id', '=', 'roles.id', 'left')->where('users.role_id',1)->where('users.school_id',$user->school_id)->where('users.deleted_at', '=', '0')->get()->all();
                            
                            
                            $data=array_merge($subject_teachers,$section_teacher);
                            $data=array_unique($data, SORT_REGULAR);
                            
                            $data=array_values($data);
                                                        
                            $data=array_merge($data,$admin_rec);
                            $data=array_unique($data, SORT_REGULAR);
                            

                            $data=array_values($data);
                            
                            if(sizeof($data) > 0)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Users fetch successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No user found!',
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
                        'status' => 'success',
                        'message' => 'Child Id is required!',
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
      



    //parent + teacher side
    public function update_appointment(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            $errors=array();
            $child_id='';
            if($request->input('api_token') && $request->input('api_token')!='')
            {
            }
            else
            {
                $errors['api_token']='Api Token is required';
            }
                        
            if($request->input('date') && $request->input('date')!='')
            {
            }
            else
            {
                $errors['date']='Date is required';
            }
                        
            if($request->input('time') && $request->input('time')!='')
            {
            }
            else
            {
                $errors['time']='Time is required';
            }
                        
            if($request->input('description') && $request->input('description')!='')
            {
            }
            else
            {
                $errors['description']='Description is required';
            }
                        
            if($request->input('user_ids') && $request->input('user_ids')!='')
            {
            }
            else
            {
                $errors['user_ids']='User is required';
            }
                                      
            if($request->input('appointment_id') && $request->input('appointment_id')!='')
            {
            }
            else
            {
                $errors['appointment_id']='Appointment Id is required';
            }
                                    
            if($request->input('child_id') && $request->input('child_id')!='')
            {
                $child_id=$request->input('child_id');
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
                    $data=array(
                        'date' => date('Y-m-d', strtotime($request->input('date'))),
                        'time' => date('h:i:s', strtotime($request->input('time'))),
                        'description' => $request->input('description'),
                        // 'user_ids' => implode(',',$request->input('user_ids')),
                        'user_ids' => $request->input('user_ids'),
                        'child_id' => $child_id,
                        'created_by' => $user->id
                    );
                    $result=DB::table('appointments')->where('id',$request->input('appointment_id'))->update($data);
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Appointment updated successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Please try again!',
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
                        'message' => 'Invalid user!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                }
            }
        }
    }
    
    //parent + teacher side
    public function create_appointment(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            $errors=array();
            $child_id='';
            if($request->input('api_token') && $request->input('api_token')!='')
            {
            }
            else
            {
                $errors['api_token']='Api Token is required';
            }
                        
            if($request->input('date') && $request->input('date')!='')
            {
            }
            else
            {
                $errors['date']='Date is required';
            }
                        
            if($request->input('time') && $request->input('time')!='')
            {
            }
            else
            {
                $errors['time']='Time is required';
            }
                        
            if($request->input('description') && $request->input('description')!='')
            {
            }
            else
            {
                $errors['description']='Description is required';
            }
                        
            if($request->input('user_ids') && $request->input('user_ids')!='')
            {
            }
            else
            {
                $errors['user_ids']='User is required';
            }
                                    
            if($request->input('child_id') && $request->input('child_id')!='')
            {
                $child_id=$request->input('child_id');
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
                    $data=array(
                        'date' => date('Y-m-d', strtotime($request->input('date'))),
                        'time' => date('h:i:s', strtotime($request->input('time'))),
                        'description' => $request->input('description'),
                        // 'user_ids' => implode(',',$request->input('user_ids')),
                        'user_ids' => $request->input('user_ids'),
                        'child_id' => $child_id,
                        'created_by' => $user->id
                    );
                    $result=DB::table('appointments')->insert($data);
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Appointment created successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Please try again!',
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
                        'message' => 'Invalid user!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                }
            }
        }
    }
     
    public function get_appointments(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('child_id') && $request->input('child_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $appointments=DB::table('appointments')->select('appointments.user_ids as id', 'users.name', 'role_categories.category as role_name', 'users.avatar','appointments.id as appointment_id', 'appointments.status', 'date', 'time', 'description')->join('users', 'appointments.user_ids', '=', 'users.id', 'left')->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')->where('appointments.created_by',$user->id)->where('appointments.child_id', $request->input('child_id'))->whereNull('appointments.deleted_at')->where('users.deleted_at', '=', '0')->get()->all();
                        
                        if(sizeof($appointments) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Appointment fetch successfully!',
                                'data' => $appointments
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No appointment found!',
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
                        'message' => 'Child Id is required!',
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
        
    public function approved_appointments(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('child_id') && $request->input('child_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $appointments=DB::table('appointments')->select('appointments.user_ids as id', 'users.name', 'role_categories.category as role_name', 'users.avatar','appointments.id as appointment_id', 'appointments.status', 'date', 'time', 'description')->join('users', 'appointments.user_ids', '=', 'users.id', 'left')->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')->where('appointments.created_by',$user->id)->where('appointments.status','Approved')->where('appointments.child_id', $request->input('child_id'))->whereNull('appointments.deleted_at')->where('users.deleted_at', '=', '0')->get()->all();
                        
                        if(sizeof($appointments) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Appointment fetch successfully!',
                                'data' => $appointments
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No appointment found!',
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
                        'message' => 'Child Id is required!',
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
        
    public function refused_appointments(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('child_id') && $request->input('child_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $appointments=DB::table('appointments')->select('appointments.user_ids as id', 'users.name', 'role_categories.category as role_name', 'users.avatar','appointments.id as appointment_id', 'appointments.status', 'date', 'time', 'description')->join('users', 'appointments.user_ids', '=', 'users.id', 'left')->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')->where('appointments.created_by',$user->id)->where('appointments.status','Refused')->where('appointments.child_id', $request->input('child_id'))->whereNull('appointments.deleted_at')->where('users.deleted_at', '=', '0')->get()->all();
                        
                        if(sizeof($appointments) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Appointment fetch successfully!',
                                'data' => $appointments
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No appointment found!',
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
                        'message' => 'Child Id is required!',
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
    
    public function pending_appointments(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('child_id') && $request->input('child_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    if($user)
                    {
                        $appointments=DB::table('appointments')->select('appointments.user_ids as id', 'users.name', 'role_categories.category as role_name', 'users.avatar','appointments.id as appointment_id', 'appointments.status', 'date', 'time', 'description')->join('users', 'appointments.user_ids', '=', 'users.id', 'left')->join('role_categories', 'users.role_category_id', '=', 'role_categories.id', 'left')->where('appointments.created_by',$user->id)->where('appointments.status','Pending')->where('appointments.child_id', $request->input('child_id'))->whereNull('appointments.deleted_at')->where('users.deleted_at', '=', '0')->get()->all();
                        
                        if(sizeof($appointments) > 0)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Appointment fetch successfully!',
                                'data' => $appointments
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No appointment found!',
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
                        'message' => 'Child Id is required!',
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
