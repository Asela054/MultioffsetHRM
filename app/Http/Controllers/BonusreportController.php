<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
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

        if($reporttype == 1){
            $query = DB::table('employees')
            ->join('payroll_profiles', 'employees.id', '=', 'payroll_profiles.emp_id')
            ->select(
                'employees.id as empid',
                'employees.emp_name_with_initial as emp_name',
                'payroll_profiles.basic_salary as basicsalary',
                'payroll_profiles.id as payroll_profiles_id'                
            )->where('employees.emp_company', '=', $company)
            ->when($department !== 'All', function ($query) use ($department) {
                return $query->where('employees.emp_department', '=', $department);
            })
            ->where('payroll_profiles.payroll_process_type_id', '=',1)
            ->where('employees.deleted', '=',0)
            ->orderBy('employees.id')
            ->get();

            foreach ($query as $row) {
                $empId = $row->empid;
                $empName = $row->emp_name;
                $basicSalary = $row->basicsalary;
                $payrollProfileId = $row->payroll_profiles_id;
            
                $totalNoPayValue = 0;

                $querysecond = DB::table('employee_payslips as ep')
                            ->leftJoin('employee_salary_payments as esp', 'ep.id', '=', 'esp.employee_payslip_id')
                            ->select(
                                'esp.fig_name as fig_name',
                                'esp.fig_value as fig_value')

                            ->where('ep.payroll_profile_id', '=', $payrollProfileId )
                            ->where('ep.payment_period_fr', '>=',  $from_date_start->format('Y-m-d'))
                            ->where('ep.payment_period_to', '<=',  $to_date_end->format('Y-m-d'))
                            ->where('ep.payslip_cancel', '=', 0)
                            ->where('ep.payslip_approved', '=', 1)
                            ->where('esp.fig_name', '=', 'No Pay')
                            ->get();
             
                            foreach ($querysecond as $secondrow) {
                                $totalNoPayValue += abs($secondrow->fig_value);
                            }

                            $annualBasicSalary = $basicSalary * $monthCount;
                            $afternopay = $annualBasicSalary - $totalNoPayValue;
                            $totalbonus =  $afternopay / $monthCount;

                            $totalBonusAllEmp += $totalbonus;

                            $datareturn[] = [
                                'empid' => $empId,
                                'emp_name' => $empName,
                                'basic_salary' => $basicSalary,
                                'total_no_pay' => $totalNoPayValue,
                                'total_bonus' => number_format($totalbonus, 2) 
                            ];
              }

              return response()->json([
                'data' => $datareturn,
                'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            ]);

        }
        else{

            $query2 = DB::table('employees')
            ->join('payroll_profiles', 'employees.id', '=', 'payroll_profiles.emp_id')
            ->select(
                'employees.id as empid',
                'employees.emp_name_with_initial as emp_name',
                'payroll_profiles.basic_salary as basicsalary',
                'payroll_profiles.id as payroll_profiles_id'                
            )->where('employees.emp_company', '=', $company)
            ->where('employees.emp_department', '=', $department)
            ->where('payroll_profiles.payroll_process_type_id', '=',2)
            ->where('employees.deleted', '=',0)
            ->get();

            foreach ($query2 as $row) {
                $empId = $row->empid;
                $empName = $row->emp_name;
                $payrollProfileId = $row->payroll_profiles_id;
            
                $totalbasicPayValue = 0;

                $querysecond2 = DB::table('employee_payslips as ep')
                            ->leftJoin('employee_salary_payments as esp', 'ep.id', '=', 'esp.employee_payslip_id')
                            ->select(
                                'esp.fig_name as fig_name',
                                'esp.fig_value as fig_value')

                            ->where('ep.payroll_profile_id', '=', $payrollProfileId )
                            ->where('ep.payment_period_fr', '>=',  $from_date_start->format('Y-m-d'))
                            ->where('ep.payment_period_to', '<=',  $to_date_end->format('Y-m-d'))
                            ->where('ep.payslip_cancel', '=', 0)
                            ->where('ep.payslip_approved', '=', 1)
                            ->where('esp.fig_name', '=', 'Basic')
                            ->get();
             
                            foreach ($querysecond2 as $secondrow) {
                                $totalbasicPayValue += $secondrow->fig_value;
                            }

                            $totalbonus =  $totalbasicPayValue / $monthCount;

                            $totalBonusAllEmp += $totalbonus;

                            $datareturn[] = [
                                'empid' => $empId,
                                'emp_name' => $empName,
                                'basic_salary' => $totalbasicPayValue,
                                'total_bonus' => number_format($totalbonus, 2) 
                            ];
            }

            return response()->json([
                'data' => $datareturn,
                'total_bonus_all_employees' => number_format($totalBonusAllEmp, 2) 
            ]);
        }
    }
}
