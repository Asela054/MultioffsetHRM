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
        $user = Auth::user();
        $departments = $user->departments;

         $today = Carbon::now()->format('Y-m-d');
         $empcount = DB::table('employees')->where('deleted', 0)->where('is_resigned', 0)->where('emp_company', $companyId)->count();
        //  $todaycount = Attendance::where('date','2023-09-18')->groupBy('date','emp_id')->count();

        // today attendance count
         $todaycount = DB::table('attendances')
        ->select('date', 'emp_id')
        ->where('date', $today)
        ->where('location', $companyId)
        ->where('deleted_at', NULL)
        ->havingRaw('MIN(attendances.timestamp) <= ?', [$today . ' ' . $late_times->time_from])
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

        // Today's Birthday Count
        $todayBirthdayCount = DB::table('employees')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
            ->whereMonth('emp_birthday', $currentMonth)
            ->whereDay('emp_birthday', $currentDay)
            ->count();

        // This Week's Birthday Count
        $thisweekBirthdayCount = DB::table('employees')
        ->where('deleted', 0)
        ->where('is_resigned', 0)
        ->whereBetween(DB::raw('DATE_FORMAT(emp_birthday, "%m-%d")'), [
            Carbon::now()->startOfWeek()->format('m-d'),
            Carbon::now()->endOfWeek()->format('m-d'),
        ])
        ->count();

        $thismonthBirthdayCount = DB::table('employees')
        ->where('deleted', 0)
        ->where('is_resigned', 0)
        ->whereMonth('emp_birthday', $currentMonth)
        ->count();


        return view('home',compact('empcount','todaycount','todaylatecount','yesterdaycount','yesterdaylatecount','todayBirthdayCount','thisweekBirthdayCount','thismonthBirthdayCount'));
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
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $employeedata= DB::table('employees')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department', 'employees.calling_name' ) 
        ->where('deleted', 0)
        ->where('is_resigned', 0)
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
        ->groupBy('attendances.date','attendances.uid')
        ->get();

        $employeedata= DB::table('employees')
        ->select('employees.emp_id', 'employees.emp_name_with_initial','employees.emp_department', 'employees.calling_name' ) 
        ->where('deleted', 0)
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

 public function getAttendentChart(Request $request)
{
    $companyId = Session::get('company_id');
    $branchId = Session::get('company_branch_id');

    // Generate last 30 dates from today (including today)
    $dates = [];
    for ($i = 0; $i < 30; $i++) {
        $dates[] = Carbon::today()->subDays($i)->format('Y-m-d');
    }

    // Convert to SQL-friendly union table
    $datesQuery = collect($dates)->map(function ($d) {
        return "SELECT DATE('{$d}') AS date";
    })->implode(" UNION ALL ");

    $sql = "
        SELECT 
            d.date,
            COUNT(DISTINCT a.uid) AS count
        FROM 
            ({$datesQuery}) d
        CROSS JOIN 
            employees e
        LEFT JOIN 
            attendances a ON a.uid = e.emp_id AND a.date = d.date
        WHERE 
            e.deleted = 0 AND 
            e.emp_company = :companyId AND 
            e.emp_location = :branchId
        GROUP BY 
            d.date
        ORDER BY 
            d.date ASC
    ";

    $data = DB::select($sql, [
        'companyId' => $companyId,
        'branchId' => $branchId,
    ]);

    return response()->json($data);
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
                    'workDays' => $workingDays // Pass calculated working days
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
        $htmlTables .= '<tr><th>#</th><th>Employee ID</th><th>Employee Name</th><th>Working Days</th></tr>';

        foreach ($employees as $employee) {
            $htmlTables .= '<tr>';
            $htmlTables .= '<td>' . $count . '</td>';
            $htmlTables .= '<td>' . $employee['emp_id'] . '</td>';
            $htmlTables .= '<td>' . $employee['emp_name_with_initial'] . '</td>';
            $htmlTables .= '<td>' . $employee['workDays'] . '</td>';
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
    
        $thisweekBirthdayCount = DB::table('employees')
            ->where('deleted', 0)
            ->where('is_resigned', 0)
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
