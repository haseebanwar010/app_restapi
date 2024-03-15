<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;

 
class FeeController extends Controller
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

    public function get_feeRecord(Request $request)
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $student_rec = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id, s.discount_id, s.joining_date FROM sh_students_$user->school_id s WHERE s.id='$user->id' and s.academic_year_id='$academic_year->id'");
                    
                    // echo '<pre>';
                    // print_r($user->id);
                    // echo '<pre>';
                    // print_r($user->school_id);
                    // echo '<pre>';
                    // print_r($academic_year->id);
                    // echo '<pre>';
                    // print_r($student_rec);
                    // die;
                    
                    $overall_total_due_amount=0;
                    $overall_total_paid_amount=0;
                    $overall_total_next_amount=0;
                    $overall_total_paying_amount=0;
                    $overall_total_remaining_amount=0;
                    if(sizeof($student_rec) > 0)
                    {
                        $student_rec=$student_rec[0];
                        $student_assigned_discount=$student_rec->discount_id;
                        $fee_types=DB::table('fee_types')->select('id as fee_type_id','name as fee_type_name','description','amount as total_amount','due_date')->where('school_id',$user->school_id)->where('class_id',$student_rec->class_id)->where('academic_year_id',$academic_year->id)->whereNull('deleted_at')->get()->all();
                        
                        
                        
                        //Only Enabled Fees
                        $fee_type_status=DB::table('feetype_status')->select('*')->where('student_id',$user->id)->where('status','1')->get()->all();
                        if(sizeof($fee_type_status) > 0)
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
                        
                        $fee_types=array_values($fee_types);
                        
                        
                       
                        //Fee Varient handling
                        foreach($fee_types as $key => $fee_type)
                        {
                            $varients=DB::table('fee_varients')->where('feetype_id',$fee_type->fee_type_id)->whereDate('admission_from', '<=', date("Y-m-d", strtotime($student_rec->joining_date)))->whereDate('admission_to', '>=', date("Y-m-d", strtotime($student_rec->joining_date)))->where('nationality',$user->nationality)->whereNull('deleted_at')->first();
                            
                            if($varients)
                            {
                                $fee_types[$key]->total_paying_amount=number_format($varients->percentage, 2, '.', '');
                                $fee_types[$key]->varient_discount_amount=number_format(($fee_type->total_amount-$varients->percentage), 2, '.', '');
                                $fee_types[$key]->nationality=$varients->nationality;
                            }
                            else
                            {
                                $fee_types[$key]->total_paying_amount=number_format($fee_type->total_amount, 2, '.', '');
                                $fee_types[$key]->varient_discount_amount='0.00';
                                $fee_types[$key]->nationality="";
                            }
                            
                            $feetype_exemptions=DB::table('request_log as rl')
                            ->select('rl.feetype_id','fe.amount')
                            ->join('fee_exemption as fe', 'rl.feetype_id', '=', 'fe.feetype_id', 'left')
                            ->where('rl.type','fee_exemption')
                            ->where('rl.feetype_id',$fee_type->fee_type_id)
                            ->where('rl.student_id',$user->id)
                            ->where('rl.school_id',$user->school_id)
                            ->where('rl.status','approved')
                            ->where('fe.student_id',$user->id)
                            ->where('fe.academic_year_id',$academic_year->id)
                            ->whereNull('rl.deleted_at')->whereNull('fe.deleted_at')->first(); 
                            
                            if($feetype_exemptions)
                            {
                                $fee_types[$key]->total_paying_amount=number_format(($fee_type->total_paying_amount-$feetype_exemptions->amount), 2, '.', '');
                                $fee_types[$key]->exempted_amount=number_format($feetype_exemptions->amount, 2, '.', '');
                            }
                            else
                            {
                                $fee_types[$key]->exempted_amount='0.00';
                            }
                            
                            
                            $fee_types[$key]->total_amount=number_format($fee_type->total_amount, 2, '.', '');
                        }
                        

                        $get_paid_amount=DB::table('fee_collection')
                                        ->join('fee_types', 'fee_collection.feetype_id', '=', 'fee_types.id', 'left')
                                        ->select('fee_types.id', 'fee_collection.id','fee_collection.paid_amount')
                                        ->where('fee_collection.student_id',$user->id)
                                        ->whereNull('fee_collection.deleted_at')
                                        ->whereNull('fee_types.deleted_at')
                                        ->get()->all();
                        
                        
                        if(sizeof($get_paid_amount) > 0)
                        {
                            foreach($get_paid_amount as $fee_am)
                            {
                                $overall_total_paid_amount=$overall_total_paid_amount+$fee_am->paid_amount;
                            }
                            // $overall_total_paid_amount=$get_paid_amount;
                        }
                        
                        
                        
                        //Muliple Discounts handling
                        foreach($fee_types as $key => $fee_type)
                        {
                            $collected_fee=0;
                            $fee_status='0';
                            $fee_collection_status='UnPaid';
                            
                            
                            if($student_assigned_discount!='' && $student_assigned_discount !=NULL)
                            {
                                $all_stu_discounts=explode(',',$student_assigned_discount);
                                
                                // echo '<pre>';
                                // print_r($all_stu_discounts); 
                                // die;
                                
                                if(sizeof($all_stu_discounts) > 0)
                                {
                                    $sizeof_all_stu_discounts=sizeof($all_stu_discounts)-1;
                                    
                                    // $fee_types[$key]->discount='0.00';
                                    // $fee_types[$key]->discount_type="";
                                    // $fee_types[$key]->discount_value='0.00';
                                    $discounted_value=0;
                                    
                                    foreach($all_stu_discounts as $chkey => $all_stu_discount)
                                    {
                                        // echo $sizeof_all_stu_discounts.' size  key '.$chkey.' ';
                                        
                                        $discount_varients=DB::table('discount_varients')->where('class_id',$student_rec->class_id)->where('fee_type_id',$fee_type->fee_type_id)->where('discount_id',$all_stu_discount)->whereNull('deleted_at')->first();
                            
                                        if($discount_varients)
                                        {
                                            // echo $chkey.' in if key ';
                                            // echo '<pre>';
                                            // print_r($discount_varients);
                                            if($discount_varients->type=='number')
                                            { 
                                                // $fee_types[$key]->total_paying_amount = number_format(($fee_type->total_paying_amount - $discount_varients->percentage), 2, '.', '');
                                                $discounted_value=$discounted_value+$discount_varients->percentage;
                                            }
                                            else if($discount_varients->type=='percentage')
                                            {
                                                $dis_cal_value = ($fee_type->total_paying_amount * ($discount_varients->percentage/100));
                                                // $fee_types[$key]->total_paying_amount = number_format(($fee_type->total_paying_amount - $dis_cal_value), 2, '.', '');
                                                $discounted_value=$discounted_value+$dis_cal_value;
                                            }
                                            
                                            if($sizeof_all_stu_discounts == $chkey)
                                            {
                                                
                                                // echo ' in matching size '.$sizeof_all_stu_discounts.' key '. $chkey; 
                                                // $fee_types[$key]->discount=$discount_varients->percentage;
                                                // $fee_types[$key]->discount_type=$discount_varients->type;
                                                // $fee_types[$key]->discount_type="";
                                                $fee_types[$key]->total_paying_amount = number_format(($fee_type->total_paying_amount - $discounted_value), 2, '.', '');
                                                $fee_types[$key]->discount_value=number_format($discounted_value, 2, '.', '');
                                                
                                                // echo '<pre>';
                                                // print_r($fee_type);
                                                // echo $discounted_value;
                                                // die;
                                                
                                                // echo '<pre>';
                                                // print_r($fee_types);
                                                // die;
                                            }
                                            
                                        }
                                        else
                                        {
                                            // echo ' 1st <pre>';
                                            // print_r($fee_type);
                                            // echo ' else key '.$chkey.' ';
                                            $fee_types[$key]->total_paying_amount = number_format(($fee_type->total_paying_amount - $discounted_value), 2, '.', '');
                                            $fee_types[$key]->discount_value=number_format($discounted_value, 2, '.', '');
                                            // echo ' paying amount '.$fee_type->total_paying_amount. ' discount '.$discounted_value. ' rem '. $fee_types[$key]->total_paying_amount.' ';
                                            // echo '<pre>';
                                            // print_r($fee_types[$key]);
                                            // die;
                                        }
                                    }
                                }
                                else
                                {
                                    // $fee_types[$key]->discount='0.00';
                                    // $fee_types[$key]->discount_type="";
                                    $fee_types[$key]->discount_value='0.00';
                                }
                            }
                            else
                            {
                                    // $fee_types[$key]->discount='0.00';
                                    // $fee_types[$key]->discount_type="";
                                    $fee_types[$key]->discount_value='0.00';
                            }
                            
                            
                            
                            
                            $overall_total_due_amount=$overall_total_due_amount+$fee_types[$key]->total_paying_amount;
                            
                            $collections=DB::table('fee_collection')->where('feetype_id',$fee_type->fee_type_id)->where('student_id',$user->id)->whereNull('deleted_at')->get()->all();
                            
                            
                            if(sizeof($collections) > 0)
                            {
                                foreach($collections as $c_key => $col)
                                {
                                    $collected_fee=$collected_fee+$col->paid_amount;
                                    $fee_status=$col->status;
                                }
                                if($fee_status==0)
                                {
                                    $fee_collection_status='UnPaid';
                                }
                                else if($fee_status==1)
                                {
                                    $fee_collection_status='Paid';
                                }
                                else if($fee_status==2)
                                {
                                    $fee_collection_status='Partially Paid';
                                }
                            }
                            $fee_types[$key]->collected_fee=number_format($collected_fee, 2, '.', '');
                            $fee_types[$key]->remaining_amount=number_format(($fee_type->total_paying_amount-$collected_fee), 2, '.', '');
                            $fee_types[$key]->status=$fee_status;
                            $fee_types[$key]->fee_status=$fee_collection_status;
                            $fee_types[$key]->fee_type_id=(int) $fee_type->fee_type_id;
                            
                            $overall_total_paying_amount=$overall_total_paying_amount+$fee_type->total_paying_amount;
                            
                            $overall_total_remaining_amount=$overall_total_remaining_amount+$fee_types[$key]->remaining_amount;
                            // $overall_total_remaining_amount=number_format($overall_total_remaining_amount, 2, '.', '');
                        }
                        
                        // echo '<pre>';
                        //     print_r($fee_types);
                        //     die;
                        
                        
                        if(sizeof($fee_types) > 0)
                        {
                            $current_currency=DB::table('school_currencies')->select('id','currency_symbol')->where('school_id', $user->school_id)->where('is_default','yes')->whereNull('deleted_at')->first();
                            $currency_symbol='';
                            if($current_currency)
                            {
                                $currency_symbol=$current_currency->currency_symbol;
                            }
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Fee details fetch successfully!',
                                'data' => $fee_types,
                                'overall_total_amount' => sprintf('%.2f',$overall_total_paying_amount),
                                'overall_paid_amount' => sprintf('%.2f',$overall_total_paid_amount),
                                // 'overall_next_fee' => sprintf('%.2f',$overall_total_next_amount),
                                'overall_due_fee' => sprintf('%.2f',($overall_total_due_amount-$overall_total_paid_amount)),
                                'overall_remaining_fee' => sprintf('%.2f',($overall_total_remaining_amount)),
                                'currency_symbol' => $currency_symbol
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'No fee types found!',
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
    
    
    
    
    
}
