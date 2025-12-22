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
                                    name="create_record" id="create_record"><i class="fas fa-plus mr-2"></i>Add Leave
                            </button>
                        </div>
                        <div class="col-12">
                            <hr class="border-dark">
                        </div>
                        <div class="col-12">
                            <div class="center-block fix-width scroll-inner">
                            <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="divicestable">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Leave Type</th>
                                    <th>Leave Type *</th>
                                    <th>Leave From</th>
                                    <th>Leave To</th>
                                    <th>Reason</th>
                                    <th>Covering Person</th>
                                    <th>Status</th>
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
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header p-2">
                        <h5 class="modal-title" id="staticBackdropLabel">Add Leave</h5>
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

                                        
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Select Employee</label>
                                            <select name="employee" id="employee" class="form-control form-control-sm">
                                                <option value="">Select</option>

                                            </select>
                                        </div>
                                        <div class="col">
                                            <label class="small font-weight-bold text-dark">Leave Type</label>
                                            <select name="leavetype" id="leavetype"
                                                    class="form-control form-control-sm">
                                                <option value="">Select</option>
                                                @foreach($leavetype as $leavetypes)
                                                    <option value="{{$leavetypes->id}}">{{$leavetypes->leave_type}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row mb-1">
                                        <div class="col-6">
                                            <table class="table table-sm small">
                                                <thead>
                                                    <tr>
                                                        <th>Leave Type</th>
                                                        <th>Total</th>
                                                        <th>Taken</th>
                                                        <th>Available</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td> <span> Annual </span> </td>
                                                        <td> <span id="annual_total"></span> </td>
                                                        <td> <span id="annual_taken"></span> </td>
                                                        <td> <span id="annual_available"></span> </td>
                                                    </tr>
                                                    <tr>
                                                        <td> <span> Casual </span> </td>
                                                        <td> <span id="casual_total"></span> </td>
                                                        <td> <span id="casual_taken"></span> </td>
                                                        <td> <span id="casual_available"></span> </td>
                                                    </tr>
                                                    <tr>
                                                        <td> <span>Medical</span> </td>
                                                        <td> <span id="med_total"></span> </td>
                                                        <td> <span id="med_taken"></span> </td>
                                                        <td> <span id="med_available"></span> </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <span id="leave_msg"></span>
                                        </div>
                                        <div class="col-6">
                                            <table class="table table-sm small">
                                                <thead>
                                                    <tr>
                                                        <th>From Date</th>
                                                        <th>To Date</th>
                                                        <th>Type</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="requestbody">
                                                   
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                        <div class="form-row mb-1">
                                            <div class="col-6">
                                                <label class="small font-weight-bold text-dark">Covering Employee</label>
                                                <select name="coveringemployee" id="coveringemployee"
                                                        class="form-control form-control-sm">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row mb-1">
                                            <div class="col-3">
                                                <label class="small font-weight-bold text-dark">From</label>
                                                <input type="date" name="fromdate" id="fromdate"
                                                       class="form-control form-control-sm" placeholder="YYYY-MM-DD"/>
                                            </div>
                                            <div class="col-3">
                                                <label class="small font-weight-bold text-dark">To</label>
                                                <input type="date" name="todate" id="todate"
                                                       class="form-control form-control-sm" placeholder="YYYY-MM-DD"/>
                                            </div>
                                        </div>
                                    
                                    <div class="row">
                                            <div class="col-6">
                                                <label class="small font-weight-bold text-dark">Half Day/ Short <span id="half_short_span"></span> </label>
                                                <select name="half_short" id="half_short"
                                                        class="form-control form-control-sm">
                                                    <option value="0.00">Select</option>
                                                    <option value="0.25">Short Leave</option>
                                                    <option value="0.5">Half Day</option>
                                                    <option value="1.00">Full Day</option>
                                                </select>
                                        </div>
                                            <div class="col-6">
                                                <label class="small font-weight-bold text-dark">No of Days</label>
                                                <input type="number" step="0.01" name="no_of_days" id="no_of_days"
                                                       class="form-control form-control-sm" />
                                            </div>
                                    </div>
                                 
                                    <div class="row">
                                            <div class="col-6">
                                                <label class="small font-weight-bold text-dark">Reason</label>
                                                <input type="text" name="reson" id="reson"
                                                       class="form-control form-control-sm"/>
                                        </div>
                       
                                            <div class="col-6">
                                                <label class="small font-weight-bold text-dark">Approve Person</label>
                                                <select name="approveby" id="approveby"
                                                        class="form-control form-control-sm">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                    </div>
                                    
                                    <div class="form-group mt-3">

                                        <input type="submit" id="action_button" class="btn btn-outline-primary btn-sm fa-pull-right px-4" value="Add"/>
                                    </div>
                                    <input type="hidden" name="action" id="action" value="Add"/>
                                    <input type="hidden" name="hidden_id" id="hidden_id"/>
                                    <input type="hidden" name="request_id" id="request_id"/>
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
        <!-- Modal Area End -->
    </main>

@endsection


@section('script')

    <script>
        $(document).ready(function () {

            $('#attendant_menu_link').addClass('active');
            $('#attendant_menu_link_icon').addClass('active');
            $('#leavemaster').addClass('navbtnactive');

            var canleaveedit = false;
            @can('leave-edit')
                canleaveedit = true;
            @endcan

            var leavedelete = false;
            @can('leave-delete')
                leavedelete = true;
            @endcan


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
                            company: company_f.val()
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
                            department: department_f.val(),
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

            let approveby = $('#approveby');
            approveby.select2({
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

            function load_dt(department, employee, location, from_date, to_date ,company){
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
                        url: scripturl + '/leave_apply_list.php',
                        type: 'POST',
                        data : 
                        {department :department, 
                        company :company,
                        employee :employee, 
                        location: location,
                        from_date: from_date,
                        to_date: to_date},

                    },
                    columns: [
                        { data: 'emp_id', name: 'emp_id' },
                        { data: 'emp_name', name: 'emp_name' },
                        { data: 'dep_name', name: 'emp_name' },
                        { data: 'leave_type', name: 'leave_type' },
                        { 
                        data: 'half_short', name: 'half_short', render: function(data, type, row) {
                            if (data == 1) {
                                return "Full Day";
                            } else if (data == 0.50) {
                                return "Half Day";
                            } else if (data == 0.25) {
                                return "Short Leave";
                            } else {
                                return "Unknown";
                            }
                        }
                    },
                        { data: 'leave_from', name: 'leave_from' },
                        { data: 'leave_to', name: 'leave_to' },
                        { data: 'reson', name: 'reson' },
                        { data: 'covering_emp', name: 'covering_emp' },
                        { data: 'status', name: 'status' },
                        
                        {
                        data: 'id',
                        name: 'action',
                        className: 'text-right',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var buttons = '';

                            if (canleaveedit) {
                                buttons += '<button name="edit" id="'+ row.id +'"class="edit btn btn-outline-primary btn-sm" style="margin:1px;" type="submit"><i class="fas fa-pencil-alt"></i></button>';
                            }

                            if (leavedelete) {
                                buttons += '<button type="submit" name="delete" id="'+ row.id +'"class="delete btn btn-outline-danger btn-sm" style="margin:1px;" ><i class="far fa-trash-alt"></i></button>';
                            }

                            return buttons;
                        }
                    }
                    ],
                    "bDestroy": true,
                    "order": [
                        [5, "desc"]
                    ]
                });
            }

            let company = $('#company_f').val();

            load_dt('', '', branchId, '', '',company);

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

            $(document).on('change', '#fromdate', function () {
                show_no_of_days();
            });

            $(document).on('change', '#todate', function () {
                show_no_of_days();
            });

            $(document).on('change', '#half_short', function () {
                show_no_of_days();
            });

            function treatAsUTC(date) {
                var result = new Date(date);
                result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
                return result;
            }

            function daysBetween(startDate, endDate) {
                var millisecondsPerDay = 24 * 60 * 60 * 1000;
                return (treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay;
            }

            function show_no_of_days() {
                let from_date = $('#fromdate').val();
                let to_date = $('#todate').val();
                let half_short = $('#half_short').val();
                let no_of_days = 0;

                if (from_date != '' && to_date != ''){
                    no_of_days = parseFloat(daysBetween(from_date, to_date)) + parseFloat(half_short) ;
                    $('#no_of_days').val(no_of_days);
                }
            }

        });

        $('#employee').change(function () {
            var _token = $('input[name="_token"]').val();
            
            var emp_id = $('#employee').val();
            var status = $('#employee option:selected').data('id');

            if (emp_id != '') {
                $.ajax({
                    url: "getEmployeeLeaveStatus",
                    method: "POST",
                    data: {status: status, emp_id: emp_id, _token: _token},
                    success: function (data) {

                        $('#leave_msg').html('');

                         $('#annual_total').html(data.total_no_of_annual_leaves);
                         $('#annual_taken').html(data.total_taken_annual_leaves);
                         $('#annual_available').html(data.available_no_of_annual_leaves);

                        $('#casual_total').html(data.total_no_of_casual_leaves);
                        $('#casual_taken').html(data.total_taken_casual_leaves);
                        $('#casual_available').html(data.available_no_of_casual_leaves);

                        $('#med_total').html(data.total_no_of_med_leaves);
                        $('#med_taken').html(data.total_taken_med_leaves);
                        $('#med_available').html(data.available_no_of_med_leaves);

                        let msg = '' +
                            '<div class="alert alert-warning text-sm" style="padding: 3px;"> ' +
                                data.leave_msg +
                            '</div>'

                        if(data.leave_msg != ''){
                            $('#leave_msg').html(msg);
                        }

                        if(data.available_no_of_annual_leaves<=0){$('#leavetype option[value="1"]').attr('disabled','disabled');}
                        if(data.available_no_of_casual_leaves<=0){$('#leavetype option[value="2"]').attr('disabled','disabled');}
                        if(data.available_no_of_med_leaves<=0){$('#leavetype option[value="4"]').attr('disabled','disabled');}
                    }
                });
            }

        });

        $('#employee').change(function () {
            var _token = $('input[name="_token"]').val();
            var emp_id = $('#employee').val();
            var leavetype = $('#leavetype').val();

            getleaverequests(emp_id);

            if (emp_id != '') {
                $.ajax({
                    url: "getEmployeeCategory",
                    method: "POST",
                    dataType: 'json',
                    data: { emp_id: emp_id, _token: _token},
                    success: function (data) {

                       let short_leave_enabled = data.short_leave_enabled;
                       if (short_leave_enabled == 0){
                           $("#half_short option[value*='0.25']").prop('disabled',true);
                           $('#half_short_span').html('<text class="text-warning"> Short Leave Disabled by Job Category </text>');
                       }else{
                           $("#half_short option[value*='0.25']").prop('disabled',false);
                           $('#half_short_span').html('');
                       }

                    }
                });

            }

        });

        $('#todate').change(function () {

            var assign_leave = $('#assign_leave').val();


            var todate = $('#fromdate').val();
            var fromdate = $('#todate').val();
            var date1 = new Date(todate);
            var date2 = new Date(fromdate);
            var diffDays = parseInt((date2 - date1) / (1000 * 60 * 60 * 24), 10);

            var leaveavailable = $('#available_leave').val();
            var assign_leave = $('#assign_leave').val();

            if (leaveavailable != '') {
                $('#available_leave').val(leaveavailable);
            } else {
                $('#available_leave').val(assign_leave);
            }


            if (leaveavailable <= diffDays) {
                $('#message').html("<div class='alert alert-danger'>You Cant Apply, You Have " + assign_leave + " Days Only</div>");
            } else {
                $('#message').html("");

            }


        });

        $(document).ready(function () {
            $('#create_record').click(function () {
                $('.modal-title').text('Apply Leave');
                $('#action_button').val('Add');
                $('#action').val('Add');
                $('#form_result').html('');

                $('#formModal').modal('show');
            });

            $('#formTitle').on('submit', function (event) {
                event.preventDefault();
                var action_url = '';


                if ($('#action').val() == 'Add') {
                    action_url = "{{ route('addLeaveApply') }}";
                }


                if ($('#action').val() == 'Edit') {
                    action_url = "{{ route('LeaveApply.update') }}";
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
                $.ajax({
                    url: "LeaveApply/" + id + "/edit",
                    dataType: "json",
                    success: function (data) {
                        $('#leavetype').val(data.result.leave_type);

                        let empOption = $("<option selected></option>").val(data.result.emp_id).text(data.result.employee.emp_name_with_initial);
                        $('#employee').append(empOption).trigger('change');

                        let coveringemployeeOption = $("<option selected></option>").val(data.result.emp_covering).text(data.result.covering_employee.emp_name_with_initial);
                        $('#coveringemployee').append(coveringemployeeOption).trigger('change');

                        let approvebyOption = $("<option selected></option>").val(data.result.leave_approv_person).text(data.result.approve_by.emp_name_with_initial);
                        $('#approveby').append(approvebyOption).trigger('change');

                        $('#employee').val(data.result.emp_id);
                        $('#fromdate').val(data.result.leave_from);
                        $('#todate').val(data.result.leave_to);
                        $('#half_short').val(data.result.half_short);
                        $('#no_of_days').val(data.result.no_of_days);
                        $('#reson').val(data.result.reson);
                        $('#comment').val(data.result.comment);
                        $('#coveringemployee').val(data.result.emp_covering);
                        $('#approveby').val(data.result.leave_approv_person);
                        $('#available_leave').val(data.result.total_leave);
                        $('#assign_leave').val(data.result.assigned_leave);
                        $('#hidden_id').val(id);
                        $('.modal-title').text('Edit Leave');
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
                $.ajax({
                    url: "LeaveApply/destroy/" + user_id,
                    beforeSend: function () {
                        $('#ok_button').text('Deleting...');
                    },
                    success: function (data) {
                        setTimeout(function () {
                            $('#confirmModal').modal('hide');
                            $('#divicestable').DataTable().ajax.reload();
                            alert('Data Deleted');
                        }, 2000);
                        location.reload();
                    }
                })
            });

        });


        $(document).on('click', '.addrequest', function () {
                var id = $(this).attr('id');
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
                        $('#fromdate').val(data.result.from_date);
                        $('#todate').val(data.result.to_date);
                        let fromDate = new Date(data.result.from_date);
                        let toDate   = new Date(data.result.to_date);
                        // Calculate difference in milliseconds
                        let diffTime = toDate - fromDate;
                        // Convert to days (add +1 if inclusive)
                        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                        $('#no_of_days').val(diffDays);
                        $('#no_of_days').val();
                        
                        $('#half_short').val(data.result.leave_category).trigger('change');
                        // $('#coveringemployee').val(data.result.covering_employee);
                       
                        let newOption = new Option(data.result.covering_employee_name, data.result.covering_employee, true, true);
                        $('#coveringemployee').append(newOption).trigger('change');
                        
                        $('#reson').val(data.result.reason);
                        $('#request_id').val(id);
                    }
                })
            });


         function getleaverequests(employee){
            $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })

            $.ajax({
                   url: '{!! route("employeeleaverequest") !!}',
                    type: 'POST',
                    dataType: "json",
                    data: { emp_id: employee},
                    success: function (data) {
                        var reuestlist = data.result;
                        $("#requestbody").html(reuestlist);    
                    }
                });

         }
    </script>

@endsection