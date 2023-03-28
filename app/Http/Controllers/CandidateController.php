<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\JobConfirmationMail;
use App\Notifications\WelcomeMail;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $searchable_fields = ['first_name', 'last_name', 'phone', 'email'];

        $data = $this->filterSearchPagination($query, $searchable_fields);

        return ok('Data', [
            'candidates'    => $data['query']->get(),
            'count'         => $data['count'],
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
        ], [
            'job_id.exists' =>  'job does not found!!!',
        ]);

        $resume = $request->file('resume');

        $resumeName = $resume->getClientOriginalName();

        $resume->move(public_path() . '/storage/resumes/', $resumeName);

        $candidate = Candidate::create($request->only(['first_name', 'last_name', 'email', 'phone', 'job_id'])
            + [
                'resume'    =>   '/storage/resumes/' . $resumeName,
            ]);

        return ok('Your details submited successfully');
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
        $request->validate([
            'status' => 'required|in:Accept,Reject',
        ]);

        $candidate = Candidate::with('job')->findOrFail($id);
        
        if ($candidate->status != "Accept" && $request->status == 'Accept') 
        {
            $username       = strtolower($candidate->first_name . $candidate->last_name[0]);
            $companyName    =   $candidate->job->company->name;
            $jobType        = $candidate->job->type;

            $email = $username . '@' . explode(" ", $companyName)[0] . '.com';
            
            $user  = User::create([

                'name'              =>  $username,
                'email'             =>  $email,
                'role'              =>  'Employee',
                'email_verified_at' =>  now(),
                'is_active'         =>  true,
                'password'          =>  Hash::make($username),
            ]);

            $employee = Employee::create($candidate->only(['first_name', 'last_name', 'email', 'phone'])
            +[
                'joining_date'  =>  now()->addDays(2),
                'company_id'    =>  $candidate->job->company_id,
                'user_id'       =>  $user->id,
                'role'          =>  'Employee',
            ]);

            $employee['company_name']   =  $companyName;
            $employee['job_type']       =  $jobType;
            $employee->notify(new JobConfirmationMail($employee));
            return ok('Mail send successfully.');
        }
        else{
            return ok('Candidated Already seleted');
        }
        $candidate->update($request->only('status'));

    }

    /**
     * Display the specified candidate.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $candidate = Candidate::with('job')->findOrFail($id);
        // $candidate = Candidate::select('candidates.*','jobs.*','companies.*')
        // ->join('jobs','jobs.id','=','candidates.job_id')
        // ->join('companies','companies.id','=','jobs.company_id')
        // ->where('candidates.id',$id)
        // ->where('companies.user_id',auth()->user()->id)->get();

        return ok('candidate data', $candidate);
    }

    /**
     * Remove the specified candidate from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $candidate = Candidate::with('job')->findOrFail($id);
        $userId  = $candidate->job->company->user_id;

        if ($userId == auth()->user()->id) 
        {
            $candidate->delete();
            return ok('Candidated data deleted.');
        } 
        else
            return ok('No record found!!!');
    }
}
