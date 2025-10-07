<?php $page_stitle = 'Report on Employees Resignation - Multi Offset HRM'; ?>
@extends('layouts.app')

@section('content')

<?php $userDepartmentNames = Auth::user()->departments->pluck('name')->toArray(); ?>

<main> 
    <div class="page-header shadow">
        <div class="container-fluid">
            @include('layouts.reports_nav_bar')
           
        </div>
    </div>
    <div class="container-fluid mt-4">
        <div class="card mb-2">
            <div class="card-body">
                <form class="form-horizontal" id="formFilter">
                    <div class="form-row mb-1">
                        <div class="col-3">
                            <label class="small font-weight-bold text-dark">Company</label>
                            <select name="company" id="company" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="small font-weight-bold text-dark">Department</label>
                            <select name="department" id="department" class="form-control form-control-sm">
                                <option value="All">All Departments</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="small font-weight-bold text-dark">Date From</label>
                           <input type="date" name="selectdatefrom" id="selectdatefrom" value="<?php echo date('Y-m-d'); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-2">
                            <label class="small font-weight-bold text-dark">Date To</label>
                           <input type="date" name="selectdateto" id="selectdateto" value="<?php echo date('Y-m-d'); ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col">
                            <br>
                            <button type="button" class="btn btn-primary btn-sm filter-btn" id="btn-filter"> Filter</button>
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
                                    <th>Date</th>
                                    <th>EMP ID</th>
                                    <th>Name with Initial</th>
                                    <th>Department</th>
                                    <th>Location</th>
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
    $('#employeereportmaster').addClass('navbtnactive');

        let company = $('#company');

        let department = $('#department');
        let departmentname = $('#department option:selected').text();

        var companyId = '{{ session("company_id") }}';
        var companyName = '{{ session("company_name") }}';
        var userDepartmentNames = {!! json_encode($userDepartmentNames) !!};
    
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

    $('#btn-filter').click(function() {
        let departmentid = $('#department').val();
        let departmentname = $('#department option:selected').text();
        let selectdatefrom = $('#selectdatefrom').val();
        let selectdateto = $('#selectdateto').val();

        $('#emptable').DataTable({
            "lengthMenu": [[10, 25, 50, 100, 500, 1000], [10, 25, 50, 100, 500, 1000]],
            dom: 'Blfrtip',
            buttons: [
                            {
                                extend: 'excelHtml5',
                                title: 'Report on Employees Absent ('+selectdatefrom+'-'+selectdateto+') '+departmentname+' - '+companyName
                            },
                            {
                            extend: 'pdf',
                            title: 'Report on Employees Absent ('+selectdatefrom+'-'+selectdateto+') '+departmentname+' - '+companyName,
                            customize: function(doc) {
                                doc.pageSize = 'LEGAL';
                                doc.pageOrientation = 'landscape';
                                doc.content[1].layout = 'auto';
                                doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                                }
                            }
                        ],
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{url('/get_absent_employees')}}",
                "data": {'selectdatefrom':selectdatefrom,
                         'selectdateto':selectdateto,
                         'department':departmentid
                },
            },
            columns: [
                { data: 'date' },
                { data: 'emp_id' },
                { data: 'emp_name_with_initial' },
                { data: 'departmentname' },
                { data: 'location' },
            ],
            "bDestroy": true,
            "order": [[ 0, "desc" ]],
        });
    });




} );
</script>

@endsection