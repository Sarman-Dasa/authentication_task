<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = ['name' ,'email' ,'website' ,'logo'];

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
     * Company-Employee Relation
     */
    public function employees()
    {
        return $this->hasMany(Employee::class,'company_id','id');
    }

    /**
     * Company-Job Relationship
     */
    public function jobs()
    {
        return $this->hasMany(Job::class,'company_id','id');
    }

    /**
     * hasoneth
     * Company-Task Relation
     */
     public function tasks()
     {
        return $this->hasManyThrough(Task::class,Employee::class,'company_id','employee_id');
     }
}
