<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class StudyplanController extends Controller
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
                $api_token=$request->input('api_token');
                $user = DB::table('users')
                        ->where(function ($match) use ($api_token){
                            $match->where('api_token', $api_token)
                            ->orWhere('id', $api_token);
                        })
                        ->whereIn('role_id', [2,3,4])
                        ->where('deleted_at', '=', '0')->first();
    
                if($user)
                {
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
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Subjects fetch successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
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
    
    //Dashboard Function
    public function get_homestudyplan(Request $request)
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
                        // ->where('api_token', $request->input('api_token'))
                        ->whereIn('role_id', [2,3,4])
                        ->where('deleted_at', '=', '0')->first();
                
                if($user)
                {
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();

                    $student_rec = DB::select("SELECT s.class_id, s.batch_id FROM sh_students_$user->school_id s WHERE  s.id='$user->id' ");
                    $todays_date=date('Y-m-d');
                    if(sizeof($student_rec) > 0)
                    {
                        $student_rec=$student_rec[0];
                        
                        $data=DB::table('syllabus_week_details_test as sp')
                        ->join('subjects', 'sp.subject_id', '=', 'subjects.id', 'left')
                        ->select('sp.id','sp.title','sp.status','sp.comments','sp.start_date','sp.end_date','sp.created_at','sp.subject_id','subjects.name')
                        ->where('sp.school_id',$user->school_id)
                        ->where('sp.academic_year_id',$academic_year->id)
                        ->where('sp.class_id',$student_rec->class_id)
                        ->where('sp.batch_id',$student_rec->batch_id)
                        ->whereDate('sp.start_date', '=', date("Y-m-d"))
                        // ->whereDate('sp.start_date', '>=', date("Y-m-d"))
                        // ->whereDate('sp.start_date', '<=', date("Y-m-d", strtotime($todays_date."+1 days")))
                        // ->whereDate('sp.end_date', '<=', date("Y-m-d", strtotime($todays_date."+1 days")))
                        ->whereNull('sp.deleted_at')
                        ->whereNull('subjects.deleted_at')
                        ->orderBy('sp.start_date','asc')->get()->all();
                        
                        if($data && sizeof($data) > 0)
                        {
                            foreach($data as $key => $dd)
                            {
                                $data[$key]->id=(int) $dd->id;
                                $data[$key]->day=date('l',strtotime($dd->created_at));
                            }
                            
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Studyplan fetch successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No study plan found!',
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
        
    //Dashboard Teacher Function
    public function get_teacherStudyplan(Request $request)
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
                            $todays_date=date('Y-m-d');
                                
                                DB::enableQueryLog();
                                $data=DB::table('syllabus_week_details_test as sp')
                                ->join('subjects', 'sp.subject_id', '=', 'subjects.id', 'left')
                                ->select('sp.id','sp.title','sp.status','sp.comments','sp.start_date','sp.end_date','sp.created_at','sp.subject_id','subjects.name')
                                ->where('sp.school_id',$user->school_id)
                                ->where('sp.academic_year_id',$academic_year->id)
                                ->where('sp.class_id',$request->input('class_id'))
                                ->where('sp.batch_id',$request->input('batch_id'))
                                ->whereDate('sp.start_date', '>=', date("Y-m-d"))
                                ->whereDate('sp.end_date', '<=', date("Y-m-d", strtotime($todays_date."+1 days")))
                                ->whereNull('sp.deleted_at')
                                ->whereNull('subjects.deleted_at')
                                ->orderBy('sp.start_date','asc')->get()->all();
                                
                                echo '<pre>';
                                print_r(DB::getQueryLog());
                                
                                if($data && sizeof($data) > 0)
                                {
                                    foreach($data as $key => $dd)
                                    {
                                        $data[$key]->day=date('l',strtotime($dd->created_at));
                                    }
                                    
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Studyplan fetch successfully!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'No study plan found!',
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
                            'message' => 'Batch Id is required!',
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
                        'message' => 'Class Id is required!',
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
    
        
    //Sidebar Function
    public function get_studyplan_subject(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('start_date') && $request->input('start_date')!='' && $request->input('end_date') && $request->input('end_date')!='')
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
                                    
                                    $result=DB::table('syllabus_week_details_test')
                                    ->select('subject_id','start_date','end_date')
                                    // ->where('subject_id',$request->input('subject_id'))
                                    ->where('school_id',$user->school_id)
                                    ->where('academic_year_id',$academic_year->id)
                                    ->whereDate('start_date', '>=', date("Y-m-d", strtotime($request->input('start_date'))))
                                    ->whereDate('end_date', '<=', date("Y-m-d", strtotime($request->input('end_date'))))
                                    ->whereNull('deleted_at')
                                    ->orderBy('start_date','asc')->get()->all();
                                    
                                    if($result && sizeof($result) > 0)
                                    {
                                        foreach($data as $pkey => $da)
                                        {
                                            // $data[$pkey]['id']=(int) $da['id'];
                                            
                                            foreach($result as $ckey => $res)
                                            {
                                                if($res->subject_id==$da['id'])
                                                {
                                                    $data[$pkey]['flag']=1;
                                                    break;
                                                }
                                                else
                                                {
                                                    $data[$pkey]['flag']=0;
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        foreach($data as $key => $dat)
                                        {
                                            // $data[$key]['id']=(int) $dat['id'];
                                            $data[$key]['flag']=0;
                                        }
                                    }
                                    
                                    
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Subjects fetch successfully!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 200);
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
                    }
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Start & End both dates are required!',
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
    
            
    //Sidebar Function
    public function get_studyplan(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('subject_id') && $request->input('subject_id')!='')
                {
                    if($request->input('start_date') && $request->input('start_date')!='' && $request->input('end_date') && $request->input('end_date')!='')
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
                            
                            $startdateOfStudyplan=$request->input('start_date');
                            
                            $enddateOfStudyplan=$request->input('end_date');
                            $enddateOfStudyplan = date('Y-m-d', strtotime($enddateOfStudyplan . ' +1 day'));
                            
                            // DB::enableQueryLog();
                            
                            $data=DB::table('syllabus_week_details_test')
                            ->select('id','title','status','comments','start_date','end_date','created_at')
                            ->where('subject_id',$request->input('subject_id'))
                            ->where('school_id',$user->school_id)
                            ->where('academic_year_id',$academic_year->id)
                            // ->whereDate('start_date', '>=', date("Y-m-d", strtotime($request->input('start_date'))))
                            // ->whereDate('end_date', '<=', date("Y-m-d", strtotime($request->input('end_date'))))
                            // ->whereDate('end_date', '<=', date("Y-m-d", strtotime($enddateOfStudyplan)))
                            
                            ->where(function ($d) use ($startdateOfStudyplan,$enddateOfStudyplan){
                                $d->whereDate('start_date', '>=', date("Y-m-d", strtotime($startdateOfStudyplan)))
                                ->WhereDate('start_date', '<=', date("Y-m-d", strtotime($enddateOfStudyplan)));
                                // ->orWhereDate('end_date', '<=', date("Y-m-d", strtotime($enddateOfStudyplan)));
                            })
                            ->whereNull('deleted_at')
                            ->orderBy('start_date','asc')->get()->all();
                            
                            // echo '<pre>';
                            // print_r(DB::getQueryLog());
                            // die;
                            
                            if($data && sizeof($data) > 0)
                            {
                                foreach($data as $key => $dd)
                                {
                                    $data[$key]->id=(int) $dd->id;
                                    $data[$key]->day=date('l',strtotime($dd->created_at));
                                }
                                
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Studyplan fetch successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No study plan found!',
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
                            'message' => 'Start & End both dates are required!',
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
                        'message' => 'Subject id is required!',
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
