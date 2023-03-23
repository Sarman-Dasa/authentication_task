<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['type' ,'vacancy' ,'company_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * One-To-Many 
     * Job-Candidated Relation
     */
    public function candidates()
    {
        return $this->hasMany(Candidate::class,'job_id','id');
    }

    /**
     * Job-Company Relation
     */
    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
