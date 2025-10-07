<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentPeriod extends Model
{
    //


    public function get_monthly_entitle_work_hours($month){
        $weekday_entitle_work_hours=0;
        $saturday_entitle_work_hours=0;
        $all_entitle_work_hours=0;

        $start_date = date('Y-m-01', strtotime($month));
        $end_date = date('Y-m-t', strtotime($month));

        $current_date = $start_date;
        while (strtotime($current_date) <= strtotime($end_date)) {

             // Check if the current date is a holiday
                $holiday = Holiday::where('date', $current_date)->first();
                
                if ($holiday) {
                    // If it's a holiday, skip this date and continue with the next one
                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                    continue;
                }
                else{
                    $dayOfWeek = date('N', strtotime($current_date)); // 'N' returns 1 (for Monday) through 7 (for Sunday)
        
                    if ($dayOfWeek == 6) {
                        $saturday_entitle_work_hours=$saturday_entitle_work_hours+1;
                    } elseif ($dayOfWeek == 7) {
                        
                    } else {
                        $weekday_entitle_work_hours=$weekday_entitle_work_hours+1;
                    }

                     // Move to the next day
                    $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
                }
               
        }
        $weekday_entitle_work_hours=$weekday_entitle_work_hours*8;
        $saturday_entitle_work_hours=$saturday_entitle_work_hours*5;

        $all_entitle_work_hours=$weekday_entitle_work_hours+$saturday_entitle_work_hours;
        
        return $all_entitle_work_hours;
    }
}


