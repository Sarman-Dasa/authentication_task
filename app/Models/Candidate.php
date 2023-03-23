<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = ['first_name' , 'last_name' ,'email' ,'phone' ,'resume' ,'job_id'];

    /**
     * Candidate-Job Relation
     */
    public function job()
    {
        return $this->belongsTo(Job::class,'job_id','id')->with('company');
    }
}
