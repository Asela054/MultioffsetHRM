<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employee;
use App\Attendance;
use App\AttendanceEdited;
use Auth;
use DB;
use Carbon\Carbon;
use DateTime;
use Session;
use Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');
        $late_times = DB::table('late_types')->where('id', 2)->first();
        // $user = Auth::user();
        // $departments = $user->departments;

         $today = Carbon::now()->format('Y-m-d');
         $empcount = DB::table('employees')->where('deleted', 0)->where('status', 1)->where('is_resigned', 0)->where('emp_company', $companyId)->count();
        //  $todaycount = Attendance::where('date','2023-09-18')->groupBy('date','emp_id')->count();

        // today attendance count
         $todaycount = DB::table('attendances')
        ->select('date', 'emp_id')
        ->where('date', $today)
        ->where('location', $companyId)
        ->where('deleted_at', NULL)
        ->havingRaw('MIN(attendances.timestamp) < ?', [$today . ' ' . $late_times->time_from])
        ->groupBy('date', 'uid')
        ->get()
        ->count();

        // today late attendance count
       
        $todaylatecount = DB::table('attendances')
        ->select('timestamp','date', 'emp_id')
        ->where('date', $today)
        ->where('location', $companyId)
        ->where('deleted_at', NULL)
        ->havingRaw('MIN(attendances.timestamp) >= ?', [$today . ' ' . $late_times->time_from])
        ->groupBy('date', 'uid')
        ->get()
        ->count();
      
        

        // --------------------------------------------------------------------------------------------------------------

        // get today daybefore day on attendance
        $yesterdayDate = Carbon::now()->subDay()->format('Y-m-d');

         // yesterday attendance count
         $yesterdaycount = DB::table('attendances')
        ->select('date', 'emp_id')
        ->where('date', $yesterdayDate)
        ->where('location', $companyId)
        ->where('deleted_at', NULL)
        ->havingRaw('MIN(attendances.timestamp) < ?', [$yesterdayDate . ' ' . $late_times->time_from])
        ->groupBy('date', 'uid')
        ->get()
        ->count();

        // yesterday late attendance count
        $yesterdaylatecount = DB::table('attendances')
        ->select('date', 'emp_id')
        ->where('date', $yesterdayDate)
        ->where('location', $companyId)
        ->where('deleted_at', NULL)
        ->havingRaw('MIN(attendances.timestamp) >= ?', [$yesterdayDate . ' ' . $late_times->time_from])
        ->groupBy('date', 'uid')

        ->get()
        ->count();


          // --------------------------------------------------------------------------------------------------------------
        // Birthday Count
        $currentMonth = Carbon::now()->month;
        $currentDay = Carbon::now()->day;
        // $currentWeek = Carbon::now()->weekOfMonth;
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d'); 
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');
        $company = Session::get('company_id');

        // Today's Birthday Count
        $todayBirthdayCount = DB::table('employees')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->whereMonth('emp_birthday', $currentMonth)
            ->where('employees.emp_company', $company)
            ->whereDay('emp_birthday', $currentDay)
            ->count();

        // This Week's Birthday Count
        $thisweekBirthdayCount = DB::table('employees')
        ->where('deleted', 0)
        ->where('is_resigned', 0)
        ->where('employees.emp_company', $company)
        ->whereBetween(DB::raw('DATE_FORMAT(emp_birthday, "%m-%d")'), [
            Carbon::now()->startOfWeek()->format('m-d'),
            Carbon::now()->endOfWeek()->format('m-d'),
        ])
        ->count();

        $thismonthBirthdayCount = DB::table('employees')
        ->where('deleted', 0)
        ->where('is_resigned', 0)
         ->where('employees.emp_company', $company)
        ->whereMonth('emp_birthday', $currentMonth)
        ->count();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $leavedatalist = DB::table('leaves')
            ->leftJoin('leave_types', 'leave_types.id', '=', 'leaves.leave_type')
            ->leftJoin('employees', 'employees.emp_id', '=', 'leaves.emp_id')
            ->leftJoin('employee_pictures', 'employee_pictures.emp_id', '=', 'employees.emp_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.emp_department')
            ->whereMonth('leaves.leave_from', $currentMonth)
            ->whereYear('leaves.leave_from', $currentYear)
            ->where('leaves.status', 'Approved')
            ->where('employees.emp_company', $companyId)
            ->select(
                'employees.emp_id',
                'leave_types.leave_type',
                'leaves.leave_from',
                'leaves.leave_to',
                'leaves.no_of_days',
                'leaves.reson',
                'employees.emp_name_with_initial',
                'employees.calling_name',
                'employee_pictures.emp_pic_filename',
                'departments.name as department'
            )
            ->orderBy('leaves.leave_from', 'DESC')
            ->get();

        $employeesbday = DB::table('employees')
            ->select('emp_name_with_initial', 'emp_birthday')
            ->where('is_resigned', 0)
            ->where('deleted', 0)
            ->whereNotNull('emp_birthday')
            ->where('emp_company', $companyId)
            ->get();

        $holidays = DB::table('holidays')
            ->select('holiday_name', 'date')
            ->get();

        $currentYear = Carbon::now()->year;

        // Process birthdays
        $birthdayEvents = $employeesbday->map(function ($employee) use ($currentYear) {
            // Format the birthday to "mm-dd-yyyy" format
            $birthdayDate = \Carbon\Carbon::parse($employee->emp_birthday);
            
            return [
                'date' => $birthdayDate->format('m-d') . '-' . $currentYear,
                'name' => $employee->emp_name_with_initial . "'s Birthday",
                'color' => '#ff69b4' // You can customize colors as needed
            ];
        })->toArray();

        // Process holidays
        $holidayEvents = $holidays->map(function ($holiday) {
            // Format the holiday date to match the same format (mm-dd-yyyy)
            $holidayDate = \Carbon\Carbon::parse($holiday->date)->format('m-d-Y');
            
            return [
                'date' => $holidayDate,
                'name' => $holiday->holiday_name,
                'color' => '#5d4697' // Different color for holidays
            ];
        })->toArray();

        $events = array_merge($birthdayEvents, $holidayEvents);

        // ---- Manpower attendance counts ----
        // Get all manpower card emp_nos
        $manpowerEmpNos = DB::table('manpower_cards')->where('status', 1)->pluck('emp_no')->toArray();

        // Manpower Today Attendance count: manpower emp_nos that have attendance today
        $manpowerTodayCount = DB::table('attendances')
            ->whereIn('uid', $manpowerEmpNos)
            ->where('date', $today)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->count('uid');

        // Manpower Today Absent count: manpower_employee_details for today not in attendances
        $manpowerTodayAllocatedCardIds = DB::table('manpower_employee_details')
            ->where('date', $today)
            ->where('status', '!=', 3)
            ->pluck('card_id')
            ->toArray();

        $manpowerTodayAllocatedEmpNos = DB::table('manpower_cards')
            ->whereIn('id', $manpowerTodayAllocatedCardIds)
            ->where('status', 1)
            ->pluck('emp_no')
            ->toArray();

        $manpowerTodayPresentEmpNos = DB::table('attendances')
            ->whereIn('uid', $manpowerTodayAllocatedEmpNos)
            ->where('date', $today)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->pluck('uid')
            ->toArray();

        $manpowerTodayAbsentCount = count(array_diff($manpowerTodayAllocatedEmpNos, $manpowerTodayPresentEmpNos));

        // Manpower Today Total = Attendance + Absent
        $manpowerTodayTotal = $manpowerTodayCount + $manpowerTodayAbsentCount;

        // Manpower Yesterday Attendance count
        $manpowerYesterdayCount = DB::table('attendances')
            ->whereIn('uid', $manpowerEmpNos)
            ->where('date', $yesterdayDate)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->count('uid');

        // Manpower Yesterday Absent count
        $manpowerYesterdayAllocatedCardIds = DB::table('manpower_employee_details')
            ->where('date', $yesterdayDate)
            ->where('status', '!=', 3)
            ->pluck('card_id')
            ->toArray();

        $manpowerYesterdayAllocatedEmpNos = DB::table('manpower_cards')
            ->whereIn('id', $manpowerYesterdayAllocatedCardIds)
            ->where('status', 1)
            ->pluck('emp_no')
            ->toArray();

        $manpowerYesterdayPresentEmpNos = DB::table('attendances')
            ->whereIn('uid', $manpowerYesterdayAllocatedEmpNos)
            ->where('date', $yesterdayDate)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->pluck('uid')
            ->toArray();

        $manpowerYesterdayAbsentCount = count(array_diff($manpowerYesterdayAllocatedEmpNos, $manpowerYesterdayPresentEmpNos));

        // Manpower Yesterday Total = Attendance + Absent
        $manpowerYesterdayTotal = $manpowerYesterdayCount + $manpowerYesterdayAbsentCount;

        return view('home',compact(
            'empcount','todaycount','todaylatecount','yesterdaycount','yesterdaylatecount',
            'todayBirthdayCount','thisweekBirthdayCount','thismonthBirthdayCount',
            'leavedatalist', 'events',
            'manpowerTodayCount','manpowerTodayAbsentCount','manpowerTodayTotal',
            'manpowerYesterdayCount','manpowerYesterdayAbsentCount','manpowerYesterdayTotal'
        ));
    }

    public function department_attendance(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');
        $late_times = DB::table('late_types')->where('id', 2)->first();

        $today = Carbon::now()->format('Y-m-d');

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();

        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select(
            'employees.emp_id', 
            'employees.emp_name_with_initial', 
            'employees.calling_name', 
            'employees.emp_department', 
            DB::raw('MIN(attendances.timestamp) as first_checkin'), 
            DB::raw('MAX(attendances.timestamp) as lasttimestamp')
        )
        ->where('attendances.date', '=', $today)
        ->where('attendances.location', $companyId)
        ->where('attendances.deleted_at', null)
        ->havingRaw('MIN(attendances.timestamp) < ?', [$today . ' ' . $late_times->time_from])
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($attendance as $employee) {
            $departmentId = $employee->emp_department;
            $first_time = date('H:i', strtotime($employee->first_checkin));

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee->emp_id,
                    'emp_name_with_initial' => $employee->emp_name_with_initial,
                    'calling_name' => $employee->calling_name,
                    'first_checkin' => $first_time
                ];
            }
        }


            $permission = Auth::user()->can('attendance-edit');

            $htmlTables = '';

            if ($attendance->count() > 0) {

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr>';
                    $htmlTables .= '<th>#</th>';
                    $htmlTables .= '<th>Employee ID</th>';
                    $htmlTables .= '<th>Employee Name with Initial</th>';
                    $htmlTables .= '<th>In Time</th>';

                    if ($permission) {
                        $htmlTables .= '<th>Action</th>';
                    }

                    $htmlTables .= '</tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        if($permission){
                            $htmlTables .= '<td><input type="time" class="form-control form-control-sm in_time" id="in_time_' . $employee['emp_id'] . '" value="' . $employee['first_checkin'] . '"></td>';
                            $htmlTables .= '<td><button title="Edit" class="update btn btn-outline-primary btn-sm" emp-id="' . $employee['emp_id'] . '"  data-date="' . $today . '" data-existing_time_stamp="' . $employee['first_checkin'] . '"><i class="fas fa-pen"></i></button></td>';
                        }
                        else{
                            $htmlTables .= '<td>' . $employee['first_checkin'] . '</td>';
                        }
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }
            }else {
                $htmlTables = '<p>No attendance records found for the today.</p>';
            }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    public function department_lateattendance(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');

        $today = Carbon::now()->format('Y-m-d');

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();
        $late_times = DB::table('late_types')->where('id', 2)->first();
        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select(
            'employees.emp_id', 
            'employees.emp_name_with_initial', 
            'employees.calling_name', 
            'employees.emp_department', 
            DB::raw('MIN(attendances.timestamp) as first_checkin'), 
            DB::raw('MAX(attendances.timestamp) as lasttimestamp')
        )
        ->where('attendances.date', '=', $today)
        ->where('attendances.location', $companyId)
        ->where('attendances.deleted_at', null)
        // ->where('attendances.timestamp','>=', $today. ' ' . $late_times->time_from)
        ->havingRaw('MIN(attendances.timestamp) >= ?', [$today . ' ' . $late_times->time_from])
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($attendance as $employee) {
            $departmentId = $employee->emp_department;
            $first_time = date('H:i', strtotime($employee->first_checkin));

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee->emp_id,
                    'emp_name_with_initial' => $employee->emp_name_with_initial,
                    'calling_name' => $employee->calling_name,
                    'first_checkin' => $first_time
                ];
            }
        }


            $htmlTables = '';

            if ($attendance->count() > 0) {

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>In Time</th></tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        $htmlTables .= '<td>' . $employee['first_checkin'] . '</td>';
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }
            }else {
                $htmlTables = '<p>No attendance records found for the today.</p>';
            }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    public function department_absent(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');

        $today = Carbon::now()->format('Y-m-d');

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();

        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department') 
        ->where('date', '=', $today)
        ->where('location', $companyId)
        ->where('attendances.deleted_at', null)
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $employeedata= DB::table('employees')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department', 'employees.calling_name' ) 
        ->where('deleted', 0)
        ->where('is_resigned', 0)
        ->where('status', 1)
        ->where('emp_company', $companyId)
        ->get();

        $employeeMap = [];
        foreach ($employeedata as $employee) {
            $employeeMap[$employee->emp_id] = [
                'emp_id' => $employee->emp_id,
                'emp_name_with_initial' => $employee->emp_name_with_initial,
                'calling_name' => $employee->calling_name,
                'emp_department' => $employee->emp_department
            ];
        }
       
        $uniqueEmployeeData = [];
        foreach ($attendance as $attendant) {
            $employeeId = $attendant->emp_id;
            if (isset($employeeMap[$employeeId])) {
                unset($employeeMap[$employeeId]);
            }
        }
    
        foreach ($employeeMap as $employeeId => $employeeData) {
            $uniqueEmployeeData[] = $employeeData;
        }

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($uniqueEmployeeData as $employee) {
            $departmentId = $employee['emp_department'];

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee['emp_id'],
                    'calling_name' => $employee['calling_name'],
                    'emp_name_with_initial' => $employee['emp_name_with_initial']
                ];
            }
        }


            $htmlTables = '';

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th></tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    //--------------------------------------------------------------------------------
    // yesterday attendance part

    public function department_yesterdayattendance(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');

        $yesterdayDate = Carbon::now()->subDay()->format('Y-m-d');
        $late_times = DB::table('late_types')->where('id', 2)->first();

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();

        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select(
            'employees.emp_id', 
            'employees.emp_name_with_initial', 
            'employees.calling_name', 
            'employees.emp_department', 
            DB::raw('MIN(attendances.timestamp) as first_checkin'), 
            DB::raw('MAX(attendances.timestamp) as lasttimestamp')
        )
        ->where('attendances.date', '=', $yesterdayDate)
        ->where('attendances.location', $companyId)
        ->where('attendances.deleted_at', null)
        ->groupBy('attendances.date','attendances.uid')
        ->havingRaw('MIN(attendances.timestamp) < ?', [$yesterdayDate . ' ' . $late_times->time_from])
        ->get();

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($attendance as $employee) {
            $departmentId = $employee->emp_department;
            $first_time = date('H:i', strtotime($employee->first_checkin));
            $last_time = date('H:i', strtotime($employee->lasttimestamp));

            if($first_time==$last_time){
                $last_time='00-00';
            }

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee->emp_id,
                    'emp_name_with_initial' => $employee->emp_name_with_initial,
                    'calling_name' => $employee->calling_name,
                    'first_checkin' => $first_time,
                    'lasttimestamp' => $last_time
                ];
            }
        }


            $htmlTables = '';

            if ($attendance->count() > 0) {

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>In Time</th><th>Out Time</th></tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        $htmlTables .= '<td>' . $employee['first_checkin'] . '</td>';
                        $htmlTables .= '<td>' . $employee['lasttimestamp'] . '</td>';
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }
            }else {
                $htmlTables = '<p>No attendance records found for the yesterday.</p>';
            }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    public function department_yesterdaylateattendance(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');

        $yesterdayDate = Carbon::now()->subDay()->format('Y-m-d');

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();
        $late_times = DB::table('late_types')->where('id', 2)->first();
        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select(
            'employees.emp_id', 
            'employees.emp_name_with_initial', 
            'employees.calling_name',
            'employees.emp_department', 
            DB::raw('MIN(attendances.timestamp) as first_checkin'), 
            DB::raw('MAX(attendances.timestamp) as lasttimestamp')
        )
        ->where('attendances.date', '=', $yesterdayDate)
        ->where('attendances.location', $companyId)
        ->where('attendances.deleted_at', null)
        ->havingRaw('MIN(attendances.timestamp) >= ?', [$yesterdayDate . ' ' . $late_times->time_from])
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($attendance as $employee) {
            $departmentId = $employee->emp_department;
            $first_time = date('H:i', strtotime($employee->first_checkin));
            $last_time = date('H:i', strtotime($employee->lasttimestamp));

            if($first_time==$last_time){
                $last_time='00-00';
            }

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee->emp_id,
                    'emp_name_with_initial' => $employee->emp_name_with_initial,
                    'calling_name' => $employee->calling_name,
                    'first_checkin' => $first_time,
                    'lasttimestamp' => $last_time
                ];
            }
        }


            $htmlTables = '';

            if ($attendance->count() > 0) {

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>In Time</th><th>Out Time</th></tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        $htmlTables .= '<td>' . $employee['first_checkin'] . '</td>';
                        $htmlTables .= '<td>' . $employee['lasttimestamp'] . '</td>';
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }
            }else {
                $htmlTables = '<p>No attendance records found for the yesterday.</p>';
            }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    public function department_yesterdayabsent(){
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');

        $yesterdayDate = Carbon::now()->subDay()->format('Y-m-d');

        $departmentdata = DB::table('departments')
        ->select('id', 'name') 
        ->get()
        ->toArray();

        $attendance= DB::table('attendances')
        ->leftjoin('employees', 'attendances.uid', '=', 'employees.emp_id')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department') 
        ->where('date', '=', $yesterdayDate)
        ->where('location', $companyId)
        ->where('attendances.deleted_at', null)
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $employeedata= DB::table('employees')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department', 'employees.calling_name' ) 
        ->where('deleted', 0)
        ->where('status', 1)
        ->where('is_resigned', 0)
        ->where('emp_company', $companyId)
        ->get();

        $employeeMap = [];
        foreach ($employeedata as $employee) {
            $employeeMap[$employee->emp_id] = [
                'emp_id' => $employee->emp_id,
                'calling_name' => $employee->calling_name,
                'emp_name_with_initial' => $employee->emp_name_with_initial,
                'emp_department' => $employee->emp_department
            ];
        }
       
        $uniqueEmployeeData = [];
        foreach ($attendance as $attendant) {
            $employeeId = $attendant->emp_id;
            if (isset($employeeMap[$employeeId])) {
                unset($employeeMap[$employeeId]);
            }
        }
    
        foreach ($employeeMap as $employeeId => $employeeData) {
            $uniqueEmployeeData[] = $employeeData;
        }

        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($uniqueEmployeeData as $employee) {
            $departmentId = $employee['emp_department'];

            if (isset($departmentMap[$departmentId])) {
                if (!isset($employeesByDepartment[$departmentMap[$departmentId]])) {
                    $employeesByDepartment[$departmentMap[$departmentId]] = [];
                }
                
                $employeesByDepartment[$departmentMap[$departmentId]][] = [
                    'emp_id' => $employee['emp_id'],
                    'calling_name' => $employee['calling_name'],
                    'emp_name_with_initial' => $employee['emp_name_with_initial']
                ];
            }
        }

        
            $htmlTables = '';

                foreach ($employeesByDepartment as $departmentName => $employees) {
                    $count=1;
                    $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';

                    $htmlTables .= '<h5>' . $departmentName . '</h5>';
                    $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th></tr>';
                
                    foreach ($employees as $employee) {
                        $htmlTables .= '<tr>';
                        $htmlTables .= '<td>' . $count . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                        $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . ' - '.$employee['calling_name'].'</td>';
                        $htmlTables .= '</tr>';

                        $count=$count+1;
                    }
                    $htmlTables .= '</table>';
                    $htmlTables .= '<hr style="border-top: 1px solid black;">';
                }

        
        return response() ->json(['result'=>  $htmlTables]);

    }

    //----------------------------------------------------------------------------------
    // MANPOWER ATTENDANCE DASHBOARD METHODS
    //----------------------------------------------------------------------------------
    private function getManpowerAttendanceSub($date, array $empNos)
    {
        if (empty($empNos)) {
            return collect();
        }

        return DB::table(DB::raw('(
            SELECT `at1`.`uid`, `at1`.`date`,
                   MIN(`at1`.`timestamp`) AS `first_time_stamp`,
                   CASE
                       WHEN MIN(`at1`.`timestamp`) = MAX(`at1`.`timestamp`) THEN NULL
                       ELSE MAX(`at1`.`timestamp`)
                   END AS `last_time_stamp`
            FROM `attendances` AS `at1`
            WHERE `at1`.`deleted_at` IS NULL
            GROUP BY `at1`.`uid`, `at1`.`date`
        ) AS `sub`'))
        ->whereIn('sub.uid', $empNos)
        ->where('sub.date', $date)
        ->get()
        ->keyBy('uid');
    }

    public function manpower_today_attendance()
    {
        $today     = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');

        // All active manpower cards indexed by emp_no
        $cards = DB::table('manpower_cards')->where('status', 1)->get()->keyBy('emp_no');
        $empNos = $cards->keys()->toArray();

        // Attendance subquery for today
        $attendanceSub = $this->getManpowerAttendanceSub($today, $empNos);

        // manpower_employee_details for today, keyed by card_id
        $detailsToday = DB::table('manpower_employee_details')
            ->where('date', $today)
            ->where('status', '!=', 3)
            ->get()
            ->keyBy('card_id');

        // manpower_employee_details for yesterday with off_next_day=1, keyed by card_id
        $detailsYesterday = DB::table('manpower_employee_details')
            ->where('date', $yesterday)
            ->where('off_next_day', 1)
            ->where('status', '!=', 3)
            ->get()
            ->keyBy('card_id');

        // Previous-day attendance for off_next_day cards (out time = yesterday last_time_stamp)
        $prevAttendanceSub = $this->getManpowerAttendanceSub($yesterday, $empNos);

        $rows = [];
        foreach ($attendanceSub as $empNo => $att) {
            // Find card
            $card = $cards->get($empNo);
            if (!$card) continue;

            $cardId   = $card->id;
            $cardNo   = $card->card_no;
            $employee = isset($detailsToday[$cardId]) ? $detailsToday[$cardId]->employee : '-';

            // In time: check off_next_day for previous day
            if (isset($detailsYesterday[$cardId])) {
                // In time = out time of previous day attendance (with yesterday's date)
                $prevAtt = $prevAttendanceSub->get($empNo);
                if ($prevAtt && $prevAtt->last_time_stamp) {
                    $inTime = date('Y-m-d H:i', strtotime($prevAtt->last_time_stamp));
                } else {
                    $inTime = $att->first_time_stamp ? date('Y-m-d H:i', strtotime($att->first_time_stamp)) : '-';
                }
            } else {
                $inTime = $att->first_time_stamp ? date('Y-m-d H:i', strtotime($att->first_time_stamp)) : '-';
            }

            $outTime = $att->last_time_stamp ? date('Y-m-d H:i', strtotime($att->last_time_stamp)) : '-';

            $rows[] = [
                'card_no'  => $cardNo,
                'employee' => $employee,
                'in_time'  => $inTime,
                'out_time' => $outTime,
            ];
        }

        $html = '';
        if (count($rows) > 0) {
            $html .= '<table class="table table-striped table-bordered table-sm small">';
            $html .= '<thead><tr><th>#</th><th>Card No</th><th>Employee</th><th>In Time</th><th>Out Time</th></tr></thead><tbody>';
            foreach ($rows as $i => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . e($row['card_no']) . '</td>';
                $html .= '<td>' . e($row['employee']) . '</td>';
                $html .= '<td>' . e($row['in_time']) . '</td>';
                $html .= '<td>' . e($row['out_time']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html = '<p>No manpower attendance records found for today.</p>';
        }

        return response()->json(['result' => $html]);
    }

    public function manpower_today_absent()
    {
        $today  = Carbon::now()->format('Y-m-d');
        $empNos = DB::table('manpower_cards')->where('status', 1)->pluck('emp_no')->toArray();

        // Cards allocated for today via manpower_employee_details
        $allocations = DB::table('manpower_employee_details')
            ->where('date', $today)
            ->where('status', '!=', 3)
            ->get();

        // emp_nos present today
        $presentEmpNos = DB::table('attendances')
            ->whereIn('uid', $empNos)
            ->where('date', $today)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->pluck('uid')
            ->toArray();

        // Index cards by id
        $cardsById = DB::table('manpower_cards')->where('status', 1)->get()->keyBy('id');

        $rows = [];
        foreach ($allocations as $detail) {
            $card = $cardsById->get($detail->card_id);
            if (!$card) continue;
            // Absent = not in present list
            if (!in_array($card->emp_no, $presentEmpNos)) {
                $rows[] = [
                    'card_no'  => $card->card_no,
                    'employee' => $detail->employee,
                ];
            }
        }

        $html = '';
        if (count($rows) > 0) {
            $html .= '<table class="table table-striped table-bordered table-sm small">';
            $html .= '<thead><tr><th>#</th><th>Card No</th><th>Employee</th></tr></thead><tbody>';
            foreach ($rows as $i => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . e($row['card_no']) . '</td>';
                $html .= '<td>' . e($row['employee']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html = '<p>No manpower absent records found for today.</p>';
        }

        return response()->json(['result' => $html]);
    }

    public function manpower_yesterday_attendance()
    {
        $yesterday    = Carbon::now()->subDay()->format('Y-m-d');
        $dayBefore    = Carbon::now()->subDays(2)->format('Y-m-d');

        $cards = DB::table('manpower_cards')->where('status', 1)->get()->keyBy('emp_no');
        $empNos = $cards->keys()->toArray();

        $attendanceSub = $this->getManpowerAttendanceSub($yesterday, $empNos);

        $detailsYesterday = DB::table('manpower_employee_details')
            ->where('date', $yesterday)
            ->where('status', '!=', 3)
            ->get()
            ->keyBy('card_id');

        $detailsDayBefore = DB::table('manpower_employee_details')
            ->where('date', $dayBefore)
            ->where('off_next_day', 1)
            ->where('status', '!=', 3)
            ->get()
            ->keyBy('card_id');

        $prevAttendanceSub = $this->getManpowerAttendanceSub($dayBefore, $empNos);

        $rows = [];
        foreach ($attendanceSub as $empNo => $att) {
            $card = $cards->get($empNo);
            if (!$card) continue;

            $cardId   = $card->id;
            $cardNo   = $card->card_no;
            $employee = isset($detailsYesterday[$cardId]) ? $detailsYesterday[$cardId]->employee : '-';

            if (isset($detailsDayBefore[$cardId])) {
                // In time = out time of day-before attendance (with day-before's date)
                $prevAtt = $prevAttendanceSub->get($empNo);
                if ($prevAtt && $prevAtt->last_time_stamp) {
                    $inTime = date('Y-m-d H:i', strtotime($prevAtt->last_time_stamp));
                } else {
                    $inTime = $att->first_time_stamp ? date('Y-m-d H:i', strtotime($att->first_time_stamp)) : '-';
                }
            } else {
                $inTime = $att->first_time_stamp ? date('Y-m-d H:i', strtotime($att->first_time_stamp)) : '-';
            }

            $outTime = $att->last_time_stamp ? date('Y-m-d H:i', strtotime($att->last_time_stamp)) : '-';

            $rows[] = [
                'card_no'  => $cardNo,
                'employee' => $employee,
                'in_time'  => $inTime,
                'out_time' => $outTime,
            ];
        }

        $html = '';
        if (count($rows) > 0) {
            $html .= '<table class="table table-striped table-bordered table-sm small">';
            $html .= '<thead><tr><th>#</th><th>Card No</th><th>Employee</th><th>In Time</th><th>Out Time</th></tr></thead><tbody>';
            foreach ($rows as $i => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . e($row['card_no']) . '</td>';
                $html .= '<td>' . e($row['employee']) . '</td>';
                $html .= '<td>' . e($row['in_time']) . '</td>';
                $html .= '<td>' . e($row['out_time']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html = '<p>No manpower attendance records found for yesterday.</p>';
        }

        return response()->json(['result' => $html]);
    }

    public function manpower_yesterday_absent()
    {
        $yesterday = Carbon::now()->subDay()->format('Y-m-d');
        $empNos    = DB::table('manpower_cards')->where('status', 1)->pluck('emp_no')->toArray();

        $allocations = DB::table('manpower_employee_details')
            ->where('date', $yesterday)
            ->where('status', '!=', 3)
            ->get();

        $presentEmpNos = DB::table('attendances')
            ->whereIn('uid', $empNos)
            ->where('date', $yesterday)
            ->where('deleted_at', null)
            ->distinct('uid')
            ->pluck('uid')
            ->toArray();

        $cardsById = DB::table('manpower_cards')->where('status', 1)->get()->keyBy('id');

        $rows = [];
        foreach ($allocations as $detail) {
            $card = $cardsById->get($detail->card_id);
            if (!$card) continue;
            if (!in_array($card->emp_no, $presentEmpNos)) {
                $rows[] = [
                    'card_no'  => $card->card_no,
                    'employee' => $detail->employee,
                ];
            }
        }

        $html = '';
        if (count($rows) > 0) {
            $html .= '<table class="table table-striped table-bordered table-sm small">';
            $html .= '<thead><tr><th>#</th><th>Card No</th><th>Employee</th></tr></thead><tbody>';
            foreach ($rows as $i => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . e($row['card_no']) . '</td>';
                $html .= '<td>' . e($row['employee']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html = '<p>No manpower absent records found for yesterday.</p>';
        }

        return response()->json(['result' => $html]);
    }

    //----------------------------------------------------------------------------------

    public function getAttendentChart(Request $request)
    {
        $companyId = Session::get('company_id');
        $branchId = Session::get('company_branch_id');

        // Generate last 30 dates from today (including today)
    
        // // Generate last 30 dates directly as a single SQL UNION string (no Carbon loop)
        // $datesQuery = collect(range(0, 29))->map(function ($i) {
        //     $date = \Carbon\Carbon::today()->subDays($i)->toDateString();
        //     return "SELECT DATE('$date') AS date";
        // })->implode(" UNION ALL ");

        // // Main SQL
        // $sql = "
        // SELECT 
        //     d.date,
        //     COUNT(DISTINCT a.uid) AS count
        // FROM 
        //     (
        //         $datesQuery
        //     ) AS d
        // JOIN employees e 
        //     ON e.deleted = 0 
        //     AND e.emp_company = :companyId 
        //     AND e.emp_location = :branchId
        // LEFT JOIN attendances a 
        //     ON a.uid = e.emp_id 
        //     AND a.date = d.date
        // GROUP BY d.date
        // ORDER BY d.date ASC
        // ";

        // // Execute the query with bindings
        // $data = DB::select($sql, [
        //     'companyId' => $companyId,
        //     'branchId' => $branchId,
        // ]);
        
        $sql = "
            SELECT
            A.report_date,
            COALESCE(A.active_employee_count, 0) AS active_employee_count,
            COALESCE(P.unique_employees_present, 0) AS unique_employees_present,
            COALESCE(A.active_employee_count, 0) - COALESCE(P.unique_employees_present, 0) AS absent_count
        FROM
            (
                SELECT
                    ds.date_value AS report_date,
                    -- Count of unique National IDs (which are not NULL/empty)
                    COUNT(DISTINCT CASE WHEN e.emp_national_id IS NOT NULL AND e.emp_national_id != '' THEN e.emp_national_id END) 
                    +
                    -- Count of records where the National ID is missing (counted by their unique primary key ID)
                    COUNT(DISTINCT CASE WHEN e.emp_national_id IS NULL OR e.emp_national_id = '' THEN e.id END)
                    AS active_employee_count
                FROM
                    (
                        -- DateSeries
                        SELECT 
                            DATE(CURRENT_DATE() - INTERVAL (A.a + (10 * B.a)) DAY) AS date_value
                        FROM 
                            (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS A
                        JOIN 
                            (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2) AS B 
                        WHERE 
                            (A.a + (10 * B.a)) < 30
                    ) AS ds
                LEFT JOIN
                    employees e ON
                        e.emp_join_date <= ds.date_value
                        AND (e.resignation_date IS NULL OR e.resignation_date > ds.date_value)
                        AND e.emp_company = ? -- Binding 1
                        AND e.emp_location = ? -- Binding 2
                        AND e.deleted = 0
                GROUP BY
                    ds.date_value
            ) AS A
        LEFT JOIN
            (
                SELECT
                    ds.date_value AS report_date,
                    COUNT(DISTINCT a.uid) AS unique_employees_present
                FROM
                    (
                        -- DateSeries
                        SELECT 
                            DATE(CURRENT_DATE() - INTERVAL (A.a + (10 * B.a)) DAY) AS date_value
                        FROM 
                            (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS A
                        JOIN 
                            (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2) AS B 
                        WHERE 
                            (A.a + (10 * B.a)) < 30
                    ) AS ds
                LEFT JOIN
                    attendances a ON
                        a.date = ds.date_value
                        AND a.location = ? -- Binding 3
                        AND a.deleted_at IS NULL
                GROUP BY
                    ds.date_value
            ) AS P ON A.report_date = P.report_date
        ORDER BY
            A.report_date DESC;
        ";
        
        $bindings = [
            $companyId, // For e.emp_company
            $branchId,   // For e.emp_location
            $companyId    // For a.location
        ];

        $reportData = DB::select($sql, $bindings);

        return $reportData;
    }


    // public function getAttendentChart(Request $request)
    //     {
    //         $companyId = Session::get('company_id');
    //         $companyName = Session::get('company_name');
    //         $companyBranchId = Session::get('company_branch_id');
    //         $companyBranchName = Session::get('company_branch_name');

    //         $data = DB::table('attendances')
    //             ->join('employees', 'attendances.uid', '=', 'employees.emp_id')
    //             ->select('attendances.date', DB::raw('COUNT(DISTINCT attendances.uid) as count'))
    //             ->where('employees.deleted', 0)
    //             ->where('employees.emp_company', $companyId)
    //             ->groupBy('attendances.date')
    //             ->limit(30)
    //             ->orderBy('attendances.date', 'desc')
    //             ->get();

    //         return response()->json($data);

    //     }


    public function dashboard_attend_update(Request $request)
    {
        $user = Auth::user();
        $permission = $user->can('attendance-edit');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }
        $today = Carbon::now()->format('Y-m-d 00:00:00');
        $date = Carbon::now()->format('Y-m-d');
        $rules = array(
            'uid' => 'required',
            'timestamp' => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if ($error->fails()) {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $existing_time=Carbon::now()->format('Y-m-d '.$request->existing_time_stamp.':00');
        $new_time=Carbon::now()->format('Y-m-d '.$request->timestamp.':00');
        $attendance = Attendance::where('uid', $request->uid)
        ->where('date', $today)
        ->where('timestamp', $existing_time)->first();

        if ($attendance) {
            $prev_timestamp = $attendance->timestamp;
        } 

        $attendance->timestamp = $new_time;
        $attendance->edit_status = '1';

        $attendance->save();

        $log_data = array(
            'attendance_id' => $attendance->id,
            'emp_id' => $attendance->emp_id,
            'date' => $date,
            'prev_val' => $prev_timestamp,
            'new_val' => $new_time,
            'edited_user_id' => Auth::user()->id,
        );

        AttendanceEdited::create($log_data);

        return response()->json(['success' => 'Data is successfully updated']);
    }

    // --------------------------------------------------------------------------------------------------------------
    public function emp_work_days(Request $request) {
        $emp_working_days = $request->input('emp_working_days'); // Get filter value from request
        $today = Carbon::now(); // Current date
        $companyId = Session::get('company_id');

        // Fetch department data
        $departmentdata = DB::table('departments')
            ->select('id', 'name')
            ->get()
            ->toArray();

        // Fetch employees
        $employeedata = DB::table('employees')
            ->select('employees.emp_id', 'employees.emp_name_with_initial', 'employees.emp_department', 'employees.emp_join_date')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->where('emp_company', $companyId)
            ->get();

        // Filter employees based on working days
        $filteredEmployees = [];
        foreach ($employeedata as $employee) {
            if (!empty($employee->emp_join_date)) {
                $joinDate = Carbon::parse($employee->emp_join_date);
                $workingDays = $today->diffInDays($joinDate); // Calculate working days

                if ($workingDays >= $emp_working_days) { // Filter based on selected working days
                    $filteredEmployees[] = [
                        'emp_id' => $employee->emp_id,
                        'emp_name_with_initial' => $employee->emp_name_with_initial,
                        'emp_department' => $employee->emp_department,
                        // 'workDays' => $workingDays // Pass calculated working days
                    ];
                }
            }
        }

        // Group by department
        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($filteredEmployees as $employee) {
            $departmentId = $employee['emp_department'];

            if (isset($departmentMap[$departmentId])) {
                $departmentName = $departmentMap[$departmentId];
                if (!isset($employeesByDepartment[$departmentName])) {
                    $employeesByDepartment[$departmentName] = [];
                }

                $employeesByDepartment[$departmentName][] = $employee;
            }
        }

        // Generate HTML table
        $htmlTables = '';
        foreach ($employeesByDepartment as $departmentName => $employees) {
            $count = 1;
            $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';
            $htmlTables .= '<h5>' . $departmentName . '</h5>';
            $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name</th></tr>';

            foreach ($employees as $employee) {
                $htmlTables .= '<tr>';
                $htmlTables .= '<td>' . $count . '</td>';
                $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . '</td>';
                $htmlTables .= '</tr>';

                $count++;
            }

            $htmlTables .= '</table>';
            $htmlTables .= '<hr style="border-top: 1px solid black;">';
        }

        return response()->json([
            'result' => $htmlTables
        ]);
    }

    // --------------------------------------------------------------------------------------------------------------


    public function today_birthday() {
        $company = Session::get('company_id');
        $today = Carbon::now(); // Current date
        $currentMonth = $today->month;
        $currentDay = $today->day;
    
        // Fetch department data
        $departmentdata = DB::table('departments')
            ->select('id', 'name')
            ->get()
            ->toArray();
    
        // Fetch employees
        $employeedata = DB::table('employees')
            ->select('employees.emp_id', 'employees.emp_name_with_initial', 'employees.emp_department', 'employees.emp_birthday')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->where('employees.emp_company', $company)
            ->get();
    
        // Filter employees whose birthday is today
        $filteredEmployees = [];
        foreach ($employeedata as $employee) {
            if (!empty($employee->emp_birthday)) {
                $birthday = Carbon::parse($employee->emp_birthday);
    
                // Match current month and day
                if ($birthday->month == $currentMonth && $birthday->day == $currentDay) {
                    $filteredEmployees[] = [
                        'emp_id' => $employee->emp_id,
                        'emp_name_with_initial' => $employee->emp_name_with_initial,
                        'emp_department' => $employee->emp_department,
                        'emp_birthday' => $birthday->format('Y-m-d')
                    ];
                }
            }
        }
    
        
        $todayBirthdayCount = count($filteredEmployees);
    
        // Group by department
        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }
    
        $employeesByDepartment = [];
        foreach ($filteredEmployees as $employee) {
            $departmentId = $employee['emp_department'];
    
            if (isset($departmentMap[$departmentId])) {
                $departmentName = $departmentMap[$departmentId];
                if (!isset($employeesByDepartment[$departmentName])) {
                    $employeesByDepartment[$departmentName] = [];
                }
    
                $employeesByDepartment[$departmentName][] = $employee;
            }
        }
    
        // Generate HTML for Birthday Table
        $htmlTables = '';
        
        foreach ($employeesByDepartment as $departmentName => $employees) {
            $count = 1;
            $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';
            $htmlTables .= '<h5>' . $departmentName . '</h5>';
            $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>Birthday</th></tr>';
    
            foreach ($employees as $employee) {
                $htmlTables .= '<tr>';
                $htmlTables .= '<td>' . $count . '</td>';
                $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . '</td>';
                $htmlTables .= '<td>' . $employee['emp_birthday'] . '</td>';
                $htmlTables .= '</tr>';
    
                $count++;
            }
    
            $htmlTables .= '</table>';
            $htmlTables .= '<hr style="border-top: 1px solid black;">';
        }

        if ($todayBirthdayCount == 0) {
            $htmlTables .= '<p>No employees have birthdays today.</p>';
        }
    
       
        return response()->json([
            'result' => $htmlTables,
            'todayBirthdayCount' => $todayBirthdayCount
        ]);
    }
    

    public function thisweek_birthday() {
        $startOfWeek = Carbon::now()->startOfWeek()->format('m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('m-d');
        $company = Session::get('company_id');
    
        $thisweekBirthdayCount = DB::table('employees')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->where('employees.emp_company', $company)
            ->whereBetween(DB::raw('DATE_FORMAT(emp_birthday, "%m-%d")'), [$startOfWeek, $endOfWeek])
            ->count();
    
        $employeesByDepartment = DB::table('employees')
            ->select('emp_id', 'emp_name_with_initial', 'emp_department', 'emp_birthday')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->whereBetween(DB::raw('DATE_FORMAT(emp_birthday, "%m-%d")'), [$startOfWeek, $endOfWeek])
            ->get()
            ->groupBy('emp_department');
    
        $departmentdata = DB::table('departments')
            ->select('id', 'name')
            ->get()
            ->keyBy('id');
    
        $htmlTables = '';
        foreach ($employeesByDepartment as $departmentId => $employees) {
            $departmentName = isset($departmentdata[$departmentId]) ? $departmentdata[$departmentId]->name : 'Unknown Department';
            $count = 1;
    
            $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';
            $htmlTables .= '<h5>' . $departmentName . '</h5>';
            $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>Birthday</th></tr>';
    
            foreach ($employees as $employee) {
                $htmlTables .= '<tr>';
                $htmlTables .= '<td>' . $count . '</td>';
                $htmlTables .= '<td>' . $employee->emp_id . '</td>';
                $htmlTables .= '<td>' . $employee->emp_name_with_initial . '</td>';
                $htmlTables .= '<td>' . Carbon::parse($employee->emp_birthday)->format('Y-m-d') . '</td>';
                $htmlTables .= '</tr>';
                $count++;
            }
    
            $htmlTables .= '</table>';
            $htmlTables .= '<hr style="border-top: 1px solid black;">';
        }
    
        if ($thisweekBirthdayCount == 0) {
            $htmlTables .= '<p>No employees have birthdays this week.</p>';
        }
    
        return response()->json([
            'result' => $htmlTables,
            'thisweekBirthdayCount' => $thisweekBirthdayCount
        ]);
    }
    
    public function thismonth_birthday() {
        $today = Carbon::now(); // Current date
        $currentMonth = $today->month;
        $company = Session::get('company_id');

        // Fetch department data
        $departmentdata = DB::table('departments')
            ->select('id', 'name')
            ->get()
            ->toArray();

        // Fetch employees
        $employeedata = DB::table('employees')
            ->select('employees.emp_id', 'employees.emp_name_with_initial', 'employees.emp_department', 'employees.emp_birthday')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->where('employees.emp_company', $company)
            ->get();

        // Filter employees whose birthday is today
        $filteredEmployees = [];
        foreach ($employeedata as $employee) {
            if (!empty($employee->emp_birthday)) {
                $birthday = Carbon::parse($employee->emp_birthday);

                // Match current month and day
                if ($birthday->month == $currentMonth) {
                    $filteredEmployees[] = [
                        'emp_id' => $employee->emp_id,
                        'emp_name_with_initial' => $employee->emp_name_with_initial,
                        'emp_department' => $employee->emp_department,
                        'emp_birthday' => $birthday->format('Y-m-d')
                    ];
                }
            }
        }

        $thismonthBirthdayCount = count($filteredEmployees);

        // Group by department
        $departmentMap = [];
        foreach ($departmentdata as $department) {
            $departmentMap[$department->id] = $department->name;
        }

        $employeesByDepartment = [];
        foreach ($filteredEmployees as $employee) {
            $departmentId = $employee['emp_department'];

            if (isset($departmentMap[$departmentId])) {
                $departmentName = $departmentMap[$departmentId];
                if (!isset($employeesByDepartment[$departmentName])) {
                    $employeesByDepartment[$departmentName] = [];
                }

                $employeesByDepartment[$departmentName][] = $employee;
            }
        }

        // Generate HTML
        $htmlTables = '';
        foreach ($employeesByDepartment as $departmentName => $employees) {
            $count = 1;
            $htmlTables .= '<table class="table table-striped table-bordered table-sm small">';
            $htmlTables .= '<h5>' . $departmentName . '</h5>';
            $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name with Initial</th><th>Birthday</th></tr>';

            foreach ($employees as $employee) {
                $htmlTables .= '<tr>';
                $htmlTables .= '<td>' . $count . '</td>';
                $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
                $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . '</td>';
                $htmlTables .= '<td>' . $employee['emp_birthday'] . '</td>';
                $htmlTables .= '</tr>';

                $count++;
            }

            $htmlTables .= '</table>';
            $htmlTables .= '<hr style="border-top: 1px solid black;">';
        }

        if ($thismonthBirthdayCount == 0) {
            $htmlTables .= '<p>No employees have birthdays this month.</p>';
        }

        return response()->json([
            'result' => $htmlTables,
            'thismonthBirthdayCount' => $thismonthBirthdayCount
        ]);
    }


    //----------------------------------------------------------------------------------
}
