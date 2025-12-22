<?php

namespace App\Http\Controllers;

use App\Employee;
use App\LeaveType;
use App\EmploymentStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DateTime;
use DB;
use Session;
use Yajra\Datatables\Datatables;
use App\Helpers\EmployeeHelper;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $permission = Auth::user()->can('leave-type-list');
        if (!$permission) {
            abort(403);
        }

        $employmentstatus= EmploymentStatus::orderBy('id', 'asc')->get();
        $leavetype = DB::table('leave_types')
            ->join('employment_statuses', 'leave_types.emp_status', '=', 'employment_statuses.id')         
            ->select('leave_types.*', 'employment_statuses.emp_status')
            ->get();
        return view('Leave.leavetype',compact('leavetype','employmentstatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = array(
            'leavetype'    =>  'required',
            'empstatus'    =>  'required',
            'assignleave'    =>  'required'            
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'leave_type'        =>  $request->leavetype,
            'emp_status'        =>  $request->empstatus,            
            'assigned_leave'        =>  $request->assignleave           
            
        );

       $leavetype=new LeaveType;
       $leavetype->leave_type=$request->input('leavetype');       
       $leavetype->emp_status=$request->input('empstatus');               
       $leavetype->assigned_leave=$request->input('assignleave');    
       $leavetype->save();

       

        return response()->json(['success' => 'Leave Details Added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveType $leaveType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(request()->ajax())
        {
            $data = LeaveType::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        $rules = array(
            'leavetype'    =>  'required',
            'empstatus'    =>  'required',
            'assignleave'    =>  'required'   
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'leave_type'        =>  $request->leavetype,
            'emp_status'        =>  $request->empstatus,            
            'assigned_leave'        =>  $request->assignleave          
            
        );

        LeaveType::whereId($request->hidden_id)->update($form_data);

        return response()->json(['success' => 'Leave Details Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = LeaveType::findOrFail($id);
        $data->delete();
    }

    public function LeaveBalance()
    {
        $permission = Auth::user()->can('leave-balance-report');
        if (!$permission) {
            abort(403);
        }
        return view('Leave.leave_balance');
    }


    /**
     * @throws Exception
     */
    // public function leave_balance_list(Request $request)
    // {
    //     $permission = Auth::user()->can('leave-balance-report');
    //     if (!$permission) {
    //         return response()->json(['error' => 'UnAuthorized'], 401);
    //     }

    //     $department = $request->get('department');
    //     $employee_sel = $request->get('employee');
      
    //     $company = Session::get('company_id');
    //     $location = Session::get('company_branch_id');

    //     $query = \Illuminate\Support\Facades\DB::query()
    //         ->select('employees.*',
    //             'branches.location',
    //             'departments.name as dep_name')
    //         ->from('employees')
    //         ->leftJoin('branches', 'employees.emp_location', '=', 'branches.id')
    //         ->leftJoin('departments', 'departments.id', '=', 'employees.emp_department')
    //         ->where('employees.deleted', '=', '0')
    //         ->where('employees.is_resigned', '=', '0')
    //         ->where('employees.emp_company', $company)
    //         ->where('employees.emp_location', $location)
    //         ->whereNull('employees.resignation_date');

    //     if($department != ''){
    //         $query->where(['departments.id' => $department]);
    //     }

    //     if($employee_sel != ''){
    //         $query->where(['employees.emp_id' => $employee_sel]);
    //     }

       
    //     $employees = $query->get();

    //     $final_data = array();

    //     foreach ($employees as $employee)
    //     {
    //         $emp_join_date = $employee->emp_join_date;
    //         $join_year = Carbon::parse($emp_join_date)->year;
    //         $join_month = Carbon::parse($emp_join_date)->month;
    //         $join_date = Carbon::parse($emp_join_date)->day;
    //         $full_date = '2022-'.$join_month.'-'.$join_date;

    //         $q_data = DB::table('quater_leaves')
    //             ->where('from_date', '<', $full_date)
    //             ->where('to_date', '>', $full_date)
    //             ->first();

    //         $like_from_date = date('Y').'-01-01';
    //         $like_from_date2 = date('Y').'-12-31';

    //         $total_taken_annual_leaves = DB::table('leaves')
    //             ->where('leaves.emp_id', '=', $employee->emp_id)
    //             ->whereBetween('leaves.leave_from', [$like_from_date, $like_from_date2])
    //             ->where('leaves.leave_type', '=', '1')
    //             ->where('leaves.status', '=', 'Approved')
    //             ->get()->toArray();

    //         $current_year_taken_a_l = 0;
    //         foreach ($total_taken_annual_leaves as $tta){
    //             $leave_from = $tta->leave_from;
    //             $leave_to = $tta->leave_to;

    //             $leave_from_year = Carbon::parse($leave_from)->year;
    //             $leave_to_year = Carbon::parse($leave_to)->year;

    //             if($leave_from_year != $leave_to_year){
    //                 //get current year leaves for that record
    //                 $lastDayOfMonth = Carbon::parse($leave_from)->endOfMonth()->toDateString();

    //                 $to = \Carbon\Carbon::createFromFormat('Y-m-d', $lastDayOfMonth);
    //                 $from = \Carbon\Carbon::createFromFormat('Y-m-d', $leave_from);

    //                 $diff_in_days = $to->diffInDays($from);
    //                 $current_year_taken_a_l += $diff_in_days;

    //                 $jan_data = DB::table('leaves')
    //                     ->where('leaves.id', '=', $tta->id)
    //                     ->first();

    //                 $firstDayOfMonth = Carbon::parse($jan_data->leave_to)->startOfMonth()->toDateString();
    //                 $to_t = \Carbon\Carbon::createFromFormat('Y-m-d', $jan_data->leave_to);
    //                 $from_t = \Carbon\Carbon::createFromFormat('Y-m-d', $firstDayOfMonth);

    //                 $diff_in_days_f = $to_t->diffInDays($from_t);
    //                 $current_year_taken_a_l += $diff_in_days_f;

    //             }else{
    //                 $current_year_taken_a_l += $tta->no_of_days;
    //             }
    //         }
            
    //         $like_from_date_cas = date('Y').'-01-01';
    //         $like_from_date2_cas = date('Y').'-12-31';
    //         $total_taken_casual_leaves = DB::table('leaves')
    //             ->where('leaves.emp_id', '=', $employee->emp_id)
    //             ->whereBetween('leaves.leave_from', [$like_from_date_cas, $like_from_date2_cas])
    //             ->where('leaves.leave_type', '=', '2')
    //             ->where('leaves.status', '=', 'Approved')
    //             ->get()->toArray();

            

    //         $current_year_taken_c_l = 0;

    //         foreach ($total_taken_casual_leaves as $tta){
    //             $leave_from = $tta->leave_from;
    //             $leave_to = $tta->leave_to;

    //             $leave_from_year = Carbon::parse($leave_from)->year;
    //             $leave_to_year = Carbon::parse($leave_to)->year;

    //             if($leave_from_year != $leave_to_year){
    //                 //get current year leaves for that record
    //                 $lastDayOfMonth = Carbon::parse($leave_from)->endOfMonth()->toDateString();

    //                 $to = \Carbon\Carbon::createFromFormat('Y-m-d', $lastDayOfMonth);
    //                 $from = \Carbon\Carbon::createFromFormat('Y-m-d', $leave_from);

    //                 $diff_in_days = $to->diffInDays($from);
    //                 $current_year_taken_c_l += $diff_in_days;
    //             }else{
    //                 $current_year_taken_c_l += $tta->no_of_days;
    //             }
    //         }

    //         $leave_msg = '';
    //         $casual_leaves = 0;
    //         if($join_year == date('Y')){
    //             if(!empty($q_data)){
    //                 $annual_leaves = $q_data->leaves;
    //             }
    //             $leave_msg = "Employee can have only a half day per month in Casual Leaves. (Employee joined in current year)";
    //         }else{
    //             $annual_leaves = 14;
    //             $casual_leaves = 7;
    //         }

    //         $total_no_of_annual_leaves = $annual_leaves;
    //         $total_no_of_casual_leaves = $casual_leaves;

    //         $available_no_of_annual_leaves = $total_no_of_annual_leaves - $current_year_taken_a_l;
    //         $available_no_of_casual_leaves = $total_no_of_casual_leaves - $current_year_taken_c_l;

    //         if($employee->emp_status != 2){
    //             $emp_status = DB::table('employment_statuses')->where('id', $employee->emp_status)->first();
    //             $leave_msg = 'Casual Leaves - '.$emp_status->emp_status.' Employee can have only a half day per month (Not a permanent employee)';
    //         }

    //         $results = array(
    //             "emp_id" => $employee->emp_id,
    //             "emp_name_with_initial" => $employee->emp_name_with_initial,
    //             "total_no_of_annual_leaves" => $total_no_of_annual_leaves,
    //             "total_no_of_casual_leaves" => $total_no_of_casual_leaves,
    //             "total_taken_annual_leaves" => $current_year_taken_a_l,
    //             "total_taken_casual_leaves" => $current_year_taken_c_l,
    //             "available_no_of_annual_leaves" => $available_no_of_annual_leaves,
    //             "available_no_of_casual_leaves" => $available_no_of_casual_leaves,
    //             "leave_msg" => $leave_msg,
    //         );

    //         $final_data[] = $results;


    //     }

    //     return Datatables::of($final_data)->make(true);

    // }
    public function leave_balance_list(Request $request)
    {
        $permission = Auth::user()->can('leave-balance-report');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $companyId = session('company_id');
        $department = $request->get('department');
        $employee_sel = $request->get('employee');
        $location = $request->get('location');

        $query = \Illuminate\Support\Facades\DB::query()
            ->select('employees.*',
                'branches.location',
                'departments.name as dep_name',
                'job_categories.annual_leaves',
                'job_categories.casual_leaves',
                'job_categories.medical_leaves')
            ->from('employees')
            ->leftJoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.emp_department')
            ->leftJoin('job_categories', 'job_categories.id', '=', 'employees.job_category_id')
            ->where('employees.emp_company', '=', $companyId)
            ->where('employees.deleted', '=', '0')
            ->whereNull('employees.special_attendance')
            ->where('employees.is_resigned', '=', '0');


        if($department != ''){
            $query->where(['departments.id' => $department]);
        }

        if($employee_sel != ''){
            $query->where(['employees.emp_id' => $employee_sel]);
        }

        if($location != ''){
            $query->where(['employees.emp_location' => $location]);
        }

        $employees = $query->get();

        $final_data = array();

        foreach ($employees as $employee)
        {
            $emp_join_date = $employee->emp_join_date;
            $empid = $employee->emp_id;
        
            $join_year = Carbon::parse($emp_join_date)->year;
            $join_month = Carbon::parse($emp_join_date)->month;
            $join_date = Carbon::parse($emp_join_date)->day;

           

             

            $like_from_date = date('Y').'-01-01';
            $like_from_date2 = date('Y').'-12-31';

            $total_taken_annual_leaves = DB::table('leaves')
                ->where('leaves.emp_id', '=', $empid)
                ->whereBetween('leaves.leave_from', [$like_from_date, $like_from_date2])
                ->where('leaves.leave_type', '=', '1')
                ->where('leaves.status', '=', 'Approved')
                ->get()->toArray();

            $current_year_taken_a_l = 0;

            
            foreach ($total_taken_annual_leaves as $tta){
                $leave_from = $tta->leave_from;
                $leave_to = $tta->leave_to;

                $leave_from_year = Carbon::parse($leave_from)->year;
                $leave_to_year = Carbon::parse($leave_to)->year;

                if($leave_from_year != $leave_to_year){
                    //get current year leaves for that record
                    $lastDayOfMonth = Carbon::parse($leave_from)->endOfMonth()->toDateString();

                    $to = \Carbon\Carbon::createFromFormat('Y-m-d', $lastDayOfMonth);
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d', $leave_from);

                    $diff_in_days = $to->diffInDays($from);
                    $current_year_taken_a_l += $diff_in_days;

                    $jan_data = DB::table('leaves')
                        ->where('leaves.id', '=', $tta->id)
                        ->first();

                    $firstDayOfMonth = Carbon::parse($jan_data->leave_to)->startOfMonth()->toDateString();
                    $to_t = \Carbon\Carbon::createFromFormat('Y-m-d', $jan_data->leave_to);
                    $from_t = \Carbon\Carbon::createFromFormat('Y-m-d', $firstDayOfMonth);

                    $diff_in_days_f = $to_t->diffInDays($from_t);
                    $current_year_taken_a_l += $diff_in_days_f;

                }else{
                    $current_year_taken_a_l += $tta->no_of_days;
                }
            }

            $like_from_date_cas = date('Y').'-01-01';
            $like_from_date2_cas = date('Y').'-12-31';
            $total_taken_casual_leaves = DB::table('leaves')
                ->where('leaves.emp_id', '=', $empid)
                ->whereBetween('leaves.leave_from', [$like_from_date_cas, $like_from_date2_cas])
                ->where('leaves.leave_type', '=', '2')
                ->where('leaves.status', '=', 'Approved')
                ->get();


                

            $current_year_taken_c_l = 0;

            foreach ($total_taken_casual_leaves as $tta){
                $leave_from = $tta->leave_from;
                $leave_to = $tta->leave_to;

                $leave_from_year = Carbon::parse($leave_from)->year;
                $leave_to_year = Carbon::parse($leave_to)->year;

                if($leave_from_year != $leave_to_year){
                    //get current year leaves for that record
                    $lastDayOfMonth = Carbon::parse($leave_from)->endOfMonth()->toDateString();

                    $to = \Carbon\Carbon::createFromFormat('Y-m-d', $lastDayOfMonth);
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d', $leave_from);

                    $diff_in_days = $to->diffInDays($from);
                    $current_year_taken_c_l += $diff_in_days;
                }else{
                    $current_year_taken_c_l += $tta->no_of_days;
                }
            }

            $like_from_date_med = date('Y').'-01-01';
            $like_from_date2_med = date('Y').'-12-31';
            $total_taken_medical_leaves = DB::table('leaves')
                ->where('leaves.emp_id', '=', $empid)
                ->whereBetween('leaves.leave_from', [$like_from_date_med, $like_from_date2_med])
                ->where('leaves.leave_type', '=', '4')
                ->where('leaves.status', '=', 'Approved')
                ->get();


                

            $current_year_taken_medical = 0;

            foreach ($total_taken_medical_leaves as $medic){
                $leave_from = $medic->leave_from;
                $leave_to = $medic->leave_to;

                $leave_from_year = Carbon::parse($leave_from)->year;
                $leave_to_year = Carbon::parse($leave_to)->year;

                if($leave_from_year != $leave_to_year){
                    //get current year leaves for that record
                    $lastDayOfMonth = Carbon::parse($leave_from)->endOfMonth()->toDateString();

                    $to = \Carbon\Carbon::createFromFormat('Y-m-d', $lastDayOfMonth);
                    $from = \Carbon\Carbon::createFromFormat('Y-m-d', $leave_from);

                    $diff_in_days = $to->diffInDays($from);
                    $current_year_taken_medical += $diff_in_days;
                }else{
                    $current_year_taken_medical += $medic->no_of_days;
                }
            }

           

            $leave_msg = '';

            $employee_join_date = Carbon::parse($emp_join_date);
            $current_date = Carbon::now();

            // Calculate months of service
            $months_of_service = $employee_join_date->diffInMonths($current_date);
            $annual_leaves = 0;

            if($employee->annual_leaves>0):
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
            if($employee->medical_leaves>0):
                if($empid=='10118' || $empid=='10089' || $empid=='10098' || $empid=='20036' || $empid=='20066' || $empid=='20091' || $empid=='20099' || $empid=='20114'){
                    $medical_leaves = 21;
                }
                else{
                    $medical_leaves = 0;
                }
            endif;


            $casual_leaves = 0;
            if($employee->casual_leaves>0):
                // $casual_leaves = 0;
                // $join_date = new DateTime($emp_join_date);
                // $current_date = new DateTime();
                // $interval = $join_date->diff($current_date);
                
                // $years_of_service = $interval->y;
                // $months_of_service = $interval->m;
                
                // // Casual leave calculation
                // if ($years_of_service == 0) {
                //     // First year - 0.5 day for every  completed month
                //    $casual_leaves = number_format((6 / 12) * $months_of_service, 2);

                // } else {
                //     $casual_leaves = 7;
                // }
                $join_date = new DateTime($emp_join_date);
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
                    // Second year - check if still within first year + 1 day
                    $first_year_end = clone $join_date;
                    $first_year_end->modify('+1 year'); // 2024-10-07 -> 2025-10-07
                    
                    $second_year_end = clone $join_date;
                    $second_year_end->modify('+2 years'); // 2024-10-07 -> 2026-10-07
                    
                    // Check if current date is between first_year_end and second_year_end
                    if ($current_date > $first_year_end && $current_date <= $second_year_end) {
                        // From 2025-10-08 to 2025-12-31: 0.5 per month
                        $start_second_year = clone $first_year_end;
                        $start_second_year->modify('+1 day'); // 2025-10-08
                        
                        // Check if we're in the calendar year after first anniversary
                        if ($current_date >= $start_second_year) {
                            $month_start = max($start_second_year, new DateTime($current_date->format('Y-01-01')));
                            
                            // Calculate months from month_start to current_date or end of year
                            $end_date = min($current_date, new DateTime($current_date->format('Y-12-31')));
                            
                            $month_interval = $month_start->diff($end_date);
                            $months_in_second_year = $month_interval->y * 12 + $month_interval->m;
                            
                            // if ($month_interval->d > 0) {
                            //     $months_in_second_year += 1; // Count partial month
                            // }
                            
                            $casual_leaves = number_format(0.5 * $months_in_second_year, 2);
                        }
                    } else {
                        $casual_leaves = 7;
                    }
                } else {
                    // After second year - always 7 casual leaves
                    $casual_leaves = 7;
                }
            endif;

             

            $total_no_of_annual_leaves = $annual_leaves;
            $total_no_of_casual_leaves = $casual_leaves;
            $total_no_of_medical_leaves = $medical_leaves;

           

            $available_no_of_annual_leaves = $total_no_of_annual_leaves - $current_year_taken_a_l;
            $available_no_of_casual_leaves = $total_no_of_casual_leaves - $current_year_taken_c_l;
            $available_no_of_medical_leaves = $total_no_of_medical_leaves - $current_year_taken_medical;

            $total_ava_leaves = $available_no_of_annual_leaves+$available_no_of_casual_leaves+$available_no_of_medical_leaves;

            if($employee->emp_status != 2){
                $emp_status = DB::table('employment_statuses')->where('id', $employee->emp_status)->first();
                $leave_msg = 'Casual Leaves - '.$emp_status->emp_status.' Employee can have only a half day per month (Not a permanent employee)';
            }



            $results = array(
                "emp_id" => $employee->emp_id,
                "emp_name_with_initial" => $employee->emp_name_with_initial,
                 "employee_display" => EmployeeHelper::getDisplayName($employee), 
                "total_no_of_annual_leaves" => $total_no_of_annual_leaves,
                "total_no_of_casual_leaves" => $total_no_of_casual_leaves,
                "total_no_of_medical_leaves" => $total_no_of_medical_leaves,
                "total_taken_annual_leaves" => $current_year_taken_a_l,
                "total_taken_casual_leaves" => $current_year_taken_c_l,
                "total_taken_medical_leaves" => $current_year_taken_medical,
                "available_no_of_annual_leaves" => $available_no_of_annual_leaves,
                "available_no_of_casual_leaves" => $available_no_of_casual_leaves,
                "available_no_of_medical_leaves" => $available_no_of_medical_leaves,
                "leave_msg" => $leave_msg,
                "totalavaleave" => $total_ava_leaves,
            );

            $final_data[] = $results;


        }

        return Datatables::of($final_data)->make(true);

    }
}
