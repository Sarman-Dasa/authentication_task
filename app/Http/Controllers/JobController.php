<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Job;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the job.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $this->ListingValidation();

        $query = Job::query();

        $searchable_fields = ['type'];
        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('Data',[
            'Job List'  =>  $data['query']->get(),
            'No Of Jobs'=>  $data['count'],
        ]);
    }

    /**
     * Store a newly created joon in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'type'      =>  'required|min:1|max:150|unique:jobs,type',
            'vacancy'   =>  'required|numeric|min:1|max_digits:3',
            'company_id'=>  'required|exists:companies,id',
        ]);

        $company = Company::where('id',$request->company_id)->first();
        // dd($company);
        if(auth()->user()->id == $company->user_id){
            $job = Job::create($request->only(['type' ,'vacancy' ,'company_id']));
            return ok('Job Data Added Successfully');
        }
        
        return ok('Company Does Not Exists');
    }

    /**
     * Update the specified jon in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'type'      =>  'required|min:1|max:150|unique:jobs,type,'.$id.',id',
            'vacancy'   =>  'required|numeric|min:1|max_digits:3',
            'company_id'=>  'required|exists:companies,id',
        ]);

        $job = Job::findOrFail($id);
        $job->update($request->only(['type' ,'vacancy' ,'company_id']));

        return ok('Job Data Updated Successfully');
    }

     /**
     * Display the specified job.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $job = Job::with('candidates','company')->findOrFail($id);
        return ok('Job Data',$job);
    }

    /**
     * Remove the specified job from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return ok('Job Data Deleted Successfully');
    }

    /**
     * force Remove the specified job from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function forceDelete($id)
    {
        $task = Job::onlyTrashed()->findOrFail($id);
        $task->forceDelete();
        
        return ok('Job Data Permanent Deleted Successfuly');
    }

    /**
     * Restore removed the specified job from Database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restoreDeletedJob($id)
    {
        $task = job::onlyTrashed()->findOrFail($id);
        $task->restore();
        
        return ok('Job Data Restore Successfuly');
    }

}
