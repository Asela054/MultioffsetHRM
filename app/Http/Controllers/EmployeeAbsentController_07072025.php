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
        // $selectdatefrom = $request->input('selectdatefrom');
        // $selectdateto = $request->input('selectdateto');
        $companyId = Session::get('company_id');
        $userDepartmentIds =  Auth::user()->departments->pluck('id');

        $selectdatefrom = Carbon::parse($request->input('selectdatefrom'));
        $selectdateto = Carbon::parse($request->input('selectdateto'));

        $department = $request->input('department');

        $absentEmployeesByDate = [];

        if($department=='All'){

            $employeedata= DB::table('employees')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select('employees.emp_id', 'employees.emp_name_with_initial', 'employees.emp_department', 
                    'departments.name AS departmentname', 'branches.location AS location',
                    'employees.is_resigned', 'employees.resignation_date') 
            ->where('deleted', 0)
            ->where(function($query) use ($selectdateto) {
                $query->where('is_resigned', 0)
                      ->orWhere(function($q) use ($selectdateto) {
                          $q->where('is_resigned', 1)
                            ->whereDate('resignation_date', '>=', $selectdateto->format('Y-m-d'));
                      });
            })
            ->where('employees.emp_company', '=', $companyId);
            if (!$userDepartmentIds->isEmpty()) {
                $employeedata->whereIn('employees.emp_department', $userDepartmentIds);
            }
            $employeedata = $employeedata->get();
            
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

            for ($date = $selectdatefrom; $date->lte($selectdateto); $date->addDay()) {

                $day = $date->dayOfWeek;
                // check day weekdays
                if($day != 6 && $day != 0) {
                    // check holidays
                    $s_date = $date->format('Y-m-d');
                    $holiday_check = Holiday::where('date', $s_date)
                    ->where('work_level', '=', '2')
                    ->first();

                    if(!$holiday_check){

                        $attendances = DB::table('attendances')
                        ->leftJoin('employees', 'attendances.uid', '=', 'employees.emp_id')
                        ->select('employees.emp_id')
                        ->whereDate('attendances.date', $date->format('Y-m-d'))
                        ->where('employees.emp_company', '=', $companyId);
                        if (!$userDepartmentIds->isEmpty()) {
                            $attendances->whereIn('employees.emp_department', $userDepartmentIds);
                        }
                        $attendances = $attendances->groupBy('attendances.date', 'attendances.uid')
                        ->pluck('employees.emp_id')
                        ->toArray();
                        
                       
                    $absentEmployees = array_filter($employeeMap, function ($employee) use ($attendances) {
                 
                        return !in_array($employee['emp_id'], $attendances);
                    });
    
                    $absentEmployeesByDate[$date->format('Y-m-d')] = $absentEmployees;
                    }
                }
            }

        }else if($department!='All'){
            $employeedata = DB::table('employees')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->select('employees.emp_id', 'employees.emp_name_with_initial', 'employees.emp_department', 
                    'departments.name AS departmentname', 'branches.location AS location',
                    'employees.is_resigned', 'employees.resignation_date') 
            ->where('deleted', 0)
            ->where(function($query) use ($selectdateto) {
                $query->where('is_resigned', 0)
                      ->orWhere(function($q) use ($selectdateto) {
                          $q->where('is_resigned', 1)
                            ->whereDate('resignation_date', '>=', $selectdateto->format('Y-m-d'));
                      });
            })
            ->where('employees.emp_company', '=', $companyId)
            ->where('employees.emp_department', '=', $department);
            if (!$userDepartmentIds->isEmpty()) {
                $employeedata->whereIn('employees.emp_department', $userDepartmentIds);
            }
            $employeedata = $employeedata->get();

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
    
                for ($date = $selectdatefrom; $date->lte($selectdateto); $date->addDay()) {

                    $day = $date->dayOfWeek;
                    // check day weekdays
                    if($day != 6 && $day != 0) {
                        // check holidays
                        $s_date = $date->format('Y-m-d');
                        $holiday_check = Holiday::where('date', $s_date)
                        ->where('work_level', '=', '2')
                        ->first();
    
                        if(!$holiday_check){
                            $attendances = DB::table('attendances')
                            ->leftJoin('employees', 'attendances.uid', '=', 'employees.emp_id')
                            ->select('employees.emp_id')
                            ->whereDate('attendances.date', $date->format('Y-m-d'))
                            ->where('employees.emp_company', '=', $companyId)
                            ->where('employees.emp_department', '=', $department);
                            if (!$userDepartmentIds->isEmpty()) {
                                $attendances->whereIn('employees.emp_department', $userDepartmentIds);
                            }
                            $attendances = $attendances->groupBy('attendances.date', 'attendances.uid')
                            ->pluck('employees.emp_id')
                            ->toArray();
                           
                        $absentEmployees = array_filter($employeeMap, function ($employee) use ($attendances) {
                     
                            return !in_array($employee['emp_id'], $attendances);
                        });
        
                        $absentEmployeesByDate[$date->format('Y-m-d')] = $absentEmployees;
                        }
                    }

                }
        }
 
        $absentEmployeesForTable = [];
        foreach ($absentEmployeesByDate as $date => $absentEmployees) {
            foreach ($absentEmployees as $absentEmployee) {
                $absentEmployeesForTable[] = [
                    'date' => $date,
                    'emp_id' => $absentEmployee['emp_id'],
                    'emp_name_with_initial' => $absentEmployee['emp_name_with_initial'],
                    'departmentname' => $absentEmployee['departmentname'],
                    'location' => $absentEmployee['location']
                ];
            }
        }


            return Datatables::of($absentEmployeesForTable)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
            })
            ->rawColumns(['action'])
            ->make(true);

            // return response() ->json(['result'=>  $types]);
    }
}
