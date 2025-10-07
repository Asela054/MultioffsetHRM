<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class LoginController extends Controller
{
    public function index()
    {
        $companies = DB::table('companies')->get();
        $branches = DB::table('branches')->get();
        return view('auth.login', compact('companies', 'branches'));
    }

    public function getbranch(Request $request)
    {
        $companyId=$request->companyId;
        $branches = DB::table('branches')->where('company_id', $companyId)->get();
        return response()->json($branches);
    }
}
