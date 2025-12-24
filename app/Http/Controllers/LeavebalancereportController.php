<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;

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
        ->whereNull('employees.special_attendance')
        ->orderBy('employees.emp_id', 'asc')
        ->get();
        
        foreach ($query as $row) {
            $empId = $row->empid;
            $emp_id = $row->emp_id;
            $empName = $row->emp_name;
            $dailySalary = $row->day_salary;

            $year = Carbon::parse($from_date)->year;

            $join_year = Carbon::parse($row->emp_join_date)->year;

            // Get annual leave quota
            // if ($join_year == $year) {
            //     $q_data = DB::table('quater_leaves')
            //         ->where('from_date', '<', $from_date)
            //         ->where('to_date', '>', $from_date)
            //         ->first();

            //     $annual_leaves = $q_data ? $q_data->leaves : 0;
            // } else {
            //     $leaves = DB::table('job_categories')->where('id', $row->job_category_id)->first();
            //     $annual_leaves = $leaves->annual_leaves ?? 0;
            // }

            $leaves = DB::table('job_categories')->where('id', $row->job_category_id)->first();
            $leave_msg = '';

            $employee_join_date = Carbon::parse($row->emp_join_date);
            $current_date = Carbon::now();
            $join_year = Carbon::parse($employee_join_date)->year;
            $join_month = Carbon::parse($employee_join_date)->month;
            $join_date = Carbon::parse($employee_join_date)->day;

            // Calculate months of service
            $months_of_service = $employee_join_date->diffInMonths($current_date);
            $annual_leaves = 0;

            if($leaves->annual_leaves>0):
                // First Year (0-12 months) - No annual leaves
                if ($months_of_service < 12) {
                    $annual_leaves = 0;
                    $leave_msg = "Employee is in the first year of service - no annual leaves yet.";
                }

                // Second Year (12-24 months) - Pro-rated leaves based on first year's quarter
                elseif ($months_of_service < 24) {
                    // Get the 1-year anniversary date
                    $anniversary_date = $employee_join_date->copy()->addYear();

                    // Check if current date is between anniversary and December 31
                    $year_end = Carbon::create($anniversary_date->year, 12, 31);

                    // Only calculate if current date is after anniversary but before next year
                    if ($current_date >= $anniversary_date && $current_date <= $year_end) {
                        // Get the quarter period from the joining year (original employment quarter)
                        $full_date = '2022-'.$join_month.'-'.$join_date;

                        $q_data = DB::table('quater_leaves')
                            ->where('from_date', '<=', $full_date)
                            ->where('to_date', '>', $full_date)
                            ->first();

                        $annual_leaves = $q_data ? $q_data->leaves : 0;
                            $leave_msg = $q_data ? "Using quarter leaves value from anniversary to year-end." : "No matching quarter found for pro-rated leaves.";
                    }
                        // After December 31, switch to standard 14 days
                    elseif ($current_date > $year_end) {
                        $annual_leaves = 14;
                        $leave_msg = "Switched to standard 14 days from January 1st.";
                    }
                    // Before anniversary date
                    else {
                        $annual_leaves = 0;
                        $leave_msg = "Waiting for 1-year anniversary date ($anniversary_date->format('Y-m-d'))";
                    } 
                }
                // Third year onwards (24+ months) - Full 14 days
                else {
                    $annual_leaves = 14;
                    $leave_msg = "Employee is eligible for full 14 annual leaves per year.";
                }
            endif;

            $medical_leaves = 0;
            if($leaves->medical_leaves>0):
                if($row->emp_id=='10118' || $row->emp_id=='10089' || $row->emp_id=='10098' || $row->emp_id=='20036' || $row->emp_id=='20066' || $row->emp_id=='20091' || $row->emp_id=='20099' || $row->emp_id=='20114'){
                    $medical_leaves = 21;
                }
                else{
                    $medical_leaves = 0;
                }
            endif;

            // Casual leaves quota
            // $leaves = DB::table('job_categories')->where('id', $row->job_category_id)->first();
            // $casual_leaves = $leaves->casual_leaves ?? 0;
            $casual_leaves = 0;
            if($leaves->casual_leaves>0):
                $join_date = new DateTime($row->emp_join_date);
                $current_date = new DateTime();
                $interval = $join_date->diff($current_date);

                $years_of_service = $interval->y;
                $months_of_service = $interval->m;
                $days_of_service = $interval->d;

                // Casual leave calculation
                if ($years_of_service == 0) {
                    // First year - 0.5 day for every completed month from join date
                    $casual_leaves = number_format(0.5 * $months_of_service, 2);
                    
                } elseif ($years_of_service == 1) {
                    // Second year - calculate leaves for current calendar year
                    $first_year_end = clone $join_date;
                    $first_year_end->modify('+1 year');
                    
                    $second_year_end = clone $join_date;
                    $second_year_end->modify('+2 years');
                    
                    // Get current calendar year
                    $current_year = (int)$current_date->format('Y');
                    $year_start = new DateTime($current_year . '-01-01');
                    $year_end = new DateTime($current_year . '-12-31');
                    
                    // Check if anniversary is in this calendar year
                    if ($first_year_end >= $year_start && $first_year_end <= $year_end) {
                        // Calculate leaves before anniversary (from Jan 1 to anniversary date)
                        $start_date = max($year_start, $join_date);
                        $pre_anniversary_end = min($first_year_end, $year_end);
                        
                        $pre_interval = $start_date->diff($pre_anniversary_end);
                        $pre_months = $pre_interval->y * 12 + $pre_interval->m;
                        if ($pre_interval->d > 0) {
                            $pre_months += 1; // Count partial month
                        }
                        
                        $pre_anniversary_leaves = 0.5 * $pre_months;
                        
                        // Calculate leaves after anniversary (from anniversary+1 to Dec 31)
                        $post_anniversary_start = clone $first_year_end;
                        $post_anniversary_start->modify('+1 day');
                        
                        if ($post_anniversary_start <= $year_end && $post_anniversary_start <= $current_date) {
                            $post_start = max($post_anniversary_start, $year_start);
                            $post_end = min($current_date, $year_end);
                            
                            $post_interval = $post_start->diff($post_end);
                            $post_months = $post_interval->y * 12 + $post_interval->m;
                            // if ($post_interval->d > 0) {
                            //     $post_months += 1; // Count partial month
                            // }
                            
                            $post_anniversary_leaves = 0.5 * $post_months;
                            
                            // For second year, show only post-anniversary leaves
                            $casual_leaves = number_format(($pre_anniversary_leaves+$post_anniversary_leaves), 2);
                        } else {
                            // No post-anniversary period yet, show pre-anniversary leaves
                            $casual_leaves = number_format($pre_anniversary_leaves, 2);
                        }
                    } else {
                        // Anniversary not in current year
                        $casual_leaves = 7;
                    }
                } else {
                    // After second year - always 7 casual leaves
                    $casual_leaves = 7;
                }
            endif;

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

            // Calculate medical leave taken in the current year
            $total_taken_medical_leaves = DB::table('leaves')
                ->where('emp_id', $row->emp_id)
                ->whereBetween('leave_from', ["$year-01-01", $from_date])
                ->where('leave_type', 4)
                ->where('leaves.status', '=', 'Approved')
                ->sum('no_of_days');
            
            
            $available_annual_leaves = $annual_leaves - $total_taken_annual_leaves;
            $available_casual_leaves = $casual_leaves - $total_taken_casual_leaves;
            $available_medical_leaves = $medical_leaves - $total_taken_medical_leaves;

            $totalbalnceleavecount =  $available_annual_leaves + $available_casual_leaves + $available_medical_leaves;
            $leavepaymentamount = ( $totalbalnceleavecount * $dailySalary );

            $totalleavebalanceAllEmp += $leavepaymentamount;

            $datareturn[] = [
                'empid' => $empId,
                'emp_id' => $emp_id,
                'emp_name' => $empName,
                'anualbalnce' => $available_annual_leaves,
                'casualbalance' => $available_casual_leaves,
                'medicalbalance' => $available_medical_leaves,
                'totalleavebalance' => $totalbalnceleavecount,
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
