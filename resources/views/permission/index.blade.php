@extends('layouts.app')
@section('content')

    <main>
        <div class="page-header shadow">
            <div class="container-fluid">
                @include('layouts.administrator_nav_bar')
               
            </div>
        </div>

        <div class="container-fluid mt-4">

            <div class="card">
                <div class="card-body p-0 p-2">
                    <div class="row">
                        <div class="col-12">
                            @can('role-create')
                                <a class="btn btn-success btn-sm float-right" href="{{ route('permissions.create') }}"> Create New Permission</a>
                            @endcan
                        </div>
                        <div class="col-12">
                            <hr class="border-dark">
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success">
                                    <span>{{ $message }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-12 table-responsive">

                            <table class="table table-bordered" id="permissiontable">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Module</th>
                                    <th width="280px">Action</th>
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
    </main>

@endsection

@section('script')
    <script>
        $(document).ready(function(){
        $('#permissiontable').DataTable({
            order: [[0, 'asc']] // Column index 0, ascending order
        });

            $('#administrator_menu_link').addClass('active');
            $('#administrator_menu_link_icon').addClass('active');
            $('#permissions_link').addClass('navbtnactive');


         $('#permissiontable').DataTable({
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
            text: 'PDF', // use "PDF" instead of "Print"
            className: 'btn btn-default',
            exportOptions: {
                columns: 'th:not(:last-child)'
            }
        }
    ],
    processing: true,
    serverSide: true,
    ajax: {
        url: scripturl + "/permission_list.php",
        type: "POST",
    },
    columns: [
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'module', name: 'module' },
        { 
            data: 'emp_id',
            name: 'emp_id',
            render: function(data, type, full) {
                return '<a class="btn btn-outline-primary btn-sm" href="permission/' + full['id'] + '/edit"><i class="fa fa-pencil-alt"></i></a>';
            }
        }
    ],
    order: [[2, "desc"]], // last column
    destroy: true
});

       


        });

        
    </script>
@endsection
