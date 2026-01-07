<?php

namespace App\Http\Controllers;

use App\SpecialNote;
use App\SpecialNoteDetail;
use App\PaymentPeriod;
use Illuminate\Http\Request;
use Validator;
use DB;

class SpecialNoteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-list');
        if (!$permission) {
            abort(403);
        }

        $specialNotes = SpecialNote::with(['details.employee', 'paymentPeriod'])
            ->orderBy('id', 'desc')
            ->get();
        $payment_period = DB::table('payment_periods')
            ->where('payroll_process_type_id', 1)
            ->orderBy('payment_period_fr', 'desc')
            ->get();
        
        return view('SpecialNote.specialNote', compact('specialNotes', 'payment_period'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-create');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $rules = array(
            'period_filter_id' => 'required',
            'employee'         => 'required|array|min:1',
            'employee.*'       => 'required|integer',
            'note'             => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $specialNote = new SpecialNote;
        $specialNote->period_id = $request->input('period_filter_id');
        $specialNote->note = $request->input('note');
        $specialNote->created_by = auth()->id();
        $specialNote->save();

        $empIdMap = $this->convertEmpIdToId($request->input('employee'));

        foreach($request->input('employee') as $emp_id) {
            if(isset($empIdMap[$emp_id])) {
                $detail = new SpecialNoteDetail;
                $detail->note_id = $specialNote->id;
                $detail->emp_id = $empIdMap[$emp_id]; 
                $detail->save();
            }
        }

        return response()->json(['success' => 'Special Note Added Successfully.']);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-edit');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        if(request()->ajax())
        {
            $data = SpecialNote::with('details')->findOrFail($id);
            
            $employee_ids = DB::table('special_note_details')
                ->join('employees', 'special_note_details.emp_id', '=', 'employees.id')
                ->where('special_note_details.note_id', $id)
                ->pluck('employees.emp_id')
                ->toArray();
            
            return response()->json([
                'result' => $data,
                'employee_ids' => $employee_ids
            ]);
        }
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-edit');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $rules = array(
            'period_filter_id' => 'required',
            'employee'         => 'required|array|min:1',
            'employee.*'       => 'required|integer',
            'note'             => 'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $specialNote = SpecialNote::findOrFail($request->hidden_id);
        $specialNote->period_id = $request->input('period_filter_id');
        $specialNote->note = $request->input('note');
        $specialNote->updated_by = auth()->id();
        $specialNote->save();

        SpecialNoteDetail::where('note_id', $specialNote->id)->delete();

        $empIdMap = $this->convertEmpIdToId($request->input('employee'));

        foreach($request->input('employee') as $emp_id) {
            if(isset($empIdMap[$emp_id])) {
                $detail = new SpecialNoteDetail;
                $detail->note_id = $specialNote->id;
                $detail->emp_id = $empIdMap[$emp_id]; 
                $detail->save();
            }
        }

        return response()->json(['success' => 'Data is successfully updated']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-delete');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        SpecialNoteDetail::where('note_id', $id)->delete();
        
        $data = SpecialNote::findOrFail($id);
        $data->delete();
        
        return response()->json(['success' => 'Note deleted successfully']);
    }

    public function viewEmployees($id)
    {
        $user = auth()->user();
        $permission = $user->can('Special-Note-list');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        if(request()->ajax())
        {
            $specialNote = SpecialNote::with(['details.employee', 'paymentPeriod'])->findOrFail($id);
            
            $period = '';
            if($specialNote->paymentPeriod) {
                $period = $specialNote->paymentPeriod->payment_period_fr . ' to ' . $specialNote->paymentPeriod->payment_period_to;
            }
            
            $employees = [];
            foreach($specialNote->details as $detail) {
                if($detail->employee) {
                    $employees[] = [
                        'id' => $detail->employee->emp_id, 
                        'name' => $detail->employee->emp_name_with_initial . ' - ' . $detail->employee->calling_name
                    ];
                }
            }
            
            return response()->json([
                'period' => $period,
                'note' => $specialNote->note,
                'employees' => $employees
            ]);
        }
    }

    public function getEmployeesForEdit(Request $request, $id)
    {
        $employeeEmpIds = $request->input('ids');
        
        $employees = DB::table('employees')
            ->whereIn('emp_id', $employeeEmpIds)
            ->where('deleted', 0)
            ->select([
                'employees.emp_id as id',
                DB::raw('CONCAT(employees.emp_name_with_initial, " - ", employees.calling_name) as text')
            ])
            ->get();
        
        return response()->json($employees);
    }

    public function getEmployeeDepartment(Request $request)
    {
        $empId = $request->input('emp_id');
        
        $employee = DB::table('employees')
            ->join('departments', 'employees.emp_department', '=', 'departments.id')
            ->where('employees.emp_id', $empId)
            ->select('departments.id', 'departments.name as text')
            ->first();
        
        return response()->json($employee);
    }

    private function convertEmpIdToId($empIds)
    {
        return DB::table('employees')
            ->whereIn('emp_id', $empIds)
            ->where('deleted', 0)
            ->pluck('id', 'emp_id');
    }

}
