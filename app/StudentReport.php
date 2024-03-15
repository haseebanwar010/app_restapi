<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentReport extends Model
{
    protected $table='student_report';
    
    protected $fillable = [
        'class_id', 'batch_id', 'subject_id', 'student_id', 'school_id', 'evaluation_id', 'category_id', 'stars'
    ];    
    
}
