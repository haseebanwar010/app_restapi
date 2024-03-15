<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use CommonHelper;
use Mail;
use App\School;
use Illuminate\Support\Facades\DB;
 

class AuthController extends Controller
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


    public function reset_password(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('password') && $request->input('password')!='')
                {
                    if($request->input('confirm_password') && $request->input('confirm_password')!='')
                    {
                        if($request->input('password')==$request->input('confirm_password'))
                        {
                            $user = DB::table('users')->select('*')->where('api_token',$request->input('api_token'))->where('deleted_at', '=', 0)->first();
                            if($user)
                            {
                                $result = DB::table('users')
                                ->where('id', $user->id)
                                ->update([
                                    'password' => md5($request->input('password'))
                                ]);
                                
                                if($result)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Password changed successfully!',
                                        'data' => ['api_token' => $user->api_token]
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'You can not use previous password!',
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
                                'message' => 'New Password & Confirm Password should be same!',
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
                            'message' => 'Confirm password is required!',
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
                        'message' => 'New Password is required!',
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


    public function forgot_password(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('email') && $request->input('email')!='')
            {
                $stu_id=$request->input('email');
                $user = DB::table('users')->select('*')
                        ->where(function ($match) use ($stu_id) {
                            $match->where('email', $stu_id)
                            ->orWhere('rollno', $stu_id);
                        })
                        ->where('deleted_at', '=', 0)->first();
                if($user)
                {
                    // echo '<pre>';print_r($user);die;
                    
                    if($user->api_token=='')
                    {
                        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $random_Usertoken=$this->generate_string($permitted_chars, 20);
    
                        $result=DB::table('users')->where(function ($match) use ($user){
                            $match->where('users.id', $user->id);
                        })->update(array(
                           'users.api_token' => $random_Usertoken
                        ));
                        
                        $user->api_token=$random_Usertoken;
                    }
                    $to_email='';
                    if($user->role_id && ($user->role_id==2 || $user->role_id==4) )//parent
                    {
                        if($user->email && $user->email!='')
                        {
                            $to_email=$user->email;
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Email is not assigned, please contact your admin support!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        
                        
                        $code = rand(100000,999999);
                        $subject = 'Reset Password Code';
                        $to_name=$user->name;
                        $data=array();
                        // $to_email=$user->email;
                        // $to_email=['haseebanwar022@gmail.com','mrazeemue@gmail.com'];
                        if($user->role_id==2)
                        {
                            $data = array(
                                'dear_sir' => "Dear Parent",
                                'msg' => "Please use the following code to reset your password for UVSchools",
                                'thanks' => "- Thanks (UVSchools Team)",
                                'poweredBy' => "Powered by united-vision.net",
                                'code' => $code
                            );
                        }
                        else if($user->role_id==4)
                        {
                            $data = array(
                                'dear_sir' => "Dear Teacher",
                                'msg' => "Please use the following code to reset your password for UVSchools",
                                'thanks' => "- Thanks (UVSchools Team)",
                                'poweredBy' => "Powered by united-vision.net",
                                'code' => $code
                            );
                        }
                        
                        
                        Mail::send('email_templates.token_authentication', $data, function($message) use ($to_name, $to_email, $subject)
                        {
                            $message->to($to_email)->subject($subject);
                            $message->from('info@uvschools.com','UVSchools');
                        });
                        
                        // var_dump( Mail:: failures());
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Authentication code has been sent to an email address, please verify your identity!',
                            'data' => ['api_token' => $user->api_token, 'confirmation_code' => $code]
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    else if($user->role_id && $user->role_id==3)//student
                    {
                        $to_email=array();
                        $guardian_detail=DB::table('student_guardians')
                        ->join('users', 'student_guardians.guardian_id', '=', 'users.id', 'left')
                        ->select('student_guardians.student_id', 'student_guardians.guardian_id', 'users.email', 'users.contact')
                        ->where('student_guardians.student_id',$user->id)
                        ->whereNull('student_guardians.deleted_at')
                        ->first();
;
                        
                        if($user->email && $user->emai!='')
                        {
                            $to_email[]=$user->email;
                        }
                        
                        
                        if($guardian_detail)
                        {
                            $to_email[]=$guardian_detail->email;
                        }
                        
                        if(($user->email && $user->emai!='') || ($guardian_detail && $guardian_detail->email!=''))
                        {
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Email is not assigned, please contact your admin support!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        
                        
                        $code = rand(100000,999999);
                        $subject = 'Reset Password Code';
                        $to_name=$user->name;
                        // $to_email=$user->email;
                        // $to_email=['haseebanwar022@gmail.com','mrazeemue@gmail.com'];
                        $data = array(
                            'dear_sir' => "Dear Student",
                            'msg' => "Please use the following code to reset your password for UVSchools",
                            'thanks' => "- Thanks (UVSchools Team)",
                            'poweredBy' => "Powered by united-vision.net",
                            'code' => $code
                        );
                        
                        Mail::send('email_templates.token_authentication', $data, function($message) use ($to_name, $to_email, $subject) {
                            $message->to($to_email)->subject($subject);
                            $message->from('info@uvschools.com','UVSchools');
                        });
                        
                        // var_dump( Mail:: failures());
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Authentication code has been sent to an email address, please verify!',
                            'data' => ['api_token' => $user->api_token, 'confirmation_code' => $code]
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
                    'message' => 'Email / Roll Number is required!',
                    'data' => ''
                );
                return response()->json($this->response, 400);
                die;
            }
        }
    }


    public function login(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('password') && ( $request->input('email') || $request->input('contact_number')))
            {
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $pass = md5($request->input('password'));
                $email='';
                $contact_number='';
                $contact_number1='';
                
                if($request->input('email') && $request->input('email')!='')
                {
                    $email = $request->input('email');
                }
                else if($request->input('contact_number') && $request->input('contact_number'))
                {
                    $contact_number=$request->input('contact_number');
                    $contact_number_arrays=explode('+', $request->input('contact_number'));
                    $contact_number1=$contact_number_arrays[1];
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Email / Contact Number is required!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }

                if($request->input('password') == 'default')
                {
                    $user='';
                    
                    if($email!='')
                    {
                        $user = DB::table('users')->select('id')
                        ->where(function ($match) use ($email) {
                            $match->where('email', $email)
                            ->orWhere('rollno', $email);
                        })
                        // ->where('status', '0')
                        ->where('password', $pass)->first();
                    }
                    else if($contact_number!='')
                    {
                        $user = DB::table('users')->select('id')
                        ->where(function ($smatch) use ($contact_number,$contact_number1) {
                            $smatch->where('contact', $contact_number)
                            ->orWhere('contact', $contact_number1);
                        })
                        // ->where('status', '0')
                        ->where('password', $pass)->first();
                    }
                    
                    if($user && $user!='')
                    {
                        $uid=$user->id;
    
                        $random_Usertoken=$this->generate_string($permitted_chars, 20);
    
                        $result=DB::table('users')->where(function ($match) use ($uid){
                            $match->where('users.id', $uid);
                        })->update(array(
                           'users.api_token' => $random_Usertoken
                        ));
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Reset your password!',
                            'data' => ['id' => $user->id, 'api_token' => $random_Usertoken]
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Invalid username or password!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }

                }
                else
                {
                    
                    
                    
                    $user='';
                    if($contact_number!='')
                    {
                        $verify_stu=DB::table('users')
                        ->where('users.deleted_at','0')
                        ->where(function ($match) use ($contact_number,$contact_number1) {
                            $match->where('users.contact', "$contact_number")
                            ->orWhere('users.contact', "$contact_number1");
                        })
                        ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                        if($verify_stu)
                        {
                            $verify_kid=DB::table('users')
                            ->where('users.deleted_at','0')
                            ->where(function ($match) use ($contact_number,$contact_number1) {
                                $match->where('users.contact', "$contact_number")
                                ->orWhere('users.contact', "$contact_number1");
                            })
                            ->where('users.status', '0')
                            ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                            
                            if($verify_kid)
                            {
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Your account is deactivated, kindly contact your school admin!',
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
                                'message' => 'Invalid username or password!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        
                        $user = DB::table('users')
                        ->join('school', 'users.school_id', '=', 'school.id')
                        ->join('academic_years', 'users.school_id', '=', 'academic_years.school_id')
                        ->join('roles', 'users.role_id', '=', 'roles.id', 'left')
                        ->join('currency', 'school.currency_symbol', '=', 'currency.code', 'left')
                        ->join('license', 'users.school_id', '=', 'license.school_id')
                        ->select('users.id as user_id','users.role_id','roles.name as role','users.name as user_name', 'users.rollno as roll_number', 'users.contact as user_contact','users.email as user_email','users.mobile_phone as user_mobile','users.avatar as user_avatar','users.gender','users.marital_status','users.dob as date_of_birth', 'users.joining_date', 'users.api_token','users.school_id', 'school.name as school_name','school.address as school_address','school.url as school_url','school.teacher_dept_id','school.phone as school_contact','school.email as school_email','school.logo as school_logo', 'school.time_zone','academic_years.id as academic_years_id','academic_years.name as academic_years_name','currency.symbol as currency','users.permissions')
                        ->where('users.deleted_at','0')
                        ->where(function ($match) use ($contact_number,$contact_number1) {
                            $match->where('users.contact', "$contact_number")
                            ->orWhere('users.contact', "$contact_number1");
                        })
                        ->where('users.status', '0')
                        ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                        
                    }
                    else
                    {
                        $verify_stu=DB::table('users')
                        ->where('users.deleted_at','0')
                        ->where(function ($match) use ($email) {
                            $match->where('users.email', $email)
                            ->orWhere('users.rollno', $email);
                        })
                        ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                        
                        if($verify_stu)
                        {
                            $verify_kid=DB::table('users')
                            ->where('users.deleted_at','0')
                            ->where(function ($match) use ($email) {
                                $match->where('users.email', $email)
                                ->orWhere('users.rollno', $email);
                            })
                            ->where('users.status', '0')
                            ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                            
                            if($verify_kid)
                            {
                                $checklic=DB::table('license')->where('school_id',$verify_kid->school_id)->where('deleted_at', 0)->first();
                                if($checklic)
                                {
                                    $date1 = strtotime(date("Y-m-d"));
                                    $date2 = strtotime($checklic->end_date);
                                    if ($date1 > $date2)
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'License expired!',
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
                                        'message' => 'School not found!',
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
                                    'message' => 'Your account is deactivated, kindly contact your school admin!',
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
                                'message' => 'Invalid username or password!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        
                        
                        
                        
                        $user = DB::table('users')
                        ->join('school', 'users.school_id', '=', 'school.id')
                        ->join('academic_years', 'users.school_id', '=', 'academic_years.school_id')
                        ->join('roles', 'users.role_id', '=', 'roles.id', 'left')
                        ->join('currency', 'school.currency_symbol', '=', 'currency.code', 'left')
                        ->join('license', 'users.school_id', '=', 'license.school_id')
                        ->select('users.id as user_id','users.role_id','roles.name as role','users.name as user_name', 'users.rollno as roll_number', 'users.contact as user_contact','users.email as user_email','users.mobile_phone as user_mobile','users.avatar as user_avatar','users.gender','users.marital_status','users.dob as date_of_birth', 'users.joining_date', 'users.api_token','users.school_id', 'school.name as school_name','school.address as school_address','school.url as school_url','school.teacher_dept_id','school.phone as school_contact','school.email as school_email','school.logo as school_logo', 'school.time_zone','academic_years.id as academic_years_id','academic_years.name as academic_years_name','currency.symbol as currency','users.permissions')
                        ->where('users.deleted_at','0')
                        ->where(function ($match) use ($email) {
                            $match->where('users.email', $email)
                            ->orWhere('users.rollno', $email);
                        })
                        ->where('users.status', '0')
                        ->where('users.password', $pass)->whereIn('users.role_id',[2,3,4])->first();
                    }

                    if($user && $user!='')
                    {
                        
                        $uid=$user->user_id;
                        
                        if($user->role_id==3)
                        {
                            $table = 'sh_students_'.$user->school_id;
                            $student_rec = DB::select("SELECT s.class_id, s.batch_id, sh_classes.name as class_name, sh_batches.name as batch_name FROM $table s left JOIN sh_batches ON sh_batches.id = s.batch_id LEFT JOIN sh_classes ON sh_classes.id = s.class_id WHERE s.id=$uid");
                            
                            //$res=DB::select('Select * from  sh_students_96'); 
                            //$res=DB::table('students_96')->get();
                            //echo '<pre>';
                            //print_r($student_rec);
                            //die;
                            
                            
                            // DB::select('SELECT * FROM users WHERE name = ?', array(Input::get('name')));
                            
                            // $student_rec=DB::table($table_name.' as s') 
                            //     ->join('batches as b','b.id','=','s.batch_id','left')
                            //     ->join('classes as c','c.id','=','s.class_id','left')
                            //     ->selectRaw('sh_s.class_id, sh_s.batch_id, sh_c.name as class_name, sh_b.name as batch_name')
                            //     ->where('s.id',$uid)->get();
                                
                            
                            if($student_rec)
                            {
                                $student_rec=$student_rec[0];
                                if($student_rec->class_name && $student_rec->batch_name)
                                {
                                    $user->class_name=$student_rec->class_name;
                                    $user->batch_name=$student_rec->batch_name;
                                }
                            }
                        }
                        

                        $random_Usertoken=$this->generate_string($permitted_chars, 20);

                        $result=DB::table('users')->where(function ($match) use ($uid){
                            $match->where('users.id', $uid);
                        })->update(array(
                           'users.api_token' => $random_Usertoken
                        ));

                        if($result)
                        {
                            $user->api_token=$random_Usertoken;
                        }
                        
                        // echo $user->permissions;

                        $user->permissions=json_decode($user->permissions);
                        
                        // echo '<pre>';
                        // print_r($user->permissions);
                        // die;
                        
                        
                        if(is_array($user->permissions) && sizeof($user->permissions) > 0)
                        {
                            foreach($user->permissions as $key => $perm)
                            {
                                // if($perm->label=="View Profile")
                                // {
                                //     echo gettype($perm->val);
                                //     die;
                                // }
                                
                                if($perm->val=="true")
                                {
                                    $user->permissions[$key]->val=true;
                                }
                                else
                                {
                                    $user->permissions[$key]->val=false;
                                }
                            }
                        }
                        

                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'User login successfully!',
                            'data' => $user
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        
                        $this->response=array
                        (
                            'status' => 'error',
                            'message' => 'Invalid username or password!',
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
                    'message' => 'Both email & password are required!',
                    'data' => ''
                );
                return response()->json($this->response, 200);
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
}
