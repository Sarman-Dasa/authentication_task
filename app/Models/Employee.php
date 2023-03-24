<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['first_name' , 'last_name' ,'email' ,'phone' ,'joining_date' ,'company_id' ,'user_id'];

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
     * Employee-Task Relation
     */
    public function tasks()
    {
        return $this->hasMany(Task::class,'employee_id','id');
    }

    /**
     * Employee-Company Relation
     */
    public function company()
    {
        return $this->belongsTo(company::class,'company_id','id');
    }
}
