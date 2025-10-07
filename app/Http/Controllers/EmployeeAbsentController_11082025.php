<?php

namespace App\Http\Controllers;

use App\Holiday;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Datatables;
use DB;
use Session;

class EmployeeAbsentController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function employee_absent_report()
    {
        $permission = Auth::user()->can('employee-absent-report');
        if (!$permission) {
            abort(403);
        }
        $departments=DB::table('departments')->select('*')->get();
        return view('Report.employee_absent_report',compact('departments'));
    }

    
    public function get_absent_employees(Request $request)
{
    $companyId = Session::get('company_id');
    $userDepartmentIds = Auth::user()->departments->pluck('id');
    $fromDate = Carbon::parse($request->input('selectdatefrom'));
    $toDate = Carbon::parse($request->input('selectdateto'));
    $department = $request->input('department');
    
    // Get all employees active during the period
    $employeeQuery = DB::table('employees')
        ->leftJoin('departments', 'employees.emp_department', '=', 'departments.id')
        ->leftJoin('branches', 'employees.emp_location', '=', 'branches.id')
        ->select(
            'employees.emp_id',
            'employees.emp_name_with_initial',
            'employees.emp_department',
            'departments.name AS departmentname',
            'branches.location AS location',
            'employees.is_resigned',
            'employees.resignation_date'
        )
        ->where('employees.deleted', 0)
        ->where('employees.emp_company', $companyId)
        ->where(function($query) use ($fromDate, $toDate) {
            $query->where('is_resigned', 0)
                ->orWhere(function($q) use ($fromDate, $toDate) {
                    $q->where('is_resigned', 1)
                        ->whereDate('resignation_date', '>=', $fromDate)
                        ->whereDate('resignation_date', '<=', $toDate);
                });
        });

    // Apply department filters
    if ($department != 'All') {
        $employeeQuery->where('employees.emp_department', $department);
    }
    if (!$userDepartmentIds->isEmpty()) {
        $employeeQuery->whereIn('employees.emp_department', $userDepartmentIds);
    }

    $employees = $employeeQuery->get();

    if ($employees->isEmpty()) {
        return Datatables::of([])->make(true);
    }

    // Create employee map with resignation info
    $employeeMap = [];
    foreach ($employees as $employee) {
        $employeeMap[$employee->emp_id] = [
            'emp_id' => $employee->emp_id,
            'emp_name_with_initial' => $employee->emp_name_with_initial,
            'emp_department' => $employee->emp_department,
            'departmentname' => $employee->departmentname,
            'location' => $employee->location,
            'is_resigned' => $employee->is_resigned,
            'resignation_date' => $employee->resignation_date
        ];
    }
    $employeeIds = array_keys($employeeMap);

    // Get all holidays in the date range
    $holidays = Holiday::where('work_level', '2')
        ->whereDate('date', '>=', $fromDate)
        ->whereDate('date', '<=', $toDate)
        ->pluck('date')
        ->toArray();

    // Generate valid working dates (exclude weekends and holidays)
    $validDates = [];
    $currentDate = clone $fromDate;
    while ($currentDate <= $toDate) {
        if (!$currentDate->isWeekend() && !in_array($currentDate->format('Y-m-d'), $holidays)) {
            $validDates[] = $currentDate->format('Y-m-d');
        }
        $currentDate->addDay();
    }

    // Get all attendance records in one query
    $attendanceRecords = DB::table('attendances')
        ->whereIn('uid', $employeeIds)
        ->whereIn(DB::raw('DATE(date)'), $validDates)
        ->select('uid', DB::raw('DATE(date) as date'))
        ->distinct()
        ->get()
        ->groupBy('date')
        ->map(function ($records) {
            return $records->pluck('uid')->toArray();
        })
        ->toArray();

    // Identify absent employees
    $absentEmployeesByDate = [];
    foreach ($validDates as $date) {
        $presentIds = $attendanceRecords[$date] ?? [];
        $absentEmployees = [];

        foreach ($employeeMap as $empId => $employee) {
            // Skip if employee resigned before this date
            if ($employee['is_resigned'] == 1 && 
                $employee['resignation_date'] && 
                $date > $employee['resignation_date']
            ) {
                continue;
            }
            
            if (!in_array($empId, $presentIds)) {
                $absentEmployees[] = $employee;
            }
        }

        if (!empty($absentEmployees)) {
            $absentEmployeesByDate[$date] = $absentEmployees;
        }
    }

    // Prepare DataTables output
    $absentEmployeesForTable = [];
    foreach ($absentEmployeesByDate as $date => $employees) {
        foreach ($employees as $employee) {
            $absentEmployeesForTable[] = [
                'date' => $date,
                'emp_id' => $employee['emp_id'],
                'emp_name_with_initial' => $employee['emp_name_with_initial'],
                'departmentname' => $employee['departmentname'],
                'location' => $employee['location']
            ];
        }
    }

    return Datatables::of($absentEmployeesForTable)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
            // Add action buttons if needed
        })
        ->rawColumns(['action'])
        ->make(true);
}
}
