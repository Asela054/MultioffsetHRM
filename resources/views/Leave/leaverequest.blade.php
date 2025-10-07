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
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Company</label>
                                <select name="company" id="company_f" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Department</label>
                                <select name="department" id="department_f" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Location</label>
                                <select name="location" id="location_f" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Employee</label>
                                <select name="employee" id="employee_f" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-dark">Date : From - To</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" id="from_date" name="from_date" class="form-control form-control-sm border-right-0" placeholder="yyyy-mm-dd">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroup-sizing-sm"> </span>
                                    </div>
                                    <input type="date" id="to_date" name="to_date" class="form-control" placeholder="yyyy-mm-dd">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-sm filter-btn float-right" id="btn-filter"> Filter</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0 p-2">
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-outline-primary btn-sm fa-pull-right"
                                    name="create_record" id="create_record"><i class="fas fa-plus mr-2"></i>Add Leave Request
                            </button>
                        </div>
                        <div class="col-12">
                            <hr class="border-dark">
                        </div>
                        <div class="col-12">
                            <div class="daily_table table-responsive">
                            <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="divicestable">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Request Leave</th>
                                    <th>Leave From</th>
                                    <th>Leave To</th>
                                    <th>Reason</th>
                                    <th>Approve Status</th>
                                    <th>Leave Type</th>
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
        <div class="modal fade" id="formModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <h5 class="modal-title" id="staticBackdropLabel">Add Leave Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <span id="form_result"></span>
                                <form method="post" id="formTitle" class="form-horizontal">
                                    {{ csrf_field() }}
                                    <div class="form-row mb-1">
                                        <div class="col-6">
                                            <label class="small font-weight-bold text-dark">Select Employee</label>
                                            <select name="employee" id="employee" class="form-control form-control-sm" required>
                                                <option value="">Select</option>

                                            </select>
                                        </div>
                                         <div class="col-6">
                                                <label class="small font-weight-bold text-dark">Covering Employee</label>
                                                <select name="coveringemployee" id="coveringemployee"
                                                        class="form-control form-control-sm">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                    </div>

                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">From</label>
                                            <input type="date" name="fromdate" id="fromdate"
                                                   class="form-control form-control-sm" placeholder="YYYY-MM-DD" required/>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">To</label>
                                            <input type="date" name="todate" id="todate"
                                                   class="form-control form-control-sm" placeholder="YYYY-MM-DD" required/>
                                        </div>
                                    </div>
                                    <div class="form-row mb-1">

                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Half Day/ Short <span id="half_short_span"></span> </label>
                                            <select name="half_short" id="half_short" class="form-control form-control-sm" required>
                                                <option value="0.00">Select</option>
                                                <option value="0.25">Short Leave</option>
                                                <option value="0.5">Half Day</option>
                                                <option value="1.00">Full Day</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Reason</label>
                                            <input type="text" name="reason" id="reason" class="form-control form-control-sm"/>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mt-3">

                                        <input type="submit" id="action_button" class="btn btn-outline-primary btn-sm fa-pull-right px-4" value="Add"/>
                                    </div>
                                    <input type="hidden" name="action" id="action" value="Add"/>
                                    <input type="hidden" name="hidden_id" id="hidden_id"/>

                                </form>
                            </div>
                        </div>
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
                                <h4 class="font-weight-normal">Are you sure you want to remove this data?</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" name="ok_button" id="ok_button" class="btn btn-danger px-3 btn-sm">OK
                        </button>
                        <button type="button" class="btn btn-dark px-3 btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="approveModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
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
                                <h4 class="font-weight-normal">Are you sure you want to Approve this Request?</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" name="approve_button" id="approve_button" class="btn btn-warning px-3 btn-sm">Approve
                        </button>
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
        $(document).ready(function () {

            var canleaverequestapprove = false;
            @can('LeaveRequest-Approve')
            canleaverequestapprove = true;
            @endcan

            var leaverequestedit = false;
            @can('LeaveRequest-edit')
            leaverequestedit = true;
            @endcan

            var leaverequestdelete = false;
            @can('LeaveRequest-delete')
                leaverequestdelete = true;
            @endcan


            $('#attendant_menu_link').addClass('active');
            $('#attendant_menu_link_icon').addClass('active');
            $('#leavereuest').addClass('navbtnactive');

            let company_f = $('#company_f');
            let department_f = $('#department_f');
            let employee_f = $('#employee_f');
            let location_f = $('#location_f');

            var companyId = '{{ session("company_id") }}';
            var companyName = '{{ session("company_name") }}';
            
            if (companyId && companyName) {
                var option = new Option(companyName, companyId, true, true);
                company_f.append(option).trigger('change');
            }

           
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
                            company: department_f.val()
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
                            department: department_f.val()
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

            let employee = $('#employee');
            
            employee.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                parent: '#formModal',
                ajax: {
                    url: '{{url("employee_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });


            let c_employee = $('#coveringemployee');
            c_employee.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                parent: '#formModal',
                ajax: {
                    url: '{{url("employee_list_sel3")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });


           
            function load_dt(department, employee, location, from_date, to_date,company){
                $('#divicestable').DataTable({
                    lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
                    dom: 'lBfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            className: 'btn btn-default',
                            exportOptions: {
                                columns: 'th:not(:last-child)'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'Print',
                            className: 'btn btn-default',
                            exportOptions: {
                                columns: 'th:not(:last-child)'
                            }
                        }
                    ],
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: scripturl + '/leave_request_list.php',
                        type: 'POST',
                        data : 
                        {department :department, 
                        company :company,
                        location: location,
                        employee :employee, 
                        from_date: from_date,
                        to_date: to_date},

                    },
                    columns: [
                        { data: 'emp_id', name: 'emp_id' },
                        { data: 'emp_name_with_initial', name: 'emp_name_with_initial' },
                        { data: 'name', name: 'name' },
                        { data: 'leave_type', name: 'leave_type' },
                        { data: 'leave_from', name: 'from_date' },
                        { data: 'leave_to', name: 'to_date' },
                        { data: 'reason', name: 'reason' },
                        { data: 'status', name: 'status' },
                        { 
                            data: 'half_short', name: 'half_short', render: function(data, type, row) {
                                if (data == 1) {
                                    return "Full Day";
                                } else if (data == 0.50) {
                                    return "Half Day";
                                } else if (data == 0.25) {
                                    return "Short Leave";
                                } else {
                                    return "";
                                }
                            }
                        },
                        {
                        data: 'id',
                        name: 'action',
                        className: 'text-right',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var buttons = '';

                            if (canleaverequestapprove && row.approvestatus == 0) {
                                buttons += '<button type="submit" name="approve" id="'+row.id+'" class="approve btn btn-outline-warning btn-sm" style="margin:1px;" ><i class="fas fa-check"></i></button>';
                            }

                            if (leaverequestedit) {
                                buttons += '<button name="edit" id="'+row.id+'" class="edit btn btn-outline-primary btn-sm" style="margin:1px;" type="submit"><i class="fas fa-pencil-alt"></i></button>';
                            }
                            if (leaverequestdelete) {
                                buttons += '<button type="submit" name="delete" id="'+row.id+'" class="delete btn btn-outline-danger btn-sm" style="margin:1px;" ><i class="far fa-trash-alt"></i></button>';
                            }

                            return buttons;
                        }
                    }
                    ],
                    "bDestroy": true,
                    "order": [
                        [4, "desc"]
                    ]
                });
            }

            let company = $('#company_f').val();
            let location = $('#location_f').val();

            load_dt('', '',location, '', '', company);

            $('#formFilter').on('submit',function(e) {
                e.preventDefault();
                let department = $('#department_f').val();
                let employee = $('#employee_f').val();
                let location = $('#location_f').val();
                let from_date = $('#from_date').val();
                let to_date = $('#to_date').val();
                let company = $('#company_f').val();

                load_dt(department, employee, location, from_date, to_date,company);
            });

        });



        $(document).ready(function () {
            $('#create_record').click(function () {
                $('.modal-title').text('Add Leave Request');
                $('#action_button').val('Add');
                $('#action').val('Add');
                $('#form_result').html('');
                $('#formModal').modal('show');
            });

            $('#formTitle').on('submit', function (event) {
                event.preventDefault();
                var action_url = '';

                if ($('#action').val() == 'Add') {
                    action_url = "{{ route('leaverequestinsert') }}";
                }
                if ($('#action').val() == 'Edit') {
                    action_url = "{{ route('leaverequestupdate') }}";
                }


                $.ajax({
                    url: action_url,
                    method: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function (data) {

                        var html = '';
                        if (data.errors) {
                            html = '<div class="alert alert-danger">';
                            for (var count = 0; count < data.errors.length; count++) {
                                html += '<p>' + data.errors[count] + '</p>';
                            }
                            html += '</div>';
                        }
                        if (data.success) {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#formTitle')[0].reset();
                            location.reload();
                        }
                        $('#form_result').html(html);
                    }
                });
            });


            $(document).on('click', '.edit', function () {
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                $.ajax({
                    url: '{!! route("leaverequestedit") !!}',
                    type: 'POST',
                    dataType: "json",
                    data: {id: id },
                    success: function (data) {
                        let empOption = $("<option selected></option>").val(data.result.emp_id).text(data.result.emp_name);
                        $('#employee').append(empOption).trigger('change');
                        $('#employee').val(data.result.emp_id);
                        $('#fromdate').val(data.result.from_date);
                        $('#todate').val(data.result.to_date);
                        $('#half_short').val(data.result.leave_category);
                        $('#reason').val(data.result.reason);
                        $('#hidden_id').val(id);
                        $('.modal-title').text('Edit Leave Request');
                        $('#action_button').val('Edit');
                        $('#action').val('Edit');
                        $('#formModal').modal('show');
                    }
                })
            });

            var user_id;

            $(document).on('click', '.delete', function () {
                user_id = $(this).attr('id');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function () {
                $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                $.ajax({
                    url: '{!! route("leaverequestdelete") !!}',
                        type: 'POST',
                        dataType: "json",
                        data: {id: user_id },
                    beforeSend: function () {
                        $('#ok_button').text('Deleting...');
                    },
                    success: function (data) {
                        setTimeout(function () {
                            $('#confirmModal').modal('hide');
                            $('#divicestable').DataTable().ajax.reload();
                        }, 2000);
                        location.reload();
                    }
                })
            });


            
            $(document).on('click', '.approve', function () {
                user_id = $(this).attr('id');
                $('#approveModal').modal('show');
            });

            $('#approve_button').click(function () {
                $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                $.ajax({
                    url: '{!! route("leaverequestapprove") !!}',
                        type: 'POST',
                        dataType: "json",
                        data: {id: user_id },
                    beforeSend: function () {
                        $('#approve_button').text('Approving...');
                    },
                    success: function (data) {
                        setTimeout(function () {
                            $('#approveModal').modal('hide');
                            $('#divicestable').DataTable().ajax.reload();
                        }, 2000);
                        location.reload();
                    }
                })
            });

        });
    </script>

@endsection