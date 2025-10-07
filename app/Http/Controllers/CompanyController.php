<?php

namespace App\Http\Controllers;

use App\Company;
use App\Department;
use App\Tbl_company;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('company-list');

        if(!$permission) {
            abort(403);
        }

        $company = Company::orderBy('id', 'asc')->paginate(10);
        return view('Organization.company', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $permission = $user->can('company-create');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rules = array(
            'name' => 'required',
            'code' => 'required',
            'mobile' => 'required|Numeric'
        );

        $error = Validator::make($request->all(), $rules);

        if ($error->fails()) {
            return response()->json(['errors' => $error->errors()->all()]);
        }
        $current_date_time = Carbon::now()->toDateTimeString();

        $company = new Company;
        $company->name = $request->input('name');
        $company->code = $request->input('code');
        $company->address = $request->input('address');
        $company->mobile = $request->input('mobile');
        $company->land = $request->input('land');
        $company->email = $request->input('email');
        $company->epf = $request->input('epf');
        $company->etf = $request->input('etf');
        $company->bank_account_name = $request->input('account_name');
        $company->bank_account_number = $request->input('account_no');
        $company->bank_account_branch_code = $request->input('account_branchcode');
        $company->employer_number = $request->input('employeeno');
        $company->zone_code = $request->input('zone_code');
        $company->ref_no = $request->input('ref_no');
        $company->vat_reg_no = $request->input('vat_reg_no');
        $company->svat_no = $request->input('svat_no');
        $company->save();

        $requestID=$company->id;

        $newCompany = new Tbl_company();
        $newCompany->idtbl_company = $requestID;
        $newCompany->company = $request->input('name');
        $newCompany->code = $request->input('code');
        $newCompany->address1 = $request->input('address');
        $newCompany->mobile = $request->input('mobile');
        $newCompany->phone = $request->input('land');
        $newCompany->email = $request->input('email');
        $newCompany->ref_no = $request->input('ref_no');
        $newCompany->vat_reg_no = $request->input('vat_reg_no');
        $newCompany->svat_no = $request->input('svat_no');
        $newCompany->status = '1';
        $newCompany->insertdatetime = $current_date_time;
        $newCompany->tbl_user_idtbl_user = Auth::id();
        $newCompany->save();

        return response()->json(['success' => 'Company Added successfully.']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Company $branch
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = auth()->user();
        $permission = $user->can('company-edit');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (request()->ajax()) {
            $data = Company::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Company $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $user = auth()->user();
        $permission = $user->can('company-edit');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rules = array(
            'name' => 'required',
            'code' => 'required',
            'mobile' => 'required|Numeric'
        );

        $error = Validator::make($request->all(), $rules);

        if ($error->fails()) {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'mobile' => $request->mobile,
            'land' => $request->land,
            'etf' => $request->etf,
            'epf' => $request->epf,
            'bank_account_name' => $request->account_name,
            'bank_account_number' => $request->account_no,
            'bank_account_branch_code' => $request->account_branchcode,
            'employer_number' => $request->employeeno,
            'zone_code' => $request->zone_code,
            'ref_no' => $request->ref_no,
            'vat_reg_no' => $request->vat_reg_no,
            'svat_no' => $request->svat_no
        );

        Company::whereId($request->hidden_id)->update($form_data);

        $current_date_time = Carbon::now()->toDateTimeString();
        $existingCompany = Tbl_company::find($request->hidden_id);

        if ($existingCompany) {
            $existingCompany->company = $request->input('name');
            $existingCompany->code = $request->input('code');
            $existingCompany->address1 = $request->input('address');
            $existingCompany->mobile = $request->input('mobile');
            $existingCompany->phone = $request->input('land');
            $existingCompany->email = $request->input('email');
            $existingCompany->ref_no = $request->input('ref_no');
            $existingCompany->vat_reg_no = $request->input('vat_reg_no');
            $existingCompany->svat_no = $request->input('svat_no');
            $existingCompany->status = '1';
            $existingCompany->updatedatetime = $current_date_time;
            $existingCompany->updateuser = Auth::id();
            $existingCompany->save();
        }

        return response()->json(['success' => 'Company is successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Company $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('company-delete');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = Company::findOrFail($id);
        $data->delete();

        $data = Tbl_company::findOrFail($id);
        $data->delete();
    }

    public function company_list_sel2(Request $request){
        if ($request->ajax())
        {
            $page = Input::get('page');
            $resultCount = 25;

            $offset = ($page - 1) * $resultCount;

            $breeds = Company::where('name', 'LIKE',  '%' . Input::get("term"). '%')
                ->orderBy('name')
                ->skip($offset)
                ->take($resultCount)
                ->get([DB::raw('id as id'),DB::raw('name as text')]);

            $count = Company::count();
            $endCount = $offset + $resultCount;
            $morePages = $endCount < $count;

            $results = array(
                "results" => $breeds,
                "pagination" => array(
                    "more" => $morePages
                )
            );

            return response()->json($results);
        }
    }

}
