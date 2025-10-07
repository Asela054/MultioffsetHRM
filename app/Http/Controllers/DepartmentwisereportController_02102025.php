<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Session;

class DepartmentwisereportController extends Controller
{
    public function index()
    {
        $company = Session::get('company_id');
        $permission = Auth::user()->can('department-wise-ot-report');
        if (!$permission) {
            abort(403);
        }
        $departments=DB::table('departments')
        ->where('company_id', $company)
        ->select('*')->get();
        return view('departmetwise_reports.ot_report',compact('departments'));
    }

    public function leavereport()
    {
        $permission = Auth::user()->can('department-wise-leave-report');
        if (!$permission) {
            abort(403);
        }
        $company = Session::get('company_id');
        $departments=DB::table('departments')
        ->where('company_id', $company)
        ->select('*')->get();
        return view('departmetwise_reports.leave_report',compact('departments'));
    }

    public function attendancereport()
    {
        $company = Session::get('company_id');
        $permission = Auth::user()->can('department-wise-attendance-report');
        if (!$permission) {
            abort(403);
        }
        $departments=DB::table('departments')
        ->where('company_id', $company)
        ->select('*')->get();
        return view('departmetwise_reports.attendance_report',compact('departments'));
    }


    public function generateotreport(Request $request)
    {
        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $company = Session::get('company_id');
    

        $query = DB::table('ot_approved')
            ->join('employees', 'ot_approved.emp_id', '=', 'employees.emp_id')
            ->join('departments', 'employees.emp_department', '=', 'departments.id')
            ->join('branches', 'employees.emp_location', '=', 'branches.id')
            ->where('employees.is_resigned', '=', 0)
            ->where('employees.emp_company', $company)
            ->select(
                'departments.id as dept_id',
                'departments.name as dept_name',
                DB::raw('SUM(ot_approved.hours) as total_ot'),
                DB::raw('SUM(ot_approved.double_hours) as total_double_ot')
                
            );

        if ($department != 'All') {
            $query->where('employees.emp_department', '=', $department);
        }

        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('ot_approved.date', [$from_date, $to_date]);
        }

        $query->groupBy('employees.emp_department');

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

    public function gettotlaotemployee(Request $request)
    {

        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $company = Session::get('company_id');


            $query = DB::table('ot_approved')
            ->join('employees', 'ot_approved.emp_id', '=', 'employees.emp_id')
            ->join('departments', 'employees.emp_department', '=', 'departments.id')
            ->join('branches', 'employees.emp_location', '=', 'branches.id')
            ->where('employees.is_resigned', '=', 0)
            ->where('employees.emp_company', $company)
            ->select(
                'employees.id as empid',
                'employees.emp_name_with_initial as emp_name',
                DB::raw('SUM(ot_approved.hours) as total_ot'),
                DB::raw('SUM(ot_approved.double_hours) as total_double_ot')
                
            );

        if ($department != 'All') {
            $query->where('employees.emp_department', '=', $department);
        }

        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('ot_approved.date', [$from_date, $to_date]);
        }

        $query->groupBy('employees.id');

        $data = $query->get();

        return response()->json(['data' => $data]);
    }


    public function generateleavereport(Request $request)
    {
        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $company = Session::get('company_id');

            $query = DB::table('leaves')
                ->join('employees', 'leaves.emp_id', '=', 'employees.emp_id')
                ->leftJoin('departments', 'employees.emp_department', '=', 'departments.id')
                ->select(
                    'departments.id as dept_id',
                    'departments.name as dept_name',
                    DB::raw('COUNT(leaves.no_of_days) as total_leave_count')
                );
                $query->where('leaves.status', "Approved");
                $query->where('employees.is_resigned', 0);
                $query->where('employees.emp_company', $company);

            if ($department != '' && $department != 'All') {
                $query->where('employees.emp_department', $department);
            }

            if (!empty($from_date) && !empty($to_date)) {
                $query->whereBetween('leaves.leave_from', [$from_date, $to_date]);
            }

            $query->groupBy('departments.id', 'departments.name');
            $data = $query->get();
            return response()->json(['data' => $data]);
    }

    public function gettotalleaveemployee(Request $request)
    {

        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $company = Session::get('company_id');


        $query = DB::table('leaves')
        ->join('employees', 'leaves.emp_id', '=', 'employees.emp_id')
        ->leftJoin('departments', 'employees.emp_department', '=', 'departments.id')
        ->select(
               'employees.id as empid',
                'employees.emp_name_with_initial as emp_name',
            DB::raw('COUNT(leaves.no_of_days) as total_leave_count')
        );
        $query->where('employees.emp_company', $company);
        $query->where('leaves.status', "Approved");
        $query->where('employees.is_resigned', 0);
        

        if ($department != '' && $department != 'All') {
            $query->where('employees.emp_department', $department);
        }

        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('leaves.leave_from', [$from_date, $to_date]);
        }
        $query->groupBy('employees.id');

        $data = $query->get();

        return response()->json(['data' => $data]);
    }

     public function generateattendreport(Request $request)
    {
        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $company = Session::get('company_id');

            $query = DB::table('attendances')
                ->join('employees', 'attendances.emp_id', '=', 'employees.emp_id')
                ->leftJoin('departments', 'employees.emp_department', '=', 'departments.id')
                ->select(
                    'departments.id as dept_id',
                    'departments.name as dept_name',
                    DB::raw('COUNT(DISTINCT attendances.date) as total_attend_count')
                );
                $query->where('attendances.deleted_at',NULL);
                $query->where('employees.is_resigned', 0);
                $query->where('employees.emp_company', $company);

            if ($department != '' && $department != 'All') {
                $query->where('employees.emp_department', $department);
            }

            if (!empty($from_date) && !empty($to_date)) {
                $query->whereBetween('attendances.date', [$from_date, $to_date]);
            }

            $query->groupBy('departments.id', 'departments.name');
            $data = $query->get();
            return response()->json(['data' => $data]);
    }

    public function gettotalattendentemployee(Request $request)
    {

        $department = $request->get('department');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');


        $query = DB::table('attendances')
        ->join('employees', 'attendances.emp_id', '=', 'employees.emp_id')
        ->leftJoin('departments', 'employees.emp_department', '=', 'departments.id')
        ->select(
               'employees.id as empid',
                'employees.emp_name_with_initial as emp_name',
           DB::raw('COUNT(DISTINCT attendances.date) as total_attend_count')
        );
         $query->where('attendances.deleted_at',NULL);
         $query->where('employees.is_resigned', 0);

        if ($department != '' && $department != 'All') {
            $query->where('employees.emp_department', $department);
        }

        if (!empty($from_date) && !empty($to_date)) {
            $query->whereBetween('attendances.date', [$from_date, $to_date]);
        }
        $query->groupBy('employees.id');

        $data = $query->get();

        return response()->json(['data' => $data]);
    }
    
    
}



