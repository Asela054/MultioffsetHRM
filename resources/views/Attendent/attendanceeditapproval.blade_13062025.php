@extends('layouts.app')

@section('content')

    <main>
        <div class="page-header shadow">
            <div class="container-fluid">
                @include('layouts.attendant&leave_nav_bar')
               
            </div>
        </div> 
        <div class="container-fluid mt-4">
            <div class="card mb-2">
                <div class="card-body">
                    <form class="form-horizontal" id="formFilter">
                        <div class="form-row mb-1">
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Company <span class="text-danger">*</span> </label>
                                <select name="company" id="company_f" class="form-control form-control-sm" required style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Location <span class="text-danger">*</span></label>
                                <select name="location" id="location_f" class="form-control form-control-sm" required style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Department</label>
                                <select name="department" id="department_f" class="form-control form-control-sm" >
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Employee</label>
                                <select name="employee" id="employee_f" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-dark">Date : From - To <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" id="from_date" name="from_date" value="<?php echo date('Y-m-d'); ?>" class="form-control form-control-sm border-right-0" required placeholder="yyyy-mm-dd">
                                    <input type="date" id="to_date" name="to_date" value="<?php echo date('Y-m-d'); ?>" class="form-control" placeholder="yyyy-mm-dd" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <br>
                                <button type="submit" class="btn btn-primary btn-sm filter-btn" id="btn-filter"> Filter</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0 p-2">
                    <div class="row">
                        <div class="col-12">
                            <hr class="border-dark">
                        </div>
                        <div class="col-12">
                            <div id="response"></div>
                        </div>
                        <div class="col-md-12">
                            <div class=" text-center" id="empty_msg">
                                <div class="alert alert-info"> <span> Filter Company and Department to view attendance </span> </div>
                            </div>
                        </div>
                        <div class="col-12" id="attendtable_outer">
                            <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm small" style="width: 100%" id="attendtable">
                                <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Location</th>
                                    <th>Department</th>
                                    <th class="text-right">Action</th>
                                </tr>
                                </thead>

                                <tbody>

                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal Area Start -->
       
        <div class="modal fade" id="AttendviewModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <h5 class="modal-title" id="staticBackdropLabel">Previous Edit Attendent Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div id="message"></div>
                                <table id='attendTable' class="table table-striped table-bordered table-sm small">
                                    <thead>
                                        <th>Previous Check In Times</th>
                                        <th>Previous Check Out Times</th>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" name="comfirm_button" id="comfirm_button" class="approvelbtn btn btn-danger px-3 btn-sm">Approve</button>
                        <button type="button" class="btn btn-dark px-3 btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="confirmModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col text-center">
                                <h4 class="font-weight-normal">Are you sure you want to Approve this data?</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" name="ok_button" id="ok_button" class="btn btn-danger px-3 btn-sm">OK</button>
                        <button type="button" class="btn btn-dark px-3 btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="getdataModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col text-center">
                                <h4 class="font-weight-normal">Please check the devices connection and confirm?</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" name="comfirm_button" id="comfirm_button" class="btn btn-danger px-3 btn-sm">Confirm</button>
                        <button type="button" class="btn btn-dark px-3 btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Area End -->



    </main>

@endsection

