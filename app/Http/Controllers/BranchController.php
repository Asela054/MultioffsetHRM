<?php

namespace App\Http\Controllers;

use App\Branch;
use App\Company;
use App\Tbl_company_branch;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class BranchController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index($company_id)
    {
        $user = auth()->user();
        $permission = $user->can('location-list');
        if(!$permission) {
            abort(403);
        }

        $branch= Branch::orderBy('id', 'asc')->where('company_id', $company_id)->get();
        $company = Company::where('id', $company_id)->first();
        return view('Organization.branch',compact('branch','company'))->with('id', $company_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $user = auth()->user();
        $permission = $user->can('location-create');
        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rules = array(
            'location'    =>  'required',
            'contactno'    =>  'required|Numeric',
            'epf'    =>  'required',
            'etf'    =>  'required',
            'code'    =>  'required',
            'address'    =>  'required',
            'company_id' => 'required',
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $branch=new Branch;
       $branch->location=$request->input('location');  
       $branch->branch_code=$request->input('code');       
       $branch->address=$request->input('address');            
       $branch->contactno=$request->input('contactno');    
       $branch->email=$request->input('email');          
       $branch->epf=$request->input('epf');
       $branch->etf=$request->input('etf');
       $branch->company_id=$request->input('company_id');
       $branch->save();

       $requestID=$branch->id;
       $current_date_time = Carbon::now()->toDateTimeString();
       
        $Tbl_company_branch = new Tbl_company_branch();
        $Tbl_company_branch->idtbl_company_branch = $requestID;
        $Tbl_company_branch->branch = $request->input('location');
        $Tbl_company_branch->code = $request->input('code');
        $Tbl_company_branch->address1 = $request->input('address');
        $Tbl_company_branch->mobile = '-';
        $Tbl_company_branch->phone = $request->input('contactno');
        $Tbl_company_branch->email = $request->input('email');
        $Tbl_company_branch->status = '1';
        $Tbl_company_branch->insertdatetime = $current_date_time;
        $Tbl_company_branch->tbl_user_idtbl_user = Auth::id();
        $Tbl_company_branch->tbl_company_idtbl_company = $request->input('company_id');
        $Tbl_company_branch->save();
       
        return response()->json(['success' => 'branch Added successfully.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function show(Branch $branch)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(request()->ajax())
        {
            $user = auth()->user();
            $permission = $user->can('location-edit');
            if(!$permission) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $data = Branch::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Branch $branch)
    {
        $user = auth()->user();
        $permission = $user->can('location-edit');
        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $rules = array(
            'location'    =>  'required|String',
            'contactno'    =>  'required|Numeric',
            'code'    =>  'required',
            'address'    =>  'required',
            'epf'    =>  'required',
            'etf'    =>  'required',
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'location'    =>  $request->location,
            'contactno'        =>  $request->contactno,
            'branch_code'        =>  $request->code,
            'address'        =>  $request->address,
            'email'        =>  $request->email,
            'epf'        =>  $request->epf,
            'etf'        =>  $request->etf,
            
        );

        Branch::whereId($request->hidden_id)->update($form_data);

        $current_date_time = Carbon::now()->toDateTimeString();
        $existingbranch = Tbl_company_branch::find($request->hidden_id);

        if ($existingbranch) {
            $existingbranch->branch = $request->input('location');
            $existingbranch->code = $request->input('code');
            $existingbranch->address1 = $request->input('address');
            $existingbranch->mobile = '-';
            $existingbranch->phone = $request->input('contactno');
            $existingbranch->email = $request->input('email');
            $existingbranch->status = '1';
            $existingbranch->updatedatetime = $current_date_time;
            $existingbranch->updateuser = Auth::id();
            $existingbranch->save();
        }

        return response()->json(['success' => 'Branch is successfully updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Branch  $branch
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('location-delete');
        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = Branch::findOrFail($id);
        $data->delete();

        $data = Tbl_company_branch::findOrFail($id);
        $data->delete();
    }
}
