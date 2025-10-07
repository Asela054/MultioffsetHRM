<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\JobCategory;

class JobCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('company-list');

        if(!$permission) {
            abort(403);
        }

        $jobcategory = JobCategory::orderBy('id', 'asc')->get();
        return view('Organization.jobCategory', compact('jobcategory'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $permission = $user->can('company-create');


        $jobcategory = new JobCategory;
        $jobcategory->category = $request->input('category');
        $jobcategory->annual_leaves = $request->input('annual_leaves');
        $jobcategory->casual_leaves = $request->input('casual_leaves');
        $jobcategory->medical_leaves = $request->input('medical_leaves');
        //$jobcategory->otdeduct = $request->input('otdeduct');
        //$jobcategory->nopaydeduct = $request->input('nopaydeduct');

        $jobcategory->emp_payroll_workdays = $request->input('emp_payroll_workdays');
        $jobcategory->emp_payroll_workhrs = $request->input('emp_payroll_workhrs');

//        if($request->input('short_leave_enabled') !== null){
//            $jobcategory->short_leave_enabled = 1;
//        }else{
//            $jobcategory->short_leave_enabled = 0;
//        }

        $jobcategory->is_sat_ot_type_as_act = $request->input('is_sat_ot_type_as_act');
        $jobcategory->is_sat_ot_type_as_act = $request->input('custom_saturday_ot_type');

        $jobcategory->save();

        return response()->json(['success' => 'Job Category Added successfully.']);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $permission = $user->can('company-edit');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (request()->ajax()) {
            $data = JobCategory::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    public function update(Request $request, JobCategory $jobcategory)
    {
        $user = auth()->user();
        $permission = $user->can('company-edit');

        $form_data = array(
            'category' => $request->category,
            'annual_leaves' => $request->annual_leaves,
            'casual_leaves' => $request->casual_leaves,
            'medical_leaves' => $request->medical_leaves,
            'emp_payroll_workdays' => $request->emp_payroll_workdays,
            'emp_payroll_workhrs' => $request->emp_payroll_workhrs,
            'is_sat_ot_type_as_act' => $request->is_sat_ot_type_as_act,
            'custom_saturday_ot_type' => $request->custom_saturday_ot_type,
        );

        JobCategory::whereId($request->hidden_id)->update($form_data);

        return response()->json(['success' => 'Job Category is successfully updated']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('company-delete');

        $data = JobCategory::findOrFail($id);
        $data->delete();
    }

}

?>