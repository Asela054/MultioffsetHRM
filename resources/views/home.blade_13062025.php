<?php
?>
@extends('layouts.app')

@section('content')

<main style="padding-top: 2.5rem;">

    <div class="container-fluid mt-4 row invoice-card-row">

        <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0 h-300" >
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
                        <!-- Header -->
                        <div class="row text-center header">
                            <div class="col-3 "></div>
                            <div class="col-3"><div><i class="fas fa-check-circle me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Attendance</h1></div>
                            <div class="col-3"><div><i class="fas fa-times-circle me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Late</h1></div>
                            <div class="col-3"><div><i class="fas fa-book me-1 fa-2x"></i></div><h1 class="d-none d-md-block">Absent</h1></div>
                        </div>

                        <!-- Today -->
                        <div class="row text-center justify-content-center align-items-center">
                            <div class="col-3 row-label"><h5 class="d-block d-md-none vertical-text">Today</h5><h1 class="d-none d-md-block">Today</h1></div>
                            <div class="col-3 status-box bg-attendance"><h1 class="text-success"> <a href="#" id="attendancebtn" class="no-underline"> {{$todaycount}} </a></h1></div>
                            <div class="col-3 status-box bg-late"><h1 class="text-warning"><a href="#" id="lateattendancebtn" class="no-underline"> {{$todaylatecount}} </a></h1></div>
                            <div class="col-3 status-box bg-absent"><h1 class="text-danger"><a href="#" id="absentbtn" class="no-underline"> {{$empcount-$todaycount}} </a></h1></div>
                        </div>

                        <!-- Yesterday -->
                        <div class="row text-center justify-content-center align-items-center">
                            <div class="col-3 row-label"><h5 class="d-block d-md-none vertical-text">Yesterday</h5><h1 class="d-none d-md-block">Yesterday</h1></div>
                            <div class="col-3 status-box bg-attendance"><h1 class="text-success"><a href="#" id="yesterdayattendancebtn" class="no-underline"> {{$yesterdaycount}} </a></h1></div>
                            <div class="col-3 status-box bg-late"><h1 class="text-warning"><a href="#" id="yesterdaylateattendancebtn" class="no-underline"> {{$yesterdaylatecount}} </a></h1></div>
                            <div class="col-3 status-box bg-absent"><h1 class="text-danger"><a href="#" id="yesterdayabsentbtn" class="no-underline"> {{$empcount-$yesterdaycount}}</a></h1></div>
                        </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid mt-4 row invoice-card-row">
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
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap border-0 pb-0">
                    <div class="me-auto mb-sm-0 mb-3">
                        <h4 class="card-title mb-2">Attendant of the Employees Line Chart</h4>
                    </div>  
                </div>
                <div class="card-body pb-2">
                    <canvas id="myLineChart" width="100%" height="30"></canvas>
                </div>
            </div>
        </div>
    </div>

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
      

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>

<script>
$(document).ready( function () {

    $('#dashboard_link').addClass('active');
    $('#dashboard_link_icon').addClass('active');

    getattend();
    getattend_linechart();
   
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
                const editedText = data.date.slice(0, -8)
                date.push(editedText);               
                count.push(data.count);
                absent_count.push(empcount-(data.count));
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

// line chart
function getattend_linechart(){
    var empcount={{$empcount}}
    var url = "{{url('getdashboard_AttendentChart')}}";
    var date = new Array();
    var Labels = new Array();
    var attendance_count = new Array();
    var absent_count = new Array();
    $(document).ready(function(){
      $.get(url, function(response){
        response.forEach(function(data){
            const editedText = data.date.slice(0, -8)
            date.push(editedText);               
            attendance_count.push(data.count);
            absent_count.push(empcount-(data.count));
        });
        var ctx = document.getElementById('myLineChart').getContext('2d');
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels:date,
                datasets: [{
                    label: 'Attendance',
                    borderColor: 'rgb(75, 192, 192)',
                    data: attendance_count,
                    fill: false
                }, {
                    label: 'Absences',
                    borderColor: 'rgb(255, 99, 132)',
                    data: absent_count,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true, // Set to true to maintain aspect ratio
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
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