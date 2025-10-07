@extends('layouts.app')

@section('content')
@auth
<main>
    <div class="page-header page-header-light bg-white shadow">
        <div class="container-fluid">
            @include('layouts.payroll_nav_bar')
           
        </div>
    </div>
    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-body p-0 p-2">
                <div class="row">
                    <div class="col-12">
                      
                    </div>
                    <div class="col-12">
                        <hr class="border-dark">
                    </div>
                    <div class="col-12">
                       
                    </div>
                </div>    
            </div>
        </div>
    </div>
    <!-- Modal Area Start -->

    <!-- Modal Area End -->
</main>
@endauth
              
@endsection


@section('script')

<script>
$(document).ready(function(){
    $('#payroll_menu_link').addClass('active');
    $('#payroll_menu_link_icon').addClass('active');
});
</script>

@endsection