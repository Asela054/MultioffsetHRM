<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    protected $guarded = [];
    protected $fillable = ['emp_id', 'uid', 'state', 'timestamp', 'date',
        'approved', 'type', 'devicesno', 'location', 'edit_status', 'created_at', 'updated_at'];

    use softDeletes;

    public function get_work_days($emp_id, $month)
    {
        $query = "SELECT Max(at1.timestamp) as lasttimestamp,
        Min(at1.timestamp) as firsttimestamp
        FROM attendances as at1
        WHERE at1.emp_id = $emp_id
        AND at1.date LIKE '$month%'
        AND at1.deleted_at IS NULL
        group by at1.uid, at1.date
        ";
        $attendance = \DB::select($query);

        $work_days = 0;
        foreach ($attendance as $att) {

            $first_time = $att->firsttimestamp;
            $last_time = $att->lasttimestamp;

            $date = Carbon::parse($first_time);
            $s_date = $date->format('Y-m-d');
            $holiday_check = Holiday::where('date', $s_date)
                ->where('work_level', '=', '2')
                ->first();

            if(!EMPTY($holiday_check)){
                continue;
            }

            //get difference in hours
            $diff = round((strtotime($last_time) - strtotime($first_time)) / 3600, 1);

            //if diff is greater than 8 hours then it is a work day
            //if diff is greater than 4 hours then it is a half day
            //if diff is greater than 2 hours then it is a half day
            if ($diff >= 8) {
                $work_days++;
            } elseif ($diff >= 4) {
                $work_days += 0.5;
            } elseif ($diff >= 2){
                //$work_days += 0.25;
            }
        }
        return $work_days;
    }

    public function get_working_week_days($emp_id, $month)
    {
        $query = "SELECT Max(at1.timestamp) as lasttimestamp,
        Min(at1.timestamp) as firsttimestamp, date
        FROM attendances as at1
        WHERE at1.emp_id = $emp_id
        AND at1.date LIKE '$month%'
        AND at1.deleted_at IS NULL
        group by at1.uid, at1.date
        ";
        $attendance = \DB::select($query);

        $no_of_working_workdays = 0;
        $working_work_days_breakdown_arr = array();
        $leave_deductions = array();
        foreach ($attendance as $att) {
            $first_time = $att->firsttimestamp;
            $last_time = $att->lasttimestamp;

            $date = Carbon::parse($att->date);
            $s_date = $date->format('Y-m-d');

            $emp = DB::table('employees')
                ->leftJoin('job_categories', 'job_categories.id' , '=', 'employees.job_category_id')
                ->leftJoin('shift_types', 'shift_types.id' , '=', 'employees.emp_shift')
                ->select('emp_shift', 'emp_etfno', 'emp_name_with_initial', 'job_category_id', 'job_categories.is_sat_ot_type_as_act', 'job_categories.custom_saturday_ot_type', 'shift_types.onduty_time', 'shift_types.offduty_time')
                ->where('emp_id', $emp_id)
                ->first();

            $shift_on = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$emp->onduty_time);
            $shift_off = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$emp->offduty_time);

            $seven_am = Carbon::parse('07:00');
            $seven_am_time = $seven_am->format('H:i');
            $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

            $eight_am = Carbon::parse('08:00');
            $eight_am_time = $eight_am->format('H:i');
            $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

            $twelve_pm = Carbon::parse('12:00');
            $twelve_pm_time = $twelve_pm->format('H:i');
            $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

            $one_pm = Carbon::parse('13:00');
            $one_pm_time = $one_pm->format('H:i');
            $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

            $work_hours_from = '';
            if($first_time <= $shift_on) {
                $work_hours_from = $today_eight;
            }

            if($first_time > $shift_on) {
                $work_hours_from = $first_time;
            }

            if($first_time >=$today_twelve && $first_time < $today_one ){
                $work_hours_from = $today_one;
            }

            $work_hours_to = '';
            if($last_time > $shift_off) {
                $work_hours_to = $shift_off;
            }else{
                $work_hours_to = $last_time;
            }

            $work_hours_to = Carbon::parse($work_hours_to);
            $work_hours_from = Carbon::parse($work_hours_from);

            if($first_time >=$today_twelve && $first_time < $today_one ){
            }else{
                $work_hours_to->subHours(1);
            }

            //get difference in hours
            $diff = round((strtotime($work_hours_to) - strtotime($work_hours_from)) / 3600, 1);

            if($first_time >=$today_twelve && $first_time < $today_one ){
            }else{
                $work_hours_to->addHours(1);
            }

            //var_dump($work_hours_from . ' - '.$work_hours_to. ' -diff- '. $diff);

            $day = $date->dayOfWeek;

            $holiday_check = Holiday::where('date', $s_date)
                ->where('work_level', '=', '2')
                ->first();

            $is_holiday = false;
            if(!EMPTY($holiday_check)){
                $is_holiday = true;
            }

            $work_days_arr = [1,2,3,4,5];

            if($emp->is_sat_ot_type_as_act == 2){
                array_push($work_days_arr, 6);
            }

            //if diff is greater than 8 hours then it is a work day
            //if diff is greater than 4 hours then it is a half day
            //if diff is greater than 2 hours then it is a half day
            if ($diff >= 8) {

                if (in_array($day ,$work_days_arr)){
                    if(!$is_holiday){
                        $no_of_working_workdays++;
//                        var_dump($work_hours_from);

                        $arr_work_days_bd = array(
                            'from' =>  $work_hours_from->format('Y-m-d H:i'),
                            'to' => $work_hours_to->format('Y-m-d H:i'),
                            'date' => $s_date,
                            'day_name' => Carbon::parse($date)->format('l'),
                            'hours' => $diff,
                            'work_day' => 1,
                            'is_no_pay_leave' => false
                        );
                        array_push($working_work_days_breakdown_arr, $arr_work_days_bd);
                    }
                }

            } elseif ($diff >= 3) {

                if (in_array($day ,$work_days_arr)){
                    if(!$is_holiday) {
                        //check if no pay half leaves exists for today
                        $leave = DB::table('leaves')
                            ->select('no_of_days', 'leave_from')
                            ->where('emp_id', $emp_id )
                            ->where('leave_type', '3' )
                            ->where('status', 'Approved' )
                            ->where('leave_from', '=',  $s_date )
                            ->first();

                        if (empty($leave) ) {
                            $no_of_working_workdays += 0.5;
                            //var_dump($s_date. ' + 0.5');
                            $arr_work_days_bd = array(
                                'from' => $work_hours_from->format('Y-m-d H:i'),
                                'to' => $work_hours_to->format('Y-m-d H:i'),
                                'date' => $s_date,
                                'day_name' => Carbon::parse($date)->format('l'),
                                'hours' => $diff,
                                'work_day' => 0.5,
                                'is_no_pay_leave' => false
                            );
                            array_push($working_work_days_breakdown_arr, $arr_work_days_bd);

                        }else{
                            $no_of_working_workdays += 0.5;
                            //var_dump($s_date. ' + 0.5');
                            $arr_work_days_bd = array(
                                'from' => $work_hours_from->format('Y-m-d H:i'),
                                'to' => $work_hours_to->format('Y-m-d H:i'),
                                'date' => $s_date,
                                'day_name' => Carbon::parse($date)->format('l'),
                                'hours' => $diff,
                                'work_day' => 0.5,
                                'is_no_pay_leave' => true
                            );
                            array_push($working_work_days_breakdown_arr, $arr_work_days_bd);
                        }

                    }
                }

            } elseif ($diff >= 2){

//                if (in_array($day ,$work_days_arr) || $is_holiday ){
//                    $no_of_working_workdays += 0.25;
//                }

            }

            //check for leaves
