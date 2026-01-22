<?php $page_stitle = 'Report on Employee Loans - Multi Offset'; ?>
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
                                <label class="small font-weight-bold text-dark">Employee*</label>
                                <select name="employee" id="employee" class="form-control form-control-sm" required>
                                </select>
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
                    <div class="col-md-12">
                        <div id="employee-info-section" class="alert alert-info" style="display: none;">
                            <h5 class="mb-0"><strong>Employee: </strong><span id="employee-name-display"></span></h5>
                            <div><strong>Loan Amount: </strong><span id="employee-loan-amount-display"></span></div>
                            <div><strong>Paid Amount: </strong><span id="employee-paid-amount-display"></span></div>
                            <div><strong>Balance: </strong><span id="employee-balance-display"></span></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                    <div class="center-block fix-width scroll-inner">
                    <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="attendreporttable">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Installment</th>
                            <th>Balance</th>
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
        </div>

    </main>

@endsection

@section('script')

    <script>
        $(document).ready(function () {

            $('#report_menu_link').addClass('active');
            $('#report_menu_link_icon').addClass('active');
            $('#employeedetailsreport').addClass('navbtnactive');

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
                        "url": scripturl + "/loan_installment_report_list.php",
                        "type": "POST",
                        "data": function(d) {
                            return $.extend({}, d, {
                                'department': department, 
                                'employee': employee, 
                                'company': '{{ session("company_id") }}',              
                                'company_branch': '{{ session("company_branch_id") }}'
                            });
                        },
                        "dataSrc": function(json) {
                            if (json.employeeInfo) {
                                $('#employee-name-display').text(json.employeeInfo.name);
                                $('#employee-loan-amount-display').text(json.employeeInfo.loan_amount);
                                $('#employee-paid-amount-display').text(json.employeeInfo.paid_amount);
                                $('#employee-balance-display').text(json.employeeInfo.balance);
                                $('#employee-info-section').show();
                            } else {
                                $('#employee-info-section').hide();
                            }
                            return json.data;
                        }
                    },
                    columns: [
                        { data: 'date', name: 'date' },
                        { data: 'installment', name: 'installment' },
                        { data: 'balance', name: 'balance' },
                        { data: 'id', name: 'id', visible: false },
                        { data: "emp_name_with_initial", visible: false },
                        { data: "calling_name", visible: false },
                    ],
                    "bDestroy": true,
                    "order": [[ 0, "desc" ]]
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
                $('#employee-info-section').hide();
                if ($.fn.DataTable.isDataTable('#attendreporttable')) {
                    $('#attendreporttable').DataTable().clear().destroy();
                }
                $('#attendreporttable tbody').empty();
            });
        });
    </script>

@endsection

