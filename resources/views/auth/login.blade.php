@extends('layouts.default')

@section('content')
<main>

<div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-4">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header justify-content-center"><img src="{{url('/images/logo.png')}}" class="img-fluid" alt=""></div>
                                    <div class="card-body">
                                        <form class="form-horizontal" method="POST" action="{{ route('login') }}" autocomplete="off">
                                            {{ csrf_field() }}
                                            <div class="form-group mb-1 {{ $errors->has('email') ? ' has-error' : '' }}"><label class="small mb-2" for="inputEmailAddress">Email</label><input class="form-control form-control-sm" name="email" id="email" type="email" placeholder="Enter email address"  value="{{ old('email') }}" required autofocus/></div>
                                            @if ($errors->has('email'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </span>
                                            @endif
                                            <div class="form-group mb-1 {{ $errors->has('password') ? ' has-error' : '' }}" ><label class="small mb-2" for="inputPassword">Password</label><input class="form-control form-control-sm" id="password" name="password" type="password" placeholder="Enter password" required/></div>
                                            @if ($errors->has('password'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </span>
                                            @endif
                                            <!-- Company Dropdown -->
                                            <div class="form-group">
                                                <label class="small mb-2" for="company">Company</label>
                                                <select name="company" id="company" class="form-control form-control-sm" onchange="updateCompanyName();getBranch(this.value)" required>
                                                    <option value="">Select Comapany</option>
                                                    @foreach($companies as $company)
                                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="company_name" id="company_name">
                                            </div>
                                            <!-- Company Branch Dropdown -->
                                            <div class="form-group mb-1">
                                                <label class="small mb-2" for="company_branch">Company Branch</label>
                                                <select name="company_branch" id="company_branch" class="form-control form-control-sm" onchange="updateBranchName();" required>
                                                    <option value="">Select Branch</option>
                                                   
                                                </select>
                                                <input type="hidden" name="company_branch_name" id="company_branch_name">
                                            </div>
                                            <div class="form-group mb-1">
                                                <div class="custom-control custom-checkbox"> <input class="custom-control-input" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> <label class="custom-control-label" for="rememberPasswordCheck">Remember password</label></div>
                                            </div>
                                            <div class="form-group text-right mt-4 mb-0">
                                                <button type="submit" class="btn btn-primary btn-sm px-3">Login</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer bg-laugfs">
                                        <div class="row">
                                            <div class="col text-center"><img src="{{url('/images/hrm.png')}}" class="img-fluid" alt=""></div>
                                            <div class="col-md-12 small text-center">Copyright &copy; ERav Technology <?php echo date('Y') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
</main>
@endsection



<script>
    function updateCompanyName() {
            var companySelect = document.getElementById('company');
            var companyNameInput = document.getElementById('company_name');
            companyNameInput.value = companySelect.options[companySelect.selectedIndex].text;
        }

        function updateBranchName() {
            var branchSelect = document.getElementById('company_branch');
            var branchNameInput = document.getElementById('company_branch_name');
            branchNameInput.value = branchSelect.options[branchSelect.selectedIndex].text;
        }
    function getBranch(companyId){

            $('#company_branch').empty().append('<option value="">Select Branch</option>');

            if (companyId) {
                $.ajax({
                    url: "{{ route('getbranch') }}", 
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', 
                        companyId: companyId,
                    },
                    success: function(data) {
                        $.each(data, function(index, branch) {
                            $('#company_branch').append('<option value="' + branch.id + '">' + branch.location + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching branches:', error);
                    }
                });
            }
    }

</script>

