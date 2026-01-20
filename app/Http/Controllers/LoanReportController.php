<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanReportController extends Controller
{
    public function loanReport(Request $request)
    {
        $permission = Auth::user()->can('employee-loan-report');
        if (!$permission) {
            abort(403);
        }

        return view('Report.loanReport');
    }
}
