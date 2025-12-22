<?php
?>
@extends('layouts.app')

@section('content')

<main>

    <div class="container-fluid mt-2 p-0 p-2">
        <div class="card">
			<div class="card-body pb-5">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-3 col-xl-3">
                        <div class="card border h-100 p-3">
                            <h5 class="title-style"><span>TODAY ATTENDANCE</span></h5>
                            <div class="card mt-3">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex bd-highlight list-group-item-primary"><i class="fa-light fa-users mr-2"></i>TOTAL EMPLOYEE <span class="ml-auto">{{$empcount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-success pointer" id="attendancebtn"><i class="fa-light fa-calendar-week mr-2"></i>ATTENDANCE <span class="ml-auto">{{$todaycount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-warning pointer" id="lateattendancebtn"><i class="fa-light fa-business-time mr-2"></i>LATE <span class="ml-auto">{{$todaylatecount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-danger pointer" id="absentbtn"><i class="fa-light fa-calendar-xmark mr-2"></i>ABSENT <span class="ml-auto">{{$empcount-($todaycount+$todaylatecount)}}</span></li>
                                </ul>
                            </div>
                            <h5 class="title-style my-3"><span>YESTERDAY ATTENDANCE</span></h5>
                            <div class="card mt-3">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex bd-highlight list-group-item-primary"><i class="fa-light fa-users mr-2"></i>TOTAL EMPLOYEE <span class="ml-auto">{{$empcount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-success pointer" id="yesterdayattendancebtn"><i class="fa-light fa-calendar-week mr-2"></i>ATTENDANCE <span class="ml-auto">{{$yesterdaycount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-warning pointer" id="yesterdaylateattendancebtn"><i class="fa-light fa-business-time mr-2"></i>LATE <span class="ml-auto">{{$yesterdaylatecount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-danger pointer" id="yesterdayabsentbtn"><i class="fa-light fa-calendar-xmark mr-2"></i>ABSENT <span class="ml-auto">{{$empcount-($yesterdaycount+$yesterdaylatecount)}}</span></li>
                                </ul>
                            </div>
                            <!-- <h5 class="title-style my-3"><span>EMPLOYEE BIRTHDAYS</span></h5>
                            <div class="card mt-3">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex bd-highlight list-group-item-primary" id="todaybdbtn"><i class="fa-light fa-cake-candles mr-2"></i>TODAY <span class="ml-auto">{{$todayBirthdayCount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-success pointer" id="thisweekbdbtn"><i class="fa-light fa-calendar-week mr-2"></i>THIS WEEK <span class="ml-auto">{{$thisweekBirthdayCount}}</span></li>
                                    <li class="list-group-item d-flex bd-highlight list-group-item-warning pointer" id="thismonthbdbtn"><i class="fa-light fa-calendar-days mr-2"></i>THIS MONTH <span class="ml-auto">{{$thismonthBirthdayCount}}</span></li>
                                </ul>
                            </div> -->
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-auto">
                        <div class="h-100 mt-sm-0 mt-3">
                            <div class="calendar border h-100">
                                <div class="calendar-header text-left">
                                    <div class="year" id="calendarYear"></div>
                                    <div class="date" id="calendarDate"></div>
                                </div>
                                <div class="calendar-nav">
                                    <button onclick="prevMonth()">&#10094;</button>
                                    <div id="monthYear"></div>
                                    <button onclick="nextMonth()">&#10095;</button>
                                </div>
                                <div class="calendar-body">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>S</th>
                                                <th>M</th>
                                                <th>T</th>
                                                <th>W</th>
                                                <th>T</th>
                                                <th>F</th>
                                                <th>S</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendarDays"></tbody>
                                    </table>
                                </div>
                                <div class="event-list">
                                    <h2>Today's Events</h2>
                                    <div id="events-today"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col pt-sm-0 pt-3">
                        <div class="card h-100 mt-sm-0 mt-3">
                            <div class="card-body p-3">
                                <h5 class="title-style"><span>{{strtoupper(date('F / Y'))}} LEAVE INFORMATION</span></h5>
                                <div class="center-block fix-width scroll-inner" style="max-height: 600px;overflow-y: auto;padding-right: 5px;">
                                    <table class="table shadow-none table-sm mt-3 border-bottom small w-100">
                                        <tbody>
                                            @foreach($leavedatalist as $leavelist)
                                                @php
                                                    $employeePicture = $leavelist->emp_pic_filename;
                                                    $imagePath = '';
                                                    if (file_exists(public_path("images/{$employeePicture}")) && !empty($employeePicture)) {
                                                        $imagePath = asset("images/{$employeePicture}");
                                                    } else {
                                                        $employeeGender = \App\Employee::where('emp_id', $leavelist->emp_id)->pluck('emp_gender')->first();
                                                        if(empty($employeeGender)){
                                                            $employeeGender = "Male";
                                                        }
                                                        $imagePath = $employeeGender == "Male" 
                                                            ? asset("images/man.png") 
                                                            : asset("images/girl.png");
                                                    }
                                                @endphp
                                            <tr>
                                                <td style='width: 2.5rem;' nowrap>
                                                    <img style="height: 2.5rem;width: 2.5rem;margin-right: 1rem;border-radius: 100%;" src="{{$imagePath}}" alt="Employee Photo"/>
                                                </td>
                                                <td nowrap>
                                                    {{$leavelist->emp_name_with_initial}} - {{$leavelist->calling_name}}<br>
                                                    <small class="text-muted">{{$leavelist->department}}</small>
                                                </td>
                                                <td nowrap class="align-text-top">{{$leavelist->leave_type}}</td>
                                                <td nowrap class="align-text-top">{{$leavelist->leave_from}}</td>
                                                <td nowrap class="align-text-top">{{$leavelist->no_of_days}}</td>
                                                <td nowrap class="align-text-top">{{$leavelist->reson}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row row-cols-1 row-cols-md-2 mt-2">
                    <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">&nbsp;
                        <div class="card h-100">
                            <div class="card-body p-3">
                                <h5 class="title-style"><span>TODAY INFORMATION</span></h5>
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-9 col-xl-9">&nbsp;
                        <div class="card h-100 mt-sm-0 mt-3 d-none d-sm-block">
                            <div class="card-body p-3">
                                <h5 class="title-style"><span>ATTENDANCE OF EMPLOYEES LAST 30 DAYS</span></h5>
                                <canvas id="myAreaChart" height="30%" width="100%"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0 h-300" >
            <div class="card bg-info invoice-card h-100">
                <div class="card-body d-flex justify-content-center align-items-center text-center">
                    <div>
                        <h1 class="text-dark fs-18" style="font-weight: bold; font-size: 30px;">Total Employees</h1>
                        <h2 class="text-dark invoice-num" style="font-size: 30px;"><a href="{{route('addEmployee')}}" class="no-underline">{{$empcount}}</a></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-12 col-lg-9">
            <div class="card h-300">
                <div class="card-body d-flex">
                    <div class="container my-4">
                        <div class="row text-center header">
                            <div class="col-3 "></div>
                            <div class="col-3"><div><i class="fas fa-check-circle me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Attendance</h1></div>
                            <div class="col-3"><div><i class="fas fa-times-circle me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Late</h1></div>
                            <div class="col-3"><div><i class="fas fa-book me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Absent</h1></div>
                        </div>

                        <div class="row text-center justify-content-center align-items-center">
                            <div class="col-3 row-label"><h5 class="d-block d-md-none vertical-text">Today</h5><h1 class="d-none d-md-block">Today</h1></div>
                            <div class="col-3 status-box bg-attendance"><h1 class="text-success"> <a href="#" id="attendancebtn" class="no-underline"> {{$todaycount}} </a></h1></div>
                            <div class="col-3 status-box bg-late"><h1 class="text-warning"><a href="#" id="lateattendancebtn" class="no-underline"> {{$todaylatecount}} </a></h1></div>
                            <div class="col-3 status-box bg-absent"><h1 class="text-danger"><a href="#" id="absentbtn" class="no-underline"> {{$empcount-($todaycount+$todaylatecount)}} </a></h1></div>
                        </div>

                        <div class="row text-center justify-content-center align-items-center">
                            <div class="col-3 row-label"><h5 class="d-block d-md-none vertical-text">Yesterday</h5><h1 class="d-none d-md-block">Yesterday</h1></div>
                            <div class="col-3 status-box bg-attendance"><h1 class="text-success"><a href="#" id="yesterdayattendancebtn" class="no-underline"> {{$yesterdaycount}} </a></h1></div>
                            <div class="col-3 status-box bg-late"><h1 class="text-warning"><a href="#" id="yesterdaylateattendancebtn" class="no-underline"> {{$yesterdaylatecount}} </a></h1></div>
                            <div class="col-3 status-box bg-absent"><h1 class="text-danger"><a href="#" id="yesterdayabsentbtn" class="no-underline"> {{$empcount-($yesterdaycount+$yesterdaylatecount)}}</a></h1></div>
                        </div>
                        </div>

                </div>
            </div>
        </div> -->
    </div>
    
    <!-- <div class="container-fluid mt-4 row invoice-card-row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap border-0 pb-0">
                    <div class="me-auto mb-sm-0 mb-3">
                        <h4 class="card-title mb-2">Attendant of the Employees</h4>
                    </div>  
                </div>
                <div class="card-body pb-2">
                    <canvas id="myAreaChart" width="100%" height="30"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4 row invoice-card-row">
        
    </div> -->

</main>

<div class="modal fade" id="attendanceformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Attendance (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="attandancetable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="lateattendanceformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Late Attendance (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="lateattandancetable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="absentformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Absent (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="absenttable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="yesterdayattendanceformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Yesterday Attendance (<?php echo date('Y-m-d', strtotime('-1 day')); ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="yesterdayattandancetable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="yesterdaylateattendanceformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Yesterday Late Attendance (<?php echo date('Y-m-d', strtotime('-1 day')); ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="yesterdaylateattandancetable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="yesterdayabsentformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Yesterday Absent (<?php echo date('Y-m-d', strtotime('-1 day')); ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="yesterdayabsenttable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





    <!-- Birthday Table -->
    <!-- <div class="container-fluid mt-4 row invoice-card-row">
     @role('Admin','Report Admin','Achini_HRM','Nilushika_HRM','Nihal_HRM', 'Tharindu_Role')
       
        @endrole
        <div class="col-md-12">
            <div class="card shadow-lg border-0 h-100" style="border-radius: 15px;">
                <div class="card-body d-flex flex-column">
                    <table class="table table-bordered text-center" style="border-collapse: collapse; border-radius: 10px; overflow: hidden;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #4e73df, #1cc88a); color: white;">
                                <th style="border: none;"></th>
                                <th style="padding: 15px;">Today</th>
                                <th style="padding: 15px;">This Week</th>
                                <th style="padding: 15px;">This Month</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="row-label text-left" style="font-weight: bold; padding: 15px; background-color: #f5f5f5;">Employees Birthday</td>
                                <td style="background-color: #f5f5f5; padding: 15px;">
                                    <h2 class="text-primary mb-0" style="font-size: 2rem;">
                                        <a href="#" id="todaybdbtn" class="text-decoration-none text-primary">
                                            {{$todayBirthdayCount}}
                                        </a>
                                    </h2>
                                </td>
                                <td style="background-color: #e9ecef; padding: 15px;">
                                    <h2 class="text-success mb-0" style="font-size: 2rem;">
                                        <a href="#" id="thisweekbdbtn" class="text-decoration-none text-success">
                                            {{$thisweekBirthdayCount}}
                                        </a>
                                    </h2>
                                </td>
                                <td style="background-color: #f8d7da; padding: 15px;">
                                    <h2 class="text-danger mb-0" style="font-size: 2rem;">
                                        <a href="#" id="thismonthbdbtn" class="text-decoration-none text-danger">
                                            {{$thismonthBirthdayCount}}
                                        </a>
                                    </h2>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div> -->
    
 
      <!-- work day count part -->
<div class="modal fade" id="empworkdayformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Work Days (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="empworkdaytable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Birthday Part -->
<div class="modal fade" id="todaybdformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Birthday (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="todaybdtable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="thisweekbdformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Birthday (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="thisweekbdtable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="thismonthbdformModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="staticBackdropLabel">Department Wise Birthday (<?php echo date('Y-m-d') ?>)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 40rem; overflow-y: auto;">
                <div class="row">
                    <div class="col">
                        <div id="thismonthbdtable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>

<script>
    const calendarYear = document.getElementById("calendarYear");
    const calendarDate = document.getElementById("calendarDate");
    const monthYear = document.getElementById("monthYear");
    const calendarDays = document.getElementById("calendarDays");
    const eventsTodayContainer = document.getElementById("events-today");

    let today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();
    let selectedDate = today;

    const months = [
        "January", "February", "March", "April", "May", "June", 
        "July", "August", "September", "October", "November", "December"
    ];
    const days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];

    // Event data
    // const events = [
    //     { date: "09-02-2025", name: "Team Meeting - 10:00 AM", color: "#5d4697" },
    //     { date: "09-02-2025", name: "Lunch with Client - 1:00 PM", color: "#ff69b4" },
    //     { date: "09-11-2025", name: "Project Deadline", color: "gray" },
    //     { date: "09-20-2025", name: "Birthday Party", color: "#ff69b4" }
    // ];
    const events = {!! json_encode($events, JSON_PRETTY_PRINT | JSON_HEX_TAG) !!};

    function renderCalendar(month, year) {
        const firstDay = new Date(year, month, 1).getDay();
        const lastDate = new Date(year, month + 1, 0).getDate();
        
        // Header
        calendarYear.textContent = year;
        calendarDate.textContent = `${days[selectedDate.getDay()]}, ${selectedDate.getDate()} ${months[selectedDate.getMonth()].substr(0,3)}`;
        monthYear.textContent = `${months[month]} ${year}`;

        let date = 1;
        let table = "";
        
        // Always render 6 weeks (6 rows) to keep height consistent
        for (let i = 0; i < 6; i++) {
        let row = "<tr>";
        for (let j = 0; j < 7; j++) {
            if (i === 0 && j < firstDay) {
            row += "<td></td>";
            } else if (date > lastDate) {
            row += "<td></td>"; // Add empty cells for padding
            } else {
            let isSelected = 
                date === selectedDate.getDate() &&
                year === selectedDate.getFullYear() &&
                month === selectedDate.getMonth();

            const dateString = `${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}-${year}`;
            const todaysEvents = events.filter(event => event.date === dateString);
            
            let classes = "";
            if (isSelected) {
                classes += "selected";
            }
            
            let dotsHtml = "";
            if (todaysEvents.length > 0) {
                dotsHtml += `<div class="dot-container">`;
                todaysEvents.forEach(event => {
                    dotsHtml += `<span class="dot" style="background-color: ${event.color};"></span>`;
                });
                dotsHtml += `</div>`;
            }
            
            row += `<td class="${classes}" onclick="selectDate(${date},${month},${year})">${date}${dotsHtml}</td>`;
            date++;
            }
        }
        row += "</tr>";
        table += row;
        }
        calendarDays.innerHTML = table;
        renderEvents(selectedDate);
    }

    function renderEvents(date) {
        const dateString = `${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}-${date.getFullYear()}`;
        const todaysEvents = events.filter(event => event.date === dateString);
        let eventsHtml = "";

        if (todaysEvents.length > 0) {
            // console.log(todaysEvents);
            
            todaysEvents.forEach(event => {
                eventsHtml += `
                    <div class="event-item">
                        <span class="dot" style="background-color: ${event.color};"></span>
                        <span>${event.name}</span>
                    </div>
                `;
            });
        } else {
            eventsHtml = `<p class="no-events">No events for this day.</p>`;
        }
        eventsTodayContainer.innerHTML = eventsHtml;
    }

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
        }
        renderCalendar(currentMonth, currentYear);
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
        }
        renderCalendar(currentMonth, currentYear);
    }

    function selectDate(day, month, year) {
        selectedDate = new Date(year, month, day);
        renderCalendar(month, year);
    }

    renderCalendar(currentMonth, currentYear);

    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        // Chart data
        const data = {
            labels: ['Present', 'Late', 'Absent'],
            datasets: [{
                data: [{{$todaycount}}, {{$todaylatecount}}, {{$empcount-$todaycount}}],
                backgroundColor: [
                    '#1AC6D9', // Green for present
                    '#fce5b8', // Orange for late
                    '#f9bdb8'  // Red for absent
                ],
                borderColor: '#fff',
                borderWidth: 3,
                hoverBorderWidth: 4,
                hoverOffset: 10
            }]
        };
        
        // Chart options
        const options = {
            cutoutPercentage: 60,
            responsive: true,
            maintainAspectRatio: true,
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 20,
                    fontColor: '#2c3e50',
                    fontSize: 14,
                    usePointStyle: true,
                    boxWidth: 16
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.round((currentValue / dataset.data.reduce((a, b) => a + b, 0)) * 100);
                        return `${data.labels[tooltipItem.index]}: ${percentage}%`;
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 1500,
                easing: 'easeOutQuart'
            }
        };
        
        // Create the chart
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: options
        });
    });

