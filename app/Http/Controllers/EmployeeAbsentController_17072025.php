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

    $selectdatefrom = Carbon::parse($request->input('selectdatefrom'));
    $selectdateto = Carbon::parse($request->input('selectdateto'));
    $department = $request->input('department');
    $absentEmployeesByDate = [];

    if ($department == 'All') {
        // Employee data for all departments
        $employeeQuery = DB::table('employees')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select(
                'employees.emp_id', 
                'employees.emp_name_with_initial', 
                'employees.emp_department', 
                'departments.name AS departmentname', 
                'branches.location AS location',
                'employees.is_resigned', 
                'employees.resignation_date'
            ) 
            ->where('deleted', 0)
            ->where(function($query) use ($selectdateto) {
                $query->where('is_resigned', 0)
                    ->orWhere(function($q) use ($selectdateto) {
                        $q->where('is_resigned', 1)
                            ->whereDate('resignation_date', '>=', $selectdateto->format('Y-m-d'));
                    });
            })
            ->where('employees.emp_company', $companyId);

        if (!$userDepartmentIds->isEmpty()) {
            $employeeQuery->whereIn('employees.emp_department', $userDepartmentIds);
        }
        
        $employeedata = $employeeQuery->get();
        
        $employeeMap = [];
        foreach ($employeedata as $employee) {
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

    } else if ($department != 'All') {
        // Employee data for specific department
        $employeeQuery = DB::table('employees')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select(
                'employees.emp_id', 
                'employees.emp_name_with_initial', 
                'employees.emp_department', 
                'departments.name AS departmentname', 
                'branches.location AS location',
                'employees.is_resigned', 
                'employees.resignation_date'
            ) 
            ->where('deleted', 0)
            ->where(function($query) use ($selectdateto) {
                $query->where('is_resigned', 0)
                    ->orWhere(function($q) use ($selectdateto) {
                        $q->where('is_resigned', 1)
                            ->whereDate('resignation_date', '>=', $selectdateto->format('Y-m-d'));
                    });
            })
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_department', $department);
            
        if (!$userDepartmentIds->isEmpty()) {
            $employeeQuery->whereIn('employees.emp_department', $userDepartmentIds);
        }
        
        $employeedata = $employeeQuery->get();

        $employeeMap = [];
        foreach ($employeedata as $employee) {
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
    }

    // Only proceed if we have employees
    if (!empty($employeeMap)) {
        for ($date = $selectdatefrom; $date->lte($selectdateto); $date->addDay()) {
            $day = $date->dayOfWeek;
            // Skip weekends (Saturday=6, Sunday=0)
            if ($day == 6 || $day == 0) {
                continue;
            }

            $s_date = $date->format('Y-m-d');
            // Check for holidays
            $holiday_check = Holiday::where('date', $s_date)
                ->where('work_level', '2')
                ->exists();

            if (!$holiday_check) {
                // Get present employees for this date
                $attendanceQuery = DB::table('attendances')
                    ->leftJoin('employees', 'attendances.uid', '=', 'employees.emp_id')
                    ->whereDate('attendances.date', $s_date)
                    ->where('employees.emp_company', $companyId);
                    
                // Add department filter if not "All"
                if ($department != 'All') {
                    $attendanceQuery->where('employees.emp_department', $department);
                }
                
                if (!$userDepartmentIds->isEmpty()) {
                    $attendanceQuery->whereIn('employees.emp_department', $userDepartmentIds);
                }
                
                $presentEmployeeIds = $attendanceQuery
                    ->groupBy('employees.emp_id')
                    ->pluck('employees.emp_id')
                    ->toArray();

                // Identify absent employees
                $absentEmployees = [];
                foreach ($employeeMap as $employee) {
                    if (!in_array($employee['emp_id'], $presentEmployeeIds)) {
                        $absentEmployees[] = $employee;
                    }
                }

                if (!empty($absentEmployees)) {
                    $absentEmployeesByDate[$s_date] = $absentEmployees;
                }
            }
        }
    }

    // Prepare data for DataTables
    $absentEmployeesForTable = [];
    foreach ($absentEmployeesByDate as $date => $absentEmployees) {
        foreach ($absentEmployees as $employee) {
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
