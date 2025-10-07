<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Employee;
use Carbon\Carbon;
use DB;
use Session;

class AttendanceSpecialController extends Controller
{
    public function index()
    {
        $permission = Auth::user()->can('Specialattendance-list');
        if (!$permission) {
            abort(403);
        }
        $company = Session::get('company_id');
        $employee = Employee::where('special_attendance', "Yes")->where('emp_company', $company)->where('deleted', 0)->orderBy('id', 'desc')->get();

        return view('Attendent.specialattendance', compact('employee'));
    }

    public function insert(Request $request)
    {
        $permission = Auth::user()->can('Specialattendance-create');
        if (!$permission) {
            abort(403);
        }

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $employeeIds = $request->input('employee_ids');

        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);
    

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {

            if ($date->isWeekday()) {

                $holidayExists = DB::table('holidays')
                ->whereDate('date', $date->toDateString())
                ->exists();

                if (!$holidayExists) {

                    foreach ($employeeIds as $empId) {
                        $employeeshift = DB::table('employees')
                            ->join('shift_types', 'employees.emp_shift', '=', 'shift_types.id')
                            ->where('employees.emp_id', $empId)
                            ->select(
                                'employees.id as employee_id',
                                'employees.emp_id',
                                'shift_types.id as shift_id',
                                'shift_types.onduty_time',
                                'shift_types.offduty_time'
                            )->first();
        

                            $ondutyTimestamp = Carbon::parse($date->toDateString() . ' ' . $employeeshift->onduty_time);
                            $offdutyTimestamp = Carbon::parse($date->toDateString() . ' ' . $employeeshift->offduty_time);

                    // Check and Insert/Update On-duty Record

                            $existingOnDuty = DB::table('attendances')
                            ->where('emp_id', $empId)
                            ->where('timestamp', $ondutyTimestamp)
                            ->first();
                    
                        if ($existingOnDuty) {
                            DB::table('attendances')
                                ->where('id', $existingOnDuty->id)
                                ->update([
                                    'state' => 1,
                                    'timestamp' => $ondutyTimestamp,
                                    'date' => $date->toDateString(),
                                    'approved' => 0,
                                    'type' => 255,
                                    'devicesno' => 0,
                                    'location' => 1,
                                ]);
                        } else {
                            DB::table('attendances')->insert([
                                'emp_id' => $empId,
                                'uid' => $empId,
                                'state' => 1,
                                'timestamp' => $ondutyTimestamp,
                                'date' => $date->toDateString(),
                                'approved' => 0,
                                'type' => 255,
                                'devicesno' => 0,
                                'location' => 1,
                            ]);
                        }

                        // Check and Insert/Update Off-duty Record
                        
                        $existingOffDuty = DB::table('attendances')
                            ->where('emp_id', $empId)
                            ->where('timestamp', $offdutyTimestamp)
                            ->first();
                    
                        if ($existingOffDuty) {
                            DB::table('attendances')
                                ->where('id', $existingOffDuty->id)
                                ->update([
                                    'state' => 1,
                                    'timestamp' => $offdutyTimestamp,
                                    'date' => $date->toDateString(),
                                    'approved' => 0,
                                    'type' => 255,
                                    'devicesno' => 0,
                                    'location' => 1,
                                ]);
                        } else {
                            DB::table('attendances')->insert([
                                'emp_id' => $empId,
                                'uid' => $empId,
                                'state' => 1,
                                'timestamp' => $offdutyTimestamp,
                                'date' => $date->toDateString(),
                                'approved' => 0,
                                'type' => 255,
                                'devicesno' => 0,
                                'location' => 1,
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json(['success' => 'Attendance Details Successfully Insert']);
    }

}
