<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanReportController extends Controller
{
    // Display the loan report view
    public function loanReport(Request $request)
    {
        $permission = Auth::user()->can('employee-loan-report');
        if (!$permission) {
            abort(403);
        }

        return view('Report.loanReport');
    }

    // Display the loan installment report view
    public function loanInstallmentReport(Request $request)
    {
        $permission = Auth::user()->can('employee-loan-report');
        if (!$permission) {
            abort(403);
        }

        return view('Report.loanInstallmentReport');
    }
}
