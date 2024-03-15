<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;
 

class DashboardController extends Controller
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


    
    public function array_flatten($array)
    { 
        if(!is_array($array))
        { 
            return false; 
        } 
        $result = array(); 
        foreach($array as $key => $value)
        { 
            if(is_array($value))
            { 
                $result = array_merge($result, $this->array_flatten($value)); 
            }
            else
            { 
                $result = array_merge($result, array($key => $value));
            } 
        } 
        return $result; 
    }

    public function get_announcements(Request $request)
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
                $data='';
                $date = date("Y-m-d");
                $user = DB::table('users')
                        ->where('api_token', $api_token)
                        ->whereIn('role_id', [2,3,4])
                        ->where('deleted_at', '=', '0')
                        ->first();
    
                if($user)
                {
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $admin_record=DB::table('users')->select('id','name','avatar')->where('school_id',$user->school_id)->where('role_id',1)->where('deleted_at', '=', '0')->first();
                    
                    if($user->role_id == 3)
                    {
                        //single record
                        $resp = DB::table('student_class_relation')->select('class_id', 'batch_id')->where('student_id', $user->id)->whereNull('deleted_at')->where('academic_year_id',$academic_year->id)->first();
                        
                        if($resp)
                        {
                              if($request->input('date') && $request->input('date')!='')
                                {
                                    $data = DB::table('announcements')->where('status' ,'=','Active')->where('school_id','=',$user->school_id)->where('academic_year_id','=', $academic_year->id)
                                    ->whereDate('from_date', '<=', date("Y-m-d", strtotime($request->input('date'))))
                                    ->whereDate('to_date', '>=', date("Y-m-d", strtotime($request->input('date'))))
                                    ->where(function ($match) use ($resp){
                                        $match->where('level','all')
                                        ->orwhere(function ($s_match) use ($resp){
                                            $s_match->where('level','students')
                                            ->whereRaw("FIND_IN_SET('$resp->class_id',classes)")
                                            ->whereRaw("FIND_IN_SET('$resp->batch_id',sections)");
                                        });
                                    })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
                                }
                                else
                                {
                                    $data = DB::table('announcements')
                                    ->where('status' ,'=','Active')
                                    ->where('school_id','=',$user->school_id)
                                    ->where('academic_year_id','=', $academic_year->id)
                                    ->where(function ($match) use ($resp){
                                        $match->where('level','all')
                                        ->orwhere(function ($s_match) use ($resp){
                                            $s_match->where('level','students')
                                            ->whereRaw("FIND_IN_SET('$resp->class_id',classes)")
                                            ->whereRaw("FIND_IN_SET('$resp->batch_id',sections)");
                                        });
                                    })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
                                }
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Class not found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                      
                        
                        
    
                    }
                    else if ($user->role_id == 2 )
                    {
                        $data1=array();
                        $data2='';
                        
                        $students=DB::table('student_guardians')->select('student_id')->where('guardian_id',$user->id)->whereNull('deleted_at')->get()->all();
                        
                        if(sizeof($students) > 0)
                        {
                            foreach($students as $key => $ids)
                            {
                                $students[$key]=(int)$ids->student_id;
                            }
                            
                            //fetch records for all students
                            $resp = DB::table('student_class_relation')
                            ->join('users', 'student_class_relation.student_id', '=', 'users.id', 'left')
                            ->select('student_class_relation.student_id', 'student_class_relation.class_id', 'student_class_relation.batch_id', 'users.name', 'users.avatar')
                            ->whereIn('student_class_relation.student_id', $students)
                            ->whereNull('student_class_relation.deleted_at')
                            ->where('student_class_relation.academic_year_id',$academic_year->id)
                            ->get()->all();
                            
                            $ahoo=array();
                            if(sizeof($resp) > 0)
                            {
                                foreach($resp as $key => $re)
                                {
                                    $stu_announcement = DB::table('announcements')
                                    ->where('status' ,'=','Active')
                                    ->where('school_id','=',$user->school_id)
                                    ->where('academic_year_id','=', $academic_year->id)
                                    ->whereDate('from_date', '<=', date("Y-m-d"))
                                    ->whereDate('to_date', '>=', date("Y-m-d"))
                                    ->where(function ($match) use ($re){
                                        $match->where('level','all')
                                        ->orwhere(function ($s_match) use ($re){
                                            $s_match->where('level','students')
                                            ->whereRaw("FIND_IN_SET('$re->class_id',classes)")
                                            ->whereRaw("FIND_IN_SET('$re->batch_id',sections)");
                                        });
                                    })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();

                                    foreach($stu_announcement as $key => $ann)
                                    {
                                        $stu_announcement[$key]->name=$re->name;
                                        $stu_announcement[$key]->avatar=$re->avatar;
                                    }
                                    
                                    $data1[]=$stu_announcement;
                                    
                                }
                                $data1=$this->array_flatten($data1);
                            }
 
                        }
                        
                        $data2 = DB::table('announcements')->where('status' ,'=','Active')->whereIn('level', ['all','parents'])->where('school_id','=',$user->school_id)->where('academic_year_id','=', $academic_year->id)
                            ->whereDate('from_date', '<=', date("Y-m-d"))
                            ->whereDate('to_date', '>=', date("Y-m-d"))
                            ->where(function ($match){
                                $match->where('level','all')
                                ->orwhere('level','parents');
                            })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
                            
                            
                        foreach($data2 as $key => $d2)
                        {
                            $data2[$key]->name=$user->name;
                            $data2[$key]->avatar=$user->avatar;
                        }
                        
                        $data=array_merge($data1,$data2);
                        
                        // $data=array_unique($data,SORT_REGULAR);
                        // $data=array_values($data); 
                        
                        $data = array_values(array_column($data, null, 'id'));
// echo var_export($comboUserPosts, true);
                        
                        
                        
                        if(sizeof($data) > 0)
                        {
                            foreach($data as $key => $d)
                            {
                                if($d->img_or_document_type=='jpg' || $d->img_or_document_type == 'JPG' || $d->img_or_document_type == 'png' || $d->img_or_document_type == 'PNG' || $d->img_or_document_type == 'JPEG' || $d->img_or_document_type == 'jpeg')
                                {
                                    $data[$key]->file_type='image';
                                }
                                else if($d->img_or_document_type == 'doc' || $d->img_or_document_type == 'docx')
                                {
                                    $data[$key]->file_type='doc';
                                }
                                else if($d->img_or_document_type == 'txt')
                                {
                                    $data[$key]->file_type='textdoc';
                                }
                                else if($d->img_or_document_type == 'ppt' || $d->img_or_document_type == 'pptx')
                                {
                                    $data[$key]->file_type='powerpoint';
                                }
                                else if($d->img_or_document_type == 'xlsx')
                                {
                                    $data[$key]->file_type='excel';
                                }
                                else
                                {
                                    $data[$key]->file_type='';
                                }
                            }
                            
                            foreach($data as $d){
                                $d->id = (int) $d->id;
                                $d->school_id = (int) $d->school_id;
                                $d->academic_year_id = (int) $d->school_id;
                            }
                            
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Announcement fetch successfully!',
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
                                'message' => 'No announcement found!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        
                        
                    }
                    else if ($user->role_id == 4 )
                    {
                        $data = DB::table('announcements')->where('status' ,'=','Active')->whereIn('level', ['all','employees'])->where('school_id','=',$user->school_id)
                        ->where('academic_year_id','=', $academic_year->id)
                        ->whereDate('from_date', '<=', date("Y-m-d"))
                        ->whereDate('to_date', '>=', date("Y-m-d"))
                        ->where(function ($match) use ($user){
                                $match->where('level','all')
                                ->orwhere(function ($s_match) use ($user){
                                    $s_match->where('level','employees')
                                    ->whereIn('employees', [$user->id], 'or');
                                });
                        })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
                        
                        foreach($data as $key => $rec)
                        {
                            if($rec->departments!='' && $rec->categories!='' && $rec->employees!='')
                            {
                                $dept=explode(',',$rec->departments);
                                $cat=explode(',',$rec->categories);
                                $emp=explode(',',$rec->employees);
                                if(!in_array($user->department_id,$dept) || !in_array($user->role_category_id,$cat) || !in_array($user->id,$emp))
                                {
                                    unset($data[$key]);
                                }
                                
                            }
                            else if($rec->departments!='' || $rec->categories!='' || $rec->employees!='')
                            {
                                if($rec->departments!='')
                                {
                                    $dept=explode(',',$rec->departments);
                                    if(!in_array($user->department_id,$dept))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                                elseif($rec->categories!='')
                                {
                                    $cat=explode(',',$rec->categories);
                                    if(!in_array($user->role_category_id,$cat))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                                elseif($rec->employees!='')
                                {
                                    $emp=explode(',',$rec->employees);
                                    if(!in_array($user->id,$emp))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                            }
                        }
                        array_values($data);
                    }
                   
                    if(sizeof($data) > 0)
                    {
                        foreach($data as $key => $d)
                        {
                            $data[$key]->creator_id=$admin_record->id;
                            $data[$key]->creator_name=$admin_record->name;
                            $data[$key]->creator_avatar=$admin_record->avatar;
                            
                            if($d->img_or_document_type=='jpg' || $d->img_or_document_type == 'JPG' || $d->img_or_document_type == 'png' || $d->img_or_document_type == 'PNG' || $d->img_or_document_type == 'JPEG' || $d->img_or_document_type == 'jpeg')
                            {
                                $data[$key]->file_type='image';
                            }
                            else if($d->img_or_document_type == 'doc' || $d->img_or_document_type == 'docx')
                            {
                                $data[$key]->file_type='doc';
                            }
                            else if($d->img_or_document_type == 'txt')
                            {
                                $data[$key]->file_type='textdoc';
                            }
                            else if($d->img_or_document_type == 'ppt' || $d->img_or_document_type == 'pptx')
                            {
                                $data[$key]->file_type='powerpoint';
                            }
                            else if($d->img_or_document_type == 'xlsx')
                            {
                                $data[$key]->file_type='excel';
                            }
                            else
                            {
                                $data[$key]->file_type='';
                            }
                        }
                        
                        foreach($data as $d){
                            $d->id = (int) $d->id;
                            $d->school_id = (int) $d->school_id;
                            $d->academic_year_id = (int) $d->school_id;
                        }
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Announcement fetch successfully!',
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
                            'message' => 'No announcement found!',
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
                        'message' => 'Unauthorized access!',
                        'data' => ''
                    );
                    return response()->json($this->response, 400);
                    die;
            }
        }
    	
    }
    

    
    public function get_childAnnouncement(Request $request)
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
                $data='';
                $date = date("Y-m-d");
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
    
                    if($user->role_id == 3)
                    {
                        //single record
                        $resp = DB::table('student_class_relation')->select('class_id', 'batch_id')->where('student_id', $user->id)->first();
                        // DB::enableQueryLog();
                        $data = DB::table('announcements')->where('status' ,'=','Active')->where('school_id','=',$user->school_id)->where('academic_year_id','=', $academic_year->id)
                        ->whereDate('from_date', '<=', date("Y-m-d"))
                        ->whereDate('to_date', '>=', date("Y-m-d"))
                        ->where(function ($match) use ($resp){
                            $match->where('level','all')
                            ->orwhere(function ($s_match) use ($resp){
                                $s_match->where('level','students')
                                ->whereRaw("FIND_IN_SET('$resp->class_id',classes)")
                                ->whereRaw("FIND_IN_SET('$resp->batch_id',sections)");
                            });
                        })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
    
                    }
                    else if ($user->role_id == 2 )
                    {
                        $data = DB::table('announcements')->where('status' ,'=','Active')->whereIn('level', ['all','parents'])->where('school_id','=',$user->school_id)->where('academic_year_id','=', $academic_year->id)
                            ->whereDate('from_date', '<=', date("Y-m-d"))
                            ->whereDate('to_date', '>=', date("Y-m-d"))
                            ->where(function ($match){
                                $match->where('level','all')
                                ->orwhere('level','parents');
                            })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
    
                        // $data =  $this->admin_model->dbSelect( "*", "announcements", " status = 'Active' AND (level = 'all' OR level = 'parents') AND ('$date' BETWEEN from_date AND to_date) AND school_id='$school_id' AND academic_year_id='$academic_year' AND deleted_at IS NULL ");
                    }
                    else if ($user->role_id == 4 )
                    {
                        $data = DB::table('announcements')->where('status' ,'=','Active')->whereIn('level', ['all','employees'])->where('school_id','=',$user->school_id)
                        ->where('academic_year_id','=', $academic_year->id)
                        ->whereDate('from_date', '<=', date("Y-m-d"))
                        ->whereDate('to_date', '>=', date("Y-m-d"))
                        ->where(function ($match) use ($user){
                                $match->where('level','all')
                                ->orwhere(function ($s_match) use ($user){
                                    $s_match->where('level','employees')
                                    ->whereIn('employees', [$user->id], 'or');
                                });
                        })->whereNull('deleted_at')->orderBy('created_at', 'DESC')->get()->all();
                        
                        foreach($data as $key => $rec)
                        {
                            if($rec->departments!='' && $rec->categories!='' && $rec->employees!='')
                            {
                                $dept=explode(',',$rec->departments);
                                $cat=explode(',',$rec->categories);
                                $emp=explode(',',$rec->employees);
                                if(!in_array($user->department_id,$dept) || !in_array($user->role_category_id,$cat) || !in_array($user->id,$emp))
                                {
                                    unset($data[$key]);
                                }
                                
                            }
                            else if($rec->departments!='' || $rec->categories!='' || $rec->employees!='')
                            {
                                if($rec->departments!='')
                                {
                                    $dept=explode(',',$rec->departments);
                                    if(!in_array($user->department_id,$dept))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                                elseif($rec->categories!='')
                                {
                                    $cat=explode(',',$rec->categories);
                                    if(!in_array($user->role_category_id,$cat))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                                elseif($rec->employees!='')
                                {
                                    $emp=explode(',',$rec->employees);
                                    if(!in_array($user->id,$emp))
                                    {
                                        unset($data[$key]);
                                    }
                                }
                            }
                        }
                        array_values($data);
                    }
                   
                    if(sizeof($data) > 0)
                    {
                        foreach($data as $key => $d)
                        {
                            
                            $data[$key]->id=(int) $d->id;
                            $data[$key]->school_id=(int) $d->school_id;
                            $data[$key]->academic_year_id=(int) $d->academic_year_id;
                            
                            if($d->img_or_document_type=='jpg' || $d->img_or_document_type == 'JPG' || $d->img_or_document_type == 'png' || $d->img_or_document_type == 'PNG' || $d->img_or_document_type == 'JPEG' || $d->img_or_document_type == 'jpeg')
                            {
                                $data[$key]->file_type='image';
                            }
                            else if($d->img_or_document_type == 'doc' || $d->img_or_document_type == 'docx')
                            {
                                $data[$key]->file_type='doc';
                            }
                            else if($d->img_or_document_type == 'txt')
                            {
                                $data[$key]->file_type='textdoc';
                            }
                            else if($d->img_or_document_type == 'ppt' || $d->img_or_document_type == 'pptx')
                            {
                                $data[$key]->file_type='powerpoint';
                            }
                            else if($d->img_or_document_type == 'xlsx')
                            {
                                $data[$key]->file_type='excel';
                            }
                            else
                            {
                                $data[$key]->file_type='';
                            }
                        }
                        
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Announcement fetch successfully!',
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
                            'message' => 'No announcement found!',
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
                        'message' => 'Unauthorized access!',
                        'data' => ''
                    );
                    return response()->json($this->response, 400);
                    die;
            }
        }
    	
    }
}
