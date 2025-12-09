<?php $page_stitle = 'Report on Employees - Multi Offset'; ?>
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
                            <select name="department" id="department" class="form-control form-control-sm" required>
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
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm small" id="emptable">
                                <thead>
                                <tr>
                                    <th>EMP ID</th>
                                    <th>EPF No</th>
                                    <th>First Name</th> 
                                    <th>Middle Name</th>
                                    <th>Last Name</th>
                                    <th>Full Name</th>
                                    <th>Name With Initial</th>
                                    <th>Nic No</th>
                                    <th>Date of Birth</th>
                                    <th>Permanent Address</th>
                                    <th>Temporary Address</th>
                                    <th>Job Title</th>
                                    <th>Job Category</th>
                                    <th>Department</th>
                                    <th>Join Date</th>
                                    <th>Confirm Date</th>
                                    <th>Basic Salary</th>
                                    <th>Daily Pay Rate</th>
                                    <th>Leave</th>
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
</main>
                
              
@endsection
@section('script')
<script>
$(document).ready(function() {

    $('#report_menu_link').addClass('active');
    $('#report_menu_link_icon').addClass('active');
    $('#employeedetailsreport').addClass('navbtnactive');

    let company = $('#company');
    let department = $('#department');

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

    load_dt('');
    function load_dt(department){
        $('#emptable').DataTable({
            "lengthMenu": [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
            dom: 'Blfrtip',
            buttons: [
                'excelHtml5',
                'pdfHtml5'
            ],
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{url('/employee_report_list')}}",
                "data": {'department':department},
            },
            columns: [
                { data: 'emp_id' },
                { data: 'emp_etfno' },
                { data: 'emp_first_name' },
                { data: 'emp_med_name' },
                { data: 'emp_last_name' },
                { data: 'emp_fullname' },
                { data: 'emp_name_with_initial' },
                { data: 'emp_national_id' },
                { data: 'emp_birthday' },
                { data: 'emp_address' },
                { data: 'emp_addressT' },
                { data: 'title' },
                { data: 'job_category' },
                { data: 'dept_name' },
                { data: 'emp_join_date' },
                { data: 'emp_permanent_date' },
                { data: 'emp_basic_salary' },
                { data: 'emp_daily_pay_rate' },
                { data: 'emp_leave' }
            ],
            "bDestroy": true,
            "order": [[ 0, "desc" ]],
        });
    }

    $('#formFilter').on('submit',function(e) {
        e.preventDefault();
        let department = $('#department').val();

        load_dt(department);
    });

    $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                
                department.val(null).trigger('change');
                
                
                load_dt('');
            });

} );
</script>

@endsection