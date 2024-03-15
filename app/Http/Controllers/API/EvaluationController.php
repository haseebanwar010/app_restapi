<?php

use App\StudentReport;

namespace App\Http\Controllers\API;

// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request; 

use App\Http\Controllers\Controller; 
// use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Validator;
use CommonHelper;

 

class EvaluationController extends Controller
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


    public function evaluateStudent(Request $request)
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
            
            if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
            {
            }
            else
            { 
                $errors['evaluation_id']='Evaluation Id is required';
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
                        
            if($request->input('student_id') && $request->input('student_id')!='')
            {
            }
            else
            {
                $errors['student_id']='Student Id is required';
            }
                                    
            if($request->input('cat_id') && $request->input('cat_id')!='')
            {
            }
            else
            {
                $errors['cat_id']='Category Id is required';
            }
                                                
            if($request->input('stars') && $request->input('stars')!='')
            {
            }
            else
            {
                $errors['stars']='Stars is required';
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    $class_id=$request->input('class_id');
                    $batch_id=$request->input('batch_id');
                    
                    $evaluations=DB::table('evaluations')
                                ->select('*')
                                ->where('id', $request->input('evaluation_id'))
                                ->where('academic_year_id', $academic_year->id)
                                ->whereNull('deleted_at')
                                ->first();
                                                
                    if($evaluations)
                    {
                        $cat_ids=$request->input('cat_id');
                        $stars=$request->input('stars');
                        $class_id=$request->input('class_id');
                        $batch_id=$request->input('batch_id');
                        $student_id=$request->input('student_id');
                        $evaluation_id=$request->input('evaluation_id');
                        
                        if($evaluations->type=='subject')
                        {
                            
                            if($request->input('subject_id') && $request->input('subject_id')!='')
                            {
                                $subject_id=$request->input('subject_id');
                                if(sizeof($cat_ids) > 0)
                                {
                                    foreach($cat_ids as $key => $cat_id)
                                    {
                                        $student_report=\App\StudentReport::updateOrCreate(
                                            ['class_id' => $class_id, 'batch_id' => $batch_id, 'subject_id' => $subject_id, 'student_id' => $student_id, 'evaluation_id' => $evaluation_id, 'category_id' => $cat_id],
                                            ['class_id' => $class_id, 'batch_id' => $batch_id, 'subject_id' => $subject_id, 'student_id' => $student_id, 'school_id' => $user->school_id, 'evaluation_id' => $evaluation_id, 'category_id' => $cat_id, 'stars' => $stars[$key]]
                                        );
                                    }
                                    
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Student evaluated successfully!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Evaluation Category Id is required!',
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
                                    'message' => 'Subject Id is required!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 200);
                            }
                            
                        }
                        else
                        {
                            if(sizeof($cat_ids) > 0)
                            {
                                foreach($cat_ids as $key => $cat_id)
                                {
                                    $student_report=\App\StudentReport::updateOrCreate(
                                        ['class_id' => $class_id, 'batch_id' => $batch_id, 'student_id' => $student_id, 'evaluation_id' => $evaluation_id, 'category_id' => $cat_id],
                                        ['class_id' => $class_id, 'batch_id' => $batch_id, 'student_id' => $student_id, 'school_id' => $user->school_id, 'evaluation_id' => $evaluation_id, 'category_id' => $cat_id, 'stars' => $stars[$key]]
                                    );
                                }
                                
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Student evaluated successfully!',
                                    'data' => ''
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Evaluation Category Id is required!',
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
                            'message' => 'Evaluation type is not found!',
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

            
    //Teacher Side - Get Evaluation terms
    public function get_studentEvaluationCat(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        if($request->input('class_id') && $request->input('class_id')!='')
                        {
                            if($request->input('batch_id') && $request->input('batch_id')!='')
                            {
                                if($request->input('student_id') && $request->input('student_id')!='')
                                {
                                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        
                                    if($user)
                                    {
                                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                        $class_id=$request->input('class_id');
                                        $batch_id=$request->input('batch_id');
                                        
                                        $evaluations=DB::table('evaluations')
                                                ->select('*')
                                                ->where('id', $request->input('evaluation_id'))
                                                ->where('academic_year_id', $academic_year->id)
                                                ->whereNull('deleted_at')
                                                ->first();
                                                
                                        if($evaluations)
                                        {
                                            $cats=array();
                                            if($evaluations->type=='subject')
                                            {
                                                if($request->input('subject_id') && $request->input('subject_id')!='')
                                                {
                                                    $subject_detail=DB::table('subjects')->select('name')->where('id', $request->input('subject_id'))->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->first();
                                                    
                                                    $cats=DB::table('evaluation_categories as ec')
                                                    ->select('ec.evaluation_id', 'ec.id as evaluation_category', 'ec.category_name')
                                                    ->where('ec.evaluation_id', $request->input('evaluation_id'))
                                                    ->where('ec.school_id', $user->school_id)
                                                    ->where('ec.academic_year_id', $academic_year->id)
                                                    ->whereNull('ec.deleted_at')
                                                    ->get()->all();
                                                    
                                                    foreach($cats as $key => $cat)
                                                    {
                                                        $eval_cats=DB::table('student_report as sr')
                                                        ->join('subjects', 'sr.subject_id', '=', 'subjects.id', 'left')
                                                        ->select('sr.stars','sr.subject_id', 'subjects.name as subject_name')
                                                        ->where('sr.class_id', $request->input('class_id'))
                                                        ->where('sr.batch_id', $request->input('batch_id'))
                                                        ->where('sr.student_id', $request->input('student_id'))
                                                        ->where('sr.evaluation_id', $request->input('evaluation_id'))
                                                        ->where('sr.category_id', $cat->evaluation_category)
                                                        ->where('sr.subject_id', $request->input('subject_id'))
                                                        ->whereNull('sr.deleted_at')
                                                        ->first();
                                                        
                                                        if($eval_cats)
                                                        {
                                                            $cats[$key]->stars=$eval_cats->stars;
                                                            $cats[$key]->subject_id=$eval_cats->subject_id;
                                                            $cats[$key]->subject_name=$eval_cats->subject_name;
                                                        }
                                                        else
                                                        {
                                                            $cats[$key]->stars=0;
                                                            $cats[$key]->subject_id=$request->input('subject_id');
                                                            $cats[$key]->subject_name=$subject_detail->name;
                                                        }
                                                    }
                                                    
                                                }
                                                else
                                                {
                                                    $this->response=array
                                                    (
                                                        'status' => 'success',
                                                        'message' => 'Subject Id is required!',
                                                        'data' => ''
                                                    );
                                                    return response()->json($this->response, 200);
                                                }
                                            }
                                            else
                                            {
                                                $cats=DB::table('evaluation_categories as ec')
                                                ->select('ec.evaluation_id', 'ec.id as evaluation_category', 'ec.category_name')
                                                ->where('ec.evaluation_id', $request->input('evaluation_id'))
                                                ->where('ec.school_id', $user->school_id)
                                                ->where('ec.academic_year_id', $academic_year->id)
                                                ->whereNull('ec.deleted_at')
                                                ->get()->all();
                                                
                                                foreach($cats as $key => $cat)
                                                {
                                                    $eval_cats=DB::table('student_report as sr')
                                                    ->select('sr.stars')
                                                    ->where('sr.class_id', $request->input('class_id'))
                                                    ->where('sr.batch_id', $request->input('batch_id'))
                                                    ->where('sr.student_id', $request->input('student_id'))
                                                    ->where('sr.evaluation_id', $request->input('evaluation_id'))
                                                    ->where('sr.category_id', $cat->evaluation_category)
                                                    ->whereNull('sr.deleted_at')
                                                    ->first();
                                                    
                                                    if($eval_cats)
                                                    {
                                                        $cats[$key]->stars=$eval_cats->stars;
                                                    }
                                                    else
                                                    {
                                                        $cats[$key]->stars=0;
                                                    }
                                                }
                                            }
                                            
                                            if(sizeof($cats) > 0)
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation catgories fetch successfully!',
                                                    'data' => $cats,
                                                    'student' => ['id'=> $user->id, 'name' => $user->name, 'avatar' => $user->avatar]
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            else
                                            { 
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation catgories not found!',
                                                    'data' => '',
                                                    'student' => ['id'=> $user->id, 'name' => $user->name, 'avatar' => $user->avatar]
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            
                                        }
                                        else
                                        {
                                            $this->response=array
                                            (
                                                'status' => 'success',
                                                'message' => 'Evaluation not found!',
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
                                        'message' => 'Student Id is required!',
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
                            'message' => 'Evaluation Id is required!',
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
                        'message' => 'Term Id is required!',
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
                 
    //Teacher Side - Get Evaluation terms
    public function get_studentEvaluation(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        if($request->input('class_id') && $request->input('class_id')!='')
                        {
                            if($request->input('batch_id') && $request->input('batch_id')!='')
                            {
                                if($request->input('subject_id') && $request->input('subject_id')!='')
                                {
                                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        
                                    if($user)
                                    {
                                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                            
                                        $class_id=$request->input('class_id');
                                        $batch_id=$request->input('batch_id');
                                        $students=DB::table('students_'.$user->school_id.' as view')
                                        ->join('users', 'view.id', '=', 'users.id', 'left')
                                        ->join('subject_groups', 'view.subject_group_id', '=', 'subject_groups.id', 'left')
                                        ->join('classes', 'view.class_id', '=', 'classes.id', 'left')
                                        ->join('batches', 'view.batch_id', '=', 'batches.id', 'left')
                                        ->join('student_guardians', 'view.id', '=', 'student_guardians.student_id', 'left')
                                        ->select('view.id', 'users.name', 'users.avatar')
                                        ->where('view.class_id', $class_id)
                                        ->where('view.batch_id', $batch_id)
                                        ->where('view.academic_year_id', $academic_year->id)
                                        ->where('users.deleted_at', '0')
                                        ->where('users.status', '0')
                                        ->whereNotNull('view.subject_group_id')
                                        ->whereNull('subject_groups.deleted_at')
                                        ->where('subject_groups.academic_year_id', $academic_year->id)
                                        ->whereNull('batches.deleted_at')
                                        ->where('batches.academic_year_id', $academic_year->id)
                                        ->whereNull('student_guardians.deleted_at')
                                        ->where('classes.academic_year_id', $academic_year->id)
                                        ->whereNull('classes.deleted_at')
                                        ->get()->all();
                                        
                                        if(sizeof($students) > 0)
                                        {
                                            $this->response=array
                                            (
                                                'status' => 'success',
                                                'message' => 'Students fetch successfully!',
                                                'data' => $students
                                            );
                                            return response()->json($this->response, 200);
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
                                        'message' => 'Subject Id is required!',
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
                            'message' => 'Evaluation Id is required!',
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
                        'message' => 'Term Id is required!',
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
         
                
    //Teacher Side - Get Evaluation terms
    public function get_studentNonEvaluation(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        if($request->input('class_id') && $request->input('class_id')!='')
                        {
                            if($request->input('batch_id') && $request->input('batch_id')!='')
                            {
                                $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                                if($user)
                                {
                                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                        
                                    $evaluations=DB::table('evaluations')
                                                ->select('*')
                                                ->where('id', $request->input('evaluation_id'))
                                                ->where('academic_year_id', $academic_year->id)
                                                ->whereNull('deleted_at')
                                                ->first();
                                                
                                    if($evaluations)
                                    {
                                        if($evaluations->type=='subject')
                                        {
                                            $subjects=DB::table('subjects')->select('id','name')->where('class_id', $request->input('class_id'))->where('batch_id', $request->input('batch_id'))->where('school_id', $user->school_id)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                                            if(sizeof($subjects) > 0)
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Subjects fetch successfully!',
                                                    'data' => '',
                                                    'subjects' => $subjects
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            else
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Subjects fetch successfully!',
                                                    'data' => '',
                                                    'subjects' => $subjects
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                        }
                                        else
                                        {
                                            $class_id=$request->input('class_id');
                                            $students=DB::table('students_'.$user->school_id.' as view')
                                            ->join('users', 'view.id', '=', 'users.id', 'left')
                                            ->join('subject_groups', 'view.subject_group_id', '=', 'subject_groups.id', 'left')
                                            ->join('classes', 'view.class_id', '=', 'classes.id', 'left')
                                            ->join('batches', 'view.batch_id', '=', 'batches.id', 'left')
                                            ->join('student_guardians', 'view.id', '=', 'student_guardians.student_id', 'left')
                                            ->select('view.id', 'users.name', 'users.avatar')
                                            ->where('view.class_id', $class_id)
                                            ->where('view.academic_year_id', $academic_year->id)
                                            ->where('users.deleted_at', '0')
                                            ->where('users.status', '0')
                                            ->whereNotNull('view.subject_group_id')
                                            ->whereNull('subject_groups.deleted_at')
                                            ->where('subject_groups.academic_year_id', $academic_year->id)
                                            ->whereNull('batches.deleted_at')
                                            ->where('batches.academic_year_id', $academic_year->id)
                                            ->whereNull('student_guardians.deleted_at')
                                            ->where('classes.academic_year_id', $academic_year->id)
                                            ->whereNull('classes.deleted_at')
                                            ->get()->all();
                                            
                                            if(sizeof($students) > 0)
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Students fetch successfully!',
                                                    'data' => $students,
                                                    'subjects' => ''
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            else
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'No students found!',
                                                    'data' => '',
                                                    'subjects' => ''
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
                                            'message' => 'No evaluation found!',
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
                            'message' => 'Evaluation Id is required!',
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
                        'message' => 'Term Id is required!',
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
         
    
    //Teacher Side - Get Evaluation types
    public function get_evaluateType(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    if($request->input('class_id') && $request->input('class_id')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            
                            // $class_teachers= DB::table('batches')
                            //                 ->join('classes', 'batches.class_id', '=', 'classes.id', 'left')
                            //                 ->select('batches.class_id as id')
                            //                 ->where('batches.school_id',$user->school_id)
                            //                 ->where('batches.teacher_id',$user->id)
                            //                 ->where('batches.academic_year_id',$academic_year->id)
                            //                 ->where('classes.academic_year_id',$academic_year->id)
                            //                 ->whereNull('classes.deleted_at')
                            //                 ->whereNull('batches.deleted_at')
                            //                 ->get()->all();
                                            
                            // $subject_teachers=  DB::table('assign_subjects')
                            //                     ->join('classes', 'assign_subjects.class_id', '=', 'classes.id', 'left')
                            //                     ->select('assign_subjects.class_id as id')
                            //                     ->where('assign_subjects.school_id',$user->school_id)
                            //                     ->whereNull('assign_subjects.deleted_at')
                            //                     ->where('classes.academic_year_id',$academic_year->id)
                            //                     ->whereNull('classes.deleted_at')
                            //                     ->where(function ($match) use ($user){
                            //                         $match->where('assign_subjects.teacher_id', $user->id)
                            //                         ->orWhere('assign_subjects.assistant_id', $user->id);
                            //                     })->get()->all();
                                                
                            // $data=array_merge($class_teachers,$subject_teachers);
                            // $data=array_unique($data, SORT_REGULAR);
                            
                            // $class_ids=array();
                            // foreach($data as $key => $d)
                            // {
                            //     $class_ids[]=(int)$d->id;
                            // }
                            
                            $class_id=$request->input('class_id');
        
                            $result=DB::table('evaluations')
                                    ->select('id', 'evaluation_name')
                                    ->where('school_id',$user->school_id)
                                    ->where('academic_year_id', $academic_year->id)
                                    ->where('term_id', $request->input('term_id'))
                                    ->whereRaw("FIND_IN_SET('$class_id',classes)")
                                    ->whereNull('deleted_at')
                                    ->get()->all();
                                
                            if($result)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Evaluation Types fetch successfully!',
                                    'data' => $result
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No evaluation type found!',
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
                        'message' => 'Term Id is required!',
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
      

    //Teacher Side - create Evaluation type   
    public function create_evaluationType(Request $request)
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
            
            if($request->input('evaluation_name') && $request->input('evaluation_name')!='')
            {
            }
            else
            {
                $errors['evaluation_name']='Evaluation Name is required';
            }
                           
            if($request->input('type') && $request->input('type')!='')
            {
            }
            else
            {
                $errors['type']='Type is required';
            }
                                          
            if($request->input('term_id') && $request->input('term_id')!='')
            {
            }
            else
            {
                $errors['term_id']='Term Id is required';
            }
                        
            if($request->input('start_date') && $request->input('start_date')!='')
            {
            }
            else
            {
                $errors['start_date']='Start Date is required';
            }
                                    
            if($request->input('end_date') && $request->input('end_date')!='')
            {
            }
            else
            {
                $errors['end_date']='End Date is required';
            }
                                                
            if($request->input('classes') && ($request->input('classes')!='' || sizeof($request->input('classes')) > 0 ))
            {
            }
            else
            {
                $errors['classes']='classes is required';
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $data=array(
                        'evaluation_name' => $request->input('evaluation_name'),
                        'type' => $request->input('type'),
                        'term_id' => $request->input('term_id'),
                        'school_id' => $user->school_id,
                        'academic_year_id' => $academic_year->id,
                        'start_date' => $request->input('start_date'),
                        'end_date' => $request->input('end_date'),
                        'classes' => implode(',', $request->input('classes'))
                        );
                    $result=DB::table('evaluations')->insert($data);
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Evaluation Type created successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Something went wrong please try again!',
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
    

    //Teacher Side - create Evaluation type   
    public function update_evaluationType(Request $request)
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
            
            if($request->input('evaluation_name') && $request->input('evaluation_name')!='')
            {
            }
            else
            {
                $errors['evaluation_name']='Evaluation Name is required';
            }
                           
            if($request->input('type') && $request->input('type')!='')
            {
            }
            else
            {
                $errors['type']='Type is required';
            }
                                          
            if($request->input('term_id') && $request->input('term_id')!='')
            {
            }
            else
            {
                $errors['term_id']='Term Id is required';
            }
                        
            if($request->input('start_date') && $request->input('start_date')!='')
            {
            }
            else
            {
                $errors['start_date']='Start Date is required';
            }
                                    
            if($request->input('end_date') && $request->input('end_date')!='')
            {
            }
            else
            {
                $errors['end_date']='End Date is required';
            }
                                                
            if($request->input('classes') && ($request->input('classes')!='' || sizeof($request->input('classes')) > 0 ))
            {
            }
            else
            {
                $errors['classes']='classes is required';
            }
                                                            
            if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
            {
            }
            else
            {
                $errors['evaluation_id']='Evaluation Id is required';
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
                    $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                    
                    $data=array(
                        'evaluation_name' => $request->input('evaluation_name'),
                        'type' => $request->input('type'),
                        'term_id' => $request->input('term_id'),
                        'school_id' => $user->school_id,
                        'academic_year_id' => $academic_year->id,
                        'start_date' => $request->input('start_date'),
                        'end_date' => $request->input('end_date'),
                        'classes' => implode(',', $request->input('classes'))
                        );
                    $result=DB::table('evaluations')->where('id',$request->input('evaluation_id'))->update($data);
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Evaluation Type updated successfully!',
                            'data' => $data
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No change found!',
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
    
            
    //Teacher Side - delete Evaluation type
    public function delete_evaluationType(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else 
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                    if($user)
                    {
                        $data=array(
                            'deleted_at' => date('Y-m-d h:i:s')
                            );
                            
                        $result=DB::table('evaluations')->where('id',$request->input('evaluation_id'))->update($data);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Evaluation Type deleted successfully!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Something went wrong please try again!',
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
                        'message' => 'Evaluation Id is required!',
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
    
    
    //Teacher Side - Get Evaluation types
    public function get_evaluationTypes(Request $request)
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
                                    ->select('batches.class_id as id')
                                    ->where('batches.school_id',$user->school_id)
                                    ->where('batches.teacher_id',$user->id)
                                    ->where('batches.academic_year_id',$academic_year->id)
                                    ->where('classes.academic_year_id',$academic_year->id)
                                    ->whereNull('classes.deleted_at')
                                    ->whereNull('batches.deleted_at')
                                    ->get()->all();
                                    
                    $subject_teachers=  DB::table('assign_subjects')
                                        ->join('classes', 'assign_subjects.class_id', '=', 'classes.id', 'left')
                                        ->select('assign_subjects.class_id as id')
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
                    
                    $class_ids=array();
                    foreach($data as $key => $d)
                    {
                        $class_ids[]=(int)$d->id;
                    }

                    $result=DB::table('evaluations')
                            ->select('id', 'evaluation_name')
                            ->where('school_id',$user->school_id)
                            ->where('academic_year_id', $academic_year->id)
                            ->whereIn('classes', $class_ids)
                            ->whereNull('deleted_at')
                            ->get()->all();
                        
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Evaluation Types fetch successfully!',
                            'data' => $result
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No evaluation type found!',
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
                    'message' => 'Invalid user!',
                    'data' => ''
                );
                return response()->json($this->response, 200);
            }
        }
    }
            
            
    //Teacher Side - Get Evaluation terms
    public function get_evaluationTerms(Request $request)
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
                        
                    $result=DB::table('evaluation_terms')->select('*')->where('school_id',$user->school_id)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                    if($result)
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Evaluation Terms fetch successfully!',
                            'data' => $result
                        );
                        return response()->json($this->response, 200);
                    }
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'No evaluation term found!',
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
        
        
    //Teacher Side - create Evaluation term
    public function create_evaluationCat(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('category_name') && $request->input('category_name')!='')
                {
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $data=array(
                                'category_name' => $request->input('category_name'),
                                'evaluation_id' => $request->input('evaluation_id'),
                                'school_id' => $user->school_id,
                                'academic_year_id' => $academic_year->id
                                );
                                
                            $result=DB::table('evaluation_categories')->insert($data);
                            if($result)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Evaluation category created successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Something went wrong please try again!',
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
                            'message' => 'Evaluation Id is required!',
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
                        'message' => 'Category name is required!',
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
                
        
    //Teacher Side - create Evaluation term
    public function update_evaluationCat(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('category_name') && $request->input('category_name')!='')
                {
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        if($request->input('cat_id') && $request->input('cat_id')!='')
                        {
                            $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                            if($user)
                            {
                                $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                                $data=array(
                                    'category_name' => $request->input('category_name'),
                                    'evaluation_id' => $request->input('evaluation_id'),
                                    'school_id' => $user->school_id,
                                    'academic_year_id' => $academic_year->id
                                    );
                                    
                                $result=DB::table('evaluation_categories')->where('id',$request->input('cat_id'))->update($data);
                                if($result)
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'Evaluation category updated successfully!',
                                        'data' => $data
                                    );
                                    return response()->json($this->response, 200);
                                }
                                else
                                {
                                    $this->response=array
                                    (
                                        'status' => 'success',
                                        'message' => 'No changes found!',
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
                                'message' => 'Evaluation Category Id is required!',
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
                            'message' => 'Evaluation Id is required!',
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
                        'message' => 'Category name is required!',
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
        
            
    //Teacher Side - delete Evaluation term
    public function delete_evaluationCat(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('cat_id') && $request->input('cat_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $data=array(
                            'deleted_at' => date('Y-m-d h:i:s')
                            );
                            
                        $result=DB::table('evaluation_categories')->where('id',$request->input('cat_id'))->update($data);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Evaluation Category deleted successfully!',
                                'data' => ''
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Something went wrong please try again!',
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
                        'message' => 'Category Id is required!',
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
    
            
    //Teacher Side - create Evaluation term
    public function create_evaluationTerm(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_name') && $request->input('term_name')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $data=array(
                            'term_name' => $request->input('term_name'),
                            'school_id' => $user->school_id,
                            'academic_year_id' => $academic_year->id
                            );
                            
                        $result=DB::table('evaluation_terms')->insert($data);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Evaluation Term created successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Something went wrong please try again!',
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
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Term name is required!',
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
    
        
    //Teacher Side - update Evaluation term
    public function update_evaluationTerm(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    if($request->input('term_name') && $request->input('term_name')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                        
                        if($user)
                        {
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $data=array(
                                'term_name' => $request->input('term_name'),
                                'school_id' => $user->school_id,
                                'academic_year_id' => $academic_year->id
                                );
                                
                            $result=DB::table('evaluation_terms')->where('id',$request->input('term_id'))->update($data);
                            if($result)
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Evaluation Term updated successfully!',
                                    'data' => $data
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No changes found!',
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
                    else
                    {
                        $this->response=array
                        (
                            'status' => 'success',
                            'message' => 'Term name is required!',
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
                        'message' => 'Term Id is required!',
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
    
        
    //Teacher Side - delete Evaluation term
    public function delete_evaluationTerm(Request $request)
    {
        if(!empty($this->response))
        {
            return response()->json($this->response, 400);
        }
        else
        {
            if($request->input('api_token') && $request->input('api_token')!='')
            {
                if($request->input('term_id') && $request->input('term_id')!='')
                {
                    $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                    if($user)
                    {
                        $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                        $data=array(
                            'deleted_at' => date('Y-m-d h:i:s')
                            );
                            
                        $result=DB::table('evaluation_terms')->where('id',$request->input('term_id'))->update($data);
                        if($result)
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Evaluation Term deleted successfully!',
                                'data' => $data
                            );
                            return response()->json($this->response, 200);
                        }
                        else
                        {
                            $this->response=array
                            (
                                'status' => 'success',
                                'message' => 'Something went wrong please try again!',
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
                else
                {
                    $this->response=array
                    (
                        'status' => 'success',
                        'message' => 'Term Id is required!',
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
    
    
    
    // Parent Side
    public function evaluation_types(Request $request)
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
                            $evaluations=DB::table('evaluations')
                            ->where('school_id', $user->school_id)
                            // ->where('start_date', '<=', date('Y-m-d'))
                            // ->where('end_date', '>=', date('Y-m-d'))
                            // ->whereIn('classes', [$student_rec->class_id])
                            ->whereRaw("find_in_set('".$student_rec->class_id."',classes)")
                            ->whereNull('deleted_at')
                            ->get()->all(); 
                            
                            if(sizeof($evaluations) > 0)
                            {
                                foreach($evaluations as $key => $eva)
                                {
                                    $evaluations[$key]->id=(int) $eva->id;
                                    $evaluations[$key]->school_id=(int) $eva->school_id;
                                    $evaluations[$key]->academic_year_id=(int) $eva->academic_year_id;
                                    $evaluations[$key]->term_id=(int) $eva->term_id;
                                }
                                
                                $all_arr[]=array("id" => "all", 'evaluation_name' => 'All');
                                $evaluations=array_merge($evaluations,$all_arr);
                                $evaluations=array_values($evaluations);
                                
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'Evaluation found successfully!',
                                    'data' => $evaluations
                                );
                                return response()->json($this->response, 200);
                            }
                            else
                            {
                                $this->response=array
                                (
                                    'status' => 'success',
                                    'message' => 'No evaluation found!',
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
                                'message' => 'Class not found!',
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
    
    
    //Psrent side - get child evaluation for parent side.
    public function evaluation_record(Request $request)
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
                    if($request->input('evaluation_id') && $request->input('evaluation_id')!='')
                    {
                        $user = DB::table('users')->where('api_token', $request->input('api_token'))->whereIn('role_id', [2,3,4])->where('deleted_at', '=', '0')->first();
                    
                        if($user)
                        {
                            $child_id=$request->input('child_id');
                            $academic_year=DB::table('academic_years')->select('id')->where('school_id',$user->school_id)->where('is_active','Y')->whereNull('deleted_at')->first();
                            $student_rec = DB::select("SELECT s.class_id, s.batch_id, s.subject_group_id FROM sh_students_$user->school_id s left JOIN sh_batches ON sh_batches.id = s.batch_id WHERE  s.id='$child_id' ");
                            $student_detail=DB::table('users')->select('id', 'name', 'avatar')->where('id',$child_id)->where('deleted_at', '=', '0')->first();
                            if(sizeof($student_rec) > 0)
                            {
                                $data=array();
                                $student_rec=$student_rec[0];
                                // $allSubjects=DB::table('subjects')->select('id','name')->where('class_id', $student_rec->class_id)->where('batch_id', $student_rec->batch_id)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                                $allSubjects=array();
                                
                                if($student_rec->subject_group_id !='' && $student_rec->subject_group_id!=NULL)
                                {
                                    
                                    $all_subjectsGroups=DB::table('subject_groups')->where('id', $student_rec->subject_group_id)->where('class_id', $student_rec->class_id)->where('batch_id', $student_rec->batch_id)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->first();
                                    
                                    if($all_subjectsGroups)
                                    {
                                        $refined_subjects=explode(',', $all_subjectsGroups->subjects);
                                        if(sizeof($refined_subjects) > 0)
                                        {
                                            foreach($refined_subjects as $key => $ref_subj)
                                            {
                                                $ref_sub_det=DB::table('subjects')->select('id')->where('id', $ref_subj)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->first();
                                                if($ref_sub_det)
                                                {
                                                    $allSubjects[]=(int) $ref_sub_det->id;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $this->response=array
                                            (
                                                'status' => 'success',
                                                'message' => 'Subjects not found!',
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
                                            'message' => 'Subject Group not found!',
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
                                        'message' => 'Subject Group not found!',
                                        'data' => ''
                                    );
                                    return response()->json($this->response, 200);
                                }
                                
                                
                                
                                // echo '<pre>';
                                // print_r($student_rec);
                                // echo '<pre>';
                                // print_r($all_subjectsGroups);
                                // echo '<pre>';
                                // print_r($allSubjects);
                                // die;
                                
                                
                                if($request->input('evaluation_id')=='all')
                                {
                                    $data=array();
                                    $subject_eval=array();
                                    $non_subject_ev=array();
                                    $Evaluations=DB::table('evaluations')->whereRaw("find_in_set('$student_rec->class_id',classes)")->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                                    // $Evaluations=DB::table('evaluations')->whereRaw("find_in_set('$student_rec->class_id',classes)")->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->get()->all();
                                    
                                    $number_of_evaluations=sizeof($Evaluations);
                                    foreach($Evaluations as $eval)
                                    {
                                        if($eval->type=='subject')
                                        {
                                            $res=DB::table('evaluation_categories')->select('id', 'category_name')->where('evaluation_categories.evaluation_id', $eval->id)->get()->all();
                                            $total_eval_cat=sizeof($res);
                                            if(sizeof($res) > 0)
                                            {
                                                $sub_count=0;
                                                $sub_count_stars=0;
                                                $subject_stars_count=0;
                                                foreach($res as $key => $re)
                                                {
                                                    $res[$key]->type='subject';
                                                    
                                                    $sub_count=$sub_count+1;
                                                    $subrecord=DB::table('student_report')
                                                    ->join('subjects', 'student_report.subject_id', '=', 'subjects.id', 'left')
                                                    ->select('student_report.subject_id', 'subjects.name', 'student_report.stars as obtained_stars')
                                                    ->where('student_report.evaluation_id', $eval->id)
                                                    ->where('student_report.category_id', $re->id)
                                                    ->where('student_report.student_id', $request->input('child_id'))
                                                    ->get()->all();
                                                    
                                                    $final_subject_array=array();
                                                    if(sizeof($subrecord) > 0)
                                                    {
                                                        foreach($subrecord as $s_key => $sub_recrd)
                                                        {
                                                            $subrecord[$s_key]->subject_id=(int) $sub_recrd->subject_id;
                                                            // $final_subject_array->id=$sub_recrd->subject_id;
                                                            // array_push($final_subject_array->id, $sub_recrd->subject_id);
                                                            $final_subject_array[] = (int) $sub_recrd->subject_id;
                                                        }
                                                        
                                                    }
                                                    
                                                    $get_different_subject=array_diff($allSubjects,$final_subject_array);
                                                    
                                                    $sizeofsubrecord=sizeof($subrecord);
                                                    
                                                    
                                                    if(sizeof($get_different_subject) > 0)
                                                    {
                                                        $get_different_subject=array_values($get_different_subject);
                                                        
                                                        foreach($get_different_subject as $diffsubkey => $getDiffSubj)
                                                        {
                                                            $dif_sub_det=DB::table('subjects')->select('id as subject_id', 'name')->where('id', $getDiffSubj)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->first();
                                                            if($dif_sub_det)
                                                            {
                                                                $dif_sub_det->obtained_stars=0;
                                                                $subrecord[$sizeofsubrecord+$diffsubkey+1]=$dif_sub_det;
                                                            }
                                                        }
                                                    }
                                                    
                                                    if(sizeof($subrecord) > 0)
                                                    {
                                                        $total_ob_stars=0;
                                                        $total_ob_stars_count=0;
                                                        
                                                        $subrecord=array_values($subrecord);
                                                        
                                                        foreach($subrecord as $fkey => $sub)
                                                        {
                                                            $subrecord[$fkey]->subject_id=(int) $sub->subject_id;
                                                            $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            // if(sizeof($subrecord) == sizeof($allSubjects))
                                                            // {
                                                            //     $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //     $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            // }
                                                            // else
                                                            // {
                                                            //     foreach($allSubjects as $ckey => $a_subj)
                                                            //     {
                                                            //         if($a_subj->id==$sub->subject_id)
                                                            //         {
                                                            //             $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //             $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            //         }
                                                            //         else
                                                            //         {
                                                            //             $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //             $total_ob_stars=$total_ob_stars+0;
                                                            //         }
                                                            //     }
                                                            // }
                                                        }
                                                        $subrecord_final_sizeOf=sizeof($subrecord);
                                                        $total_ob_stars=round(($total_ob_stars/$subrecord_final_sizeOf) ,2);
                                                        // $total_ob_stars=ceil($total_ob_stars/$total_ob_stars_count);
                                                        $res[$key]->total_obtained_stars=$total_ob_stars;
                                                        
                                                        $sub_count_stars=$sub_count_stars+$total_ob_stars;
                                                        
                                                        if($total_ob_stars==1)
                                                        {
                                                            $res[$key]->status='Weak';
                                                        }
                                                        else if($total_ob_stars==2)
                                                        {
                                                            $res[$key]->status='Fair';
                                                        }
                                                        else if($total_ob_stars==3)
                                                        {
                                                            $res[$key]->status='Good';
                                                        }
                                                        else if($total_ob_stars==4)
                                                        {
                                                            $res[$key]->status='Excellent';
                                                        }
                                                        else if($total_ob_stars==5)
                                                        {
                                                            $res[$key]->status='Exceptional';
                                                        }
                                                        else
                                                        {
                                                            $res[$key]->status='';
                                                        }
                                                        
                                                        $res[$key]->subjects=$subrecord;
                                                    }
                                                    
                                                    
                                                }
                                                
                                                // echo $sub_count_stars. ' count '.$sub_count;die;
                                                $sub_count_stars=number_format($sub_count_stars/$total_eval_cat,2);
                                                
                                                if(isset($student_detail->overall_stars))
                                                {
                                                    $student_detail->overall_stars=$sub_count_stars+$student_detail->overall_stars;
                                                }
                                                else
                                                {
                                                    $student_detail->overall_stars=$sub_count_stars;
                                                }
                                                
                                                // $student_detail->overall_stars=$sub_count_stars;
                                                
                                                // echo 'pehly '.$student_detail->overall_stars.' ';
                                                
                                                $subject_eval[]=$res;
                                            }
                                            
                                        }
                                        else if($eval->type=='non-subject')
                                        {
                                            $record=DB::table('student_report')
                                            ->join('evaluation_categories', 'student_report.category_id', '=', 'evaluation_categories.id', 'left')
                                            ->select('student_report.category_id as id', 'evaluation_categories.category_name', 'student_report.stars as obtained_stars')
                                            ->where('student_report.evaluation_id', $eval->id)
                                            ->where('student_report.student_id', $child_id)
                                            ->whereNull('student_report.deleted_at')
                                            ->whereNull('evaluation_categories.deleted_at')
                                            ->get()->all();

                                            if(sizeof($record) > 0)
                                            {
                                                $non_sub_count=0;
                                                $non_sub_starts=0;
                                                foreach($record as $key => $rec)
                                                {
                                                    $record[$key]->type='non-subject';
                                                    
                                                    $non_sub_count=$non_sub_count+1;
                                                    $non_sub_starts=$non_sub_starts+$rec->obtained_stars;
                                                    $record[$key]->total_obtained_stars=$rec->obtained_stars;
                                                    
                                                    if($rec->obtained_stars==1)
                                                    {
                                                        $record[$key]->status='Weak';
                                                    }
                                                    else if($rec->obtained_stars==2)
                                                    {
                                                        $record[$key]->status='Fair';
                                                    }
                                                    else if($rec->obtained_stars==3)
                                                    {
                                                        $record[$key]->status='Good';
                                                    }
                                                    else if($rec->obtained_stars==4)
                                                    {
                                                        $record[$key]->status='Excellent';
                                                    }
                                                    else if($rec->obtained_stars==5)
                                                    {
                                                        $record[$key]->status='Exceptional';
                                                    }
                                                    else
                                                    {
                                                        $record[$key]->status='';
                                                    }
                                                    
                                                }

                                                $non_sub_starts=number_format(ceil($non_sub_starts/$non_sub_count),2);
                                                
                                                
                                                // echo ' bad ami'. $student_detail->overall_stars;
                                                
                                                if(isset($student_detail->overall_stars))
                                                {
                                                    $student_detail->overall_stars=$non_sub_starts+$student_detail->overall_stars;
                                                }
                                                else
                                                {
                                                    $student_detail->overall_stars=$non_sub_starts;
                                                }
                                                
                                                
                                                // echo ' phir '.$student_detail->overall_stars;
                                                
                                                // $student_detail->overall_stars=$non_sub_starts+$student_detail->overall_stars;
                                            }
                                            $non_subject_ev[]=$record;
                                            
                                        }
                                    }//end of evaluation loop
                                    $data1=array();
                                    $data2=array();
                                    if(sizeof($subject_eval) > 0)
                                    {
                                        $data1=$subject_eval[0];
                                    }
                                    
                                    if(sizeof($non_subject_ev) > 0)
                                    {
                                        $data2=$non_subject_ev[0];
                                    }
                                    
                                    
                                    $data=array_merge($data1,$data2);
                                    $data=array_values($data);
                                    
                                    if(sizeof($data) > 0)
                                    {
     
                                        $final_eval_stars=number_format($student_detail->overall_stars/$number_of_evaluations,2);
                                        $student_detail->overall_stars=$final_eval_stars;
                                                
                                        if($final_eval_stars >=1 && $final_eval_stars < 1.5)
                                        {
                                            $student_detail->overall_status='Weak';
                                        }
                                        else if($final_eval_stars >=1.5 && $final_eval_stars < 2.5)
                                        {
                                            $student_detail->overall_status='Fair';
                                        }
                                        else if($final_eval_stars >=2.5 && $final_eval_stars < 3.5)
                                        {
                                            $student_detail->overall_status='Good';
                                        }
                                        else if($final_eval_stars >=3.5 && $final_eval_stars < 4.5)
                                        {
                                            $student_detail->overall_status='Excellent';
                                        }
                                        else if($final_eval_stars >=4.5 && $final_eval_stars <= 5)
                                        {
                                            $student_detail->overall_status='Exceptional';
                                        }
                                        else
                                        {
                                            $student_detail->overall_status='';
                                        }
                                        
                                        
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'Evaluation fetch successfully!',
                                            'data' => $data,
                                            'student' => $student_detail
                                        );
                                        return response()->json($this->response, 200);
                                    }
                                    else
                                    {
                                        $this->response=array
                                        (
                                            'status' => 'success',
                                            'message' => 'No evaluation found!',
                                            'data' => ''
                                        );
                                        return response()->json($this->response, 200);
                                    }
                                    
                                }
                                else
                                {
                                    $evaluation_types=DB::table('evaluations')->select('*')->where('id',$request->input('evaluation_id'))->first();
                                    if($evaluation_types)
                                    {
                                        $record=array();
                                        if($evaluation_types->type=='subject')
                                        {
                                            $res=DB::table('evaluation_categories')->select('id as category_id', 'evaluation_id as id', 'category_name')->where('evaluation_categories.evaluation_id', $request->input('evaluation_id'))->get()->all();
                                            $total_eval_cat=sizeof($res);
                                            if(sizeof($res) > 0)
                                            {
                                                $sub_count=0;
                                                $sub_count_stars=0;
                                                $subject_stars_count=0;
                                                foreach($res as $key => $re)
                                                {
                                                    $res[$key]->type='subject';
                                                    
                                                    $sub_count=$sub_count+1;
                                                    $subrecord=DB::table('student_report')
                                                    ->join('subjects', 'student_report.subject_id', '=', 'subjects.id', 'left')
                                                    ->select('student_report.subject_id', 'subjects.name', 'student_report.stars as obtained_stars')
                                                    ->where('student_report.evaluation_id', $request->input('evaluation_id'))
                                                    ->where('student_report.category_id', $re->category_id)
                                                    ->where('student_report.student_id', $request->input('child_id'))
                                                    ->get()->all();
                                                    
                                                    $final_subject_array=array();
                                                    if(sizeof($subrecord) > 0)
                                                    {
                                                        foreach($subrecord as $s_key => $sub_recrd)
                                                        {
                                                            $subrecord[$s_key]->subject_id=(int) $sub_recrd->subject_id;
                                                            // $final_subject_array->id=$sub_recrd->subject_id;
                                                            // array_push($final_subject_array->id, $sub_recrd->subject_id);
                                                            $final_subject_array[] = (int) $sub_recrd->subject_id;
                                                        }
                                                        
                                                    }
                                                    
                                                    $get_different_subject=array_diff($allSubjects,$final_subject_array);
                                                    
                                                    
                                                    
                                                    $sizeofsubrecord=sizeof($subrecord);
                                                    
                                                    
                                                    if(sizeof($get_different_subject) > 0)
                                                    {
                                                        $get_different_subject=array_values($get_different_subject);
                                                        
                                                        foreach($get_different_subject as $diffsubkey => $getDiffSubj)
                                                        {
                                                            $dif_sub_det=DB::table('subjects')->select('id as subject_id', 'name')->where('id', $getDiffSubj)->where('academic_year_id', $academic_year->id)->whereNull('deleted_at')->first();
                                                            if($dif_sub_det)
                                                            {
                                                                $dif_sub_det->obtained_stars=0;
                                                                $subrecord[$sizeofsubrecord+$diffsubkey+1]=$dif_sub_det;
                                                            }
                                                        }
                                                    } 
                                                    
                                                    if(sizeof($subrecord) > 0)
                                                    {
                                                        $total_ob_stars=0;
                                                        $total_ob_stars_count=0;
                                                        
                                                        $subrecord=array_values($subrecord);
                                                        
                                                        foreach($subrecord as $fkey => $sub)
                                                        {
                                                            $subrecord[$fkey]->subject_id=(int) $sub->subject_id;
                                                            $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            
                                                            // if(sizeof($subrecord) == sizeof($allSubjects))
                                                            // {
                                                            //     $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //     $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            // }
                                                            // else
                                                            // {
                                                            //     foreach($allSubjects as $ckey => $a_subj)
                                                            //     {
                                                            //         if($a_subj->id==$sub->subject_id)
                                                            //         {
                                                            //             $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //             $total_ob_stars=$total_ob_stars+$sub->obtained_stars;
                                                            //         }
                                                            //         else
                                                            //         {
                                                            //             $total_ob_stars_count=$total_ob_stars_count+1;
                                                            //             $total_ob_stars=$total_ob_stars+0;
                                                            //         }
                                                            //     }
                                                            // }
                                                        }
                                                        $subrecord_final_sizeOf=sizeof($subrecord);
                                                        $total_ob_stars=round(($total_ob_stars/$subrecord_final_sizeOf) ,2);
                                                        // $total_ob_stars=ceil($total_ob_stars/$total_ob_stars_count);
                                                        $res[$key]->total_obtained_stars=$total_ob_stars;
                                                        
                                                        
                                                        $sub_count_stars=$sub_count_stars+$total_ob_stars;
                                                        
                                                        if($total_ob_stars==1)
                                                        {
                                                            $res[$key]->status='Weak';
                                                        }
                                                        else if($total_ob_stars==2)
                                                        {
                                                            $res[$key]->status='Fair';
                                                        }
                                                        else if($total_ob_stars==3)
                                                        {
                                                            $res[$key]->status='Good';
                                                        }
                                                        else if($total_ob_stars==4)
                                                        {
                                                            $res[$key]->status='Excellent';
                                                        }
                                                        else if($total_ob_stars==5)
                                                        {
                                                            $res[$key]->status='Exceptional';
                                                        }
                                                        else
                                                        {
                                                            $res[$key]->status='';
                                                        }
                                                        
                                                        $res[$key]->subjects=$subrecord;
                                                        
                                                        
                                                        
                                                    }
                                                    
                                                    
                                                }
                                                
                                            
                                                
                                                // echo $sub_count_stars. ' count '.$sub_count;die;
                                                $sub_count_stars=number_format($sub_count_stars/$total_eval_cat,2);
                                                
                                                $student_detail->overall_stars=$sub_count_stars;
                                                
                                                if($sub_count_stars >=1 && $sub_count_stars < 1.5)
                                                {
                                                    $student_detail->overall_status='Weak';
                                                }
                                                else if($sub_count_stars >=1.5 && $sub_count_stars < 2.5)
                                                {
                                                    $student_detail->overall_status='Fair';
                                                }
                                                else if($sub_count_stars >=2.5 && $sub_count_stars < 3.5)
                                                {
                                                    $student_detail->overall_status='Good';
                                                }
                                                else if($sub_count_stars >=3.5 && $sub_count_stars < 4.5)
                                                {
                                                    $student_detail->overall_status='Excellent';
                                                }
                                                else if($sub_count_stars >=4.5 && $sub_count_stars <= 5)
                                                {
                                                    $student_detail->overall_status='Exceptional';
                                                }
                                                else
                                                {
                                                    $student_detail->overall_status='';
                                                }
                                                
                                                
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation fetch successfully!',
                                                    'data' => $res,
                                                    'student' => $student_detail
                                                );
                                                return response()->json($this->response, 200);
                                                
                                            }
                                            else
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation category not found!',
                                                    'data' => ''
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            
                                        }
                                        else if($evaluation_types->type=='non-subject')
                                        {
                                            $record=DB::table('student_report')
                                            ->join('evaluation_categories', 'student_report.category_id', '=', 'evaluation_categories.id', 'left')
                                            ->select('student_report.evaluation_id as id', 'student_report.category_id', 'evaluation_categories.category_name', 'student_report.stars as obtained_stars')
                                            ->where('student_report.evaluation_id', $request->input('evaluation_id'))
                                            ->where('student_report.student_id', $child_id)
                                            ->whereNull('student_report.deleted_at')
                                            ->whereNull('evaluation_categories.deleted_at')
                                            ->get()->all();

                                            if(sizeof($record) > 0)
                                            {
                                                $non_sub_count=0;
                                                $non_sub_starts=0;
                                                foreach($record as $key => $rec)
                                                {
                                                    $record[$key]->type='non-subject';
                                                    
                                                    $non_sub_count=$non_sub_count+1;
                                                    $non_sub_starts=$non_sub_starts+$rec->obtained_stars;
                                                    $record[$key]->total_obtained_stars=$rec->obtained_stars;
                                                    
                                                    if($rec->obtained_stars==1)
                                                    {
                                                        $record[$key]->status='Weak';
                                                    }
                                                    else if($rec->obtained_stars==2)
                                                    {
                                                        $record[$key]->status='Fair';
                                                    }
                                                    else if($rec->obtained_stars==3)
                                                    {
                                                        $record[$key]->status='Good';
                                                    }
                                                    else if($rec->obtained_stars==4)
                                                    {
                                                        $record[$key]->status='Excellent';
                                                    }
                                                    else if($rec->obtained_stars==5)
                                                    {
                                                        $record[$key]->status='Exceptional';
                                                    }
                                                    else
                                                    {
                                                        $record[$key]->status='';
                                                    }
                                                    
                                                }
                                                
                                                
                                                
                                                
                                                $non_sub_starts=number_format(ceil($non_sub_starts/$non_sub_count),2);
                                                $student_detail->overall_stars=$non_sub_starts;
                                                
                                                if($non_sub_starts >=1 && $non_sub_starts < 2) 
                                                {
                                                    $student_detail->overall_status='Weak';
                                                }
                                                else if($non_sub_starts >=2 && $non_sub_starts < 3)
                                                {
                                                    $student_detail->overall_status='Fair';
                                                }
                                                else if($non_sub_starts >=3 && $non_sub_starts < 4)
                                                {
                                                    $student_detail->overall_status='Good';
                                                }
                                                else if($non_sub_starts >=4 && $non_sub_starts < 5)
                                                {
                                                    $student_detail->overall_status='Excellent';
                                                }
                                                else if($non_sub_starts >=5)
                                                {
                                                    $student_detail->overall_status='Exceptional';
                                                }
                                                else
                                                {
                                                    $student_detail->overall_status='';
                                                }
                                                
                                                
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation fetch successfully!',
                                                    'data' => $record,
                                                    'student' => $student_detail
                                                );
                                                return response()->json($this->response, 200);
                                            }
                                            else
                                            {
                                                $this->response=array
                                                (
                                                    'status' => 'success',
                                                    'message' => 'Evaluation category not found!',
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
                                                'message' => 'Invalid evaluation type!',
                                                'data' => ''
                                            );
                                            return response()->json($this->response, 200);
                                        }
                                        
                                    }
                                    else
                                    {
                                        
                                    }
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
                            'message' => 'Evaluation Id is required!',
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
