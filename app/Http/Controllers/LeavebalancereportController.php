<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeavebalancereportController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(){

        $companyId = session('company_id');
        $branch=Company::orderBy('id', 'asc')->get(); 
        $department = DB::select("select id, company_id, name from departments WHERE company_id = ?", [$companyId]);
        return view('Payroll.Reports.leavebalance',compact('branch', 'department'));
    }

    
    public function generatereport(Request $request){
        $company = $request->input('company');
        $department = $request->input('department');
        $from_date = $request->input('from_date');

        $datareturn = [];
        $totalleavebalanceAllEmp = 0;

        $query = DB::table('employees')
        ->join('payroll_profiles', 'employees.id', '=', 'payroll_profiles.emp_id')
        ->select(
            'employees.*',
            'employees.id as empid',
            'employees.emp_name_with_initial as emp_name',
            'payroll_profiles.day_salary as day_salary',
            'payroll_profiles.id as payroll_profiles_id'                
        )->where('employees.emp_company', '=', $company)
        ->when($department !== 'All', function ($query) use ($department) {
            return $query->where('employees.emp_department', '=', $department);
        })
        ->where('payroll_profiles.payroll_process_type_id', '=',1)
        ->where('employees.deleted', '=',0)
        ->where('employees.is_resigned', '=',0)
        ->orderBy('employees.id')
        ->get();
        
        foreach ($query as $row) {
            $empId = $row->empid;
            $empName = $row->emp_name;
            $dailySalary = $row->day_salary;

            $year = Carbon::parse($from_date)->year;

            $join_year = Carbon::parse($row->emp_join_date)->year;

            // Get annual leave quota
            if ($join_year == $year) {
                $q_data = DB::table('quater_leaves')
                    ->where('from_date', '<', $from_date)
                    ->where('to_date', '>', $from_date)
                    ->first();

                $annual_leaves = $q_data ? $q_data->leaves : 0;
            } else {
                $leaves = DB::table('job_categories')->where('id', $row->job_category_id)->first();
                $annual_leaves = $leaves->annual_leaves ?? 0;
            }

            // Casual leaves quota
            $leaves = DB::table('job_categories')->where('id', $row->job_category_id)->first();
            $casual_leaves = $leaves->casual_leaves ?? 0;

            // Calculate annual leaves taken in the current year
            $total_taken_annual_leaves = DB::table('leaves')
                ->where('emp_id', $row->emp_id)
                ->whereBetween('leave_from', ["$year-01-01", $from_date])
                ->where('leave_type', 1)
                ->where('leaves.status', '=', 'Approved')
                ->sum('no_of_days');

            // Calculate casual leaves taken in the current year
            $total_taken_casual_leaves = DB::table('leaves')
                ->where('emp_id', $row->emp_id)
                ->whereBetween('leave_from', ["$year-01-01", $from_date])
                ->where('leave_type', 2)
                ->where('leaves.status', '=', 'Approved')
                ->sum('no_of_days');
            
            
            $available_annual_leaves = $annual_leaves - $total_taken_annual_leaves;
            $available_casual_leaves = $casual_leaves - $total_taken_casual_leaves;

            $totalbalnceleavecount =  $available_annual_leaves + $available_casual_leaves;
            $leavepaymentamount = ( $totalbalnceleavecount * $dailySalary );

            $totalleavebalanceAllEmp += $leavepaymentamount;

            $datareturn[] = [
                'empid' => $empId,
                'emp_name' => $empName,
                'anualbalnce' => $available_annual_leaves,
                'casualbalance' => $available_casual_leaves,
                'daliyrate' => $dailySalary,
                'total_amount' => number_format($leavepaymentamount, 2) 
            ];

        }

        return response()->json([
            'data' => $datareturn,
            'total_amount_all_employees' => number_format($totalleavebalanceAllEmp, 2) 
        ]);

    }

}
