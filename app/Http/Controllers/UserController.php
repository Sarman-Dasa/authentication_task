<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ListingApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ListingApiTrait;

    public function list(Request $request)
    {
        $this->ListingValidation();
        
        $query = User::query();
        $searchable_fields = ['name','email']; 
        $data = $this->filterSearchPagination($query,$searchable_fields);

        return ok('User List',[
            'users' =>  $data['query']->get(),
            'count' =>  $data['count'],
        ]);
    }

    public function get($id)
    {
        $id = $id ?? auth()->user();
        $user = User::findOrFail($id);
        return ok('User Profile',$user);
    }

    public function logout()
    {
        $user = auth()->user()->tokens();
        $user->delete();
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      =>  'required|current_password',
            'password'              =>  'required|min:8|max:12',
            'password_confirmation' =>  'required|same:password', 
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return ok('password Change Successfully');
    }
}
