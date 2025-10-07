<?php

namespace App\Http\Controllers;

use App\Department;
use App\Employee;
use App\WorkCategory;
use DB;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PaySheetReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function PaySheetReport()
    {
        $user = Auth::user();
        $permission = $user->can('pay-sheet-report');
        if(!$permission) {
            abort(403);
        }

        return view('payRollReport.paySheetReport');
    }

    public function PaySheetReportPrint(Request $request)
    {
        $user = Auth::user();
        $permission = $user->can('pay-sheet-report');
        if(!$permission) {
            abort(403);
        }

        $wc = WorkCategory::find($request->work_category_id);
        $employee = Employee::where('work_category_id', $request->work_category_id)
                    ->where('is_resigned', 0)
                    ->where('deleted', 0)
                    //->where('id', 3)
                    ->get();

        $print_date = date("Y-m-d h:i:s");
        $dompdf = new Dompdf();
        $options = $dompdf->getOptions();
        $options->setIsHtml5ParserEnabled(true);
        $options->isPhpEnabled(true);
        $dompdf->setOptions($options);

        $html = '

            <h3 style="text-align: center"> MULTI OFFSET PRINTERS (PVT) LTD  </h3>
            <h5 style="text-align: center"> 345, NEGAMBO ROAD, MUKALANGAMUWA SEEDUWA  </h5>
            <h6 style="text-align: center"> Pay Sheet II For the Month of '. $request->month .' </h6>
            
            <table style="width: 100%; margin-bottom: 15px;">
                <tr>
                    <td> SECTION : ' . $wc->name . ' </td>
                    <td style="text-align: right">    </td>
                </tr>
            </table> 
                
            <table style="width: 100%">
                <thead>
                        <tr>
                            <th  style="text-align: left; border-top: solid black 1px; border-bottom: solid black 1px;">EMP NO</th>
                            <th style="text-align: left; border-top: solid black 1px; border-bottom: solid black 1px;">NAME</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">BASIC</th> 
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">NOPAY</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">HOLIDAY</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">SALARY<br>FOR EPF</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">OVERTIME</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">TOTAL</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">EPF 8%</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">EPF 12%</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">EPF 20%</th>
                            <th style="text-align: right; border-top: solid black 1px; border-bottom: solid black 1px;">ETF 3%</th>
                        </tr>
                    </thead>
                <tbody>';
        $total_basic = 0;
        $total_nopay = 0;
        $total_sal_epf = 0;
        $total_ot = 0;
        $total_net_total = 0;
        $total_epf_8 = 0;
        $total_epf_12 = 0;
        $total_epf_20 = 0;
        $total_etf_3 = 0;
        $no_of_emps = 0;
        foreach ($employee as $emp) {
            $like_date = $request->date.'%';

            $data_basic = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'Basic'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_basic += $data_basic[0]->fig_value;

            $data_nopay = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'No pay'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_nopay += $data_nopay[0]->fig_value;

            $data_salary_epf = DB::select(" SELECT employee_paid_rate_id,
            SUM(fig_value) AS fig_value 
            FROM payroll_profiles
            JOIN employee_payslips ON payroll_profiles.id = employee_payslips.payroll_profile_id
            JOIN employee_salary_payments ON employee_payslips.id = employee_salary_payments.employee_payslip_id
            WHERE (epf_payable=1 OR remuneration_payslip_spec_code IN ('NOPAY','OTHRS2'))
            AND employee_payslips.payment_period_fr LIKE '$like_date'
            AND employee_payslips.payment_period_to LIKE '$like_date'
            AND payroll_profiles.emp_id = $emp->id ");
            $total_sal_epf += $data_salary_epf[0]->fig_value;

            $data_ot = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_group_title', 'OTHRS'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_ot += $data_ot[0]->fig_value ?? 0;

            $net_total = $data_basic[0]->fig_value ?? 0 + $data_nopay[0]->fig_value ?? 0 + $data_salary_epf[0]->fig_value ?? 0 + $data_ot[0]->fig_value ?? 0;
            $total_net_total += $net_total;

            //EPF-8%
            $data_epf_8 = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'EPF-8%'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_epf_8 += $data_epf_8[0]->fig_value ?? 0;

            //EPF-12%
            $data_epf_12 = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'EPF-12%'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_epf_12 += $data_epf_12[0]->fig_value ?? 0;

            //EPF-20%
            $data_epf_20 = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'EPF-20%'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_epf_20 += $data_epf_20[0]->fig_value ?? 0;

            //ETF-3%
            $data_etf_3 = DB::table('payroll_profiles')
                ->select('employee_salary_payments.*')
                ->Join('employee_payslips', 'payroll_profiles.id', '=', 'employee_payslips.payroll_profile_id')
                ->Join('employee_salary_payments', 'employee_payslips.id', '=', 'employee_salary_payments.employee_payslip_id')
                ->where([
                    ['employee_payslips.payment_period_fr', 'LIKE', $like_date],
                    ['employee_payslips.payment_period_to', 'LIKE', $like_date],
                    ['employee_salary_payments.fig_name', 'ETF-3%'],
                    ['payroll_profiles.emp_id', $emp->id]
                ])->get();
            $total_etf_3 += $data_etf_3[0]->fig_value ?? 0;

            $no_of_emps++;

            $html .= '<tr>
                                <td style="text-align: left">' . $emp->id . '</td>
                                <td style="text-align: left">' . $emp->emp_name_with_initial . '</td>
                                <td style="text-align: right">' . number_format($data_basic[0]->fig_value, 2) . '</td> 
                                <td style="text-align: right">' . number_format($data_nopay[0]->fig_value, 2) . '</td> 
                                <td style="text-align: right">' . number_format(0, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_salary_epf[0]->fig_value, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_ot[0]->fig_value, 2) . '</td>
                                <td style="text-align: right">' . number_format($net_total, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_epf_8[0]->fig_value ?? 0, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_epf_12[0]->fig_value ?? 0, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_epf_20[0]->fig_value ?? 0, 2) . '</td>
                                <td style="text-align: right">' . number_format($data_etf_3[0]->fig_value ?? 0, 2) . '</td>
                            </tr>';
        }
        $html .='</tbody>';

        $html.='
                <tfoot>
                <tr>
                            <td style="text-align: left; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;"> </td>
                            <td style="text-align: left; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;"> SECTION TOTAL </td> 
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_basic, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_nopay, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format(0, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_sal_epf, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_ot, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_net_total, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_epf_8, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_epf_12, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_epf_20, 2).'</td>
                            <td style="text-align: right; font-weight: bold; border-top: solid black 1px; border-bottom: solid black 1px;" > '.number_format($total_etf_3, 2).'</td>
                        </tr>
                </tfoot> ';

        $html .='</table>';
        $html .='<p> No of Employees : '.$no_of_emps.' </p> ';

        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        $canvas = $dompdf->get_canvas();

        $font = $dompdf->getFontMetrics()->get_font("Arial, Helvetica, sans-serif", "normal");
        $size = 8;
        //$pageText = $PAGE_NUM . "/" . $PAGE_COUNT;
        $pageText = "Page: {PAGE_NUM} of {PAGE_COUNT}";
        $y = $canvas->get_height() - 24;
        //$x = $pdf->get_width() - 15 - Font_Metrics::get_text_width($pageText, $font, $size);
        $x = round(($canvas->get_width() - $dompdf->getFontMetrics()->get_text_width("Page: 000 of 000", $font, $size)) / 2, 0);
        $canvas->page_text($x, $y, $pageText, $font, $size);

        $canvas->page_script('
	  if ($PAGE_NUM < $PAGE_COUNT) {
		$font = $dompdf->getFontMetrics()->get_font("helvetica", "bold");
		$current_page = $PAGE_NUM;
		$total_pages = $PAGE_COUNT;
		$pdf->text($pdf->get_width()-100, $pdf->get_height()-60, "Continued", $font, 10, array(0,0,0));
	  }
	');

        // Output the generated PDF to Browser

        $file_name = "Pay Sheet - " . ' ' . $print_date . ".pdf";
        $dompdf->stream($file_name);

    }



}
