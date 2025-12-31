@extends('layouts.app')

@section('content')

<main> 
    <div class="page-header shadow">
        <div class="container-fluid">
            @include('layouts.payroll_nav_bar')
        </div>
    </div>
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-body p-0 p-2">
                <div class="row">
                    <div class="col-12">
                        @can('Special-Note-create')
                            <button type="button" class="btn btn-outline-primary btn-sm fa-pull-right" name="create_record" id="create_record"><i class="fas fa-plus mr-2"></i>Add Note</button>
                        @endcan
                    </div>
                    <div class="col-12">
                        <hr class="border-dark">
                    </div>
                    <div class="col-12">
                        <div class="center-block fix-width scroll-inner">
                        <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="dataTable">
                            <thead>
                                <tr>
                                    <th>ID </th>
                                    <th>Payment Period</th>
                                    <th>Note</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($specialNotes as $specialNote)
                                <tr>
                                    <td>{{$specialNote->id}}</td>
                                    <td>
                                        @if($specialNote->paymentPeriod)
                                            {{$specialNote->paymentPeriod->payment_period_fr}} to {{$specialNote->paymentPeriod->payment_period_to}}
                                        @endif
                                    </td>
                                    <td>{{$specialNote->note}}</td>
                                    <td class="text-right">
                                        @can('Special-Note-list')
                                            <button name="view" id="{{$specialNote->id}}" class="view btn btn-outline-info btn-sm" type="button" title="View Employees"><i class="fas fa-eye"></i></button>
                                        @endcan
                                        @can('Special-Note-edit')
                                            <button name="edit" id="{{$specialNote->id}}" class="edit btn btn-outline-primary btn-sm" type="submit"><i class="fas fa-pencil-alt"></i></button>
                                        @endcan
                                        @can('Special-Note-delete')
                                            <button type="submit" name="delete" id="{{$specialNote->id}}" class="delete btn btn-outline-danger btn-sm"><i class="far fa-trash-alt"></i></button>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
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
                    <h5 class="modal-title" id="staticBackdropLabel">Add Note</h5>
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
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Working Period*</label>
                                        <select name="period_filter_id" id="period_filter_id" class="form-control form-control-sm" required>
                                            <option value="" disabled="disabled" selected="selected">Please Select</option>
                                            @foreach($payment_period as $schedule)
                                            <option value="{{$schedule->id}}" data-payroll="{{$schedule->payroll_process_type_id}}">
                                                {{$schedule->payment_period_fr}} to {{$schedule->payment_period_to}}
                                            </option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="form-group mb-1" id="company_field" style="display: none;">
                                    <label class="small font-weight-bold text-dark">Company</label>
                                    <select name="company" id="company" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                    </select>
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Department</label>
                                    <select name="department" id="department" class="form-control form-control-sm">
                                    </select>
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Employees*</label>
                                    <select name="employee[]" id="employee" class="form-control form-control-sm" multiple="multiple" required>
                                    </select>
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Note*</label>
                                    <textarea name="note" id="note" class="form-control form-control-sm" rows="3" required></textarea>
                                </div>
                                <div class="form-group mt-3">
                                    <button type="submit" name="action_button" id="action_button" class="btn btn-outline-primary btn-sm fa-pull-right px-4"><i class="fas fa-plus"></i>&nbsp;Add</button>
                                </div>
                                <input type="hidden" name="action" id="action" value="Add" />
                                <input type="hidden" name="hidden_id" id="hidden_id" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Employees Modal -->
    <div class="modal fade" id="viewModal" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="viewModalLabel">Allocated Employees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <strong>Payment Period:</strong> <span id="view_period"></span>
                        </div>
                        <div class="col-12 mb-2">
                            <strong>Note:</strong> <span id="view_note"></span>
                        </div>
                        <div class="col-12">
                            <strong>Employees:</strong>
                            <div id="employee_list" class="mt-2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
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
                    <button type="button" name="ok_button" id="ok_button" class="btn btn-danger px-3 btn-sm">OK</button>
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
$(document).ready(function(){

    $('#payroll_menu_link').addClass('active');
    $('#payroll_menu_link_icon').addClass('active');
    $('#payroll_Remunerations').addClass('navbtnactive');

    let company = $('#company');
    let department = $('#department');
    let employee = $('#employee');

    var companyId = '{{ session("company_id") }}';
    var companyName = '{{ session("company_name") }}';

    if (companyId && companyName) {
        var option = new Option(companyName, companyId, true, true);
        company.append(option).trigger('change');
    }

    department.select2({
        placeholder: 'Select Department...',
        width: '100%',
        allowClear: true,
        ajax: {
            url: '{{url("department_list_sel2")}}',
            dataType: 'json',
            data: function(params) {
                return {
                    term: params.term || '',
                    page: params.page || 1,
                    company: company.val()
                }
            },
            cache: true
        }
    });

    employee.select2({
        placeholder: 'Select Employees...',
        width: '100%',
        allowClear: true,
        multiple: true,
        ajax: {
            url: '{{url("employee_list_sel2")}}',
            dataType: 'json',
            data: function(params) {
                return {
                    term: params.term || '',
                    page: params.page || 1,
                    department: department.val()
                }
            },
            cache: true
        }
    });

    $('#dataTable').DataTable();
 
        $('#create_record').click(function () {
        $('.modal-title').text('Add Note');
        $('#action_button').html('<i class="fas fa-plus"></i>&nbsp;Add');
        $('#action').val('Add');
        $('#form_result').html('');
        $('#formTitle')[0].reset();
        $('#employee').val(null).trigger('change');
        $('#department').val(null).trigger('change');
        
        // Hide company field for add
        $('#company_field').hide();
        $('#formModal').modal('show');
    });


    $('#formTitle').on('submit', function (event) {
        event.preventDefault();
        var action_url = '';

        if ($('#action').val() == 'Add') {
            action_url = "{{ route('addSpecialNote') }}";
        }
        if ($('#action').val() == 'Edit') {
            action_url = "{{ route('SpecialNote.update') }}";
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
                    location.reload();
                }
                $('#form_result').html(html);
            },
            error: function(xhr) {
                var html = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                $('#form_result').html(html);
            }
        });
    });

    $(document).on('click', '.edit', function () {
        var id = $(this).attr('id');
        $('#form_result').html('');
        
        $.ajax({
            url: "SpecialNote/" + id + "/edit",
            dataType: "json",
            success: function (data) {
                $('#period_filter_id').val(data.result.period_id);
                $('#note').val(data.result.note);
                
                $('#company_field').hide();
                
                // Get department from first employee
                if(data.employee_ids && data.employee_ids.length > 0) {
                    $.ajax({
                        url: "{{ url('get_employee_department') }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            emp_id: data.employee_ids[0]
                        },
                        success: function(dept) {
                            if(dept.id && dept.text) {
                                var deptOption = new Option(dept.text, dept.id, true, true);
                                $('#department').append(deptOption).trigger('change');
                            }
                            
                            $.ajax({
                                url: "SpecialNote/" + id + "/getEmployees",
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    ids: data.employee_ids
                                },
                                success: function(employees) {
                                    $('#employee').empty();
                                    employees.forEach(function(emp) {
                                        var option = new Option(emp.text, emp.id, true, true);
                                        $('#employee').append(option);
                                    });
                                    $('#employee').trigger('change');
                                }
                            });
                        }
                    });
                } else {
                    $('#department').val(null).trigger('change');
                    $('#employee').val(null).trigger('change');
                }
                
                $('#hidden_id').val(id);
                $('.modal-title').text('Edit Note');
                $('#action_button').html('<i class="fas fa-save"></i>&nbsp;Update');
                $('#action').val('Edit');
                $('#formModal').modal('show');
            }
        });
    });

    $(document).on('click', '.view', function () {
        var id = $(this).attr('id');
        
        $.ajax({
            url: "SpecialNote/" + id + "/view",
            dataType: "json",
            success: function (data) {
                $('#view_period').text(data.period);
                $('#view_note').text(data.note);
                
                var employeeHtml = '<ul class="list-group">';
                if(data.employees && data.employees.length > 0) {
                    data.employees.forEach(function(emp) {
                        employeeHtml += '<li class="list-group-item py-2 small">' + emp.name + '</li>';
                    });
                } else {
                    employeeHtml += '<li class="list-group-item py-2 small text-muted">No employees allocated</li>';
                }
                employeeHtml += '</ul>';
                
                $('#employee_list').html(employeeHtml);
                $('#viewModalLabel').text('Note Details');
                $('#viewModal').modal('show');
            },
            error: function(xhr) {
                alert('Error loading employee details');
            }
        });
    });

    var user_id;

    $(document).on('click', '.delete', function () {
        user_id = $(this).attr('id');
        $('#confirmModal').modal('show');
    });

    $('#ok_button').click(function () {
        $.ajax({
            url: "SpecialNote/destroy/" + user_id,
            beforeSend: function () {
                $('#ok_button').text('Deleting...');
            },
            success: function (data) {
                setTimeout(function () {
                    $('#confirmModal').modal('hide');
                    location.reload();
                }, 1000);
            }
        });
    });

});
</script>

@endsection