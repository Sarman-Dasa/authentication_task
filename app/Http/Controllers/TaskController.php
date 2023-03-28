<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Employee;
use App\Models\Task;
use App\Traits\ListingApiTrait;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the task.
     *
     * @return json Response 
     */
    public function list(Request $request)
    {
        $request->validate([
            'search'    =>  'min:3',
        ]);

        $this->ListingValidation();

        $query = Task::query();
        $searchable_fields = ['title','description'];

        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('Data',[
            'tasks' => $data['query']->get(),
            'count' => $data['count'],
        ]);
    }

    /**
     * Store a newly created task in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'title'         =>  'required|string|min:8|max:100',
            'description'   =>  'required|min:10|max:150',
            'employee_id'   =>  'required|numeric|exists:employees,id',
        ]);

        $task = Task::create($request->only(['title' ,'description' ,'employee_id']));

        return ok('Task added successfully');
    }

    /**
     * Update the specified task in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $task = Task::with('employee')->findOrFail($id);
        $message = 'Task updated successfully';
        if(auth()->user()->role == 'Admin')
        {
            $request->validate([
                'title'         =>  'required|string|min:8|max:100',
                'description'   =>  'required|min:10|max:150',
                'employee_id'   =>  'required|numeric|exists:employees,id',
            ]);
            $task->update($request->only(['title' ,'description' ,'employee_id']));
            return ok($message);
        }
        
        $request->validate([
            'status'    =>  'required|in:Completed,Pending,Inprogress',
        ]);
        
        if($task->employee->user_id == auth()->user()->id){
            $task->update($request->only('status'));
        }
        else{
            return ok('Does not have any task.');
        }
        return ok($message);
    }

     /**
     * Display the specified task.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $task = Task::with('employee')->findOrFail($id);
        
        $user =  auth()->user();
        if($task->employee->user_id == $user->id || $user->role == "Admin"){
            return ok('Task data',$task);
        }
        else{
            return ok('Does not have any task.');
        }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return ok('Task data deleted successfuly');
    }

     /**
     * force Remove the specified task from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->forceDelete();
        
        return ok('Task data permanent deleted successfuly');
    }

    /**
     * Restore removed the specified task from Database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restoreDeletedTask($id)
    {
        $task = Task::onlyTrashed()->findOrFail($id);
        $task->restore();
        
        return ok('Task data restore successfuly');
    }
}
