<?php

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

     /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function login(Request $request)
     {
         $this->validateLogin($request);
 
         // Validate the additional fields using Validator facade
         $validator = Validator::make($request->all(), [
             'company' => 'required|exists:companies,id',
             'company_name' => 'required|string',
             'company_branch' => 'required|exists:branches,id',
             'company_branch_name' => 'required|string',
         ]);
 
         if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput();
         }

            
 
         // Store company and branch in session
         Session::put('company_id', $request->input('company'));
         Session::put('company_name', $request->input('company_name'));
         Session::put('company_branch_id', $request->input('company_branch'));
         Session::put('company_branch_name', $request->input('company_branch_name'));
 
         $company = Company::select('address', 'mobile', 'land')
            ->where('id', $request->input('company'))
            ->first();

            if ($company) {
                Session::put('company_address', $company->address);
                Session::put('company_mobile', $company->mobile);
                Session::put('company_land', $company->land);
            }

         // Proceed with the default login logic
         if (method_exists($this, 'hasTooManyLoginAttempts') &&
             $this->hasTooManyLoginAttempts($request)) {
             $this->fireLockoutEvent($request);
 
             return $this->sendLockoutResponse($request);
         }
 
         if ($this->attemptLogin($request)) {
             return $this->sendLoginResponse($request);
         }
 
         $this->incrementLoginAttempts($request);
 
         return $this->sendFailedLoginResponse($request);
     }

        public function logout(Request $request)
        {
            Auth::logout();

            // Optionally preserve specific session values
            // $company = Session::get('company_id'); // if you want to save before flush

            // Clear session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Optional: re-store specific values
            // Session::put('company_id', $company);

          return redirect()->route('home');
        }

}
