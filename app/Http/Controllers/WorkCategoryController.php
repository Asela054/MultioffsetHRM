<?php

namespace App\Http\Controllers;

use App\AllowanceAmount;
use App\OtherFacility;
use App\WorkCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yajra\Datatables\Facades\Datatables;

class WorkCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('work-category-list');

        if(!$permission) {
            abort(403);
        }

        return view('organization.workCategory');
    }

    public function work_category_list_dt(Request $request)
    {
        $permission = Auth::user()->can('work-category-list');
        if (!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $query =  WorkCategory::query();

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '';

                if(Auth::user()->can('work-category-edit')) {
                    $btn .= '<button name="edit" style="margin-right:2px;" id="'.$row->id.'" class="edit btn btn-outline-primary btn-sm" type="submit"><i class="fas fa-pencil-alt"></i></button>';
                }

                if(Auth::user()->can('work-category-delete')) {
                    $btn .= '<button type="submit" name="delete" id="'.$row->id.'" class="delete btn btn-outline-danger btn-sm"><i class="far fa-trash-alt"></i></button>';
                }

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $permission = $user->can('work-category-create');

        if(!$permission) {
            abort(403);
        }

        $obj = new WorkCategory();
        $obj->name = $request->input('name');
        $obj->save();

        return response()->json(['success' => 'Work Category Added successfully.']);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $permission = $user->can('work-category-edit');

        if(!$permission) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (request()->ajax()) {
            $data = WorkCategory::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    public function update(Request $request, AllowanceAmount $allowanceAmount)
    {
        $user = auth()->user();
        $permission = $user->can('work-category-edit');

        if(!$permission) {
            abort(403);
        }

        $form_data = array(
            'name' => $request->name
        );

        WorkCategory::whereId($request->hidden_id)->update($form_data);

        return response()->json(['success' => 'Work Category is successfully updated']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('work-category-delete');

        if(!$permission) {
            abort(403);
        }

        $data = WorkCategory::findOrFail($id);
        $data->delete();
    }

    public function work_category_list_sel2(Request $request)
    {
        if ($request->ajax())
        {
            $page = Input::get('page');
            $resultCount = 25;

            $offset = ($page - 1) * $resultCount;

            $breeds = \Illuminate\Support\Facades\DB::query()
                ->from('work_categories')
                ->where('name', 'LIKE',  '%' . Input::get("term"). '%')
                ->orderBy('name')
                ->skip($offset)
                ->take($resultCount)
                ->get([DB::raw('DISTINCT id'),DB::raw('name as text')]);

            $count = DB::query()
                ->from('work_categories')
                ->where('name', 'LIKE',  '%' . Input::get("term"). '%')
                ->count();
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
