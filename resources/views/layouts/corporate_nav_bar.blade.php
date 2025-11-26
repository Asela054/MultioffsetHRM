
<div class="row nowrap" style="padding-top: 5px;padding-bottom: 5px;">

  @if(auth()->user()->can('location-list') 
  || auth()->user()->can('company-list') 
  || auth()->user()->can('bank-list') 
  || auth()->user()->can('work-category-list') 
  || auth()->user()->can('month-work-hours-list'))
      <div class="dropdown">
        @can('company-list')
        <a role="button" class="btn navbtncolor" href="{{ url('/Company') }}" id="companylink">Company <span class="caret"></span></a>
        @endcan
        @can('bank-list')
        <a role="button" class="btn navbtncolor" href="{{ url('/Bank') }}" id="banklink">Bank <span class="caret"></span></a>
        @endcan
        @can('job-category-list')
        <a role="button" class="btn navbtncolor" href="{{ url('/JobCategory') }}" id="jobcategorylink">Job Category <span class="caret"></span></a>
        @endcan
         @can('Payroll-Accounts-list')
        <a role="button" class="btn navbtncolor" href="{{ route('payrollchartaccount')}}" id="chartaccountslink">Payroll Chart of Accounts<span class="caret"></span></a>
        @endcan
        {{-- @can('work-category-list')
        <a role="button" class="btn navbtncolor" href="{{ url('/workCategoryList') }}" id="work_category_link">Work Category <span class="caret"></span></a>
        @endcan
        @can('month-work-hours-list')
        <a role="button" class="btn navbtncolor" href="{{ url('/MonthWorkHour') }}" id="month_work_hours_link">Monthly Work Hours <span class="caret"></span></a>
        @endcan --}}
      </div>
  @endif

    </div>


