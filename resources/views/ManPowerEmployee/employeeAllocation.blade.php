@extends('layouts.app')

@section('content')
<main>
    <div class="page-header shadow">
        <div class="container-fluid d-none d-sm-block shadow">
             @include('layouts.employee_nav_bar')
        </div>
    </div>
      <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-body p-0 p-2">
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary btn-sm fa-pull-right mr-2" name="create_record"
                            id="create_record"><i class="fas fa-plus mr-2"></i>Add</button>
                    </div>
                    <div class="col-12">
                        <hr class="border-dark">
                    </div>
                    <div class="col-12">
                        <div class="center-block fix-width scroll-inner">
                            <table class="table table-striped table-bordered table-sm small nowrap display" style="width: 100%"
                                id="dataTable">
                                <thead>
                                    <tr>
                                        <th>ID </th>
                                        <th>EMPLOYEE</th>
                                        <th>ID NO</th>
                                        <th>CARD NO</th>
                                        <th>DATE</th>
                                        <th>OFF NEXT DAY</th>
                                        <th>COMPANY</th>
                                        <th>PHONE</th>
                                        <th class="text-right">ACTION</th>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="staticBackdropLabel">Add Employee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mt-3">
                            <span id="form_result"></span>
                            <form method="post" id="formTitle" class="form-horizontal">
                                {{ csrf_field() }}
                                <div class="form-row mb-1">
                                    <div class="col-12 col-sm-6">
                                        <label class="small font-weight-bold text-dark">Date*</label>
                                        <input type="date" name="fromdate" id="fromdate" class="form-control form-control-sm" required />
                                    </div>
                                    </div>
                                    <hr>
                                    <div class="form-row mb-1">
                                    <div class="col-12 col-sm-6">
                                        <label class="small font-weight-bold text-dark">Card No*</label>
                                        <select name="card_no" id="card_no" class="form-control form-control-sm" style="width: 100%;">
                                            <option value="">Select Card</option>
                                            @foreach ($card as $cards)
                                                <option value="{{ $cards->id }}">{{ $cards->card_no }} - {{ $cards->emp_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="small font-weight-bold text-dark">Off Next Day</label>
                                        <br>
                                        <div class="form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input off_next_day" name="off_next_day" id="off_next_day_0" value="0" checked>No
                                            </label>
                                        </div>
                                        <div class="form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input off_next_day" name="off_next_day" id="off_next_day_1" value="1">Yes
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="small font-weight-bold text-dark">National ID*</label>
                                        <input type="text" name="national_id" id="national_id"
                                            list="national_id_datalist"
                                            class="form-control form-control-sm"
                                            autocomplete="off" required />
                                        <datalist id="national_id_datalist">
                                            @foreach ($nationalIds as $nid)
                                                <option value="{{ $nid->national_id }}">{{ $nid->employee }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="small font-weight-bold text-dark">Employee*</label>
                                        <input type="text" name="employee" id="employee" class="form-control form-control-sm" required />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                            <label class="small font-weight-bold text-dark">Company</label>
                                            <input type="text" name="company" id="company" class="form-control form-control-sm" />
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <label class="small font-weight-bold text-dark">Phone</label>
                                        <input type="number" name="phone" id="phone" class="form-control form-control-sm" />
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="col-12 col-sm-6">
                                        <button type="button" id="formsubmit"
                                            class="btn btn-primary btn-sm px-4 float-right"><i
                                                class="fas fa-plus"></i>&nbsp;Add to list</button>
                                        <input name="submitBtn" type="submit" value="Save" id="submitBtn" class="d-none">
                                    </div>
                                </div>
                                <input type="hidden" name="action" id="action" value="Add" />
                                <input type="hidden" name="hidden_id" id="hidden_id" />
                                <input type="hidden" name="detailsid" id="detailsid">
                            </form>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm small" id="tableorder">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Card No</th>
                                            <th>Off Next Day</th>
                                            <th>Employee</th>
                                            <th>National ID</th>
                                            <th class="d-none"></th>
                                            <th class="d-none"></th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableorderlist"></tbody>
                                </table>
                            </div>
                            <div class="form-group mt-2">
                                <button type="button" name="btncreateorder" id="btncreateorder"
                                    class="btn btn-primary btn-sm fa-pull-right px-4"><i
                                        class="fas fa-plus"></i>&nbsp;Create</button>
                            </div>
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
    $(document).ready(function () {

    $('#employee_menu_link').addClass('active');
    $('#employee_menu_link_icon').addClass('active');
    $('#manpower_emp').addClass('navbtnactive');

    var nationalIdMap = {};
    @foreach ($nationalIds as $nid)
    nationalIdMap[{!! json_encode($nid->national_id) !!}] = {
        employee : {!! json_encode($nid->employee) !!},
        company  : {!! json_encode($nid->company) !!},
        phone    : {!! json_encode($nid->phone) !!}
    };
    @endforeach

    $(document).on('change', '#national_id', function () {
        var val = $(this).val().trim();
        if (nationalIdMap.hasOwnProperty(val)) {
            var rec = nationalIdMap[val];
            $('#employee').val(rec.employee);
            $('#company').val(rec.company);
            $('#phone').val(rec.phone);
        }
    });

        $('#dataTable').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            dom: "<'row'<'col-sm-4 mb-sm-0 mb-2'B><'col-sm-2'l><'col-sm-6'f>>" + "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            "buttons": [{
                    extend: 'csv',
                    className: 'btn btn-success btn-sm',
                    title: 'ManPower Employee Information',
                    text: '<i class="fas fa-file-csv mr-2"></i> CSV',
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger btn-sm',
                    title: 'ManPower Employee Information',
                    text: '<i class="fas fa-file-pdf mr-2"></i> PDF',
                    orientation: 'landscape',
                    pageSize: 'legal',
                    customize: function(doc) {
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                    }
                },
                {
                    extend: 'print',
                    title: 'Man Power Employee Information',
                    className: 'btn btn-primary btn-sm',
                    text: '<i class="fas fa-print mr-2"></i> Print',
                    customize: function(win) {
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    },
                },
                // 'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            "order": [
                [0, "desc"]
            ],
            ajax: {
                url: scripturl + "/ManPowerEmployee/EmployeeAllocationlist.php",
                type: "POST",
                data: {},
            },
            columns: [
                { 
                    data: 'id', 
                    name: 'id'
                },
                { 
                    data: 'employee', 
                    name: 'employee'
                },
                {
                    data: 'national_id',
                    name: 'national_id'
                },
                {
                    data: 'card_no',
                    name: 'card_no'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'off_next_day',
                    name: 'off_next_day',
                    render: function(data, type, row) {
                        return data == '1' ? 'Yes' : 'No';
                    }
                },
                {
                    data: 'company',
                    name: 'company'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'id',
                    name: 'action',
                    className: 'text-right',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        var buttons = '';
                        
                        buttons += '<button type="button" name="delete" id="'+row.id+'" class="delete btn btn-danger btn-sm mr-1" data-toggle="tooltip" title="Remove"><i class="far fa-trash-alt"></i></button>';
                        return buttons;
                    }

                }
            ],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#create_record').click(function () {
            $('.modal-title').text('Add Employee');
            $('#action').val('Add');
            $('#form_result').html('');
            $('#formTitle')[0].reset();
            $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-plus"></i> Create');
            
            $('#formModal').modal('show');
        });

        $("#formsubmit").click(function () {
            if (!$("#formTitle")[0].checkValidity()) {
                $("#submitBtn").click();
            } else {
                var fromdate       = $('#fromdate').val();
                var card_no        = $('#card_no').val();
                var card_no_label  = $('#card_no option:selected').text();
                var off_next_day   = $('input[name="off_next_day"]:checked').val();
                var off_next_day_label = off_next_day == '1' ? 'Yes' : 'No';
                var employee    = $('#employee').val();
                var national_id = $('#national_id').val();
                var company     = $('#company').val();
                var phone       = $('#phone').val();

                $('#tableorder > tbody:last').append(
                    '<tr class="pointer">' +
                    '<td>' + fromdate + '</td>' +
                    '<td data-value="' + card_no + '">' + card_no_label + '</td>' +
                    '<td>' + off_next_day_label + '</td>' +
                    '<td>' + employee + '</td>' +
                    '<td>' + national_id + '</td>' +
                    '<td class="d-none">' + company + '</td>' +
                    '<td class="d-none">' + phone + '</td>' +
                    '<td class="text-right"><button type="button" onclick="productDelete(this);" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button></td>' +
                    '</tr>'
                );

                // keep the shift-level fields (date/card/off day), only clear the per-employee fields
                $('#card_no').val('');
                $('#national_id').val('');
                $('#employee').val('');
                $('#company').val('');
                $('#phone').val('');
                $('#national_id').focus();
            }
        });

        $('#btncreateorder').click(function () {
            var action_url = '';
            if ($('#action').val() == 'Add') {
                action_url = "{{ route('Manpoweremployee.insert') }}";
            }
            $('#btncreateorder').prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Creating');
            var tbody = $("#tableorder tbody");

            if (tbody.children().length > 0) {
                var jsonObj = [];
                $("#tableorder tbody tr").each(function () {
                    var item = {};
                    $(this).find('td').each(function (col_idx) {
                        var val = $(this).data('value');
                        item["col_" + (col_idx + 1)] = (val !== undefined && val !== '') ? val : $(this).text();
                    });
                    jsonObj.push(item);
                });

                var hidden_id = $('#hidden_id').val();

                $.ajax({
                    method: "POST",
                    dataType: "json",
                    data: {
                        _token: '{{ csrf_token() }}',
                        tableData: jsonObj,
                        hidden_id: hidden_id,
                    },
                    url: action_url,
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
                            $('#tableorder tbody').empty();
                            $('#dataTable').DataTable().ajax.reload();
                            setTimeout(function () {
                                $('#formModal').modal('hide');
                            }, 2000);
                            $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Create');
                        } else {
                            $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Create');
                        }
                        $('#form_result').html(html);
                    },
                    error: function () {
                        $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Create');
                    }
                });
            } else {
                alert('Cannot Create.. Table Empty!!');
                $('#btncreateorder').prop('disabled', false).html('<i class="fas fa-plus mr-2"></i> Create');
            }
        });


        var user_id;

        $(document).on('click', '.delete', function () {
            user_id = $(this).attr('id');
            $('#confirmModal').modal('show');
        });

        $('#ok_button').click(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            $.ajax({
                url: '{!! route("Manpoweremployee.delete") !!}',
                type: 'POST',
                dataType: "json",
                data: { id: user_id },
                beforeSend: function () {
                    $('#ok_button').prop('disabled', true).text('Deleting...');
                },
                success: function (data) {
                    setTimeout(function () {
                        $('#confirmModal').modal('hide');
                        $('#dataTable').DataTable().ajax.reload();
                        $('#ok_button').prop('disabled', false).text('Yes, Delete');
                        alert('Data Deleted');
                    }, 2000);
                    location.reload();
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    $('#ok_button').prop('disabled', false).text('Yes, Delete');
                    alert('Delete failed.');
                }
            });
        });

    });

    function productDelete(row) {
        $(row).closest('tr').remove();
    }
</script>
@endsection