<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\JobCategory;
use App\PayrollchartAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PayrollchartAccountController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('Payroll-Accounts-list');
        $company_id = session('company_id');
        $company_branch_id = session('company_branch_id');
        
        if(!$permission) {
            abort(403);
        }

        $payrollaccounts = DB::table('payroll_chart_accounts')
            ->leftJoin('tbl_account as credit_account', 'credit_account.idtbl_account', '=', 'payroll_chart_accounts.credit_account_id')
            ->leftJoin('tbl_account as debit_account', 'debit_account.idtbl_account', '=', 'payroll_chart_accounts.debit_account_id')
            ->where('payroll_chart_accounts.status', '=', 1)
            ->select(
                'payroll_chart_accounts.*',
                'credit_account.accountno as credit_account_no',
                'credit_account.accountname as credit_account_name',
                'debit_account.accountno as debit_account_no',
                'debit_account.accountname as debit_account_name'
            )
            ->get();

        $accounts = DB::select("SELECT `tbl_account`.`idtbl_account` AS `accountid`, `tbl_account`.`accountno`, `tbl_account`.`accountname`, '1' AS `acctype` FROM `tbl_account` LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` LEFT JOIN `tbl_account_detail` ON `tbl_account_detail`.`tbl_account_idtbl_account`=`tbl_account`.`idtbl_account` WHERE `tbl_account`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=? AND `tbl_account_detail`.`tbl_account_idtbl_account` IS NULL UNION ALL SELECT `tbl_account_detail`.`idtbl_account_detail` AS `accountid`, `tbl_account_detail`.`accountno`, `tbl_account_detail`.`accountname`, '2' AS `acctype` FROM `tbl_account_detail` LEFT JOIN `tbl_account_allocation` ON `tbl_account_allocation`.`tbl_account_detail_idtbl_account_detail`=`tbl_account_detail`.`idtbl_account_detail` WHERE `tbl_account_detail`.`status`=? AND `tbl_account_allocation`.`status`=? AND `tbl_account_allocation`.`companybank`=? AND `tbl_account_allocation`.`branchcompanybank`=?",
        [1, 1, $company_id, $company_branch_id, 1, 1, $company_id, $company_branch_id]);


        return view('Organization.payrollaccounts', compact('payrollaccounts','accounts'));
    }

    public function store(Request $request)
    {
      $permission = \Auth::user()->can('Payroll-Accounts-create');
        if (!$permission) {
            abort(403);
        }

        $companyId = Session::get('company_id');
        $branchId = Session::get('company_branch_id');

        $type=$request->input('type');
        $creditacc=$request->input('creditacc');
        $debitacc=$request->input('debitacc');
        $typeCode = strtoupper(str_replace(' ', '_', $type));
        
        $request = new PayrollchartAccount();
        $request->type=$type;
        $request->type_code= $typeCode;
        $request->credit_account_id=$creditacc;
        $request->debit_account_id=$debitacc;
        $request->company_id=$companyId;
        $request->branch_id=$branchId;
        $request->status= '1';
        $request->created_by=Auth::id();
        $request->updated_by = '0';
        $request->save();


       
        return response()->json(['success' => 'Payroll Chart of Account Added successfully.']);
    }

     public function edit(Request $request)
    {
        $permission = \Auth::user()->can('Payroll-Accounts-edit');
        if (!$permission) {
            abort(403);
        }

        $id = Request('id');
        if (request()->ajax()){
        $data = DB::table('payroll_chart_accounts')
        ->select('payroll_chart_accounts.*')
        ->where('payroll_chart_accounts.id', $id)
        ->get(); 
        return response() ->json(['result'=> $data[0]]);
        }
    }

     public function update(Request $request){

        $permission = \Auth::user()->can('Payroll-Accounts-edit');
        if (!$permission) {
            abort(403);
        }

        $type=$request->input('type');
        $creditacc=$request->input('creditacc');
        $debitacc=$request->input('debitacc');

        $typeCode = strtoupper(str_replace(' ', '_', $type));
        $hidden_id=$request->input('hidden_id');

        $data = array(
            'type' => $type,
            'type_code' => $typeCode,
            'credit_account_id' => $creditacc,
            'debit_account_id' => $debitacc,
            'updated_by' => Auth::id(),
        );

        PayrollchartAccount::where('id', $hidden_id)
        ->update($data);
        return response()->json(['success' => 'Payroll Chart of Account Details Updated successfully.']);
    }

      public function delete(Request $request)
    {
        $id = Request('id');
        $form_data = array(
            'status' =>  '3',
            'updated_by' => Auth::id()
        );
        PayrollchartAccount::where('id',$id)
        ->update($form_data);

        return response()->json(['success' => 'Payroll Chart of Account is Successfully Deleted']);
    }
   
}
