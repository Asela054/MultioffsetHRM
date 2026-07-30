<?php

namespace App\Http\Controllers\ManPowerEmployee;

use App\ManPowerEmployee\Card;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;

class CardDetailController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = auth()->user();
        $permission = $user->can('card-list');
        if (!$permission) {
            abort(403);
        }

        $card= Card::orderBy('id', 'asc')
            ->where('status', '=', 1)->get();
        return view('ManPowerEmployee.cardDetail',compact('card'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $permission = $user->can('card-create');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $rules = array(
            'card_no'    =>  'required',
            'emp_no'    =>  'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'card_no'        =>  $request->card_no,
            'emp_no'        =>  $request->emp_no,
            'status'        =>  1
        );

        $card=new Card;
        $card->card_no=$request->input('card_no');       
        $card->emp_no=$request->input('emp_no');
        $card->status=1;
        $card->save();

        return response()->json(['success' => 'Card Added Successfully.']);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $permission = $user->can('card-edit');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        if(request()->ajax())
        {
            $data = Card::findOrFail($id);
            return response()->json(['result' => $data]);
        }
    }

    public function update(Request $request, Card $card)
    {
        $user = auth()->user();
        $permission = $user->can('card-edit');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $rules = array(
            'card_no'    =>  'required',
            'emp_no'    =>  'required'
        );

        $error = Validator::make($request->all(), $rules);

        if($error->fails())
        {
            return response()->json(['errors' => $error->errors()->all()]);
        }

        $form_data = array(
            'card_no'    =>  $request->card_no,
            'emp_no'    =>  $request->emp_no,
            'status'    =>  1
        );

        Card::whereId($request->hidden_id)->update($form_data);

        return response()->json(['success' => 'Data is successfully updated']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $permission = $user->can('card-delete');
        if(!$permission) {
            return response()->json(['error' => 'UnAuthorized'], 401);
        }

        $data = Card::findOrFail($id);
        $data->status = 3;
        $data->save();
        return response()->json(['success' => 'Data is successfully deleted']);
    }
}
