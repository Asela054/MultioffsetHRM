@extends('layouts.app')

@section('content')

<main>
    <div class="page-header shadow">
        <div class="container-fluid">
            @include('layouts.corporate_nav_bar')
           
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-body p-0 p-2">
                <div class="row">
                    <div class="col-12">
                    @can('company-create')
                        <button type="button" class="btn btn-outline-primary btn-sm fa-pull-right" name="create_record" id="create_record"><i class="fas fa-plus mr-2"></i>Add Job Category</button>
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
                                    <th>Category</th>
                                    <th>Annual Leaves</th>
                                    <th>Casual Leaves</th>
                                    <th>Medical Leaves</th>
                                    <th>Payroll Work Days </th>
                                    <th>Payroll Work Hours</th>
                                    <th>Saturday OT Type</th>
                                    <th>Custom Saturday OT Type</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>


                            <tbody>
                                @foreach($jobcategory as $jobcategories)
                                <tr>
                                    <td>{{$jobcategories->id}}</td>
                                    <td>{{$jobcategories->category}}</td>
                                    <td>{{$jobcategories->annual_leaves}}</td>
                                    <td>{{$jobcategories->casual_leaves}}</td>
                                    <td>{{$jobcategories->medical_leaves}}</td>
                                    <td>{{$jobcategories->emp_payroll_workdays}}</td>
                                    <td>{{$jobcategories->emp_payroll_workhrs}}</td>
                                    <td>
                                        <?php
                                        if($jobcategories->is_sat_ot_type_as_act == 1){
                                            echo 'As Act';
                                        }elseif ($jobcategories->is_sat_ot_type_as_act == 0){
                                            echo 'Custom';
                                        }else{
                                            echo 'Working Day';
                                        }
                                        ?>
                                    </td>
                                    <td>{{$jobcategories->custom_saturday_ot_type}}</td>
                                    <td class="text-right">
                                        @can('company-edit')
                                            <button style="margin:2px;" name="edit" id="{{$jobcategories->id}}" class="edit btn btn-outline-primary btn-sm" type="submit"><i class="fas fa-pencil-alt"></i></button>
                                        @endcan
                                        @can('company-delete')
                                            <button style="margin:2px;" type="submit" name="delete" id="{{$jobcategories->id}}" class="delete btn btn-outline-danger btn-sm"><i class="far fa-trash-alt"></i></button>
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
                    <h5 class="modal-title" id="staticBackdropLabel">Add Job Category</h5>
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
                                    <div class="col-12">
                                        <label class="small font-weight-bold text-dark">Category</label>
                                        <input type="text" name="category" id="category" class="form-control form-control-sm" required />
                                    </div>
                                    <div class="col-12">
                                        <label class="small font-weight-bold text-dark">Annual Leaves</label>
                                        <input type="number" name="annual_leaves" id="annual_leaves" class="form-control form-control-sm" required />
                                    </div>                                    
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Casual Leaves</label>
                                    <input type="number" name="casual_leaves" id="casual_leaves" class="form-control form-control-sm" required />
                                </div>
                                <div class="form-row mb-1">
                                    <div class="col-12">
                                        <label class="small font-weight-bold text-dark">Medical Leaves</label>
                                        <input type="number" name="medical_leaves" id="medical_leaves" class="form-control form-control-sm" required />
                                    </div>
{{--                                    <div class="col-12">--}}
{{--                                        <label class="small font-weight-bold text-dark">OT Deduct</label>--}}
{{--                                        <input type="number" name="otdeduct" step="0.01" id="otdeduct" class="form-control form-control-sm" required />--}}
{{--                                    </div>                                    --}}
                                </div>
{{--                                <div class="form-group mb-1">--}}
{{--                                    <label class="small font-weight-bold text-dark">No Pay Deduct</label>--}}
{{--                                    <input type="number" name="nopaydeduct" step="0.01" id="nopaydeduct" class="form-control form-control-sm" required />--}}
{{--                                </div>--}}
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Payroll Workdays</label>
                                    <input type="number" name="emp_payroll_workdays" step="0.01" id="emp_payroll_workdays" class="form-control form-control-sm" required />
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Payroll Work Hours</label>
                                    <input type="number" name="emp_payroll_workhrs" step="0.01" id="emp_payroll_workhrs" class="form-control form-control-sm" required />
                                </div>
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-dark">Saturday OT Type</label>
                                    <br>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input is_sat_ot_type_as_act" name="is_sat_ot_type_as_act" id="is_sat_ot_type_as_act_1" value="1" checked>As Act
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input is_sat_ot_type_as_act" name="is_sat_ot_type_as_act" id="is_sat_ot_type_as_act_0" value="0">Custom
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input is_sat_ot_type_as_act" name="is_sat_ot_type_as_act" id="is_sat_ot_type_as_act_2" value="2">Saturday is a working date
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group mb-1 custom_sat" style="display: none">
                                    <label class="small font-weight-bold text-dark">Custom Saturday OT Type</label>
                                    <br>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="custom_saturday_ot_type" id="custom_saturday_ot_type_1" value="1" checked>1
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="custom_saturday_ot_type" id="custom_saturday_ot_type_1_5" value="1.5">1.5
                                        </label>
                                    </div>
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="custom_saturday_ot_type" id="custom_saturday_ot_type_2" value="2">2
                                        </label>
                                    </div>
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

    $('#organization_menu_link').addClass('active');
    $('#organization_menu_link_icon').addClass('active');
    $('#jobcategorylink').addClass('navbtnactive');

    $('#dataTable').DataTable();

    $('#create_record').click(function(){
        $('.modal-title').text('Add New Job Category');
        $('#action_button').html('Add');
        $('#action').val('Add');
        $('#form_result').html('');
        $('#formTitle')[0].reset();

        $('#formModal').modal('show');
    });
 
    $('#formTitle').on('submit', function(event){
        event.preventDefault();
        var action_url = '';

        if ($('#action').val() == 'Add') {
            action_url = "{{ route('addJobCategory') }}";
        }
        if ($('#action').val() == 'Edit') {
            action_url = "{{ route('JobCategory.update') }}";
        }

        $.ajax({
            url: action_url,
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (data) {//alert(data);

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
                    //$('#titletable').DataTable().ajax.reload();
                    location.reload()
                }
                $('#form_result').html(html);
            }
        });
    });

    $(document).on('click', '.edit', function () {
        var id = $(this).attr('id');
        $('#form_result').html('');
        $.ajax({
            url: "JobCategory/" + id + "/edit",
            dataType: "json",
            success: function (data) {

                //$('#sunday_rate').val(data.result.sunday_rate);
                let short_leave_enabled = data.result.short_leave_enabled;
                if(short_leave_enabled == 1){
                    $('#short_leave_enabled').prop( "checked", true );
                }else {
                    $('#short_leave_enabled').prop( "checked", false );
                }

                $('#category').val(data.result.category);
                $('#annual_leaves').val(data.result.annual_leaves);
                $('#casual_leaves').val(data.result.casual_leaves);
                $('#medical_leaves').val(data.result.medical_leaves);
                $('#emp_payroll_workdays').val(data.result.emp_payroll_workdays);
                $('#emp_payroll_workhrs').val(data.result.emp_payroll_workhrs);
               // $('#otdeduct').val(data.result.otdeduct);
               // $('#nopaydeduct').val(data.result.nopaydeduct);

                if(data.result.is_sat_ot_type_as_act == 1){
                    $('#is_sat_ot_type_as_act_1').prop( "checked", true );
                    $('.custom_sat').css('display', 'none');
                }else if(data.result.is_sat_ot_type_as_act == 0) {
                    $('#is_sat_ot_type_as_act_0').prop( "checked", true );
                    $('.custom_sat').css('display', 'block');
                }else{
                    $('#is_sat_ot_type_as_act_2').prop( "checked", true );
                    $('.custom_sat').css('display', 'none');
                }

                if(data.result.custom_saturday_ot_type == 1 ){
                    $('#custom_saturday_ot_type_1').prop( "checked", true );
                }

                if(data.result.custom_saturday_ot_type == 1.5 ){
                    $('#custom_saturday_ot_type_1_5').prop( "checked", true );
                }

                if(data.result.custom_saturday_ot_type == 2 ){
                    $('#custom_saturday_ot_type_2').prop( "checked", true );
                }

                $('#hidden_id').val(id);
                $('.modal-title').text('Edit Job Category');
                $('#action_button').html('Edit');
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
            url: "JobCategory/destroy/" + user_id,
            beforeSend: function () {
                $('#ok_button').text('Deleting...');
            },
            success: function (data) {//alert(data);
                setTimeout(function () {
                    $('#confirmModal').modal('hide');
                    $('#dataTable').DataTable().ajax.reload();
                    alert('Data Deleted');
                }, 2000);
                location.reload()
            }
        })
    });


    $(document).on('change', '.is_sat_ot_type_as_act', function (e) {
        let val = $(this).val();
        if(val == 0){
            $('.custom_sat').css('display', 'block');
        }else{
            $('.custom_sat').css('display', 'none');
        }

    });
});
</script>


@endsection




