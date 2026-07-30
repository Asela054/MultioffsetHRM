<?php

namespace App\Http\Controllers\ManPowerEmployee;

use App\ManPowerEmployee\EmployeeDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Datatables;
use DB;

class EmployeeAllocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
       
        $user = Auth::user();
        $permission = $user->can('manpower-employee-list');
        if(!$permission) {
            abort(403);
        }

        $card = DB::table('manpower_cards')
        ->select('manpower_cards.*')
        ->where('manpower_cards.status', '1')
        ->get();

        $nationalIds = DB::table('manpower_employee_details')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('manpower_employee_details')
                      ->whereNotNull('national_id')
                      ->where('national_id', '<>', '')
                      ->groupBy('national_id');
            })
            ->select('national_id', 'employee', 'company', 'phone')
            ->orderBy('national_id')
            ->get();

        return view('ManPowerEmployee.employeeAllocation', compact('card', 'nationalIds'));
    }

    public function insert(Request $request)
    {

        $user = Auth::user();
        $permission = $user->can('manpower-employee-create');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $tableData = $request->input('tableData');

        foreach ($tableData as $rowtabledata) {
            $date           = $rowtabledata['col_1'];
            $card_id          = $rowtabledata['col_2'];
            $off_next_day_lbl = $rowtabledata['col_3']; 
            $employee       = $rowtabledata['col_4']; 
            $national_id    = $rowtabledata['col_5'];
            $company         = $rowtabledata['col_6'];   
            $phone          = $rowtabledata['col_7'];   
            $off_next_day     = ($off_next_day_lbl === 'Yes') ? 1 : 0;

            $Employeeshiftdetail = new EmployeeDetail();
            $Employeeshiftdetail->card_id         = $card_id;
            $Employeeshiftdetail->date        = $date;
            $Employeeshiftdetail->employee       = $employee;
            $Employeeshiftdetail->national_id    = $national_id;
            $Employeeshiftdetail->off_next_day     = $off_next_day;
            $Employeeshiftdetail->company         = $company;
            $Employeeshiftdetail->phone          = $phone;
            $Employeeshiftdetail->status           = '1';
            $Employeeshiftdetail->created_by       = Auth::id();
            $Employeeshiftdetail->updated_by       = '0';
            $Employeeshiftdetail->save();
        }

        return response()->json(['success' => 'Employees are Successfully Added']);
    }

    public function delete(Request $request){

        $user = Auth::user();
        $permission = $user->can('manpower-employee-delete');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }
        
        $id = $request->input('id');
        $current_date_time = Carbon::now()->toDateTimeString();
        $form_data = array(
            'status' =>  '3',
            'updated_by' => Auth::id(),
            'updated_at' => $current_date_time,
        );
        EmployeeDetail::findOrFail($id)
        ->update($form_data);

        return response()->json(['success' => 'Employee Detail is Successfully Deleted']);

    }

}
