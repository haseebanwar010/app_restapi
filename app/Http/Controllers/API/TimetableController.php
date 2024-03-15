<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class TimetableController extends Controller
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


    public function all_studentsTimetable(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            $todays_day='';
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $filter_day='';
                if($request->input('date') && $request->input('date')!='')
                {
                    $todays_day = strtolower(date('l', strtotime($request->input('date'))));
                    $find_day = date('l', strtotime($request->input('date')));
                    $filter_day=$find_day;
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'error',
                        'message' => 'Date is required!',
                        'data' => ''
                    );
                    return response()->json($this->response, 400);
                    die;
                }
                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                if($user)
                {
                    $class_id=0;
                    $batch_id=0;
                    $subject_group_id=0;
                    $academic_year_id=0;
                    
                    $stud_view="students_".$user->school_id;
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $stu_rec=DB::table($stud_view)->select('class_id','batch_id','subject_group_id','academic_year_id')->where('id',$user->id)->first();
                    if($stu_rec->class_id !='' && $stu_rec->batch_id !='' && $stu_rec->subject_group_id !='')
                    {
                        $class_id=$stu_rec->class_id;
                        $batch_id=$stu_rec->batch_id;
                        $subject_group_id=$stu_rec->subject_group_id;
                        $academic_year_id=$stu_rec->academic_year_id;
                    }
                    else
                    {
                        if($stu_rec->class_id =='')
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Class not assigned!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else if($stu_rec->batch_id =='')
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Batch not assigned!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else if($stu_rec->subject_group_id =='')
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
                    
                    $subjects_new=array();
                    $subjectids=DB::table('subject_groups')->select('subjects')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('id',$subject_group_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->first();
                    if($subjectids->subjects && $subjectids->subjects!='')
                    {
                        $subjectids = array_map( 'trim', explode( ",",$subjectids->subjects ) );
                        $subjects=DB::table('subjects')->select('id')->where('class_id',$class_id)->where('batch_id',$batch_id)->whereIn('id',$subjectids)->where('school_id',$user->school_id)->whereNull('deleted_at')->get()->all();
                        if(sizeof($subjects) > 0)
                        {
                            foreach($subjects as $sub)
                            {
                                array_push($subjects_new, $sub->id);
                            }
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Subjects are not assigned!',
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
                            'message' => 'Subjects are not assigned!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    //Fetch Periods
                    $periods=DB::table('periods')->selectRaw('sh_periods.*,time_format("start_time","%h:%i %p") as start_time2,time_format("end_time","%h:%i %p") as end_time2')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('academic_year_id',$academic_year_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->orderBy('start_time')->get()->all();
                    if(sizeof($periods) > 0)
                    {
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No such periods!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die; 
                    }
                    
                    
                    //Fetch Days Of Week
                    $days_of_week=array();
                    $offdays_of_week=array();
                    $sorted_days_of_week=array();
                    $working_days=DB::table('school')->select('working_days','start_day_of_the_week')->where('id',$user->school_id)->where('deleted_at',0)->first();

                    if($working_days->working_days && $working_days->working_days!='')
                    {
                        $all_days=json_decode($working_days->working_days);
                        foreach ($all_days as $days)
                        {
                            if ($days->val == 'true') {
                                array_push($days_of_week, strtolower($days->label));
                            }
                            else
                            {
                                array_push($offdays_of_week, strtolower($days->label));
                            }
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Working days are not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    

                    if($working_days->start_day_of_the_week && $working_days->start_day_of_the_week!='')
                    {
                        $index = array_search(strtolower($working_days->start_day_of_the_week), $days_of_week);
                        $loop1 = count($days_of_week) - $index;
                        
                        for ($i = 0; $i < $loop1; $i++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$index + $i]);
                        }
                
                        for ($j = 0; $j < $index; $j++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$j]);
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Starting day of the week is not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    if(in_array(strtolower($filter_day), $offdays_of_week))
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => $filter_day.' is an off day!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    
                    //now we have to play with sorted array of week
                    $final_arr = array();
                    foreach ($sorted_days_of_week as $day) {
                        foreach ($periods as $p) {
                            $array = array(
                                "period_id" => $p->id,
                                "start_time" => $p->start_time,
                                "end_time" => $p->end_time,
                                "class_id" => $class_id,
                                "batch_id" => $batch_id,
                                "is_break" => $p->is_break,
                                "period_order" => $p->title,
                                "timetable_id" => NULL,
                                "teacher_id" => NULL,
                                "teacher_name" => NULL,
                                "day_of_week" => $day,
                                "sub_id" => NULL,
                                "room_no" => NULL,
                                "sub_name" => NULL,
                                "sub_code" => NULL
                            );
                            $final_arr[$day][$p->id] = $array;
                        }
                    }
                    
                    //Comma Seprated string of all working days
                    $daysofweekstring = null;
                    foreach ($sorted_days_of_week as $_d)
                    {
                        // $daysofweekstring .= "'" . $_d . "',";
                        if($todays_day==$_d)
                        {
                            $daysofweekstring=$todays_day;
                        }
                    }
                    
                    
                    $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week='$daysofweekstring' ORDER BY p.start_time ASC ");
                    // $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week in (" . rtrim($daysofweekstring, ",") . ") ORDER BY p.start_time ASC ");
                    
                    foreach ($dbTimetables as $tb)
                    {
                        $index1 = $tb->day_of_week;
                        $index2 = $tb->period_id;
                        $final_arr[$index1][$index2] = $tb;
                    }
                    
                    foreach ($final_arr as $key => $value)
                    {
                        $i = 0;
                        foreach ($value as $row_key => $row_value)
                        {
                            $final_arr[ucfirst($key)][$i++] = $row_value;
                            // $final_arr[][$i++] = $row_value;
                            unset($final_arr[$key]);
                            unset($final_arr[$key][$row_key]);
                        }
                    }
                    
                    
                    if(count($final_arr) > 0)
                    {
                        foreach($final_arr as $key=>$fr)
                        {
                            if($key==$filter_day)
                            {
                                foreach($fr as $key2=>$row)
                                {
                                    if(isset($row->sub_id))
                                    {
                                        if(!in_array($row->sub_id, $subjects_new))
                                        {
                                            $arr = array(
                                                "batch_id" => $row->batch_id,
                                                "class_id" => $row->batch_id,
                                                "day_of_week" => $row->day_of_week,
                                                "end_time" => $row->end_time,
                                                "is_break" => "Y",
                                                "period_id" => null,
                                                "period_order" => "Break",
                                                "room_no" => null,
                                                "start_time" => $row->start_time,
                                                "sub_code" => null,
                                                "sub_id" => null,
                                                "sub_name" => null,
                                                "teacher_id" => null,
                                                "teacher_name" => null,
                                                "timetable_id" => null
                                            );
                                            $final_arr[$key][$key2] = $arr;
                                            //unset($final_arr[$key][$key2]);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                unset($final_arr[$key]);
                            }
                        }
                    }
                    
                    
                    $data=array();
                    // $data['periods']=$periods;
                    // $data['timetable']=$final_arr;
                    $data=$final_arr[$find_day];
                    
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Timetable fetch successfully!',
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
    
   
    //single child 
    public function student_timetable(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            $todays_day='';
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $api_token=$request->input('api_token');
                $filter_day='';
                if($request->input('date') && $request->input('date')!='')
                {
                    $todays_day = strtolower(date('l', strtotime($request->input('date'))));
                    $find_day = date('l', strtotime($request->input('date')));
                    $filter_day=$find_day;
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Date is required!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }
                
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
                    $class_id=0;
                    $batch_id=0;
                    $subject_group_id=0;
                    $academic_year_id=0;
                    
                    $stud_view="students_".$user->school_id;
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $stu_rec=DB::table($stud_view)->select('class_id','batch_id','subject_group_id','academic_year_id')->where('id',$user->id)->first();
                    if($stu_rec->class_id !='' && $stu_rec->batch_id !='' && $stu_rec->subject_group_id !='')
                    {
                        $class_id=$stu_rec->class_id;
                        $batch_id=$stu_rec->batch_id;
                        $subject_group_id=$stu_rec->subject_group_id;
                        $academic_year_id=$stu_rec->academic_year_id;
                    }
                    else
                    {
                        if($stu_rec->class_id =='')
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Class is not assigned!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else if($stu_rec->batch_id =='')
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Batch is not assigned!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                            die;
                        }
                        else if($stu_rec->subject_group_id =='')
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
                    
                    $subjects_new=array();
                    $subjectids=DB::table('subject_groups')->select('subjects')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('id',$subject_group_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->first();

                    if($subjectids->subjects && $subjectids->subjects!='')
                    {
                        $subjectids = array_map( 'trim', explode( ",",$subjectids->subjects ) );
                        $subjects=DB::table('subjects')->select('id')->where('class_id',$class_id)->where('batch_id',$batch_id)->whereIn('id',$subjectids)->where('school_id',$user->school_id)->whereNull('deleted_at')->get()->all();
                        if(sizeof($subjects) > 0)
                        {
                            foreach($subjects as $sub)
                            {
                                array_push($subjects_new, $sub->id);
                            }
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Subjects are not assigned!',
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
                            'message' => 'Subjects are not assigned!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    //Fetch Periods
                    $periods=DB::table('periods')->selectRaw('sh_periods.*,time_format("start_time","%h:%i %p") as start_time2,time_format("end_time","%h:%i %p") as end_time2')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('academic_year_id',$academic_year_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->orderBy('start_time')->get()->all();
                    if(sizeof($periods) > 0)
                    {
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No such periods!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    //Fetch Days Of Week
                    $days_of_week=array();
                    $offdays_of_week=array();
                    $sorted_days_of_week=array();
                    $working_days=DB::table('school')->select('working_days','start_day_of_the_week')->where('id',$user->school_id)->where('deleted_at',0)->first();

                    if($working_days->working_days && $working_days->working_days!='')
                    {
                        $all_days=json_decode($working_days->working_days);
                        foreach ($all_days as $days)
                        {
                            if ($days->val == 'true') {
                                array_push($days_of_week, strtolower($days->label));
                            }
                            else
                            {
                                array_push($offdays_of_week, strtolower($days->label));
                            }
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Working days are not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    

                    if($working_days->start_day_of_the_week && $working_days->start_day_of_the_week!='')
                    {
                        $index = array_search(strtolower($working_days->start_day_of_the_week), $days_of_week);
                        $loop1 = count($days_of_week) - $index;
                        
                        for ($i = 0; $i < $loop1; $i++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$index + $i]);
                        }
                
                        for ($j = 0; $j < $index; $j++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$j]);
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Starting day of the week is not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    if(in_array(strtolower($filter_day), $offdays_of_week))
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => $filter_day.' is an off day!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    
                    //now we have to play with sorted array of week
                    $final_arr = array();
                    foreach ($sorted_days_of_week as $day) {
                        foreach ($periods as $p) {
                            $array = array(
                                "period_id" => (int)$p->id,
                                "start_time" => $p->start_time,
                                "end_time" => $p->end_time,
                                "class_id" => (int)$class_id,
                                "batch_id" => (int)$batch_id,
                                "is_break" => $p->is_break,
                                "period_order" => $p->title,
                                "timetable_id" => "",
                                "teacher_id" => "",
                                "teacher_name" => "",
                                "day_of_week" => $day,
                                "sub_id" => "",
                                "room_no" => "",
                                "sub_name" => "",
                                "sub_code" => ""
                            );
                            $final_arr[$day][$p->id] = $array;
                        }
                    }
                    
                    //Comma Seprated string of all working days 
                    $daysofweekstring = null;
                    foreach ($sorted_days_of_week as $_d)
                    {
                        // $daysofweekstring .= "'" . $_d . "',";
                        if($todays_day==$_d)
                        {
                            $daysofweekstring=$todays_day;
                        }
                    }
                    
                   
                    
                    
                    $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week='$daysofweekstring' ORDER BY p.start_time ASC ");
                    
                    // $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week in (" . rtrim($daysofweekstring, ",") . ") ORDER BY p.start_time ASC ");
                    
                    foreach ($dbTimetables as $tb)
                    {
                        $index1 = $tb->day_of_week;
                        $index2 = $tb->period_id;
                        $final_arr[$index1][$index2] = $tb;
                    }
                    
                    foreach ($final_arr as $key => $value)
                    {
                        $i = 0;
                        foreach ($value as $row_key => $row_value)
                        {
                            $final_arr[ucfirst($key)][$i++] = $row_value;
                            // $final_arr[][$i++] = $row_value;
                            unset($final_arr[$key]);
                            unset($final_arr[$key][$row_key]);
                        }
                    }
                    
                    
                    if(count($final_arr) > 0)
                    {
                        foreach($final_arr as $key=>$fr)
                        {
                            if($key==$filter_day)
                            {
                                foreach($fr as $key2=>$row)
                                {
                                    if(isset($row->sub_id))
                                    {
                                        if(!in_array($row->sub_id, $subjects_new))
                                        {
                                            $arr = array(
                                                "batch_id" => (int)$row->batch_id,
                                                "class_id" => (int)$row->batch_id,
                                                "day_of_week" => $row->day_of_week,
                                                "end_time" => $row->end_time,
                                                "is_break" => "Y",
                                                "period_id" => NULL,
                                                "period_order" => "Break",
                                                "room_no" => "",
                                                "start_time" => $row->start_time,
                                                "sub_code" => "",
                                                "sub_id" => "",
                                                "sub_name" => "",
                                                "teacher_id" => "",
                                                "teacher_name" => "",
                                                "timetable_id" => ""
                                            );
                                            $final_arr[$key][$key2] = $arr;
                                            //unset($final_arr[$key][$key2]);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                unset($final_arr[$key]);
                            }
                        }
                    }
                    
                    
                    $data=array();
                    // $data['periods']=$periods;
                    // $data['timetable']=$final_arr;
                    $data=$final_arr[$find_day];
                    
                    foreach($data as $ydd){
                        if(!is_array($ydd)) {
                            if($ydd->period_id == null){
                                    $ydd->period_id = 0;
                            } else {
                                $ydd->period_id = (int) $ydd->period_id;
                            }
                            
                            if($ydd->class_id == null){
                                $ydd->class_id = 0;
                            } else {
                                $ydd->class_id = (int) $ydd->class_id;
                            }
                            
                            if($ydd->batch_id == null){
                                $ydd->batch_id = 0;
                            } else {
                                $ydd->batch_id = (int) $ydd->class_id;
                            }
                        }
                    }
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Timetable fetch successfully!',
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
    
       
    //Teacher side / get upon class & batch 
    public function teacher_timetable(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            $todays_day='';
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                $api_token=$request->input('api_token');
                $filter_day='';
                if($request->input('date') && $request->input('date')!='')
                {
                    $todays_day = strtolower(date('l', strtotime($request->input('date'))));
                    $find_day = date('l', strtotime($request->input('date')));
                    $filter_day=$find_day;
                }
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Date is required!',
                        'data' => ''
                    );
                    return response()->json($this->response, 200);
                    die;
                }
                
                if($request->input('class_id') && $request->input('class_id')!='')
                {
                    if($request->input('batch_id') && $request->input('batch_id')!='')
                    {
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
                    $class_id=$request->input('class_id');
                    $batch_id=$request->input('batch_id');
                    $subject_group_id=0;
                    $academic_year_id=0;
                    
                    // $stud_view="students_".$user->school_id;
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    if($academic_year)
                    {
                        $academic_year_id=$academic_year->id;
                    }

                    $stu_rec=DB::table('subject_groups')->select('*')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('academic_year_id',$academic_year_id)->whereNull('deleted_at')->first();

                    if($stu_rec && $stu_rec->id !='')
                    {
                        $subject_group_id=$stu_rec->id;
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
                    
                    $subjects_new=array();
                    $subjectids=DB::table('subject_groups')->select('subjects')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('id',$subject_group_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->first();

                    if($subjectids->subjects && $subjectids->subjects!='')
                    {
                        $subjectids = array_map( 'trim', explode( ",",$subjectids->subjects ) );
                        $subjects=DB::table('subjects')->select('id')->where('class_id',$class_id)->where('batch_id',$batch_id)->whereIn('id',$subjectids)->where('school_id',$user->school_id)->whereNull('deleted_at')->get()->all();
                        if(sizeof($subjects) > 0)
                        {
                            foreach($subjects as $sub)
                            {
                                array_push($subjects_new, $sub->id);
                            }
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Subjects are not assigned!',
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
                            'message' => 'Subjects are not assigned!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    //Fetch Periods
                    $periods=DB::table('periods')->selectRaw('sh_periods.*,time_format("start_time","%h:%i %p") as start_time2,time_format("end_time","%h:%i %p") as end_time2')->where('class_id',$class_id)->where('batch_id',$batch_id)->where('academic_year_id',$academic_year_id)->where('school_id',$user->school_id)->whereNull('deleted_at')->orderBy('start_time')->get()->all();
                    if(sizeof($periods) > 0)
                    {
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No such periods!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    //Fetch Days Of Week
                    $days_of_week=array();
                    $offdays_of_week=array();
                    $sorted_days_of_week=array();
                    $working_days=DB::table('school')->select('working_days','start_day_of_the_week')->where('id',$user->school_id)->where('deleted_at',0)->first();

                    if($working_days->working_days && $working_days->working_days!='')
                    {
                        $all_days=json_decode($working_days->working_days);
                        foreach ($all_days as $days)
                        {
                            if ($days->val == 'true') {
                                array_push($days_of_week, strtolower($days->label));
                            }
                            else
                            {
                                array_push($offdays_of_week, strtolower($days->label));
                            }
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Working days are not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    

                    if($working_days->start_day_of_the_week && $working_days->start_day_of_the_week!='')
                    {
                        $index = array_search(strtolower($working_days->start_day_of_the_week), $days_of_week);
                        $loop1 = count($days_of_week) - $index;
                        
                        for ($i = 0; $i < $loop1; $i++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$index + $i]);
                        }
                
                        for ($j = 0; $j < $index; $j++)
                        {
                            array_push($sorted_days_of_week, $days_of_week[$j]);
                        }
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Starting day of the week is not defined!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    if(in_array(strtolower($filter_day), $offdays_of_week))
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => $filter_day.' is an off day!',
                            'data' => ''
                        );
                        return response()->json($this->response, 200);
                        die;
                    }
                    
                    
                    
                    //now we have to play with sorted array of week
                    $final_arr = array();
                    foreach ($sorted_days_of_week as $day) {
                        foreach ($periods as $p) {
                            $array = array(
                                "period_id" => $p->id,
                                "start_time" => $p->start_time,
                                "end_time" => $p->end_time,
                                "class_id" => $class_id,
                                "batch_id" => $batch_id,
                                "is_break" => $p->is_break,
                                "period_order" => $p->title,
                                "timetable_id" => NULL,
                                "teacher_id" => NULL,
                                "teacher_name" => NULL,
                                "day_of_week" => $day,
                                "sub_id" => NULL,
                                "room_no" => NULL,
                                "sub_name" => NULL,
                                "sub_code" => NULL
                            );
                            $final_arr[$day][$p->id] = $array;
                        }
                    }
                    
                    //Comma Seprated string of all working days 
                    $daysofweekstring = null;
                    foreach ($sorted_days_of_week as $_d)
                    {
                        // $daysofweekstring .= "'" . $_d . "',";
                        if($todays_day==$_d)
                        {
                            $daysofweekstring=$todays_day;
                        }
                    }
                    
                   
                    
                    
                    $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week='$daysofweekstring' ORDER BY p.start_time ASC ");
                    
                    // $dbTimetables=DB::select("Select p.id as period_id, p.start_time as start_time, p.end_time as end_time, p.class_id as class_id, p.batch_id as batch_id, p.is_break as is_break, p.title as period_order, t.id as timetable_id, t.day_of_week as day_of_week, t.subject_id as sub_id, t.room_no as room_no, s.name as sub_name, s.code as sub_code, asn.teacher_id as teacher_id, u.name as teacher_name FROM sh_periods p LEFT JOIN sh_timetable_new t ON p.id=t.period_id INNER JOIN sh_subjects s ON t.subject_id=s.id LEFT JOIN sh_assign_subjects asn ON t.subject_id=asn.subject_id and p.class_id = asn.class_id and p.batch_id = asn.batch_id LEFT JOIN sh_users u ON u.id=asn.teacher_id WHERE p.school_id=$user->school_id AND p.class_id=$class_id AND p.batch_id=$batch_id AND p.deleted_at IS NULL AND t.day_of_week in (" . rtrim($daysofweekstring, ",") . ") ORDER BY p.start_time ASC ");
                    
                    foreach ($dbTimetables as $tb)
                    {
                        $index1 = $tb->day_of_week;
                        $index2 = $tb->period_id;
                        $final_arr[$index1][$index2] = $tb;
                    }
                    
                    foreach ($final_arr as $key => $value)
                    {
                        $i = 0;
                        foreach ($value as $row_key => $row_value)
                        {
                            $final_arr[ucfirst($key)][$i++] = $row_value;
                            // $final_arr[][$i++] = $row_value;
                            unset($final_arr[$key]);
                            unset($final_arr[$key][$row_key]);
                        }
                    }
                    
                    
                    if(count($final_arr) > 0)
                    {
                        foreach($final_arr as $key=>$fr)
                        {
                            if($key==$filter_day)
                            {
                                foreach($fr as $key2=>$row)
                                {
                                    if(isset($row->sub_id))
                                    {
                                        if(!in_array($row->sub_id, $subjects_new))
                                        {
                                            $arr = array(
                                                "batch_id" => $row->batch_id,
                                                "class_id" => $row->batch_id,
                                                "day_of_week" => $row->day_of_week,
                                                "end_time" => $row->end_time,
                                                "is_break" => "Y",
                                                "period_id" => null,
                                                "period_order" => "Break",
                                                "room_no" => null,
                                                "start_time" => $row->start_time,
                                                "sub_code" => null,
                                                "sub_id" => null,
                                                "sub_name" => null,
                                                "teacher_id" => null,
                                                "teacher_name" => null,
                                                "timetable_id" => null
                                            );
                                            $final_arr[$key][$key2] = $arr;
                                            //unset($final_arr[$key][$key2]);
                                        }
                                    }
                                }
                            }
                            else
                            {
                                unset($final_arr[$key]);
                            }
                        }
                    }
                    
                    
                    $data=array();
                    // $data['periods']=$periods;
                    // $data['timetable']=$final_arr;
                    $data=$final_arr[$find_day];
                    
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Timetable fetch successfully!',
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
    
    
    
    
    
}