$(document).ready( function () {

    $('#dashboard_link').addClass('active');
    $('#dashboard_link_icon').addClass('active');

    getattend();

   
    // today part
    $('#attendancebtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_attendance') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#attandancetable').html(data.result)
            }
        });

        $('#attendanceformModal').modal('show');
    });

    $('#lateattendancebtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_lateattendance') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#lateattandancetable').html(data.result)
            }
        });

        $('#lateattendanceformModal').modal('show');
    });

    $('#absentbtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_absent') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#absenttable').html(data.result)
            }
        });

        $('#absentformModal').modal('show');
    });

    // yesterday part
    $('#yesterdayattendancebtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_yesterdayattendance') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#yesterdayattandancetable').html(data.result)
            }
        });

        $('#yesterdayattendanceformModal').modal('show');
    });

    $('#yesterdaylateattendancebtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_yesterdaylateattendance') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#yesterdaylateattandancetable').html(data.result)
            }
        });

        $('#yesterdaylateattendanceformModal').modal('show');
    });

    $('#yesterdayabsentbtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_department_yesterdayabsent') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#yesterdayabsenttable').html(data.result)
            }
        });

        $('#yesterdayabsentformModal').modal('show');
    });

     // birthday part
    $('#count-btn-filter').click(function () {
    const empWorkingDays = $('#emp_working_days').val(); // Get selected value

    $.ajax({
        url: "{{ route('getdashboard_emp_work_days') }}",
        method: "GET",
        data: { emp_working_days: empWorkingDays }, // Pass value to the back-end
        dataType: "json",
        success: function (data) {
            $('#empworkdaytable').html(data.result);
            $('#empworkdayformModal').modal('show'); // Show modal after loading data
        }
    });
});


    $('#todaybdbtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_today_birthday') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#todaybdtable').html(data.result)
            }
        });

        $('#todaybdformModal').modal('show');
    });

    $('#thisweekbdbtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_thisweek_birthday') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#thisweekbdtable').html(data.result)
            }
        });

        $('#thisweekbdformModal').modal('show');
    });

    $('#thismonthbdbtn').click(function(){
        $.ajax({
            url: "{{ route('getdashboard_thismonth_birthday') }}",
            method: "GET",
            // data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

               $('#thismonthbdtable').html(data.result)
            }
        });

        $('#thismonthbdformModal').modal('show');
    });

    showTime();

    function showTime(){
        var date = new Date();
        var h = date.getHours(); // 0 - 23
        var m = date.getMinutes(); // 0 - 59
        var s = date.getSeconds(); // 0 - 59
        var session = "AM";

        if(h == 0){
            h = 12;
        }

        if(h > 12){
            h = h - 12;
            session = "PM";
        }

        h = (h < 10) ? "0" + h : h;
        m = (m < 10) ? "0" + m : m;
        s = (s < 10) ? "0" + s : s;

        var time = h + ":" + m + ":" + s + " " + session;
        document.getElementById("clock").innerText = time;
        document.getElementById("clock").textContent = time;

        setTimeout(showTime, 1000);
    }


    // getbranchattend();
  
} );
function getattend(){
    var empcount={{$empcount}}

        var url = "{{url('getdashboard_AttendentChart')}}";
        var date = new Array();
        var Labels = new Array();
        var count = new Array();
        var absent_count = new Array();
        $(document).ready(function(){
          $.get(url, function(response){
            response.forEach(function(data){
                const editedText = data.report_date.slice(0)
                date.push(editedText);               
                count.push(data.unique_employees_present);
                absent_count.push(data.absent_count);
            });
            var ctx = document.getElementById("myAreaChart");
                var myChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                      labels:date,
                      datasets: [{
                          label: 'Attendent',
                          data: count,
                          backgroundColor: 'rgb(75, 192, 192)',
                          borderWidth: 1
                      }, {
                    label: 'Absences',
                    data: absent_count,
                    backgroundColor: 'rgb(255, 99, 132)',
                    borderWidth: 1
                }]
                  },
                  options: {
                      scales: {
                          yAxes: [{
                              ticks: {
                                  beginAtZero:true
                              }
                          }]
                      },
                      tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            titleMarginBottom: 10,
            titleFontColor: "#6e707e",
            titleFontSize: 14,
            borderColor: "#dddfeb",
           
        }
                      
                  }
              });
          });
        });
};


   
        </script>

<script>
$(document).ready( function () {
    $('#empTable').DataTable();
} );
</script>



@endsection