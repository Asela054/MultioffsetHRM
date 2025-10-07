<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\DB;

class DailyattendacereportController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
        $permission = Auth::user()->can('attendance-report');
        if (!$permission) {
           abort(403);
        }
        return view('Report.daliyattendacecheking');
    }


public function generatedailyattendacereport(Request $request)
{
    $department = $request->department;
    $from_date = $request->from_date;

    $employees = DB::table('employees')
        ->select('emp_id', 'emp_name_with_initial as emp_name')
        ->where('emp_department', $department)
        ->where('deleted', 0)
        ->orderBy('emp_id')
        ->get();

    $reportData = [];
    $totalabsent = 0;
    $totallate = 0;

    foreach ($employees as $employee) {
        $attendance = DB::table('attendances')
            ->where('emp_id', $employee->emp_id)
            ->whereDate('date', $from_date)
            ->whereNull('deleted_at')
            ->exists();

        $late = null;
        $status = 'Present';
        $latein = '';
        $duration = '';

        if (!$attendance) {
            $status = 'Absent';
            $totalabsent++;

            $reportData[] = [
                'emp_id' => $employee->emp_id,
                'emp_name' => $employee->emp_name,
                'status' => $status,
                'half_day' => '',
                'short_leave' => '',
                'informal' => '',
                'remark' => '',
                'late_arrival' => $latein,
                'duration' => $duration
            ];

        } else {
            $late = DB::table('employee_late_attendances')
                ->where('emp_id', $employee->emp_id)
                ->whereDate('date', $from_date)
                ->first();

            if ($late) {
                $status = 'Late';
                $latein = $late->check_in_time;
                $duration = $late->working_hours;
                $totallate++;

                $reportData[] = [
                    'emp_id' => $employee->emp_id,
                    'emp_name' => $employee->emp_name,
                    'status' => $status,
                    'half_day' => '',
                    'short_leave' => '',
                    'informal' => '',
                    'remark' => '',
                    'late_arrival' => $latein,
                    'duration' => $duration
                ];
            }
        }
    }

    return response()->json([
        'data' => $reportData,
        'total_absent' => $totalabsent,
        'total_late' => $totallate
    ]);
}

}
