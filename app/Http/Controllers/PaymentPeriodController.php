<?php

namespace App\Http\Controllers;


use App\PaymentPeriod;
use Auth;
use BankAccountTest;
/*
use App\PayrollProcessType;

use App\PayrollProfile;
use App\Remuneration;
*/
use DB;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\VarDumper\Cloner\Data;
//use Illuminate\Validation\Rule;
use Validator;

class PaymentPeriodController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $payroll_process_type=DB::select("SELECT payroll_process_types.id AS payroll_process_type_id, payroll_process_types.process_name, IFNULL(drv_info.payment_period_id, '') AS payment_period_id, IFNULL(drv_info.payment_period_fr, '') AS payment_period_fr, IFNULL(drv_info.payment_period_to, '') AS payment_period_to FROM payroll_process_types LEFT OUTER JOIN (SELECT drv_list.id AS payment_period_id, drv_list.payroll_process_type_id, drv_list.payment_period_fr, drv_list.payment_period_to FROM (SELECT id, payroll_process_type_id, payment_period_fr, payment_period_to FROM payment_periods) AS drv_list INNER JOIN (SELECT max(`id`) AS last_id, `payroll_process_type_id` FROM `payment_periods` group by `payroll_process_type_id`) AS drv_key ON drv_list.id=drv_key.last_id) AS drv_info ON payroll_process_types.id=drv_info.payroll_process_type_id");
		return view('Payroll.paymentPeriod.paymentPeriod_list',compact('payroll_process_type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
		$data = $request->all();
		
		$rules = array(
            'payment_period_fr' => 'required',
			'payment_period_to' => 'required'
        );

        
		$error = Validator::make($data, $rules);

        if($error->fails()){
            return response()->json(['errors' => $error->errors()->all()]);
        }else if(PaymentPeriod::where(array(['payroll_process_type_id', '=', $request->payroll_process_type_id], 
								 ['payment_period_to', '>=', $request->payment_period_fr]))->count() > 0){
			return response()->json(['errors' => ['Invalid schedule']]);
		}

        // get pre month for pass data for accounts
        $companyId = Session::get('company_id');
        $companyName = Session::get('company_name');
        $companyBranchId = Session::get('company_branch_id');
        $companyBranchName = Session::get('company_branch_name');
        $user = Auth::id();

        $date=date('Y-m-d');
        $preshedule = PaymentPeriod::where('payroll_process_type_id', $request->payroll_process_type_id)
        ->orderBy('id', 'DESC')
        ->first();

        $payment_period_id=$preshedule->id;
        $payment_period_to=$preshedule->payment_period_to;
		$payment_period_fr=$preshedule->payment_period_fr;

        $fig_list = array(
            'ATTBONUS_W'=>array('amt'=>0, 'cnt'=>0),
            'BASIC'=>array('amt'=>0, 'cnt'=>0),
            'BRA_I'=>array('amt'=>0, 'cnt'=>0),
            'add_bra2'=>array('amt'=>0, 'cnt'=>0),
            'BRA_I'=>array('amt'=>0, 'cnt'=>0),
            'add_bra2'=>array('amt'=>0, 'cnt'=>0),
            'NOPAY'=>array('amt'=>0, 'cnt'=>0),
            'SAL_AFT_NOPAY'=>array('amt'=>0, 'cnt'=>''),
            'OTHRS1'=>array('amt'=>0, 'cnt'=>0),
            'OTHRS2'=>array('amt'=>0, 'cnt'=>0),
            'OTHRS'=>array('amt'=>0, 'cnt'=>0),//*
            'add_holiday_x'=>array('amt'=>0, 'cnt'=>0),//holiday
            'add_transport_x'=>array('amt'=>0, 'cnt'=>0),//reimburse traveling
            'INCNTV_EMP'=>array('amt'=>0, 'cnt'=>0),//incentive
            'INCNTV_DIR'=>array('amt'=>0, 'cnt'=>0),//directors incentive
            'add_other'=>array('amt'=>0, 'cnt'=>0),
            'tot_earn'=>array('amt'=>0, 'cnt'=>''),
            'EPF8'=>array('amt'=>0, 'cnt'=>0),
            'sal_adv'=>array('amt'=>0, 'cnt'=>0),
            'ded_fund_1'=>array('amt'=>0, 'cnt'=>0),//funeral fund
            'ded_IOU'=>array('amt'=>0, 'cnt'=>0),
            'PAYE'=>array('amt'=>0, 'cnt'=>0),
            'add_transport'=>array('amt'=>0, 'cnt'=>0),
            'LOAN'=>array('amt'=>0, 'cnt'=>0),
            'ded_other'=>array('amt'=>0, 'cnt'=>0),
            'tot_ded'=>array('amt'=>0, 'cnt'=>''),
            'bal_earn'=>array('amt'=>0, 'cnt'=>''),
            'EPF12'=>array('amt'=>0, 'cnt'=>0),
            'ETF3'=>array('amt'=>0, 'cnt'=>0),
            'tot_sal_voucher'=>array('amt'=>0, 'cnt'=>''),
            'epf_etf_res'=>array('amt'=>0, 'cnt'=>''),
            'tot_epf12etf3'=>array('amt'=>0, 'cnt'=>''),
            
        );

        $acc_group = array(
                    '7083_087_0'=>array('grp_val'=>0),//hnb ja-ela
                    '7083_209_0'=>array('grp_val'=>0),//hnb seeduwa
                    '1_1_0'=>array('grp_val'=>0),//other banks
                    '0_0_0'=>array('grp_val'=>0)//cash, other
                );

        $sqlslip="SELECT IFNULL(BINARY drv_pssc.opt_pssc, drv_info.remuneration_pssc) AS fig_pssc, drv_info.fig_value AS fig_value, CONCAT(drv_emp.bank_group, '_', drv_info.fig_hidden) AS acc_group_code FROM (SELECT employees.id AS employee_id, employee_payslips.id AS emp_payslip_id, CONCAT(IFNULL(bank_branches.bankcode, (payroll_profiles.employee_bank_id IS NOT NULL)), '_', IFNULL(bank_branches.code, (payroll_profiles.employee_bank_id IS NOT NULL))) AS bank_group FROM `employee_payslips` INNER JOIN payroll_profiles ON employee_payslips.payroll_profile_id=payroll_profiles.id INNER JOIN employees ON payroll_profiles.emp_id=employees.id LEFT OUTER JOIN employee_banks ON payroll_profiles.employee_bank_id=employee_banks.id LEFT OUTER JOIN (SELECT bankcode, code from bank_branches WHERE bankcode='7083' AND code IN ('087','209')) AS bank_branches ON (employee_banks.bank_code=bank_branches.bankcode AND employee_banks.branch_code=bank_branches.code) WHERE employee_payslips.payroll_process_type_id=? AND employees.emp_company=? AND employee_payslips.payslip_cancel=0 AND (employee_payslips.payment_period_id=?)) AS drv_emp INNER JOIN (SELECT `employee_payslip_id`, remuneration_payslip_spec_code as remuneration_pssc, employee_salary_payments.fig_hidden, (fig_value>=0) AS fig_opt, SUM(`fig_value`) AS fig_value FROM employee_salary_payments WHERE employee_salary_payments.payment_period_id=? GROUP BY `employee_payslip_id`, remuneration_payslip_spec_code, fig_hidden, (fig_value>=0)) AS drv_info ON drv_emp.emp_payslip_id=drv_info.employee_payslip_id LEFT OUTER JOIN (select '1' AS fig_opt, 'OTHER_REM' AS pssc, 'add_other' AS opt_pssc UNION ALL select '0' AS fig_opt, 'OTHER_REM' AS pssc, 'ded_other' AS opt_pssc) AS drv_pssc ON (drv_info.remuneration_pssc=BINARY drv_pssc.pssc AND drv_info.fig_opt=BINARY drv_pssc.fig_opt) ORDER BY drv_emp.employee_id";

        $employee = DB::select($sqlslip, [$request->payroll_process_type_id, $companyId, $payment_period_id, $payment_period_id]);
        /*
        $employee = DB::select($sqlslip);
        */


        foreach($employee as $r){
        if(isset($fig_list[$r->fig_pssc])){
            $pay_fig_val = $fig_list[$r->fig_pssc]['amt']+$r->fig_value;
            $fig_list[$r->fig_pssc]['amt']=$pay_fig_val;
            $pay_fig_cnt = $fig_list[$r->fig_pssc]['cnt']+1;
            $fig_list[$r->fig_pssc]['cnt']=$pay_fig_cnt;
            
            //considering fig-list keys
            if(isset($acc_group[$r->acc_group_code])){
                $acc_group_val = $acc_group[$r->acc_group_code]['grp_val']+$r->fig_value;
                $acc_group[$r->acc_group_code]['grp_val'] = $acc_group_val;
            }

        }
        }

        $act_basic = $fig_list['BASIC']['amt'];
        $act_basic += ($fig_list['BRA_I']['amt']+$fig_list['add_bra2']['amt']);
        $fig_list['BASIC']['amt'] = $act_basic;

        $fig_list['SAL_AFT_NOPAY']['amt']=$fig_list['BASIC']['amt']+$fig_list['NOPAY']['amt'];

        $fig_list['OTHRS']['amt']=$fig_list['OTHRS1']['amt']+$fig_list['OTHRS2']['amt'];
        $othrs_cnt = ($fig_list['OTHRS1']['cnt']>$fig_list['OTHRS2']['cnt'])?$fig_list['OTHRS1']['cnt']:$fig_list['OTHRS2']['cnt'];
        $fig_list['OTHRS']['cnt']=$othrs_cnt;
                                    

        $act_tot_earn=($fig_list['SAL_AFT_NOPAY']['amt']+$fig_list['OTHRS']['amt']);
        $act_tot_earn+=$fig_list['add_holiday_x']['amt'];
        $act_tot_earn+=$fig_list['add_transport_x']['amt'];
        $act_tot_earn+=$fig_list['INCNTV_EMP']['amt'];
        $act_tot_earn+=$fig_list['INCNTV_DIR']['amt'];
        $act_tot_earn+=$fig_list['add_other']['amt'];
        $fig_list['tot_earn']['amt']=$act_tot_earn;

        $act_tot_ded=($fig_list['EPF8']['amt']+$fig_list['sal_adv']['amt']);
        $act_tot_ded+=$fig_list['ded_fund_1']['amt'];
        $act_tot_ded+=$fig_list['ded_IOU']['amt'];
        $act_tot_ded+=$fig_list['PAYE']['amt'];
        $act_tot_ded+=$fig_list['add_transport']['amt'];
        $act_tot_ded+=$fig_list['LOAN']['amt'];
        $act_tot_ded+=$fig_list['ded_other']['amt'];
        $fig_list['tot_ded']['amt']=$act_tot_ded;

        $fig_list['bal_earn']['amt']=$fig_list['tot_earn']['amt']+$fig_list['tot_ded']['amt'];



        $fig_list['tot_sal_voucher']['amt']=$fig_list['BASIC']['amt']+$fig_list['INCNTV_EMP']['amt'];
        $fig_list['epf_etf_res']['amt']=$fig_list['EPF8']['amt']+$fig_list['EPF12']['amt']+$fig_list['ETF3']['amt'];
        $fig_list['tot_epf12etf3']['amt']=$fig_list['EPF12']['amt']+$fig_list['ETF3']['amt'];

        $total_earn=$fig_list['tot_earn']['amt'];
        $salary_and_wages=$fig_list['SAL_AFT_NOPAY']['amt']+$fig_list['OTHRS']['amt'];
        $travelling=$fig_list['ATTBONUS_W']['amt'];
        $incentive=$fig_list['INCNTV_EMP']['amt']+$fig_list['INCNTV_DIR']['amt'];
        $payee_tax=abs($fig_list['PAYE']['amt']);
        $salary_advance=abs($fig_list['sal_adv']['amt']);
        $emp_fund_reserve=abs($fig_list['EPF8']['amt']);

        $payment_suspense=$salary_and_wages-($payee_tax+$salary_advance+$emp_fund_reserve);

        $debit_total=$salary_and_wages+$travelling+$incentive;
        $credit_total=$travelling+$incentive+$payee_tax+$salary_advance+$emp_fund_reserve+$payment_suspense;

        
        $EPFAdministration=$fig_list['EPF12']['amt'];
        $ETFAdministration=$fig_list['ETF3']['amt'];

        $DataArray = [
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $payee_tax,
                "accountcrno" => '140',
                "narrationcr" => 'Payee Tax',
                "accountdrno" => '53',
                "narrationdr" => 'Salary & Wages - Administrative',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $salary_advance,
                "accountcrno" => '123',
                "narrationcr" => 'Salary Advance',
                "accountdrno" => '53',
                "narrationdr" => 'Salary & Wages - Administrative',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $emp_fund_reserve,
                "accountcrno" => '136',
                "narrationcr" => 'Employee Provident Fund Reserve',
                "accountdrno" => '53',
                "narrationdr" => 'Salary & Wages - Administrative',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $payment_suspense,
                "accountcrno" => '135',
                "narrationcr" => 'Salary Payment Suspense',
                "accountdrno" => '53',
                "narrationdr" => 'Salary & Wages - Administrative',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $travelling,
                "accountcrno" => '138',
                "narrationcr" => 'Employee Travelling Expenses Reserve',
                "accountdrno" => '9',
                "narrationdr" => 'Employee Travelling Expenses',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $incentive,
                "accountcrno" => '139',
                "narrationcr" => 'Employee Incentive Reserve',
                "accountdrno" => '10',
                "narrationdr" => 'Employee Incentive',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $EPFAdministration,
                "accountcrno" => '136',
                "narrationcr" => 'Employee Provident Fund Reserve',
                "accountdrno" => '54',
                "narrationdr" => 'EPF-Administration',
            ],
            [
                "userid" => $user,
                "company" => $companyId,
                "branch" => $companyBranchId,
                "tradate" => $date,
                "traamount" => $ETFAdministration,
                "accountcrno" => '137',
                "narrationcr" => 'Employee Trust Fund Reserve',
                "accountdrno" => '55',
                "narrationdr" => 'ETF-Administration',
            ],
        ];
           
        $allSuccessful = true;
        $responses = [];
        foreach ($DataArray as $Data) {
            $response = $this->sendCurlRequest($Data);
            $responses[] = $response;
            if (isset($response['error'])) {
                $allSuccessful = false;
                break; 
            }
        }

        if ($allSuccessful) {
            $schedule = new PaymentPeriod;
            $schedule->payroll_process_type_id = $request->input('payroll_process_type_id');
            $schedule->payment_period_fr = $request->input('payment_period_fr');
            $schedule->payment_period_to = $request->input('payment_period_to');
            $schedule->created_by = $request->user()->id;
            $schedule->save();

            return response()->json(['success' => 'Schedule Added Successfully.', 'new_obj' => $schedule]);
        } else {
            return response()->json(['errors' => ['One or more API requests failed.']]);
        }

	}

    private function sendCurlRequest($data) {
        $postfields = http_build_query($data);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://aws.erav.lk/multioffsetaccount/Api/Payrollsalaryprocess");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['error' => "cURL Error: " . $err];
        }
    
        $responseArray = json_decode($server_output, true);
        if ($responseArray['status'] != 200) {
            return ['error' => "Response not OK: " . $server_output];
        }
    
        return ['success' => "Request successful!", 'data' => $responseArray];
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\PaymentPeriod  $info
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentPeriod $info){
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PaymentPeriod  $info
     * @return \Illuminate\Http\Response
     */
	public function edit($id){
		
	}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentPeriod  $info
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PaymentPeriod $info){
        /*
		$rules = array(
            'pre_eligible_amount' => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

		$form_data = array(
            'pre_eligible_amount' =>  $request->pre_eligible_amount,
			'updated_by' => $request->user()->id
            
        );

        PaymentPeriod::whereId($request->remuneration_criteria)->update($form_data);

        return response()->json(['success' => 'Data is successfully updated']);
		
		*/
    }
	
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PaymentPeriod  $info
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
		
	}
	
	
	/*
	
	*/
	

}
