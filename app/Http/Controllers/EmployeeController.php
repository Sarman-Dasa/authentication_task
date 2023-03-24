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
            'Employee List' =>  $data['query']->get(),
            'No Of Employee'=>  $data['count'],    
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
        //dd($company);
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
           
            return ok('Employee Data Added Successfully');
        }


        return error('unauthenticated',type:'unauthenticated');
       
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

        return ok('Employee Data Updated Successfully');
    }

    /**
     * Display the specified Employee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $employee = Employee::with('company','tasks')->withCount('tasks AS NO-OF-TASK')->findOrFail($id);

        return ok('Employee Data',$employee);
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

        return ok('Employee Data Deleted Successfully');
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

        return ok('Employee Data Permanent Deleted Successfully');
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

        return ok('Employee Data Restore  Successfully');
    }

    public function export(Request $request) 
    {
        return Excel::download(new ExportEmployee($request->start_date,$request->end_date), 'employee.csv');
        // $data = (new ExportEmployee($request->start_date,$request->end_date))->download('employee.csv',Excel::CSV);
         //return ($data);
        // return (new InvoicesExport(2018))->download('invoices.xlsx');

    }
    
    public function import(Request $request)
    {
        Excel::import(new EmployeesImport,$request->file('file'));
    }

}
