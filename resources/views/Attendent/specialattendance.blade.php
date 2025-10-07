@extends('layouts.app')

@section('content')

<main>
    <div class="page-header shadow">
        <div class="container-fluid">
            @include('layouts.attendant&leave_nav_bar')
           
        </div>
    </div>      
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-body p-0 p-2">

                <div class="row">
                    <div class="col-12">
                        <form class="form-horizontal" id="formFilter">
                            <div class="form-row mb-1">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-dark">Company</label>
                                    <select name="company" id="company_f" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small font-weight-bold text-dark">Date : From - To</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="date" id="from_date" name="from_date" class="form-control form-control-sm border-right-0" placeholder="yyyy-mm-dd" required>
                                        <input type="date" id="to_date" name="to_date" class="form-control" placeholder="yyyy-mm-dd" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-sm filter-btn float-right" id="btn-submit" style="margin-top:30px;"> Add Attendance</button>
                                </div>
                            </div>
    
                        </form>

                        <div class="mb-2">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="select-all">
                                <label class="form-check-label" for="select-all">Select All</label>
                            </div>
                        </div>
                        <div class="center-block fix-width scroll-inner">
                            <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%"
                                id="attendtable">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employee as $employees)
                                    <tr>
                                        <td> 
                                            <input type="checkbox" class="select-item" data-emp-id="{{ $employees->emp_id }}">
                                        </td>
                                        <td>{{$employees->id}}</td>
                                        <td>{{$employees->emp_id}}</td>
                                       <td>{{$employees->emp_name_with_initial}} - {{$employees->calling_name}}</td>
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
	 
</main>
              
@endsection


@section('script')

<script>


$(document).ready(function() {

    $('#attendant_menu_link').addClass('active');
    $('#attendant_menu_link_icon').addClass('active');
    $('#specialattendance').addClass('navbtnactive');

    let company_f = $('#company_f');
    var companyId = '{{ session("company_id") }}';
    var companyName = '{{ session("company_name") }}';
    
    if (companyId && companyName) {
        var option = new Option(companyName, companyId, true, true);
        company_f.append(option).trigger('change');
    }

        $('#btn-submit').on('click', function (e) {
            e.preventDefault(); 

            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var selectedIds = getSelectedIds();

            if (selectedIds.length === 0) {
                alert('Please select at least one employee.');
                return;
            }

            $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })

            $.ajax({
                url: '{!! route("speecialattendaceinsert") !!}', 
                type: 'POST',
                dataType: "json",
                data: {
                    from_date: fromDate,
                    to_date: toDate,
                    employee_ids: selectedIds,
                },
                success: function (data) {
                    alert(data.success);
                    location.reload();
                }
            });
        });



       $('#select-all').on('change', function () {
            const isChecked = $(this).prop('checked');
            $('.select-item').prop('checked', isChecked);
        });

        $('.select-item').on('change', function () {
            const allChecked = $('.select-item').length === $('.select-item:checked').length;
            $('#select-all').prop('checked', allChecked);
        });

        function getSelectedIds() {
            const selectedIds = [];
            $('.select-item:checked').each(function () {
                selectedIds.push($(this).data('emp-id'));
            });
            return selectedIds;
        }
});

  
</script>

@endsection