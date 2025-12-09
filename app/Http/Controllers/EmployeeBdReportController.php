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

    public function employee_bd_report_list(Request $request)
    {
        $permission = Auth::user()->can('employee-report');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $companyId = Session::get('company_id');
        $companyBranchId = Session::get('company_branch_id');

        ## Read value
        $department = $request->get('department');
        $date = $request->get('date');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = DB::table('employees')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->leftjoin('employment_statuses', 'employment_statuses.id', '=', 'employees.emp_status')
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_location', $companyBranchId)
            ->where('employees.is_resigned', 0)
            ->where('employees.deleted', 0)
            ->count();

        $query = DB::table('employees');
        $query->select('count(*) as allcount');
            $query->where(function ($querysub) use ($searchValue) {
                $querysub->where('employees.id', 'like', '%' . $searchValue . '%')
                    ->orWhere('employees.emp_name_with_initial', 'like', '%' . $searchValue . '%')
                    ->orWhere('branches.location', 'like', '%' . $searchValue . '%')
                    ->orWhere('departments.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('employees.emp_id', 'like', '%' . $searchValue . '%')
                    ->orWhere('employees.emp_etfno', 'like', '%' . $searchValue . '%')
                    ->orWhere('employees.calling_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('employees.emp_birthday', 'like', '%' . $searchValue . '%');
            })
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_location', $companyBranchId)
            ->where('employees.is_resigned', 0)
            ->where('employees.deleted', 0);
        if ($department != "") {
            $query->where('employees.emp_department', $department);
        }
        if ($date != "") {
            $query->whereMonth('employees.emp_birthday', '=', date('m', strtotime($date)))
                ->whereDay('employees.emp_birthday', '=', date('d', strtotime($date)));
        }

        $totalRecordswithFilter = $query->count();

        // Fetch records
        $query = DB::table('employees');
        $query->where(function ($querysub) use ($searchValue) {
            $querysub->where('employees.id', 'like', '%' . $searchValue . '%')
                ->orWhere('employees.emp_name_with_initial', 'like', '%' . $searchValue . '%')
                ->orWhere('branches.location', 'like', '%' . $searchValue . '%')
                ->orWhere('departments.name', 'like', '%' . $searchValue . '%')
                ->orWhere('employees.emp_id', 'like', '%' . $searchValue . '%')
                ->orWhere('employees.emp_etfno', 'like', '%' . $searchValue . '%')
                ->orWhere('employees.calling_name', 'like', '%' . $searchValue . '%')
                ->orWhere('employees.emp_birthday', 'like', '%' . $searchValue . '%');
        })
            ->leftjoin('branches', 'employees.emp_location', '=', 'branches.id')
            ->leftjoin('departments', 'employees.emp_department', '=', 'departments.id')
            ->where('employees.emp_company', $companyId)
            ->where('employees.emp_location', $companyBranchId)
            ->where('employees.is_resigned', 0)
            ->where('employees.deleted', 0);
        if ($department != "") {
            $query->where('employees.emp_department', $department);
        }
        if ($date != "") {
            $query->whereMonth('employees.emp_birthday', '=', date('m', strtotime($date)))
                ->whereDay('employees.emp_birthday', '=', date('d', strtotime($date)));
        }

        $query->select(
            "employees.id",
            "employees.emp_id",
            "employees.emp_etfno",
            "employees.emp_name_with_initial",
            "employees.calling_name",
            "departments.name as dept_name",
            "employees.emp_birthday"
        );

        $query->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage);
        $records = $query->get();

        $data_arr = array();

        foreach ($records as $record) {

             // Calculate age
            $age = null;
            if ($record->emp_birthday) {
                $birthDate = new \DateTime($record->emp_birthday);
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
            }

            $data_arr[] = array(
                "emp_id" => $record->emp_id,
                "emp_etfno" => $record->emp_etfno,
                "employee_display" => EmployeeHelper::getDisplayName((object)[
                    'emp_id' => $record->emp_id,
                    'emp_name_with_initial' => $record->emp_name_with_initial,
                    'calling_name' => $record->calling_name
                ]),
                "emp_birthday" => $record->emp_birthday,
                "age" => $age,
                "dept_name" => $record->dept_name,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }
}
