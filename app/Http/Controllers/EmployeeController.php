<?php

namespace App\Http\Controllers;

use App\Exports\ExportEmployee;
use App\Imports\EmployeesImport;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the Employees.
     *
     * @return json response
     */
    public function list(Request $request)
    {
        $this->ListingValidation();

        $query = Employee::query();
        $searchable_fields = ['first_name','last_name','email','email','phone','joining_date'];
        
        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('Data',[
            'employees' =>  $data['query']->get(),
            'count'     =>  $data['count'],    
        ]);
    }

    /**
     * Store a newly created Employee in Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'first_name'        =>  'required|string|min:3|max:30',
            'last_name'         =>  'required|string|min:3|max:30',
            'email'             =>  'required|email|unique:employees,email',
            'phone'             =>  'required|unique:employees,phone',
            'joining_date'      =>  'required|date_format:Y-m-d|before_or_equal:'.now(),
            'company_id'        =>  'required|exists:companies,id',
        ],[
            'company_id.exists' =>  'Company does not found!!!',
        ]);

        $company = Company::where('id',$request->company_id)->first();

        if(auth()->user()->id == $company->user_id)
        {
            $username = strtolower($request->first_name . $request->last_name[0]);
            $email = $username .'@'. explode(" " ,$company->name)[0] .'.com';
            $user  = User::create([
                'name'              =>  $username,
                'email'             =>  $email,
                'role'              =>  'Employee',
                'email_verified_at' =>  now(),
                'is_active'         =>  true,
                'password'          =>  Hash::make($username),
            ]);

            $employee = Employee::create($request->only(['first_name' , 'last_name' ,'email' ,'phone' ,'joining_date' ,'company_id'])
            +[
                'user_id'   =>  $user->id,
            ]);
           
            return ok('Employee data added successfully');
        }


        return error('unauthenticated',[],'unauthenticated');
       
    }

    /**
     * Update the specified Employee in Database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return json Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name'        =>  'required|string|min:3|max:30',
            'last_name'         =>  'required|string|min:3|max:30',
            'email'             =>  'required|email|unique:employees,email,'.$id.',id',
            'phone'             =>  'required|unique:employees,phone,'.$id.',id',
            'joining_date'      =>  'required|date_format:Y-m-d|before_or_equal:'.now(),
            'company_id'        =>  'required|exists:companies,id',
        ],[
            'company_id.exists' =>  'Company does not found!!!',
        ]);

        $employee = Employee::findOrFail($id); 
        $employee->update($request->only(['first_name' , 'last_name' ,'email' ,'phone' ,'joining_date' ,'company_id']));

        return ok('Employee data updated successfully');
    }

    /**
     * Display the specified Employee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $employee = Employee::with('company','tasks')->withCount('tasks AS no_of_task')->findOrFail($id);

        return ok('Employee data',$employee);
    }

    /**
     * soft Remove the specified Employee from Database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return ok('Employee data deleted successfully');
    }

    /**
     * force Remove the specified Employee from Database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->forceDelete();

        return ok('Employee data permanent deleted successfully');
    }

     /**
     * Restore Removed the specified Employee from Database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restoreDeletedEmployee($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->restore();

        return ok('Employee data restore successfully');
    }

    public function export(Request $request) 
    {
        return Excel::download(new ExportEmployee($request->start_date,$request->end_date), 'employee.csv');
    }
    
    public function import(Request $request)
    {
        Excel::import(new EmployeesImport,$request->file('file'));

        return ok('Data imported successfully');
    }

}
