<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
| 
*/    

//////////////////////////////////////Default App Apis////////////////////////////////////////
Route::post('login', 'API\AuthController@login'); 
Route::post('forgot_password', 'API\AuthController@forgot_password');
Route::post('reset_password', 'API\AuthController@reset_password');

Route::get('send', 'API\PusherNotificationController@notification');

Route::post('whatsapp_msgs', 'API\PusherNotificationController@whatsapp_msgs');

Route::post('notification_status', 'API\NotificationController@notification_status');

Route::get('verify_permission', 'API\PermissionController@verify_permission');

//////////////////////////////////////Student App APIS////////////////////////////////////////
Route::post('get_announcements', 'API\DashboardController@get_announcements'); //Generic API use for 2,3,4 roles. // Also handle both child & parent
Route::post('get_notifications', 'API\NotificationController@get_notifications');
Route::post('student_studymaterial', 'API\StudymaterialController@student_studymaterial');
Route::post('student_studymaterialDates', 'API\StudymaterialController@student_studymaterialDates');
Route::post('studyMaterial_detail', 'API\StudymaterialController@studyMaterial_detail'); // for all
Route::post('student_timetable', 'API\TimetableController@student_timetable');
Route::post('student_classactivity', 'API\ClassworkController@student_class_activity');
Route::post('student_classactivity_dates', 'API\ClassworkController@student_classactivity_dates');
Route::post('student_class_activity_subjects', 'API\ClassworkController@student_class_activity_subjects');

Route::post('detail_classactivity', 'API\ClassworkController@detail_classactivity');//detail
Route::post('submit_classactivity', 'API\ClassworkController@submit_classactivity');//submit assignment/homework
Route::post('comments_classactivity', 'API\ClassworkController@comments_classactivity');//comments assignment/homework
Route::post('get_comments', 'API\ClassworkController@get_comments_classactivity');//allcomments assignment/homework
Route::post('student_exams', 'API\ExamsController@student_exams');
Route::post('student_marksheet', 'API\ExamsController@student_marksheet');
Route::post('student_attendance', 'API\AttendanceController@student_attendance');
Route::post('online_classes', 'API\OnlineclassesController@get_online_classes');//for student & parent
Route::post('get_events', 'API\EventsController@get_events');
Route::post('inbox_conversations', 'API\MessagesController@inbox_conversations');
Route::post('conversation_detail', 'API\MessagesController@inbox_conversation_detail');
Route::post('send_message', 'API\MessagesController@send_message');
Route::post('sent_conversations', 'API\MessagesController@sent_conversations');

Route::get('get_roles', 'API\MessagesController@get_roles');
Route::get('get_roleDetails', 'API\MessagesController@get_roleDetails');
Route::post('create_conversation', 'API\MessagesController@create_conversation');

Route::get('get_feeRecord', 'API\FeeController@get_feeRecord');




//////////////////////////////////////Parents App APIS////////////////////////////////////////
Route::post('get_childs', 'API\ParentsController@get_childs');
Route::post('update_parentpic', 'API\ParentsController@update_parentpic');
Route::post('parent_studymaterial', 'API\StudymaterialController@parent_studymaterial');
Route::post('students_classactivities', 'API\ParentsController@students_classactivities');
Route::post('get_childAnnouncement', 'API\DashboardController@get_childAnnouncement');
Route::post('get_allNotifications', 'API\NotificationController@get_allNotifications');
Route::get('get_notificationStatus', 'API\NotificationController@get_notificationStatus');
Route::post('all_studentsAttendance', 'API\AttendanceController@all_studentsAttendance');
Route::post('all_studentsTimetable', 'API\TimetableController@all_studentsTimetable');
//study plan start
Route::post('get_subjects', 'API\StudyplanController@get_subjects'); //both for student & parent
Route::post('get_studyplan', 'API\StudyplanController@get_studyplan'); //both for student & parent
Route::post('get_studyplan_subject', 'API\StudyplanController@get_studyplan_subject'); //both for student & parent
Route::post('get_homestudyplan', 'API\StudyplanController@get_homestudyplan'); //both for student
//study plan end

Route::post('get_books', 'API\BooksController@get_books'); //both for student & parent
Route::post('get_bookDetail', 'API\BooksController@get_bookDetail'); //both for student & parent


//Evaluation
Route::get('evaluation_types', 'API\EvaluationController@evaluation_types');
Route::get('evaluation_record', 'API\EvaluationController@evaluation_record');