//            $leave = DB::table('leaves')
//                ->select('no_of_days', 'leave_from')
//                ->where('emp_id', $emp_id )
//                ->where('status', 'Approved' )
//                ->where('leave_from', '=',  $s_date )
//                ->first();
//
//            if (!empty($leave) ) {
//                if($leave->no_of_days == 0.50 ){
//                    //$no_of_working_workdays -= 0.5;
//                    $leave_arr = array(
//                        'date'=> $leave->leave_from,
//                        'no_of_days'=> (float) $leave->no_of_days,
//                        'day_name' => Carbon::parse($date)->format('l'),
//                    );
//                    array_push($leave_deductions, $leave_arr);
//                }
//
//            }


        }

        return array(
            'no_of_working_workdays' => $no_of_working_workdays,
            'working_work_days_breakdown' => $working_work_days_breakdown_arr,
            'leave_deductions' => $leave_deductions
        );

    }

    public function get_working_week_days_confirmed($emp_id, $month)
    {
        $data = WorkHour::where('date', 'like', $month . '%')
            ->where('emp_id', $emp_id)
            ->select(DB::raw('SUM(no_of_days) as total'))
            ->get();

        return array(
            'no_of_days' => $data[0]->total
        );
    }

    public function get_leave_days($emp_id, $month)
    {
        $query = DB::table('leaves')
            ->select(DB::raw('SUM(no_of_days) as total'))
            ->where('emp_id', $emp_id )
            ->where('status', 'Approved' )
            ->where('leave_from', 'like',  $month . '%');
        $leave_days_data = $query->get();
        $leave_days = (!empty($leave_days_data[0]->total)) ? $leave_days_data[0]->total : 0;

        return $leave_days;
    }

    public function get_no_pay_days($emp_id, $month){

        $query = DB::table('leaves')
            ->select(DB::raw('SUM(no_of_days) as total'))
            ->where('emp_id', '=' , $emp_id)
            ->where('leave_from', 'like',  $month . '%')
            ->where('leave_type', '=', '3')
            ->where('status', '=', 'Approved');

        $no_pay_days_data = $query->get();
        $no_pay_days = (!empty($no_pay_days_data[0]->total)) ? $no_pay_days_data[0]->total : 0;

        return $no_pay_days;
    }

    public function get_ot_hours($emp_id, $month){

        $month = $month . '%';

        $normal_rate_otwork_hrs = 0;
        $double_rate_otwork_hrs = 0;

        $att_query = 'SELECT at1.*, 
                Max(at1.timestamp) as lasttimestamp,
                Min(at1.timestamp) as firsttimestamp,
                employees.emp_shift,  
                employees.id as emp_auto_id,
                employees.emp_name_with_initial,
                employees.emp_department,
                shift_types.onduty_time, 
                shift_types.offduty_time
                FROM `attendances`  as `at1`
                join `employees` on `employees`.`emp_id` = `at1`.`uid` 
                left join shift_types ON employees.emp_shift = shift_types.id 
                WHERE at1.emp_id = '.$emp_id.' AND date LIKE  "'.$month.'"
                AND at1.deleted_at IS NULL
                 group by at1.uid, at1.date
                ';
        $att_records = DB::select($att_query);

        foreach ($att_records as $att_record) {

            $off_time = $att_record->lasttimestamp;
            $on_time = $att_record->firsttimestamp;
            $record_date = $att_record->date;

            $on_duty_time = $att_record->onduty_time;
            $off_duty_time =  $att_record->offduty_time;

            $ot_hours = (new \App\Attendance)->get_ot_hours_by_date($att_record->uid, $off_time, $on_time, $record_date, $on_duty_time, $off_duty_time, $att_record->emp_department);

            $normal_rate_otwork_hrs += $ot_hours['normal_rate_otwork_hrs'];
            $double_rate_otwork_hrs += $ot_hours['double_rate_otwork_hrs'];
        }

        $data = array(
            'normal_rate_otwork_hrs' => $normal_rate_otwork_hrs,
            'double_rate_otwork_hrs' => $double_rate_otwork_hrs,
        );

        return $data;

    }

    public function get_ot_hours_by_date($emp_id, $off_time, $on_time, $record_date, $shift_start_, $shift_end_, $emp_department ){

        if($shift_start_ == ''){
            $data = array(
                'normal_rate_otwork_hrs' => 0,
                'double_rate_otwork_hrs' => 0,
            );

            return $data;
        }

        $off_time = Carbon::parse($off_time);
        $on_time = Carbon::parse($on_time);
        $record_date = Carbon::parse($record_date);

        $date_period = $off_time->diffInDays($on_time);

        $total_ot_hours = 0;
        $total_ot_hours_double = 0;
        $total_ot_hours_one_point_five = 0;
        $ot_breakdown = array();

        $department = Department::where('id', $emp_department)->first();

        if(empty($department)){
            $data = array(
                'normal_rate_otwork_hrs' => 0,
                'double_rate_otwork_hrs' => 0,
                '15_rate_otwork_hrs' => 0,
                'ot_breakdown' => $ot_breakdown,
                'info' => 'Department not found',
            );

            return $data;
        }


        $emp = DB::table('employees')
            ->leftJoin('job_categories', 'job_categories.id' , '=', 'employees.job_category_id')
            ->select('emp_shift', 'emp_etfno', 'emp_name_with_initial', 'job_category_id', 'job_categories.is_sat_ot_type_as_act', 'job_categories.custom_saturday_ot_type')
            ->where('emp_id', $emp_id)
            ->first();

        $shift = DB::table('shift_types')
            ->where('id', $emp->emp_shift)
            ->first();

        if($date_period == 0){

            $date = $record_date;
            $day = $date->dayOfWeek;
            $is_sunday = false;
            $is_holiday = false;

            $is_double = false;
            $is_one_point_five = false;

            //check for as_act saturday

            if($day == 6){
                if($emp->is_sat_ot_type_as_act == 1){
                    $saturday_on_duty_time = $shift->saturday_onduty_time;
                    $saturday_off_duty_time = $shift->saturday_offduty_time;
                    $shift_start_ = $saturday_on_duty_time;
                    $shift_end_ = $saturday_off_duty_time;
                }
            }

            $shift_start =  Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_start_);
            $shift_end = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_end_);

            //date format is YYYY-MM-DD
            
            $s_date = $date->format('Y-m-d');

            $holiday_check = Holiday::where('date', $s_date)
                ->where('work_level', '=', '2')
                ->first();

            if(!EMPTY($holiday_check)){
                $is_holiday = true;
                $is_double = true;
            }

            if($day == 0){

                $is_sunday = true;
                $is_double = true;

                $ot_from = $on_time;
                $ot_to = $off_time;

                $seven_am = Carbon::parse('07:00');
                $seven_am_time = $seven_am->format('H:i');
                $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                $eight_am = Carbon::parse('08:00');
                $eight_am_time = $eight_am->format('H:i');
                $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                if ($ot_from > $today_seven && $ot_from < $today_eight ){
                    $ot_from =  $today_eight;
                }

                $twelve_pm = Carbon::parse('12:00');
                $twelve_pm_time = $twelve_pm->format('H:i');
                $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

                $one_pm = Carbon::parse('13:00');
                $one_pm_time = $one_pm->format('H:i');
                $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

                if($ot_to >=$today_twelve && $ot_to < $today_one ){
                    $ot_to = $today_twelve;
                }

                if($ot_to >= $today_one){
                    $ot_to->subHours(1);
                }

                $ot_minutes = $ot_from->diffInMinutes($ot_to);
                $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                if($ot_to >= $today_one){
                    $ot_to->addHours(1);
                }

                if($ot_minutes >= 60){
                    $ot_hours = 0;
                    $double_ot_hours = 0;
                    $double_ot_hours = round($ot_minutes / 60, 2);
                    $total_ot_hours_double += $double_ot_hours;

                    $ob = array(
                        'emp_id' => $emp_id,
                        'etf_no' => $emp->emp_etfno,
                        'name' => $emp->emp_name_with_initial,
                        'date' => $record_date->format('Y-m-d'),
                        'day_name' => $date->format('l'),
                        'from' => $ot_from->format('Y-m-d h:i A'),
                        'from_24' => $ot_from->format('Y-m-d H:i'),
                        'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                        'to' => $ot_to->format('Y-m-d h:i A'),
                        'to_24' => $ot_to->format('Y-m-d H:i'),
                        'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                        'hours' => $ot_hours,
                        'double_hours' => $double_ot_hours,
                        'one_point_five_ot_hours' => 0,
                        'is_holiday' => $is_holiday,
                    );
                    array_push($ot_breakdown, $ob);
                }

            }elseif ($day == 6){
                //if saturday
                if($emp->is_sat_ot_type_as_act == 0){

                    if($emp->custom_saturday_ot_type == 1 ){
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        $seven_am = Carbon::parse('07:00');
                        $seven_am_time = $seven_am->format('H:i');
                        $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                        $eight_am = Carbon::parse('08:00');
                        $eight_am_time = $eight_am->format('H:i');
                        $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $twelve_pm = Carbon::parse('12:00');
                        $twelve_pm_time = $twelve_pm->format('H:i');
                        $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

                        $one_pm = Carbon::parse('13:00');
                        $one_pm_time = $one_pm->format('H:i');
                        $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

                        if($ot_to >=$today_twelve && $ot_to < $today_one ){
                            $ot_to = $today_twelve;
                        }

                        if($ot_to >= $today_one){
                            $ot_to->subHours(1);
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_to >= $today_one){
                            $ot_to->addHours(1);
                        }

                        if($ot_minutes >= 60){
                            $ot_hours = 0;
                            $one_point_five_ot_hours = 0;
                            $ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours += $ot_hours;

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => $ot_hours,
                                'double_hours' => 0,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }

                    }

                    if($emp->custom_saturday_ot_type == 1.5 ){
                        $is_one_point_five = true;
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        $seven_am = Carbon::parse('07:00');
                        $seven_am_time = $seven_am->format('H:i');
                        $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                        $eight_am = Carbon::parse('08:00');
                        $eight_am_time = $eight_am->format('H:i');
                        $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $twelve_pm = Carbon::parse('12:00');
                        $twelve_pm_time = $twelve_pm->format('H:i');
                        $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

                        $one_pm = Carbon::parse('13:00');
                        $one_pm_time = $one_pm->format('H:i');
                        $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

                        if($ot_to >=$today_twelve && $ot_to < $today_one ){
                            $ot_to = $today_twelve;
                        }

                        if($ot_to >= $today_one){
                            $ot_to->subHours(1);
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_to >= $today_one){
                            $ot_to->addHours(1);
                        }

                        if($ot_minutes >= 60){
                            $ot_hours = 0;
                            $one_point_five_ot_hours = 0;
                            $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_one_point_five += $one_point_five_ot_hours;

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => ($one_point_five_ot_hours),
                                'double_hours' => 0,
                                'one_point_five_ot_hours' => $one_point_five_ot_hours,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }

                    }

                    if($emp->custom_saturday_ot_type == 2 ){
                        $is_double = true;
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        $seven_am = Carbon::parse('07:00');
                        $seven_am_time = $seven_am->format('H:i');
                        $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                        $eight_am = Carbon::parse('08:00');
                        $eight_am_time = $eight_am->format('H:i');
                        $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $twelve_pm = Carbon::parse('12:00');
                        $twelve_pm_time = $twelve_pm->format('H:i');
                        $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

                        $one_pm = Carbon::parse('13:00');
                        $one_pm_time = $one_pm->format('H:i');
                        $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

                        if($ot_to >=$today_twelve && $ot_to < $today_one ){
                            $ot_to = $today_twelve;
                        }

                        if($ot_to >= $today_one){
                            $ot_to->subHours(1);
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_to >= $today_one){
                            $ot_to->addHours(1);
                        }

                        if($ot_minutes >= 60){
                            $ot_hours = 0;
                            $double_ot_hours = 0;
                            $double_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_double += $double_ot_hours;

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => 0,
                                'double_hours' => $double_ot_hours,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }

                    }

                }

                //saturday is a working date
                if($emp->is_sat_ot_type_as_act == 2){

                    if($on_time < $shift_start) { //morning
                        $ot_from = $on_time;
                        $ot_to = $shift_start;

                        $seven_am = Carbon::parse('07:00');
                        $seven_am_time = $seven_am->format('H:i');
                        $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                        $eight_am = Carbon::parse('08:00');
                        $eight_am_time = $eight_am->format('H:i');
                        $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_minutes >= 60){
                            $ot_hours = 0;
                            $double_ot_hours = 0;

                            if($is_double){
                                $double_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_double += $double_ot_hours;
                            }elseif ($is_one_point_five){
                                $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                            }else{
                                $ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours += $ot_hours;
                            }

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => $ot_hours,
                                'double_hours' => $double_ot_hours,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                                'is_morning' => true,
                            );
                            array_push($ot_breakdown, $ob);

                        }

                    }

                    if($off_time > $shift_end ){
                        $ot_from = $shift_end;

                        $next_date = $date->copy()->addDays(1);
                        $next_date = $next_date->format('Y-m-d');
                        $next_date_morning_shift_start = Carbon::parse($next_date.' '.$shift_start_);

                        if($next_date_morning_shift_start < $off_time ){
                            $ot_to = $next_date_morning_shift_start;
                        }else{
                            $ot_to = $off_time;
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_minutes >= 60){

                            $ot_hours = 0;
                            $double_ot_hours = 0;

                            if($is_double){
                                $double_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_double += $double_ot_hours;
                            }elseif ($is_one_point_five){
                                $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                            }else{
                                $ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours += $ot_hours;
                            }

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => $ot_hours,
                                'double_hours' => $double_ot_hours,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                                'is_morning' => false,
                            );
                            array_push($ot_breakdown, $ob);

                 //                            var_dump($total_ot_hours);


                        }

                    }

                }
            }
            else{
                if($is_holiday){
                    $is_double = true;
                    $ot_from = $on_time;
                    $ot_to = $off_time;

                    $seven_am = Carbon::parse('07:00');
                    $seven_am_time = $seven_am->format('H:i');
                    $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                    $eight_am = Carbon::parse('08:00');
                    $eight_am_time = $eight_am->format('H:i');
                    $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                    if ($ot_from > $today_seven && $ot_from < $today_eight ){
                        $ot_from =  $today_eight;
                    }

                    $twelve_pm = Carbon::parse('12:00');
                    $twelve_pm_time = $twelve_pm->format('H:i');
                    $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

                    $one_pm = Carbon::parse('13:00');
                    $one_pm_time = $one_pm->format('H:i');
                    $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

                    if($ot_to >=$today_twelve && $ot_to < $today_one ){
                        $ot_to = $today_twelve;
                    }

                    if($ot_to >= $today_one){
                        $ot_to->subHours(1);
                    }

                    $ot_minutes = $ot_from->diffInMinutes($ot_to);
                    $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                    if($ot_to >= $today_one){
                        $ot_to->addHours(1);
                    }

                    if($ot_minutes >= 60){
                        $ot_hours = 0;
                        $double_ot_hours = 0;
                        $double_ot_hours = round($ot_minutes / 60, 2);
                        $total_ot_hours_double += $double_ot_hours;

                        $ob = array(
                            'emp_id' => $emp_id,
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'from' => $ot_from->format('Y-m-d h:i A'),
                            'from_24' => $ot_from->format('Y-m-d H:i'),
                            'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                            'to' => $ot_to->format('Y-m-d h:i A'),
                            'to_24' => $ot_to->format('Y-m-d H:i'),
                            'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                            'hours' => $ot_hours,
                            'double_hours' => $double_ot_hours,
                            'one_point_five_ot_hours' => 0,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);
                    }

                }
                else{

                    if($on_time < $shift_start) { //morning
                        $ot_from = $on_time;
                        $ot_to = $shift_start;

                        $seven_am = Carbon::parse('07:00');
                        $seven_am_time = $seven_am->format('H:i');
                        $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

                        $eight_am = Carbon::parse('08:00');
                        $eight_am_time = $eight_am->format('H:i');
                        $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_minutes >= 60){
                            $ot_hours = 0;
                            $double_ot_hours = 0;

                            if($is_double){
                                $double_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_double += $double_ot_hours;
                            }elseif ($is_one_point_five){
                                $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                            }else{
                                $ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours += $ot_hours;
                            }

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => $ot_hours,
                                'double_hours' => $double_ot_hours,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                                'is_morning' => true,
                            );
                            array_push($ot_breakdown, $ob);

                        }

                    }

                    if($off_time > $shift_end ){
                        $ot_from = $shift_end;

                        $next_date = $date->copy()->addDays(1);
                        $next_date = $next_date->format('Y-m-d');
                        $next_date_morning_shift_start = Carbon::parse($next_date.' '.$shift_start_);

                        if($next_date_morning_shift_start < $off_time ){
                            $ot_to = $next_date_morning_shift_start;
                        }else{
                            $ot_to = $off_time;
                        }

                        $ot_minutes = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes = $ot_minutes - ($ot_minutes % 30);

                        if($ot_minutes >= 60){

                            $ot_hours = 0;
                            $double_ot_hours = 0;

                            if($is_double){
                                $double_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_double += $double_ot_hours;
                            }elseif ($is_one_point_five){
                                $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                            }else{
                                $ot_hours = round($ot_minutes / 60, 2);
                                $total_ot_hours += $ot_hours;
                            }

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'from' => $ot_from->format('Y-m-d h:i A'),
                                'from_24' => $ot_from->format('Y-m-d H:i'),
                                'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                                'to' => $ot_to->format('Y-m-d h:i A'),
                                'to_24' => $ot_to->format('Y-m-d H:i'),
                                'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                                'hours' => $ot_hours,
                                'double_hours' => $double_ot_hours,
                                'one_point_five_ot_hours' => 0,
                                'is_holiday' => $is_holiday,
                                'is_morning' => false,
                            );
                            array_push($ot_breakdown, $ob);



                        }

                    }
                }

            }

        }else{
            //this condition not continued developing since multioffset has no longer shifts than 1 day.

            for($i = 0; $i < $date_period; $i++){

                $date = $record_date->copy()->addDays($i);

                $day = $date->dayOfWeek;
                $is_sunday = false;
                $is_holiday = false;

                $is_double = false;
                $is_one_point_five = false;

                if($day == 6){
                    if($emp->is_sat_ot_type_as_act == 1){
                        $saturday_on_duty_time = $shift->saturday_onduty_time;
                        $saturday_off_duty_time = $shift->saturday_offduty_time;

                        $shift_start_ = $saturday_on_duty_time;
                        $shift_end_ = $saturday_off_duty_time;
                    }

                    if($emp->is_sat_ot_type_as_act == 0){
                        if($emp->custom_saturday_ot_type == 1.5 ){
                            $is_one_point_five = true;
                        }
                        if($emp->custom_saturday_ot_type == 2 ){
                            $is_double = true;
                        }
                    }
                }

                if($day == 0){
                    $is_sunday = true;
                    $is_double = true;
                }


                $shift_start =  Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_start_);
                $shift_end = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_end_);

                //date format is YYYY-MM-DD
                $s_date = $date->format('Y-m-d');

                $holiday_check = Holiday::where('date', $s_date)
                    ->where('work_level', '=', '2')
                    ->first();

                if(!EMPTY($holiday_check)){
                    $is_holiday = true;
                    $is_double = true;
                }

                if($on_time < $shift_start) { //morning
                    $ot_from = $on_time;
                    $ot_to = $shift_start;

                    $ot_minutes = $ot_from->diffInMinutes($ot_to);
                    if($ot_minutes >= 60){

                        $ot_hours = 0;
                        $double_ot_hours = 0;
                        $one_point_five_ot_hours = 0;

                        if($is_double){
                            $double_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_double += $double_ot_hours;
                        }elseif ($is_one_point_five){
                            $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                        }else{
                            $ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours += $ot_hours;
                        }

                        $ob = array(
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'from' => $ot_from->format('Y-m-d h:i A'),
                            'from_24' => $ot_from->format('Y-m-d H:i'),
                            'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                            'to' => $ot_to->format('Y-m-d h:i A'),
                            'to_24' => $ot_to->format('Y-m-d H:i'),
                            'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                            'hours' => $ot_hours,
                            'double_hours' => $double_ot_hours,
                            'one_point_five_ot_hours' => $one_point_five_ot_hours,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);

                    }

                }

                if($off_time > $shift_end ){
                    $ot_from = $shift_end;

                    $next_date = $date->copy()->addDays(1);
                    $next_date = $next_date->format('Y-m-d');
                    $next_date_morning_shift_start = Carbon::parse($next_date.' '.$shift_start_);

                    if($next_date_morning_shift_start < $off_time ){
                        $ot_to = $next_date_morning_shift_start;
                    }else{
                        $ot_to = $off_time;
                    }

                    $ot_minutes = $ot_from->diffInMinutes($ot_to);

                    if($ot_minutes >= 60){

                        $ot_hours = 0;
                        $double_ot_hours = 0;
                        $one_point_five_ot_hours = 0;

                        if($is_double){
                            $double_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_double += $double_ot_hours;
                        }elseif ($is_one_point_five){
                            $one_point_five_ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours_one_point_five += $one_point_five_ot_hours;
                        }else{
                            $ot_hours = round($ot_minutes / 60, 2);
                            $total_ot_hours += $ot_hours;
                        }

                        $ob = array(
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'from' => $ot_from->format('Y-m-d h:i A'),
                            'from_24' => $ot_from->format('Y-m-d H:i'),
                            'from_rfc' => $ot_from->format('Y-m-d\TH:i:s'),
                            'to' => $ot_to->format('Y-m-d h:i A'),
                            'to_24' => $ot_to->format('Y-m-d H:i'),
                            'to_rfc' => $ot_to->format('Y-m-d\TH:i:s'),
                            'hours' => $ot_hours,
                            'double_hours' => $double_ot_hours,
                            'one_point_five_ot_hours' => $one_point_five_ot_hours,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);

                   }

                }

            }

        }

        $data = array(
            'normal_rate_otwork_hrs' => $total_ot_hours,
            'double_rate_otwork_hrs' => $total_ot_hours_double,
            'one_point_five_rate_otwork_hrs' => $total_ot_hours_one_point_five,
            'ot_breakdown' => $ot_breakdown,
        );

        return $data;

    }

    public function get_ot_hours_approved($emp_id, $month){

        $month = $month . '%';

        $normal_rate_otwork_hrs = 0;
        $double_rate_otwork_hrs = 0;

        $att_query = 'SELECT at1.*, 
                Max(at1.timestamp) as lasttimestamp,
                Min(at1.timestamp) as firsttimestamp,
                employees.emp_shift,  
                employees.id as emp_auto_id,
                employees.emp_name_with_initial,
                employees.emp_department,
                shift_types.onduty_time, 
                shift_types.offduty_time
                FROM `attendances`  as `at1`
                join `employees` on `employees`.`emp_id` = `at1`.`uid` 
                left join shift_types ON employees.emp_shift = shift_types.id 
                WHERE at1.emp_id = '.$emp_id.' AND date LIKE  "'.$month.'"
                AND at1.deleted_at IS NULL
                 group by at1.uid, at1.date
                ';

        $att_query = 'SELECT ot_approved.*,  
                employees.emp_shift,  
                employees.id as emp_auto_id,
                employees.emp_name_with_initial,
                employees.emp_department, 
                branches.location as b_location,
                departments.name as dept_name 
                FROM ot_approved
                join `employees` on `employees`.`emp_id` = ot_approved.emp_id
                left join shift_types ON employees.emp_shift = shift_types.id  
                left join departments ON employees.emp_department = departments.id 
                left join branches ON employees.emp_location = branches.id 
                WHERE ot_approved.emp_id = '.$emp_id.' AND ot_approved.date LIKE  "'.$month.'"
                ';

        $att_records = DB::select($att_query);

        foreach ($att_records as $att_record) {
            $normal_rate_otwork_hrs += $att_record->hours ?? 0;
            $double_rate_otwork_hrs += $att_record->double_hours ?? 0;
        }

        $data = array(
            'normal_rate_otwork_hrs' => $normal_rate_otwork_hrs,
            'double_rate_otwork_hrs' => $double_rate_otwork_hrs,
        );

        return $data;

    }

    public function get_ot_hours_by_date_morning_evening($emp_id, $off_time, $on_time, $record_date, $shift_start_, $shift_end_, $emp_department ){

        if($shift_start_ == ''){
            $data = array(
                'normal_rate_otwork_hrs' => 0,
                'double_rate_otwork_hrs' => 0,
            );

            return $data;
        }

        $off_time = Carbon::parse($off_time);
        $on_time = Carbon::parse($on_time);
        $record_date = Carbon::parse($record_date);

        $date_period = $off_time->diffInDays($on_time);

        $total_ot_hours = 0;
        $total_ot_hours_double = 0;
        $total_ot_hours_one_point_five = 0;
        $ot_breakdown = array();

        $morning_ot_from = '';
        $morning_ot_to = '';
        $evening_ot_from = '';
        $evening_ot_to = '';

        $department = Department::where('id', $emp_department)->first();

        if(empty($department)){
            $data = array(
                'normal_rate_otwork_hrs' => 0,
                'double_rate_otwork_hrs' => 0,
                '15_rate_otwork_hrs' => 0,
                'ot_breakdown' => $ot_breakdown,
                'info' => 'Department not found',
            );

            return $data;
        }

        $emp = DB::table('employees')
            ->leftJoin('job_categories', 'job_categories.id' , '=', 'employees.job_category_id')
            ->select('emp_shift', 'emp_etfno', 'emp_name_with_initial', 'job_category_id', 'job_categories.is_sat_ot_type_as_act', 'job_categories.custom_saturday_ot_type')
            ->where('emp_id', $emp_id)
            ->first();

        $shift = DB::table('shift_types')
            ->where('id', $emp->emp_shift)
            ->first();

        if($date_period == 0){

            $date = $record_date;
            $day = $date->dayOfWeek;
            $is_sunday = false;
            $is_holiday = false;

            $is_double = false;
            $is_one_point_five = false;

            //check for as_act saturday
            if($day == 6){
                if($emp->is_sat_ot_type_as_act == 1){
                    $saturday_on_duty_time = $shift->saturday_onduty_time;
                    $saturday_off_duty_time = $shift->saturday_offduty_time;

                    $shift_start_ = $saturday_on_duty_time;
                    $shift_end_ = $saturday_off_duty_time;
                }
            }

            $shift_start =  Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_start_);
            $shift_end = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$shift_end_);

            //date format is YYYY-MM-DD
            $s_date = $date->format('Y-m-d');

            $holiday_check = Holiday::where('date', $s_date)
                ->where('work_level', '=', '2')
                ->first();

            if(!EMPTY($holiday_check)){
                $is_holiday = true;
                $is_double = true;
            }

            $seven_am = Carbon::parse('07:00');
            $seven_am_time = $seven_am->format('H:i');
            $today_seven = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$seven_am_time);

            $eight_am = Carbon::parse('08:00');
            $eight_am_time = $eight_am->format('H:i');
            $today_eight = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$eight_am_time);

            $twelve_pm = Carbon::parse('12:00');
            $twelve_pm_time = $twelve_pm->format('H:i');
            $today_twelve = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$twelve_pm_time);

            $one_pm = Carbon::parse('13:00');
            $one_pm_time = $one_pm->format('H:i');
            $today_one = Carbon::parse($date->year.'-'.$date->month.'-'.$date->day.' '.$one_pm_time);

            if($day == 0){

                $is_sunday = true;
                $is_double = true;

                $ot_from = $on_time;
                $ot_to = $off_time;

                if ($ot_from > $today_seven && $ot_from < $today_eight ){
                    $ot_from =  $today_eight;
                }

                if($ot_to >= $today_twelve){
                    $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                    $morning_ot_from = $ot_from;
                    $morning_ot_to = $today_twelve;
                }else{
                    $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                    $morning_ot_from = $ot_from;
                    $morning_ot_to = $ot_to;
                }
                $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                if($ot_to >= $today_one){
                    $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                    $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                    $evening_ot_from = $today_one;
                    $evening_ot_to = $ot_to;
                }else{
                    $ot_minutes_evening = 0;
                }

                if($ot_minutes_morning < 60){
                    $ot_minutes_morning = 0;
                }

                if($ot_minutes_evening < 60){
                    $ot_minutes_evening = 0;
                }

                if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                    $double_ot_hours_morning = round($ot_minutes_morning / 60, 2);
                    $double_ot_hours_evening = round($ot_minutes_evening / 60, 2);

                    $ob = array(
                        'emp_id' => $emp_id,
                        'etf_no' => $emp->emp_etfno,
                        'name' => $emp->emp_name_with_initial,
                        'date' => $record_date->format('Y-m-d'),
                        'day_name' => $date->format('l'),
                        'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                        'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                        'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                        'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                        'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                        'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                        'morning_hours' => 0,
                        'morning_double_hours' => $double_ot_hours_morning,
                        'morning_one_point_five_ot_hours' => 0,
                        'evening_from' => $evening_ot_from->format('Y-m-d h:i A'),
                        'evening_from_24' => $evening_ot_from->format('Y-m-d H:i'),
                        'evening_rfc' => $evening_ot_from->format('Y-m-d\TH:i:s'),
                        'evening_to' => $evening_ot_to->format('Y-m-d h:i A'),
                        'evening_to_24' => $evening_ot_to->format('Y-m-d H:i'),
                        'evening_to_rfc' => $evening_ot_to->format('Y-m-d\TH:i:s'),
                        'evening_hours' => 0,
                        'evening_double_hours' => $double_ot_hours_evening,
                        'evening_one_point_five_ot_hours' => 0,
                        'total_hours' => 0,
                        'total_double_hours' => $double_ot_hours_morning + $double_ot_hours_evening ,
                        'is_holiday' => $is_holiday,
                    );
                    array_push($ot_breakdown, $ob);

                }

            }elseif ($day == 6){
                //if saturday
                if($emp->is_sat_ot_type_as_act == 0){

                    if($emp->custom_saturday_ot_type == 1 ){
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        if($ot_to >= $today_twelve){
                            $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $today_twelve;
                        }else{
                            $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $ot_to;
                        }
                        $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                        if($ot_to >= $today_one){
                            $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                            $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                            $evening_ot_from = $today_one;
                            $evening_ot_to = $ot_to;
                        }else{
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning < 60){
                            $ot_minutes_morning = 0;
                        }

                        if($ot_minutes_evening < 60){
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                            $ot_hours_morning = round($ot_minutes_morning / 60, 2);
                            $ot_hours_evening = round($ot_minutes_evening / 60, 2);

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                                'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                                'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                                'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                                'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                                'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                                'morning_hours' => $ot_hours_morning,
                                'morning_double_hours' => 0,
                                'morning_one_point_five_ot_hours' => 0,
                                'evening_from' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d h:i A') : '',
                                'evening_from_24' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d H:i') : '',
                                'evening_rfc' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d\TH:i:s') : '',
                                'evening_to' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d h:i A') : '',
                                'evening_to_24' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d H:i') : '',
                                'evening_to_rfc' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d\TH:i:s') : '',
                                'evening_hours' => $ot_hours_evening,
                                'evening_double_hours' => 0,
                                'evening_one_point_five_ot_hours' => 0,
                                'total_hours' => $ot_hours_morning + $ot_hours_evening,
                                'total_double_hours' => 0,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }
                    }

                    if($emp->custom_saturday_ot_type == 1.5 ){
                        $is_one_point_five = true;
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        if($ot_to >= $today_twelve){
                            $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $today_twelve;
                        }else{
                            $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $ot_to;
                        }
                        $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                        if($ot_to >= $today_one){
                            $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                            $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                            $evening_ot_from = $today_one;
                            $evening_ot_to = $ot_to;
                        }else{
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning < 60){
                            $ot_minutes_morning = 0;
                        }

                        if($ot_minutes_evening < 60){
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                            $one_point_five_ot_hours_morning = round($ot_minutes_morning / 60, 2);
                            $one_point_five_ot_hours_evening = round($ot_minutes_evening / 60, 2);

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                                'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                                'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                                'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                                'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                                'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                                'morning_hours' => 0,
                                'morning_double_hours' => 0,
                                'morning_one_point_five_ot_hours' => $one_point_five_ot_hours_morning,
                                'evening_from' => $evening_ot_from->format('Y-m-d h:i A'),
                                'evening_from_24' => $evening_ot_from->format('Y-m-d H:i'),
                                'evening_rfc' => $evening_ot_from->format('Y-m-d\TH:i:s'),
                                'evening_to' => $evening_ot_to->format('Y-m-d h:i A'),
                                'evening_to_24' => $evening_ot_to->format('Y-m-d H:i'),
                                'evening_to_rfc' => $evening_ot_to->format('Y-m-d\TH:i:s'),
                                'evening_hours' => 0,
                                'evening_double_hours' => 0,
                                'evening_one_point_five_ot_hours' => $one_point_five_ot_hours_evening,
                                'total_hours' => $one_point_five_ot_hours_morning + $one_point_five_ot_hours_evening,
                                'total_double_hours' => 0,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }
                    }

                    if($emp->custom_saturday_ot_type == 2 ){
                        $is_double = true;
                        $ot_from = $on_time;
                        $ot_to = $off_time;

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        if($ot_to >= $today_twelve){
                            $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $today_twelve;
                        }else{
                            $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                            $morning_ot_from = $ot_from;
                            $morning_ot_to = $ot_to;
                        }
                        $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                        if($ot_to >= $today_one){
                            $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                            $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                            $evening_ot_from = $today_one;
                            $evening_ot_to = $ot_to;
                        }else{
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning < 60){
                            $ot_minutes_morning = 0;
                        }

                        if($ot_minutes_evening < 60){
                            $ot_minutes_evening = 0;
                        }

                        if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                            $double_ot_hours_morning = round($ot_minutes_morning / 60, 2);
                            $double_ot_hours_evening = round($ot_minutes_evening / 60, 2);

                            $ob = array(
                                'emp_id' => $emp_id,
                                'etf_no' => $emp->emp_etfno,
                                'name' => $emp->emp_name_with_initial,
                                'date' => $record_date->format('Y-m-d'),
                                'day_name' => $date->format('l'),
                                'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                                'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                                'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                                'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                                'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                                'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                                'morning_hours' => 0,
                                'morning_double_hours' => $double_ot_hours_morning,
                                'morning_one_point_five_ot_hours' => 0,
                                'evening_from' => $evening_ot_from->format('Y-m-d h:i A'),
                                'evening_from_24' => $evening_ot_from->format('Y-m-d H:i'),
                                'evening_rfc' => $evening_ot_from->format('Y-m-d\TH:i:s'),
                                'evening_to' => $evening_ot_to->format('Y-m-d h:i A'),
                                'evening_to_24' => $evening_ot_to->format('Y-m-d H:i'),
                                'evening_to_rfc' => $evening_ot_to->format('Y-m-d\TH:i:s'),
                                'evening_hours' => 0,
                                'evening_double_hours' => $double_ot_hours_evening,
                                'evening_one_point_five_ot_hours' => 0,
                                'total_hours' => 0,
                                'total_double_hours' => $double_ot_hours_morning + $double_ot_hours_evening,
                                'is_holiday' => $is_holiday,
                            );
                            array_push($ot_breakdown, $ob);
                        }
                    }

                }

                //saturday is a working date
                if($emp->is_sat_ot_type_as_act == 2){

                    $ot_from = $on_time;
                    $ot_to = $off_time;

                    if ($ot_from > $today_seven && $ot_from < $today_eight ){
                        $ot_from =  $today_eight;
                    }

                    if($ot_to >= $today_twelve){
                        $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                        $morning_ot_from = $ot_from;
                        $morning_ot_to = $today_twelve;
                    }else{
                        $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                        $morning_ot_from = $ot_from;
                        $morning_ot_to = $ot_to;
                    }
                    $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                    if($ot_to >= $today_one){
                        $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                        $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                        $evening_ot_from = $today_one;
                        $evening_ot_to = $ot_to;
                    }else{
                        $ot_minutes_evening = 0;
                    }

                    if($ot_minutes_morning < 60){
                        $ot_minutes_morning = 0;
                    }

                    if($ot_minutes_evening < 60){
                        $ot_minutes_evening = 0;
                    }

                    if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                        $ot_hours_morning = round($ot_minutes_morning / 60, 2);
                        $ot_hours_evening = round($ot_minutes_evening / 60, 2);

                        $ob = array(
                            'emp_id' => $emp_id,
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                            'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                            'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                            'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                            'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                            'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                            'morning_hours' => 0,
                            'morning_double_hours' => $ot_hours_morning,
                            'morning_one_point_five_ot_hours' => 0,
                            'evening_from' => $evening_ot_from->format('Y-m-d h:i A'),
                            'evening_from_24' => $evening_ot_from->format('Y-m-d H:i'),
                            'evening_rfc' => $evening_ot_from->format('Y-m-d\TH:i:s'),
                            'evening_to' => $evening_ot_to->format('Y-m-d h:i A'),
                            'evening_to_24' => $evening_ot_to->format('Y-m-d H:i'),
                            'evening_to_rfc' => $evening_ot_to->format('Y-m-d\TH:i:s'),
                            'evening_hours' => 0,
                            'evening_double_hours' => $ot_hours_evening,
                            'evening_one_point_five_ot_hours' => 0,
                            'total_hours' => $ot_hours_morning + $ot_hours_evening,
                            'total_double_hours' => 0,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);
                    }
                }

            }
            else{

                if($is_holiday){
                    $is_double = true;
                    $ot_from = $on_time;
                    $ot_to = $off_time;

                    if ($ot_from > $today_seven && $ot_from < $today_eight ){
                        $ot_from =  $today_eight;
                    }

                    if($ot_to >= $today_twelve){
                        $ot_minutes_morning = $ot_from->diffInMinutes($today_twelve);
                        $morning_ot_from = $ot_from;
                        $morning_ot_to = $today_twelve;
                    }else{
                        $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                        $morning_ot_from = $ot_from;
                        $morning_ot_to = $ot_to;
                    }
                    $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                    if($ot_to >= $today_one){
                        $ot_minutes_evening = $today_one->diffInMinutes($ot_to);
                        $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);
                        $evening_ot_from = $today_one;
                        $evening_ot_to = $ot_to;
                    }else{
                        $ot_minutes_evening = 0;
                    }

                    if($ot_minutes_morning < 60){
                        $ot_minutes_morning = 0;
                    }

                    if($ot_minutes_evening < 60){
                        $ot_minutes_evening = 0;
                    }


                    if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                        $double_ot_hours_morning = round($ot_minutes_morning / 60, 2);
                        $double_ot_hours_evening = round($ot_minutes_evening / 60, 2);

                        $ob = array(
                            'emp_id' => $emp_id,
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'morning_from' => $morning_ot_from->format('Y-m-d h:i A'),
                            'morning_from_24' => $morning_ot_from->format('Y-m-d H:i'),
                            'morning_rfc' => $morning_ot_from->format('Y-m-d\TH:i:s'),
                            'morning_to' => $morning_ot_to->format('Y-m-d h:i A'),
                            'morning_to_24' => $morning_ot_to->format('Y-m-d H:i'),
                            'morning_to_rfc' => $morning_ot_to->format('Y-m-d\TH:i:s'),
                            'morning_hours' => 0,
                            'morning_double_hours' => $double_ot_hours_morning,
                            'morning_one_point_five_ot_hours' => 0,
                            'evening_from' => $evening_ot_from->format('Y-m-d h:i A'),
                            'evening_from_24' => $evening_ot_from->format('Y-m-d H:i'),
                            'evening_rfc' => $evening_ot_from->format('Y-m-d\TH:i:s'),
                            'evening_to' => $evening_ot_to->format('Y-m-d h:i A'),
                            'evening_to_24' => $evening_ot_to->format('Y-m-d H:i'),
                            'evening_to_rfc' => $evening_ot_to->format('Y-m-d\TH:i:s'),
                            'evening_hours' => 0,
                            'evening_double_hours' => $double_ot_hours_evening,
                            'evening_one_point_five_ot_hours' => 0,
                            'total_hours' => 0,
                            'total_double_hours' => $double_ot_hours_morning + $double_ot_hours_evening,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);
                    }

                }else{

                    $ot_minutes_morning = 0;
                    $ot_minutes_evening = 0;

                    // $morning_ot_from = 0;
                    // $morning_ot_to = 0;
                    // $evening_ot_from = 0;
                    // $evening_ot_to = 0;

                    if($on_time < $shift_start) { //morning

                        $ot_from = $on_time;
                        $ot_to = $shift_start;

                        $morning_ot_from = $ot_from;
                        $morning_ot_to = $ot_to;

                        if ($ot_from > $today_seven && $ot_from < $today_eight ){
                            $ot_from =  $today_eight;
                        }

                        $ot_minutes_morning = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes_morning = $ot_minutes_morning - ($ot_minutes_morning % 30);

                        if($ot_minutes_morning < 60){
                            $ot_minutes_morning = 0;
                        }

                    }

                    if($off_time > $shift_end ){ //evening

                        $ot_from = $shift_end;

                        $next_date = $date->copy()->addDays(1);
                        $next_date = $next_date->format('Y-m-d');
                        $next_date_morning_shift_start = Carbon::parse($next_date.' '.$shift_start_);

                        if($next_date_morning_shift_start < $off_time ){
                            $ot_to = $next_date_morning_shift_start;
                        }else{
                            $ot_to = $off_time;
                        }

                        $evening_ot_from = $ot_from;
                        $evening_ot_to = $ot_to;

                        $ot_minutes_evening = $ot_from->diffInMinutes($ot_to);
                        $ot_minutes_evening = $ot_minutes_evening - ($ot_minutes_evening % 30);

                        if($ot_minutes_evening < 60){
                            $ot_minutes_evening = 0;
                        }

                    }


                    if($ot_minutes_morning > 0 || $ot_minutes_evening > 0){

                        $ot_hours_morning = round($ot_minutes_morning / 60, 2);
                        $ot_hours_evening = round($ot_minutes_evening / 60, 2);

                        $ob = array(
                            'emp_id' => $emp_id,
                            'etf_no' => $emp->emp_etfno,
                            'name' => $emp->emp_name_with_initial,
                            'date' => $record_date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
                            'morning_from' => ($morning_ot_from != '') ? $morning_ot_from->format('Y-m-d h:i A') : '',
                            'morning_from_24' => ($morning_ot_from != '') ? $morning_ot_from->format('Y-m-d H:i') : '',
                            'morning_rfc' => ($morning_ot_from != '') ? $morning_ot_from->format('Y-m-d\TH:i:s') : '',
                            'morning_to' => ($morning_ot_to != '') ? $morning_ot_to->format('Y-m-d h:i A') : '',
                            'morning_to_24' => ($morning_ot_to != '') ? $morning_ot_to->format('Y-m-d H:i') : '',
                            'morning_to_rfc' => ($morning_ot_to != '') ? $morning_ot_to->format('Y-m-d\TH:i:s') : '',
                            'morning_hours' => $ot_hours_morning,
                            'morning_double_hours' => 0,
                            'morning_one_point_five_ot_hours' => 0,
                            'evening_from' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d h:i A') : '',
                            'evening_from_24' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d H:i') : '',
                            'evening_rfc' => ($evening_ot_from != '') ? $evening_ot_from->format('Y-m-d\TH:i:s') : '',
                            'evening_to' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d h:i A') : '',
                            'evening_to_24' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d H:i') : '',
                            'evening_to_rfc' => ($evening_ot_to != '') ? $evening_ot_to->format('Y-m-d\TH:i:s') : '',
                            'evening_hours' => $ot_hours_evening,
                            'evening_double_hours' => 0,
                            'evening_one_point_five_ot_hours' => 0,
                            'total_hours' => $ot_hours_morning + $ot_hours_evening,
                            'total_double_hours' => 0,
                            'is_holiday' => $is_holiday,
                        );
                        array_push($ot_breakdown, $ob);

                    }


                }

            }

        }

        return array(
            'normal_rate_otwork_hrs' => $total_ot_hours,
            'double_rate_otwork_hrs' => $total_ot_hours_double,
            'one_point_five_rate_otwork_hrs' => $total_ot_hours_one_point_five,
            'ot_breakdown' => $ot_breakdown,
        );


    }

    public function get_attendance_details($emp_id, $month)
    {
        $month = $month.'%';

        $att_query = 'SELECT at1.*, 
                Max(at1.timestamp) as lasttimestamp,
                Min(at1.timestamp) as firsttimestamp,
                employees.emp_shift,  
                employees.id as emp_auto_id,
                employees.emp_name_with_initial,
                employees.emp_etfno,
                employees.emp_department,
                shift_types.onduty_time, 
                shift_types.offduty_time
                FROM `attendances`  as `at1`
                join `employees` on `employees`.`emp_id` = `at1`.`uid` 
                left join shift_types ON employees.emp_shift = shift_types.id 
                WHERE at1.emp_id = '.$emp_id.' AND date LIKE  "'.$month.'"
                AND at1.deleted_at IS NULL
                 group by at1.uid, at1.date
                 order by at1.date';

        $att_records = DB::select($att_query);

        $emp_data = array();

        foreach ($att_records as $att_record) {
            $att_date = Carbon::parse($att_record->date);
            $date = $att_date->year.'-'.$att_date->month.'-'.$att_date->day;

            $day = Carbon::parse($date)->dayOfWeek;
            $day_name = Carbon::parse($date)->format('l');

            $off_time = Carbon::parse($att_record->lasttimestamp);
            $on_time = Carbon::parse($att_record->firsttimestamp);
            $record_date = Carbon::parse($att_record->date);

            //difference between on and off time in minutes
            $work_minutes = $off_time->diffInMinutes($on_time);
            $work_hours = $work_minutes/60;

            $work_hours = round($work_hours, 2);

            // $ot_hours = (new \App\Attendance)->get_ot_hours_by_date($att_record->uid, $off_time, $on_time, $record_date, $att_record->onduty_time, $att_record->offduty_time, $att_record->emp_department);
            $otdatalist = DB::table('ot_approved')
                ->select(
                    DB::raw('SUM(hours) as total_hours'), 
                    DB::raw('SUM(double_hours) as total_double_hours')
                )
                ->where('date', $record_date->format('Y-m-d')) 
                ->where('emp_id', $emp_id) 
                ->first();
            // dd($otdatalist);    

            if($att_record->firsttimestamp == $att_record->lasttimestamp){
                $last_timestamp = '';
            }else{
                $last_timestamp = $att_record->lasttimestamp;
            }

            //get date from date
            $date_from_date = Carbon::parse($att_record->date)->format('Y-m-d');

            // get edit status
            $get_edit_status = DB::table('attendances')
            ->join('attendance_edits', 'attendance_edits.attendance_id', '=', 'attendances.id')
            ->select('attendances.edit_status')
            ->where('attendances.emp_id', $emp_id)
            ->where('attendances.date', 'like', $att_record->date.'%')
            ->whereNull('attendances.deleted_at')
            ->where('attendances.edit_status', 1)
            ->exists();

            if ($get_edit_status) {
                $edit_status='1';
            } 
            else{
                $edit_status='0';
            }

            // get edit details
            $get_edit_details = DB::table('attendances')
            ->leftjoin('attendance_edits', 'attendance_edits.attendance_id', '=', 'attendances.id')
            ->select('attendances.*','attendance_edits.prev_val', 'attendance_edits.new_val')
            ->where('attendances.emp_id', $emp_id)
            ->where('attendances.date', 'like', $att_record->date.'%')
            ->whereNull('attendances.deleted_at')
            ->get();

            $lastdetails = [];
            $firstdetails = [];

            if ($get_edit_details->isNotEmpty()) {
                $maxTimestamp = $get_edit_details->max('timestamp');
                $minTimestamp = $get_edit_details->min('timestamp');
                
                foreach ($get_edit_details as $detail) {
                    if ($detail->timestamp == $maxTimestamp && !is_null($detail->prev_val)) {
                        $lastdetails[] = $detail->prev_val;
                    }
                    if ($detail->timestamp == $minTimestamp && !is_null($detail->prev_val)) {
                        $firstdetails[] = $detail->prev_val;
                    }
                }
            }
            $editdetails_array = [
                'lastdetails' => $lastdetails,
                'firstdetails' => $firstdetails,
            ];

            $data = array(
                'emp_id' => $att_record->emp_id,
                'emp_name' => $att_record->emp_name_with_initial,
                'etf_no' => $att_record->emp_etfno,
                'date' => $date_from_date,
                'day' => $day,
                'day_name' => $day_name,
                'first_timestamp' => $att_record->firsttimestamp,
                'last_timestamp' => $last_timestamp,
                'normal_rate_otwork_hrs' => number_format($otdatalist->total_hours, 2),
                'double_rate_otwork_hrs' => number_format($otdatalist->total_double_hours, 2),
                'work_hours' => $work_hours,
                'edit_status' => $edit_status,
                'editdetails_array' => $editdetails_array,
            );

            $emp_data[] = $data;

        }

        return $emp_data;

    }

}
