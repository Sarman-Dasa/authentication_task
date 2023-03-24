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
        $query->where('user_id','=',auth()->user()->id);
        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('Company Data',[
            'Company List'  =>  $data['query']->get(),
            'count' =>  $data['count'],
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
        
        $company = Company::create($request->only(['name' ,'email' ,'website'])
        +[
            'logo'  =>  '/storage/logo/'.$logoName,
        ]);

        return ok("Company Data Added Successfully" ,$company);
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
        $userId = auth()->user()->id;
        $company->update($request->only(['name' ,'email' ,'website'])
        +[
            'logo'      =>  $logoPath,
            'user_id'   =>  $userId,
        ]);

        return ok('Company Data Updated Successfully');
    }

    public function get($id)
    {

        $company = Company::with('employees','jobs','tasks')->withCount('employees AS NO-OF-EMPLOYEES')->findOrFail($id);
        
        return ok('Company Data',$company);
    }

    public function destroy($id)
    {

        $company = Company::findOrFail($id);
        $company->delete();

        return ok('Company Data Deleted Successfully');

    }

    public function forceDelete($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->forceDelete();

        return ok('Company Data Permanent Deleted Successfully');
    }

    public function restoreDeletedCompany($id)
    {
        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        return ok('Company Data Restore Successfully');
    }
}
