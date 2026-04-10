<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;

class BonusreportController extends Controller
{
    
    public function index(){

        $companyId = session('company_id');

        $branch=Company::orderBy('id', 'asc')->get(); 
        $department = DB::select("select id, company_id, name from departments WHERE company_id = ?", [$companyId]);
        return view('Payroll.Reports.bonus',compact('branch', 'department'));
    }

    public function generatereport(Request $request){

        $company = $request->input('company');
        $department = $request->input('department');
        $reporttype = $request->input('reporttype');
        $from_date = $request->input('from_date'); // Assuming format 'Y-m'
        $to_date = $request->input('to_date');

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        // Get Mount count
        $from_date_start = new DateTime($from_date . '-01'); 
        $to_date_end = new DateTime($to_date . '-01');

        $to_date_end->modify('last day of this month');

        $interval = $from_date_start->diff($to_date_end);
        $monthCount = ($interval->y * 12) + $interval->m + 1;
        
        $datareturn = [];
        $totalBonusAllEmp = 0;

        $from_date_start = $from_date_start->format('Y-m-d');
        $to_date_end = $to_date_end->format('Y-m-d');

        if($reporttype == 1){
            $sql="SELECT 
                `e`.`emp_id`, 
                `e`.`emp_etfno`, 
                `e`.`emp_name_with_initial`, 
                (SELECT GROUP_CONCAT(CONCAT(sub.fig_value, ' X ', sub.cnt) SEPARATOR ', ')
                FROM (
                    SELECT payroll_profile_id, fig_value, COUNT(*) as cnt
                    FROM employee_salary_payments
                    JOIN payment_periods ON payment_periods.id = employee_salary_payments.payment_period_id
                    WHERE fig_name = 'Basic' AND fig_value > 0
                    AND payment_periods.payment_period_fr BETWEEN '$from_date_start' AND '$to_date_end'
                    GROUP BY payroll_profile_id, fig_value
                ) AS sub
                WHERE sub.payroll_profile_id = `esp`.`payroll_profile_id`
                ) AS `basic_history`,
                SUM(CASE WHEN `esp`.`fig_name` = 'Basic' THEN `esp`.`fig_value` ELSE 0 END) AS `total_basic_value`, 
                SUM(CASE WHEN `esp`.`fig_name` = 'No Pay' THEN `esp`.`fig_value` ELSE 0 END) AS `total_no_pay_value`, 
                (TIMESTAMPDIFF(MONTH, '$from_date_start', '$to_date_end') + 1) AS `MonthCount`, 
                ((SUM(CASE WHEN `esp`.`fig_name` = 'Basic' THEN `esp`.`fig_value` ELSE 0 END) + 
                SUM(CASE WHEN `esp`.`fig_name` = 'No Pay' THEN `esp`.`fig_value` ELSE 0 END)) / 
                (TIMESTAMPDIFF(MONTH, '$from_date_start', '$to_date_end') + 1)) AS `bonus_amount` 
            FROM `employee_salary_payments` AS `esp`
            LEFT JOIN `payment_periods` ON `payment_periods`.`id` = `esp`.`payment_period_id` 
            LEFT JOIN `payroll_profiles` ON `payroll_profiles`.`id` = `esp`.`payroll_profile_id` 
            LEFT JOIN `employees` AS `e` ON `e`.`id` = `payroll_profiles`.`emp_id` 
            WHERE `payment_periods`.`payment_period_fr` BETWEEN '$from_date_start' AND '$to_date_end' 
            AND `payment_periods`.`payment_period_to` BETWEEN '$from_date_start' AND '$to_date_end'
            AND `e`.`emp_company` = '$company'
            AND (`e`.`emp_department` = '$department' OR '$department' = 'All')
            AND `payroll_profiles`.`payroll_process_type_id` = 1
            AND `e`.`deleted` = 0
            AND `e`.`is_resigned` = 0 
            GROUP BY `esp`.`payroll_profile_id`";
            $query = DB::select($sql);

            foreach ($query as $row) {
                $empId = $row->emp_id;
                $empEtFno = $row->emp_etfno;
                $empName = $row->emp_name_with_initial;
                $basicHistory = $row->basic_history;
                $totalBasicValue = $row->total_basic_value;
                $totalNoPayValue = $row->total_no_pay_value;
                $monthCount = $row->MonthCount;
                $bonusAmount = $row->bonus_amount;

                $totalBonusAllEmp += $bonusAmount;

                $datareturn[] = [
                    'empid' => $empId,
                    'emp_etfno' => $empEtFno,
                    'emp_name' => $empName,
                    'basic_history' => $basicHistory,
                    'total_basic_value' => number_format($totalBasicValue, 2),
                    'total_no_pay_value' => number_format($totalNoPayValue, 2),
                    'month_count' => $monthCount,
                    'bonus_amount' => number_format($bonusAmount, 2)
                ];
            }

             return response()->json([
                'data' => $datareturn,
                'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            ]);

            // $query = DB::table('employees')
            // ->join('payroll_profiles', 'employees.id', '=', 'payroll_profiles.emp_id')
            // ->select(
            //     'employees.id as empid',
            //     'employees.emp_etfno as emp_etfno',
            //     'employees.emp_name_with_initial as emp_name',
            //     'payroll_profiles.basic_salary as basicsalary',
            //     'payroll_profiles.id as payroll_profiles_id'                
            // )->where('employees.emp_company', '=', $company)
            // ->when($department !== 'All', function ($query) use ($department) {
            //     return $query->where('employees.emp_department', '=', $department);
            // })
            // ->where('payroll_profiles.payroll_process_type_id', '=',1)
            // ->where('employees.deleted', '=',0)
            // ->where('employees.is_resigned', '=',0)
            // ->orderBy('employees.emp_etfno', 'asc')
            // ->get();

            // foreach ($query as $row) {
            //     $empId = $row->empid;
            //     $emp_etfno = $row->emp_etfno;
            //     $empName = $row->emp_name;
            //     $basicSalary = $row->basicsalary;
            //     $payrollProfileId = $row->payroll_profiles_id;
            
            //     $totalNoPayValue = 0;
            //     $totalBasicValue = 0;
            //     $monthCount = count($monthsArray);

            //     foreach ($monthsArray as $month) {
            //         $monthStart = new DateTime($month . '-01');
            //         $monthEnd = (clone $monthStart)->modify('last day of this month');
                    
            //         $querysecond = DB::table('employee_payslips as ep')
            //             ->leftJoin('employee_salary_payments as esp', 'ep.id', '=', 'esp.employee_payslip_id')
            //             ->select(
            //                 'esp.fig_name as fig_name',
            //                 'esp.fig_value as fig_value')

            //             ->where('ep.payroll_profile_id', '=', $payrollProfileId )
            //             ->where('ep.payment_period_fr', '>=',  $monthStart->format('Y-m-d'))
            //             ->where('ep.payment_period_to', '<=',  $monthEnd->format('Y-m-d'))
            //             ->where('ep.payslip_cancel', '=', 0)
            //             ->where('ep.payslip_approved', '=', 1)
            //             ->whereIn('esp.fig_name', ['No Pay', 'Basic'])
            //             ->get();
                    
            //         foreach ($querysecond as $secondrow) {
            //             if($secondrow->fig_name == 'No Pay'){
            //                 $totalNoPayValue += abs($secondrow->fig_value);
            //             }
            //             if($secondrow->fig_name == 'Basic'){
            //                 $totalBasicValue += abs($secondrow->fig_value);
            //             }
            //         }
            //     }

            //     print_r("Employee: " . $empName . " - Month: " . $month . " - Total No Pay: " . $totalNoPayValue . " - Total Basic: " . $totalBasicValue . "\n");

                

            //     // $annualBasicSalary = $basicSalary * $monthCount;
            //     $annualBasicSalary = $totalBasicValue;
            //     $afternopay = $annualBasicSalary - $totalNoPayValue;
            //     $totalbonus =  $afternopay / $monthCount;

            //     $totalBonusAllEmp += $totalbonus;

            //     $datareturn[] = [
            //         'empid' => $empId,
            //         'emp_etfno' => $emp_etfno,
            //         'emp_name' => $empName,
            //         'basic_salary' => $basicSalary,
            //         'total_no_pay' => $totalNoPayValue,
            //         'total_bonus' => number_format($totalbonus, 2) 
            //     ];
            // }

            // return response()->json([
            //     'data' => $datareturn,
            //     'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            // ]);

        }
        else{
            $sql="SELECT 
                `e`.`emp_id`, 
                `e`.`emp_etfno`,
                `e`.`emp_name_with_initial`, 
                (SELECT GROUP_CONCAT(CONCAT(sub.val, ' X ', sub.cnt) ORDER BY sub.val DESC SEPARATOR ', ')
                FROM (
                    SELECT 
                        payroll_profile_id, 
                        fig_value AS val, 
                        COUNT(*) AS cnt
                    FROM employee_salary_payments
                    JOIN payment_periods ON payment_periods.id = employee_salary_payments.payment_period_id
                    WHERE fig_name = 'Basic' 
                    AND fig_value > 0
                    AND payment_periods.payment_period_fr >= '$from_date_start'
                    AND payment_periods.payment_period_to <= '$to_date_end'
                    GROUP BY payroll_profile_id, fig_value
                ) AS sub
                WHERE sub.payroll_profile_id = `pp`.`id`
                ) AS `basic_history`,
                SUM(CASE WHEN `esp`.`fig_name` = 'Basic' THEN `esp`.`fig_value` ELSE 0 END) AS `total_basic_value`, 
                SUM(CASE WHEN `esp`.`fig_name` = 'No Pay' THEN `esp`.`fig_value` ELSE 0 END) AS `total_no_pay_value`, 
                (TIMESTAMPDIFF(MONTH, '$from_date_start', '$to_date_end') + 1) AS `MonthCount`,
                ((SUM(CASE WHEN `esp`.`fig_name` = 'Basic' THEN `esp`.`fig_value` ELSE 0 END) + 
                SUM(CASE WHEN `esp`.`fig_name` = 'No Pay' THEN `esp`.`fig_value` ELSE 0 END)) / 12) AS `bonus_amount`
            FROM `employees` AS `e`
            JOIN `payroll_profiles` AS `pp` ON `e`.`id` = `pp`.`emp_id`
            LEFT JOIN `employee_salary_payments` AS `esp` ON `pp`.`id` = `esp`.`payroll_profile_id`
            LEFT JOIN `payment_periods` AS `pr` ON `pr`.`id` = `esp`.`payment_period_id`
            WHERE `e`.`emp_company` = '$company'
            AND (`e`.`emp_department` = '$department' OR '$department' = 'All')
            AND `pp`.`payroll_process_type_id` = 4
            AND `e`.`deleted` = 0
            AND `e`.`is_resigned` = 0
            AND `pr`.`payment_period_fr` >= '$from_date_start'
            AND `pr`.`payment_period_to` <= '$to_date_end'
            GROUP BY `e`.`id`, `pp`.`id`";
            $query = DB::select($sql);

            foreach ($query as $row) {
                $empId = $row->emp_id;
                $empName = $row->emp_name_with_initial;
                $basicHistory = $row->basic_history;
                $totalBasicValue = $row->total_basic_value;
                $totalNoPayValue = $row->total_no_pay_value;
                $monthCount = $row->MonthCount;
                $bonusAmount = $row->bonus_amount;

                $totalBonusAllEmp += $bonusAmount;

                $datareturn[] = [
                    'empid' => $empId,
                    'emp_etfno' => $row->emp_etfno,
                    'emp_name' => $empName,
                    'basic_history' => $basicHistory,
                    'total_basic_value' => number_format($totalBasicValue, 2),
                    'total_no_pay_value' => number_format($totalNoPayValue, 2),
                    'month_count' => $monthCount,
                    'bonus_amount' => number_format($bonusAmount, 2)
                ];
            }

            return response()->json([
                'data' => $datareturn,
                'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            ]);


            // $query2 = DB::table('employees')
            // ->join('payroll_profiles', 'employees.id', '=', 'payroll_profiles.emp_id')
            // ->select(
            //     'employees.id as empid',
            //     'employees.emp_etfno as emp_etfno',
            //     'employees.emp_name_with_initial as emp_name',
            //     'payroll_profiles.basic_salary as basicsalary',
            //     'payroll_profiles.id as payroll_profiles_id'                
            // )->where('employees.emp_company', '=', $company)
            // ->where('employees.emp_department', '=', $department)
            // ->where('payroll_profiles.payroll_process_type_id', '=',2)
            // ->where('employees.deleted', '=',0)
            // ->where('employees.is_resigned', '=',0)
            // ->orderBy('employees.emp_etfno', 'asc')
            // ->get();

            // foreach ($query2 as $row) {
            //     $empId = $row->empid;
            //     $emp_etfno = $row->emp_etfno;
            //     $empName = $row->emp_name;
            //     $payrollProfileId = $row->payroll_profiles_id;
            
            //     $totalbasicPayValue = 0;

            //     $querysecond2 = DB::table('employee_payslips as ep')
            //                 ->leftJoin('employee_salary_payments as esp', 'ep.id', '=', 'esp.employee_payslip_id')
            //                 ->select(
            //                     'esp.fig_name as fig_name',
            //                     'esp.fig_value as fig_value')

            //                 ->where('ep.payroll_profile_id', '=', $payrollProfileId )
            //                 ->where('ep.payment_period_fr', '>=',  $from_date_start->format('Y-m-d'))
            //                 ->where('ep.payment_period_to', '<=',  $to_date_end->format('Y-m-d'))
            //                 ->where('ep.payslip_cancel', '=', 0)
            //                 ->where('ep.payslip_approved', '=', 1)
            //                 ->where('esp.fig_name', '=', 'Basic')
            //                 ->get();
             
            //                 foreach ($querysecond2 as $secondrow) {
            //                     $totalbasicPayValue += $secondrow->fig_value;
            //                 }

            //                 $totalbonus =  $totalbasicPayValue / $monthCount;

            //                 $totalBonusAllEmp += $totalbonus;

            //                 $datareturn[] = [
            //                     'empid' => $empId,
            //                     'emp_etfno' => $emp_etfno,
            //                     'emp_name' => $empName,
            //                     'basic_salary' => $totalbasicPayValue,
            //                     'total_bonus' => number_format($totalbonus, 2) 
            //                 ];
            // }

            // return response()->json([
            //     'data' => $datareturn,
            //     'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            // ]);
        }
    }
}
