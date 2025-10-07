<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Session;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->get();
        return view('users.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }


    public function create()
    {
        $companyId = Session::get('company_id');
        $departments = Department::where('company_id',$companyId)->orderBy('id', 'asc')->get();
        $roles = Role::pluck('name','name')->all();
        return view('users.create',compact('roles','departments'));
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required',
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        $selectedDepartments = $request->input('department');
        $user->departments()->sync($selectedDepartments);

        return redirect()->route('users.index')
                        ->with('success','User created successfully');
    }


    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }


    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();

        $companyId = Session::get('company_id');
        $allocate_departments = DB::table('user_has_departments')
        ->leftJoin('departments', 'user_has_departments.department_id', '=', 'departments.id')
        ->where('user_has_departments.user_id', $id)
        ->orderBy('user_has_departments.department_id', 'asc')
        ->select('departments.*') 
        ->get();

        $departments = Department::where('company_id',$companyId)->orderBy('id', 'asc')->get();

        return view('users.edit',compact('user','roles','userRole','departments','allocate_departments'));
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required',
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));
        }

        $user = User::find($id);
        $user->update($input);

        //remove roles from user
        $user->roles()->detach();
        $user->assignRole($request->input('roles'));

        $selectedDepartments = $request->input('department');
        $user->departments()->sync($selectedDepartments);

        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
    }
}
