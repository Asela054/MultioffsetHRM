<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmployeeHelper;

class EmployeeBdReportController extends Controller
{

    public function getemployeeBdlist()
    {
        $permission = Auth::user()->can('employee-report');
        if (!$permission) {
            abort(403);
        }
        return view('Report.employeeBdReport');
    }
}
