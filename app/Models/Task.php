<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['title' ,'description' ,'employee_id'];

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
     * Task-Employee Relation
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    public function company()
    {
        return $this->hasOneThrough(Company::class,Employee::class,'company_id','id','employee_id','id');
    }

}
