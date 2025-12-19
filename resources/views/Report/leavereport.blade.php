<?php $page_stitle = 'Report on Employee Leaves - Multi Offset'; ?>
@extends('layouts.app')

@section('content')

    <main>
        <div class="page-header shadow">
            <div class="container-fluid">
                @include('layouts.reports_nav_bar')
               
            </div>
        </div>

        <div class="container-fluid mt-2 p-0 p-2">
            <div class="card mb-2">
                <div class="card-body">
                    <form class="form-horizontal" id="formFilter">
                        <div class="form-row mb-1">
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Company</label>
                                <select name="company" id="company" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Department</label>
                                <select name="department" id="department" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Employee</label>
                                <select name="employee" id="employee" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Leave Type</label>
                                <select name="leave_type" id="leave_type" class="form-control form-control-sm">
                                    <option value="">Select Type</option>
                                    @foreach ($leave_types as $leave_type)
                                    <option value="{{$leave_type->id}}">{{$leave_type->leave_type}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Covering Person Wise</label>
                                <select name="covering_employee" id="covering_employee" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col">
                                <label class="small font-weight-bold text-dark">Date : From - To</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" id="from_date" name="from_date" class="form-control form-control-sm border-right-0" placeholder="yyyy-mm-dd"
                                    >
                                    <input type="date" id="to_date" name="to_date" class="form-control" placeholder="yyyy-mm-dd"
                                    >
                                </div>
                            </div>
                            <div class="col">
                                <br>
                                <button type="submit" class="btn btn-primary btn-sm filter-btn px-3" id="btn-filter"> Filter</button>
                                <button type="button" class="btn btn-danger btn-sm filter-btn px-3" id="btn-reset"> Clear</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0 p-2">
                    <div class="center-block fix-width scroll-inner">
                    <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="attendreporttable">
                        <thead>
                        <tr>
                            <th>EMP ID</th>
                            <th>EPF NO</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Day Type</th>
                            <th>Leave From</th>
                            <th>Leave To</th>
                            <th>Covering Person</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th style="display:none;">ID</th>
                            <th style="display:none;">Name With Initial</th>
                            <th style="display:none;">Calling Name</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    {{ csrf_field() }}
                </div>
                </div>
            </div>
        </div>

    </main>

@endsection

@section('script')

    <script>
        $(document).ready(function () {

            $('#report_menu_link').addClass('active');
            $('#report_menu_link_icon').addClass('active');
            $('#employeereportmaster').addClass('navbtnactive');

            let company = $('#company');
            let department = $('#department');
            let employee = $('#employee');
            let covering_employee = $('#covering_employee');
            let leave_type = $('#leave_type');
            let from_date = $('#from_date');
            let to_date = $('#to_date');

            leave_type.select2({
                placeholder: 'Select...',
                allowClear: true,
                width: '100%',
            })
            
            covering_employee.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("employee_list_from_attendance_sel2")}}',
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

            var companyId = '{{ session("company_id") }}';
            var companyName = '{{ session("company_name") }}';
                    
            if (companyId && companyName) {
                var option = new Option(companyName, companyId, true, true);
                company.append(option).trigger('change');
            }
            // company.select2({
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

            department.select2({
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
                            company: company.val()
                        }
                    },
                    cache: true
                }
            });

            employee.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("employee_list_from_leaves_sel2")}}',
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

            load_dt('');
            function load_dt(department, employee){
                $('#attendreporttable').DataTable({
                    "lengthMenu": [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
                    dom: 'Blfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            className: 'btn btn-default btn-sm',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'Print',
                            className: 'btn btn-default btn-sm',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ],
                    processing: true,
                    serverSide: true,
                    ajax: {
                        "url": scripturl + "/leave_report_list.php",
                        "type": "POST",
                        "data": function(d) {
                            return $.extend({}, d, {
                                'department': department, 
                                'employee': employee, 
                                'leave_type': leave_type.val(),
                                'covering_employee': covering_employee.val(),
                                'from_date': from_date.val(), 
                                'to_date': to_date.val(),
                                'company': '{{ session("company_id") }}',              
                                'company_branch': '{{ session("company_branch_id") }}'
                            });
                        }
                    },
                    columns: [
                        { data: 'emp_id' },
                        { data: 'emp_etfno' },
                        { data: 'employee_display' },
                        { data: 'dept_name' },
                        { data: 'leave_type_name' },
                        { 
                            data: 'half_short', 
                            render: function(data, type, row) {
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
                        { data: 'leave_from' },
                        { data: 'leave_to' },
                        { data: 'emp_covering_name' },
                        { data: 'reson' },
                        { data: 'status' },
                        { data: 'id', name: 'id' , visible: false},
                        { data: "emp_name_with_initial", visible: false },
                        { data: "calling_name", visible: false },
                    ],
                    "bDestroy": true,
                    "order": [[ 6, "desc" ]]
                });
            }

            $('#formFilter').on('submit',function(e) {
                e.preventDefault();
                let department = $('#department').val();
                let employee = $('#employee').val();

                load_dt(department, employee);
            });

            $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                
                department.val(null).trigger('change');
                employee.val(null).trigger('change');
                leave_type.val(null).trigger('change');
                covering_employee.val(null).trigger('change');
                
                from_date.val('');
                to_date.val('');
                
                load_dt('', '');
            });
        });
    </script>

@endsection