//////////////////////////////////////Teacher App APIS////////////////////////////////////////
Route::get('get_classes', 'API\TeacherController@get_classes');
Route::get('get_batches', 'API\TeacherController@get_batches');
Route::get('get_students', 'API\ClassworkController@get_students');
Route::get('get_subjects', 'API\ClassworkController@get_subjects');
Route::post('create_classActivity', 'API\ClassworkController@create_classActivity');
Route::post('create_studyMaterial', 'API\ClassworkController@create_studyMaterial');
Route::post('update_studyMaterial', 'API\ClassworkController@update_studyMaterial');
Route::post('delete_studyMaterial', 'API\ClassworkController@delete_studyMaterial');
Route::post('teacher_studymaterial', 'API\StudymaterialController@teacher_studymaterial');
Route::post('teacher_classActivity', 'API\ClassworkController@teacher_classActivity');
Route::post('teacher_onlineClasses', 'API\OnlineclassesController@get_teacherOnlineclasses');
Route::post('create_onlineclass', 'API\OnlineclassesController@create_onlineclass');
Route::post('end_onlineclass', 'API\OnlineclassesController@end_onlineclass');
Route::post('teacher_timetable', 'API\TimetableController@teacher_timetable');
Route::post('teacher_exams', 'API\ExamsController@teacher_exams');



Route::post('create_exam', 'API\ExamsController@create_exam');
Route::post('delete_exam', 'API\ExamsController@delete_exam');
Route::post('update_exam', 'API\ExamsController@update_exam');


//Teacher Details
Route::get('get_employeeimage', 'API\TeacherController@get_employeeimage');
Route::get('get_employeedetails', 'API\TeacherController@get_employeedetails');
Route::get('get_empcountries', 'API\TeacherController@get_empcountries');
Route::get('get_empdesignation', 'API\TeacherController@get_empdesignation');
Route::get('get_empdepartment', 'API\TeacherController@get_empdepartment');
Route::post('update_employeedetails', 'API\TeacherController@update_employeedetails');
Route::post('update_employeeimg', 'API\TeacherController@update_employeeimg');


Route::get('get_booksActivity', 'API\ExamsController@get_booksActivity');
Route::post('create_examActivity', 'API\ExamsController@create_examActivity');
Route::post('update_examActivity', 'API\ExamsController@update_examActivity');
Route::post('delete_examActivity', 'API\ExamsController@delete_examActivity');


Route::post('get_teacherStudyplan', 'API\StudyplanController@get_teacherStudyplan');


//Evaluation
Route::post('create_evaluationTerm', 'API\EvaluationController@create_evaluationTerm');
Route::post('update_evaluationTerm', 'API\EvaluationController@update_evaluationTerm');
Route::post('delete_evaluationTerm', 'API\EvaluationController@delete_evaluationTerm');


Route::get('get_evaluateType', 'API\EvaluationController@get_evaluateType');
Route::get('get_studentNonEvaluation', 'API\EvaluationController@get_studentNonEvaluation');
Route::get('get_studentEvaluation', 'API\EvaluationController@get_studentEvaluation');
Route::get('get_studentEvaluationCat', 'API\EvaluationController@get_studentEvaluationCat');
Route::post('evaluateStudent', 'API\EvaluationController@evaluateStudent');


Route::get('get_evaluationTypes', 'API\EvaluationController@get_evaluationTypes');
Route::post('create_evaluationCat', 'API\EvaluationController@create_evaluationCat');
Route::post('update_evaluationCat', 'API\EvaluationController@update_evaluationCat');
Route::post('delete_evaluationCat', 'API\EvaluationController@delete_evaluationCat');


Route::get('get_evaluationTerms', 'API\EvaluationController@get_evaluationTerms');
Route::post('create_evaluationType', 'API\EvaluationController@create_evaluationType');
Route::post('update_evaluationType', 'API\EvaluationController@update_evaluationType');
Route::post('delete_evaluationType', 'API\EvaluationController@delete_evaluationType');

 
 
//Appointment
Route::get('get_teachers', 'API\AppointmentController@get_teachers');
Route::post('create_appointment', 'API\AppointmentController@create_appointment');
Route::post('update_appointment', 'API\AppointmentController@update_appointment');
Route::get('get_appointments', 'API\AppointmentController@get_appointments');
Route::get('approved_appointments', 'API\AppointmentController@approved_appointments');
Route::get('refused_appointments', 'API\AppointmentController@refused_appointments');
Route::get('pending_appointments', 'API\AppointmentController@pending_appointments');















Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('config:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('route:clear');
    $exitCode = Artisan::call('config:cache');
    // return what you want
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
