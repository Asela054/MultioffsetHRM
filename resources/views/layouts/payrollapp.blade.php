<!DOCTYPE html>
<!-- Website - www.codingnepalweb.com -->
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title><?= (isset($page_stitle)) ? $page_stitle : ' Multi Offset Printers - By Erav Technology' ?>
    </title>
    <!-- Styles -->
    <link href="{{ url('/css/styles.css') }}" rel="stylesheet"/>
    <link href="{{ url('/css/custom_styles.css') }}" rel="stylesheet"/>
    <link href="{{ url('/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet"/>
    <link href="{{ url('/css/full_calendar.min.css') }}" rel="stylesheet"/>
    <link href="{{ url('/css/font/flaticon.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ url('/css/fontawesome/css/all.min.css') }}">

    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
<!--link href="{{ asset('css/app.css') }}" rel="stylesheet"-->
    <script data-search-pseudo-elements defer
            src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js"
            crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css" rel="stylesheet"/>
    
    <link href="{{ url('/payroll/css/payroll_styles.css') }}" rel="stylesheet" />
    <link href="{{ url('/payroll/jqchosen/chosen.css') }}" rel="stylesheet" />

    <style> 
        .no-underline {
            text-decoration: none;
            color: inherit; 
            font-weight: bold;
        }
        .no-underline:hover {
            text-decoration: none;
            color: inherit;
        }
        .custom-table {
                border-collapse: collapse;
                width: 100%;
            }

            .custom-table th, .custom-table td {
                text-align: center;
                font-size: 24px;
                padding: 20px;
            }

            .custom-table th:first-child {
                background-color: #fafbfd;
                color: #000;
            }
            .custom-table th:not(:first-child) {
                background-color: #87CEEB;
                color: #000;
            }

            .custom-table td:nth-child(2) {
                background-color: #32CD32; /* Green */
                color: #000;
            }

            .custom-table td:nth-child(3) {
                background-color: #FFA500; /* Orange */
                color: #000;
            }

            .custom-table td:nth-child(4) {
                background-color: #FF6347; /* Red */
                color: #000;
            }

            .row-label {
                font-weight: bold;
                font-size: 30px;
                text-align: right;
                padding-right: 20px;
                color: #000;
            }
    </style>

    @yield('style')
  </head>
  <body class="nav-fixed">
    <div id="app">
        <nav class="topnav navbar navbar-expand shadow navbar-light topnavbarcolor" id="sidenavAccordion">
            <a class="navbar-brand d-none d-sm-block" href="{{ url('/home') }}" style="color: white">
                <img class="img-fluid" src="{{ url('/images/toplogo.png') }}" alt="Employee Photo"/>
            </a>
            <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 mr-lg-2" id="sidebarToggle" href="#"><i class="fas fa-bars text-light"></i></button>
            {{-- @include('layouts.breadcrumblist') --}}
            <ul class="navbar-nav align-items-center ml-auto">
                @if (Auth::guest())
                    <li class="nav-item dropdown no-caret mr-3 dropdown-user"><a href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item dropdown no-caret mr-3 dropdown-user"><a href="{{ route('register') }}">Register</a>
                    </li>
                @else
                    {{-- <li class="nav-item no-caret mr-3">
                        <a href="https://aws.erav.lk/multioffsetpay" title="Goto Payroll System"
                           class="text-decoration-none text-dark"><i class="fas fa-book"></i>&nbsp;Payroll</a>
                    </li> --}}
                    <li class="nav-item">
                        <span class="fw-500 text-primary text-white"><?= date("l") ?></span>
                        &nbsp;&nbsp;<span class="text-primary text-white"><?= date("jS \of F Y") ?></span>
                        <span id="clock" onload="showTime()"></span>
                    </li>&nbsp;&nbsp;&nbsp;&nbsp;

                    <li class="nav-item dropdown no-caret mr-3 dropdown-user">
                        <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
                           href="javascript:void(0);" role="button" data-toggle="dropdown" aria-haspopup="true"
                           aria-expanded="false">
                           <!-- <img class="img-fluid" src="/images/{{ \App\EmployeePicture::where(['emp_id' =>  $empid=Auth::user()->emp_id ])->pluck('emp_pic_filename')->first() }}"/> -->
                        @php 
                            $id = Auth::user()->emp_id;

                            $employeePicture = \App\EmployeePicture::join('employees', 'employee_pictures.emp_id', '=', 'employees.id')
                                ->where('employees.emp_id', $id)
                                ->select('employee_pictures.emp_pic_filename')
                                ->first();
                                
                            $imagePath = '';
                            
                            if ($employeePicture && file_exists(public_path("images/{$employeePicture->emp_pic_filename}"))) {
                                $imagePath = asset("images/{$employeePicture->emp_pic_filename}");
                            } else {
                                $employeeGender = \App\Employee::where('emp_id', $id)->pluck('emp_gender')->first();
                                if(empty($employeeGender)){
                                    $employeeGender = "Male";
                                }
                                $imagePath = $employeeGender == "Male" 
                                    ? asset("images/man.png") 
                                    : asset("images/girl.png");
                            }
                        @endphp
                           <img class="img-fluid" src="{{ $imagePath }}"/>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right border-0 shadow animated--fade-in-up"
                             aria-labelledby="navbarDropdownUserImage">
                            <h6 class="dropdown-header d-flex align-items-center">
                                <img class="dropdown-user-img"
                                     src="{{ $imagePath }}"/>
                                <div class="dropdown-user-details">
                                    <div class="dropdown-user-details-name"> {{ Auth::user()->name }}</div>
                                    <div class="dropdown-user-details-email">{{ Auth::user()->email }}</div>
                                </div>
                            </h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#!"
                            >
                                <div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                                Account</a
                            ><a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();  document.getElementById('logout-form').submit();">
                                <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                                Logout</a
                            >
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </li>
                @endif
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div>
                @include('layouts.side_bar')
            </div>
            <div id="layoutSidenav_content">
    
                <section class="home-section">
                    @yield('content')
                  </section>
                <footer class="footer mt-auto footer-light" style="margin-left: 5rem;margin-right: 3rem">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 small">Copyright &copy;  Multi Offset Printers <?php echo date('Y') ?>
                                Made with <i class="fa fa-heart pulse text-danger"></i> by <a href="https://www.erav.lk" target="_blank">eRAV
                                    technologies</a>
                            </div>
                            <div class="col-md-6 text-md-right small">
                                <a href="#!">Privacy Policy</a>
                                &middot;
                                <a href="#!">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
<!-- Scripts -->
<script src="{{ url('/js/app.js') }}"></script>
<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>
<script src="{{ url('/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ url('/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ url('/js/scripts.js') }}"></script>
<script src="{{ url('/js/moment.js') }}"></script>
<script src="{{ url('/js/bootstrap-datetimepicker.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>

<!--script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js" ></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script-->

<!--script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.24.1/feather.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.17.1/components/prism-core.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.17.1/plugins/autoloader/prism-autoloader.min.js" crossorigin="anonymous"></script-->
{{-- <script src="{{ asset('/js/scripts.js') }}"></script> --}}
{{--    <script src="{{ asset('/js/bootstrap-datetimepicker.js') }}"></script>--}}


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script src="{{ asset('/payroll/jqchosen/chosen.jquery.js') }}"></script>
<script src="{{ asset('/payroll/js/payroll_scripts.js') }}"></script>
<script>
    $(document).ready(function(){
        window.scripturl = '{{ url('/scripts') }}';
});
</script>

@yield('script')
  </body>
</html>