@section('script')

    <script>
        $(document).ready(function() {

            $('#attendant_menu_link').addClass('active');
            $('#attendant_menu_link_icon').addClass('active');
            $('#attendantmaster').addClass('navbtnactive');

            $('#attendtable_outer').css('display', 'none');

            let company_f = $('#company_f');
            let department_f = $('#department_f');
            let location_f = $('#location_f');
            let area_f = $('#area_f');
            let employee_f = $('#employee_f');

            var companyId = '{{ session("company_id") }}';
            var companyName = '{{ session("company_name") }}';
            
            if (companyId && companyName) {
                var option = new Option(companyName, companyId, true, true);
                company_f.append(option).trigger('change');
            }
            // company_f.select2({
            //     placeholder: 'Select...',
            //     width: '100%',
            //     allowClear: true,
            //     ajax: {
            //         url: '{{url("company_list_sel2")}}',
            //         dataType: 'json',
            //         data: function(params) {
            //             return {
            //                 term: params.term || '',
            //                 page: params.page || 1
            //             }
            //         },
            //         cache: true
            //     }
            // });

            area_f.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("area_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                            company: company_f.val()
                        }
                    },
                    cache: true
                }
            });

            var branchId = '{{ session("company_branch_id") }}';
            var branchName = '{{ session("company_branch_name") }}';

            if (branchId && branchName) {
                var option = new Option(branchName, branchId, true, true);
                location_f.append(option).trigger('change');
            }

            // location_f.select2({
            //     placeholder: 'Select...',
            //     width: '100%',
            //     allowClear: true,
            //     ajax: {
            //         url: '{{url("location_list_sel2")}}',
            //         dataType: 'json',
            //         data: function(params) {
            //             return {
            //                 term: params.term || '',
            //                 page: params.page || 1,
            //                 company: company_f.val(),
            //                 area: area_f.val()
            //             }
            //         },
            //         cache: true
            //     }
            // });

            department_f.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("department_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                            company: company_f.val(),
                            area: area_f.val(),
                            location: location_f.val()
                        }
                    },
                    cache: true
                }
            });

            employee_f.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("employee_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                            company: company_f.val(),
                            area: area_f.val(),
                            location: location_f.val(),
                            department: department_f.val()
                        }
                    },
                    cache: true
                }
            });

            //employee_m
            $('#employee_m').select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("employee_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                            company: company_f.val(),
                            area: area_f.val(),
                            location: location_f.val(),
                            department: department_f.val()
                        }
                    },
                    cache: true,
                    dropdownParent: $('#monthAtModal')
                }
            });

            load_dt();
            $('#empty_msg').css('display', 'none');
            $('#attendtable_outer').css('display', 'block');

            function load_dt(){
                $('#attendtable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        "url": "{!! route('attendance_list_for_edit_approvel') !!}",
                        "data": {'company':company_f.val(),
                            'location': location_f.val(),
                            'department':department_f.val(),
                            'employee':employee_f.val(),
                            'from_date': $('#from_date').val(),
                            'to_date': $('#to_date').val(),
                        },
                    },
                    columns: [
                        { data: 'uid', name: 'uid' },
                        { data: 'emp_name_with_initial', name: 'emp_name_with_initial' },
                        { data: 'formatted_date', name: 'formatted_date' },
                        { data: 'first_time_stamp', name: 'first_time_stamp' },
                        { data: 'last_time_stamp', name: 'last_time_stamp' },
                        { data: 'location', name: 'location' },
                        { data: 'dep_name', name: 'dep_name' },
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                    "bDestroy": true,
                    "order": [
                        [2, "desc"]
                    ]
                });
            }

            //load_dt('', '', '', '', '');

            $('#formFilter').on('submit',function(e) {
                e.preventDefault();
                load_dt();

                $('#empty_msg').css('display', 'none');
                $('#attendtable_outer').css('display', 'block');
            });


            var emp_id='';
            var attend_date='';
            $(document).on('click', '.view_button', function () {
                id = $(this).attr('uid');
                date = $(this).attr('data-date');
                emp_name_with_initial = $(this).attr('data-name');

                emp_id=id;
                attend_date=date;

                var formdata = {
                    _token: $('input[name=_token]').val(),
                    id: id,
                    date: date
                };
                // alert(date);
                $('#form_result').html('');
                $.ajax({
                    url: "get_attendentedit_details",
                    dataType: "json",
                    data: formdata,
                    success: function (data) {
                        var editdetails_array = data;
                        $('#attendTable tbody').empty();

                        var maxLength = Math.max(editdetails_array.firstdetails.length, editdetails_array.lastdetails.length);

                        // Populate table with data
                        for (var i = 0; i < maxLength; i++) {
                            var firstValue = i < editdetails_array.firstdetails.length ? editdetails_array.firstdetails[i] : '';
                            var lastValue = i < editdetails_array.lastdetails.length ? editdetails_array.lastdetails[i] : '';

                            var row = '<tr>' +
                                '<td>' + firstValue + '</td>' +
                                '<td>' + lastValue + '</td>' +
                                '</tr>';

                            $('#attendTable tbody').append(row);
                        }
                    }
                });


                $('#AttendviewModal').modal('show');
            });
           

            // approvel
            $(document).on('click', '.approvelbtn', function () {
            rowid = $(this).attr('rowid');
            $('#confirmModal').modal('show');

            });

            $('#ok_button').click(function () {

                $('#message').html('');

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })

                $.ajax({
                    url: '{!! route("attendance_edit_approval") !!}',
                    type: 'POST',
                    dataType: "json",
                    data: {
                        emp_id: emp_id,
                        attend_date:attend_date
                    },
                    beforeSend: function () {
                        $('#ok_button').text('Approving...');
                    },
                    success: function (data) {
                        $('#confirmModal').modal('hide');
                        $('#message').html("<div class='alert alert-success'>"+data.success+"</div>");
                        setTimeout(function () {
                            // $('#dataTable').DataTable().ajax.reload();
                            // alert('Data Deleted');
                        }, 2000);
                        location.reload()
                    }
                })
            });

        });
    </script>

@endsection