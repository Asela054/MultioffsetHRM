<?php $page_stitle = 'Report on Employees Birthday - Multi Offset'; ?>
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
                            <label class="small font-weight-bold text-dark">Date</label>
                            <input type="date" name="date" id="date" class="form-control form-control-sm">
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
                                    <th>Employee Name</th>
                                    <th>Date of Birth</th>
                                    <th>Age</th>
                                    <th>Department</th>
                                    <th style="display:none;">ID</th>
                                    <th style="display:none;">Calling Name</th>
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
    function load_dt(department,date){
        $('#emptable').DataTable({
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
                    orientation: 'landscape', 
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            processing: true,
            serverSide: true,
            ajax: {
                "url": scripturl + "/employee_bd_report_list.php",
                "type": "POST",
                "data": {'department':department,
                        'date':date,
                        'company': '{{ session("company_id") }}',              
                        'company_branch': '{{ session("company_branch_id") }}'
                        },
            },
            columns: [
                { data: 'emp_id' },
                { data: 'emp_etfno' },
                { data: 'employee_display' },
                { data: 'emp_birthday' },
                { data: 'age' },
                { data: 'dept_name' },
                { data: 'id', name: 'id' , visible: false},
                { data: "calling_name", visible: false }
            ],
            "bDestroy": true,
            "order": [[ 0, "desc" ]],
        });
    }

    $('#formFilter').on('submit',function(e) {
        e.preventDefault();
        let department = $('#department').val();
        let date = $('#date').val();

        load_dt(department,date);
    });

    $('#btn-reset').on('click', function(e) {
                e.preventDefault();
                
                department.val(null).trigger('change');
                $('#date').val('');
                
                
                load_dt('','');
            });

} );
</script>

@endsection