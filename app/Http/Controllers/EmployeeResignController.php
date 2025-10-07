<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Datatables;
use DB;
use Session;

class EmployeeResignController extends Controller
{
    public function employee_resign_report()
    {
        $permission = Auth::user()->can('employee-resign-report');
        if (!$permission) {
            abort(403);
        }

        $departments=DB::table('departments')->select('*')->get();
        return view('Report.employee_resign_report',compact('departments'));
    }

    
    public function get_resign_employees(Request $request)
    {
        $companyId = Session::get('company_id');
        $companyBranchId = Session::get('company_branch_id');
        $department = $request->input('department');

        if($department==''){
            $types = DB::table('employees')
            ->leftjoin('departments', 'departments.id', '=', 'employees.emp_department')
            ->leftjoin('job_titles', 'employees.emp_job_code', '=', 'job_titles.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select(
                'employees.*',
                'departments.name AS department_name',
                'job_titles.title AS title',
                'branches.location AS location')
            ->where('employees.deleted', '0')
            ->where('employees.is_resigned', 1)
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_location', $companyBranchId)
            ->get();

        }else if($department!='All'){
            $types = DB::table('employees')
            ->leftjoin('departments', 'departments.id', '=', 'employees.emp_department')
            ->leftjoin('job_titles', 'employees.emp_job_code', '=', 'job_titles.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select(
                'employees.*',
                'departments.name AS department_name',
                'job_titles.title AS title',
                'branches.location AS location'
            )
            ->where('employees.deleted', '0')
            ->where('employees.is_resigned', 1)
            ->where('employees.emp_department', $department)
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_location', $companyBranchId)
            ->get();
        }


            return Datatables::of($types)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
            })
            ->rawColumns(['action'])
            ->make(true);

            // return response() ->json(['result'=>  $types]);
    }
}
