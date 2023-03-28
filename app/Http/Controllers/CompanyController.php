<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ListingApiTrait;

    public function list(Request $request)
    {

        $this->ListingValidation();

        $query = Company::query();
        $searchable_fields = ['name','email'];
        $query->where('user_id',auth()->user()->id);
        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('Company data',[
            'companies' =>  $data['query']->get(),
            'count'     =>  $data['count'],
        ]);
    }

    public function create(Request $request)
    {

        $request->validate([
            'name'      =>  'required|min:10|max:150|unique:companies,name',
            'email'     =>  'required|email|min:15|max:100|unique:companies,email',
            'logo'      =>  'required|image|mimes:png,jpg|dimensions:min_width=100,min_height=100',
            'website'   =>  'required|url|min:15|max:150',
        ]);

        $logo = $request->file('logo');
        
        $extenstion  = $logo->getClientOriginalExtension();
        $logoName = explode(" " ,$request->name)[0] . "." .$extenstion;
        $logo->move(public_path(). '/storage/logo/' ,$logoName);
        
        $userId = auth()->user()->id;
        $company = Company::create($request->only(['name' ,'email' ,'website'])
        +[
            'logo'      =>  '/storage/logo/'.$logoName,
            'user_id'   =>  $userId,
        ]);

        return ok("Company data added successfully" ,$company);
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'name'      =>  'required|min:10|max:150|unique:companies,name,'.$id.',id',
            'email'     =>  'required|email|min:15|max:100|unique:companies,email,'.$id.',id',
            'logo'      =>  'image|mimes:png,jpg|dimensions:min_width=100,min_height=100',
            'website'   =>  'required|url|min:15|max:150',
        ]);

        $company = Company::where('user_id',auth()->user()->id)->findOrFail($id);
        $logoPath = $company->logo;
        if($request->hasFile('logo'))
        {
            unlink(public_path().$company->logo);
            $logo = $request->file('logo');
            $extenstion  = $logo->getClientOriginalExtension();
            $logoName = explode(" ",$request->name)[0] . "." .$extenstion;
            $logo->move(public_path(). '/storage/logo/',$logoName);
            $logoPath = '/storage/logo/'.$logoName;
        }
      
        $company->update($request->only(['name' ,'email' ,'website'])
        +[
            'logo'      =>  $logoPath,
        ]);

        return ok('Company data updated successfully');
    }

    public function get($id)
    {

        $company = Company::with('employees','jobs','tasks')->withCount('employees AS no_of_employees')->findOrFail($id);
        
        return ok('Company data',$company);
    }

    public function destroy($id)
    {

        $company = Company::findOrFail($id);
        $company->delete();
        return ok('Company data deleted successfully');

    }

    public function forceDelete($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->forceDelete();
        unlink(public_path().$company->logo);
        return ok('Company data permanent deleted successfully');
    }

    public function restoreDeletedCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return ok('Company data restore successfully');
    }
}
