<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;


class AttendanceController extends Controller
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


public function unique_array($my_array, $key)
{ 
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

 
    public function all_studentsAttendance(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
            die;
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('month') && $request->input('month')!='')
                {
                    if($request->input('month') >= 0 && $request->input('month') <=12)
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->where('deleted_at', '=', '0')->first();
                        if($user)
                        {
                            $students=DB::table('student_guardians')->select('student_id')->where('guardian_id',$user->id)->whereNull('deleted_at')->get()->all();
                        
                            if(sizeof($students) > 0)
                            {
                                foreach($students as $key => $ids)
                                {
                                    $students[$key]=(int)$ids->student_id;
                                }
                                
                                $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                $month = $request->input('month');
                                $year=date("Y");
                                // $year=2020;
                                $day=date("d");
                                $current_date=$year.'-'.$month.'-'.$day;
                                
                                $start_date=date("Y-m-01", strtotime($current_date));
                                $end_date=date("Y-m-t", strtotime($current_date));
                                
                                $response=DB::table('attendance')
                                ->join('users', 'attendance.user_id', '=', 'users.id', 'left')
                                ->select('attendance.id', 'users.name', 'users.avatar', 'attendance.date','attendance.status')
                                ->whereIn('attendance.user_id',$students)
                                ->where('attendance.school_id',$user->school_id)
                                ->whereBetween('attendance.date', [$start_date,$end_date])
                                ->whereNULL('attendance.deleted_at')
                                ->where('users.deleted_at',0)
                                ->get()->all();
                                
                                if($response)
                                {
                                    $total_days=0;
                                    $total_present=0;
                                    $total_absent=0;
                                    $total_late=0;
                                    $total_leave=0;
                                    foreach($response as $key => $res)
                                    {
                                        if($res->status=='Leave')
                                        {
                                            $total_leave=$total_leave+1;
                                            $total_days=$total_days+$total_leave;
                                        }
                                        else if($res->status=='Present')
                                        {
                                            $total_present=$total_present+1;
                                            $total_days=$total_days+$total_present;
                                        }
                                        else if($res->status=='Late')
                                        {
                                            $total_late=$total_late+1;
                                            $total_days=$total_days+$total_late;
                                        }
                                        else if($res->status=='Absent')
                                        {
                                            $total_absent=$total_absent+1;
                                            $total_days=$total_days+$total_absent;
                                        }
                                        
                                        $response[$key]->day=date("d", strtotime($res->date));
                                        $response[$key]->today=date("l", strtotime($res->date));
                                        $response[$key]->month=date("F", strtotime($res->date));
                                    }
                                    
                                    $total_days=$total_present+$total_absent+$total_late+$total_leave;
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Attendance fetch successfully!',
                                        'data' => $response,
                                        'total_days' => $total_days,
                                        'Present' => $total_present,
                                        'Absent' => $total_absent,
                                        'Late' => $total_late,
                                        'Leave' => $total_leave
                                    );
                                    return response()->json($this->response, 200);
                                    die;
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'No attendance found!',
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
                            'message' => 'Please select valid month!',
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
                        'message' => 'Month is required!!',
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
    
    
    
    //single child attendence
    public function student_attendance(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
            die;
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('month') && $request->input('month')!='')
                {
                    if($request->input('month') >= 0 && $request->input('month') <=12)
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
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $month = $request->input('month');
                            $year=date("Y");
                            // $year=2020;
                            $day=date("d");
                            $current_date=$year.'-'.$month.'-'.$day;
                            $total_days_in_month=cal_days_in_month(CAL_GREGORIAN,$month,$year);
                            
                            $start_date=date("Y-m-01", strtotime($current_date));
                            $end_date=date("Y-m-t", strtotime($current_date));
                            
                            $response=DB::table('attendance')
                            ->select('id','date','status')
                            ->where('user_id',$user->id)
                            ->where('school_id',$user->school_id)
                            ->whereBetween('date', [$start_date,$end_date])
                            ->whereNULL('deleted_at')
                            ->get()->all();
                            
                            
                            
                            if($response)
                            {
                                $total_days=0;
                                $total_present=0;
                                $total_absent=0;
                                $total_late=0;
                                $total_leave=0;
                                $total_non_marked=0;
                                // echo '<pre>';
                                // print_r($response);
                                // die;
                                foreach($response as $key => $res)
                                {
                                    $response[$key]->id=(int) $res->id;
                                    
                                    if($res->status=='Leave')
                                    {
                                        $total_leave=$total_leave+1;
                                        $total_days=$total_days+$total_leave;
                                    }
                                    else if($res->status=='Present')
                                    {
                                        $total_present=$total_present+1;
                                        $total_days=$total_days+$total_present;
                                    }
                                    else if($res->status=='Late')
                                    {
                                        $total_late=$total_late+1;
                                        $total_days=$total_days+$total_late;
                                    }
                                    else if($res->status=='Absent')
                                    {
                                        $total_absent=$total_absent+1;
                                        $total_days=$total_days+$total_absent;
                                    }
                                    
                                    $response[$key]->day=date("d", strtotime($res->date));
                                    $response[$key]->today=date("l", strtotime($res->date));
                                    $response[$key]->month=date("F", strtotime($res->date));
                                }
                                
                                // $response  = json_encode($response);
                                // $response = json_decode($response, true);
                                
                                
                                
                                // for($i=1; $i<=$total_days_in_month; $i++)
                                // {
                                //     if(isset($response[$i]))
                                //     {
                                //         if($response[$i]['day']==$i)
                                //         {
                                //             echo ' in if ';
                                //         }
                                //         else
                                //         {
                                //             $newid=rand(0,100000);
                                //             $total_non_marked=$total_non_marked+1;
                                //             $this_date=$year.'-'.$month.'-'.$i;
                                //             $response[$i]['id']=$newid;
                                //             $response[$i]['date']="$this_date";
                                //             $response[$i]['status']="---";
                                //             $response[$i]['day']="$i";
                                //             $response[$i]['today']=date("l", strtotime($this_date));
                                //             $response[$i]['month']=date("F", strtotime($this_date));
                                //         }
                                //     }
                                //     else
                                //     {
                                //         $newid=rand(0,100000);
                                //         $total_non_marked=$total_non_marked+1;
                                //         $this_date=$year.'-'.$month.'-'.$i;
                                //         $response[$i]['id']=$newid;
                                //         $response[$i]['date']="$this_date";
                                //         $response[$i]['status']="---";
                                //         $response[$i]['day']="$i";
                                //         $response[$i]['today']=date("l", strtotime($this_date));
                                //         $response[$i]['month']=date("F", strtotime($this_date));
                                //     }
                                // }
                                
                                
                                
                                
                                
                                
                                
                                $total_days=$total_present+$total_absent+$total_late+$total_leave;
                                // $total_days=$total_present+$total_absent+$total_late+$total_leave+$total_non_marked;
                                
                                // $response=$this->unique_array($response, "day");
                                // array_multisort($response, SORT_ASC, SORT_STRING, $response);
                                usort($response, function($a, $b) {
                                    return $a->date <=> $b->date;
                                });
                                
                                // echo '<pre>';
                                // print_r($response);
                                // die;
                                
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Attendance fetch successfully!',
                                    'data' => $response,
                                    'total_days' => $total_days,
                                    'Present' => $total_present,
                                    'Absent' => $total_absent,
                                    'Late' => $total_late,
                                    'Leave' => $total_leave
                                );
                                return response()->json($this->response, 200);
                                die;
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No attendance found!',
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
                            'message' => 'Please select valid month!',
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
                        'message' => 'Month is required!!',
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
