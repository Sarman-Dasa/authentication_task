<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    use ListingApiTrait;
    /**
     * Display a listing of the candidate.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $this->ListingValidation();

        $query = Candidate::query();

        $searchable_fields = ['first_name' ,'last_name' ,'phone' ,'email'];

        $data = $this->filterSearchPagination($query ,$searchable_fields);

        return ok('Data',[
            'Candidate List' => $data['query']->get(),
            'No Of Candidate'=> $data['count'],
        ]);
    }

    /**
     * Store a newly created candidate in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'first_name'        =>  'required|string|min:3|max:30',
            'last_name'         =>  'required|string|min:3|max:30',
            'email'             =>  'required|email|unique:candidates,email',
            'phone'             =>  'required|unique:candidates,phone',
            'resume'            =>  'required|mimes:pdf',
            'job_id'            =>  'required|exists:jobs,id'
        ],[
            'job_id.exists' =>  'job does not found!!!',
        ]);

        $resume = $request->file('resume');
        
        $resumeName = $resume->getClientOriginalName();
    
        $resume->move(public_path(). '/storage/resumes/' ,$resumeName);
        
        $candidate = Candidate::create($request->only(['first_name' , 'last_name' ,'email' ,'phone' ,'job_id'])
        +[
            'resume'    =>   '/storage/resumes/'.$resumeName,
        ]);

        return ok('Your Details Submited Successfully');
    }

    /**
     * Update the specified candidate in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

     /**
     * Display the specified candidate.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $candidate = Candidate::findOrFail($id);
        return ok('candidate data' ,$candidate);
    }

    /**
     * Remove the specified candidate from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
