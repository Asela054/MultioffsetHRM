<div class="sidebar open">
    {{--<div class="logo-details">
      <i class="bx bxl-c-plus-plus icon"></i>
      <div class="logo_name"></div>
      <i class="bx bx-menu" id="btn"></i>
    </div>--}}
    <ul class="nav-list">
      <li>
        <a href="{{ url('/home') }}" id="dashboard_link">
          <i class="fa-light fa-desktop"></i>
          <span class="links_name">Dashboard</span>
        </a>
        <span class="tooltip">Dashboard</span>
      </li>

      @if(auth()->user()->can('location-list') 
      || auth()->user()->can('company-list') 
      || auth()->user()->can('bank-list') 
      || auth()->user()->can('work-category-list') 
      || auth()->user()->can('month-work-hours-list'))
      <li>
        <a href="{{ url('/corporatedashboard') }}" id="organization_menu_link">
          <i class="fa-light fa-building"></i>
          <span class="links_name">Organization</span>
        </a>
        <span class="tooltip">Organization</span>
      </li>
      @endif

      @if(auth()->user()->can('job-title-list')
            || auth()->user()->can('pay-grade-list')
            || auth()->user()->can('job-category-list')
            || auth()->user()->can('job-employment-status-list')
            || auth()->user()->can('skill-list')
            || auth()->user()->can('employee-list')
            || auth()->user()->can('employee-select')
            || auth()->user()->can('pe-task-list')
            || auth()->user()->can('allowance-amount-list'))
      <li>
        <a href="{{ url('/employeemanagementdashboard') }}" id="employee_menu_link">
          <i class="fa-light fa-users-gear"></i>
          <span class="links_name">Employee Management</span>
        </a>
        <span class="tooltip">Employee Management</span>
      </li>
      @endif

      @if(auth()->user()->can('attendance-sync')
            || auth()->user()->can('attendance-incomplete-data-list')
            || auth()->user()->can('attendance-list')
            || auth()->user()->can('attendance-create')
            || auth()->user()->can('attendance-edit')
            || auth()->user()->can('attendance-delete')
            || auth()->user()->can('attendance-approve')
            || auth()->user()->can('late-attendance-create')
            || auth()->user()->can('late-attendance-approve')
            || auth()->user()->can('late-attendance-list')
            || auth()->user()->can('attendance-incomplete-data-list')
            || auth()->user()->can('ot-approve')
            || auth()->user()->can('ot-list')
            || auth()->user()->can('finger-print-device-list')
            || auth()->user()->can('finger-print-user-list')
            || auth()->user()->can('attendance-device-clear')
            || auth()->user()->can('leave-list')
            || auth()->user()->can('leave-type-list')
            || auth()->user()->can('leave-approve')
            || auth()->user()->can('holiday-list'))
      <li>
        <a href="{{ url('/attendenceleavedashboard') }}" id="attendant_menu_link">
          <i class="fa-light fa-calendar-pen"></i>
          <span class="links_name">Attendance & Leave</span>
        </a>
        <span class="tooltip">Attendance & Leave</span>
      </li>
      @endif

      @if(auth()->user()->can('shift-list')
            || auth()->user()->can('work-shift-list')
            || auth()->user()->can('additional-shift-list'))
      <li>
        <a href="{{ url('/shiftmanagementdashboard') }}" id="shift_menu_link">
          <i class="fa-light fa-business-time"></i>
          <span class="links_name">Shift Management</span>
        </a>
        <span class="tooltip">Shift Management</span>
      </li>
      @endif

      @if(auth()->user()->can('employee-report')
            || auth()->user()->can('attendance-report')
            || auth()->user()->can('late-attendance-report')
            || auth()->user()->can('leave-report')
            || auth()->user()->can('employee-bank-report')
            || auth()->user()->can('leave-balance-report')
            || auth()->user()->can('ot-report')
            || auth()->user()->can('no-pay-report')
            || auth()->user()->can('employee-resign-report')
            || auth()->user()->can('employee-absent-report'))
      <li>
        <a href="{{ url('/reportdashboard') }}" id="report_menu_link">
          <i class="fa-light fa-file-contract"></i>
          <span class="links_name">Reports</span>
        </a>
        <span class="tooltip">Reports</span>
      </li>
      @endif

      @if(auth()->user()->can('EmployeePayment-list'))
      
      <li>
        <a href="{{ url('/payrolldashboard') }}" id="payroll_menu_link">
          <i class="fa-light fa-money-check-dollar-pen"></i>
          <span class="links_name">Payroll</span>
        </a>
        <span class="tooltip">Payroll</span>
      </li>
      @endif

    @if(auth()->user()->can('user-account-summery-list'))
      <li>
        <a href="{{ url('/useraccountsummery') }}" id="user_information_menu_link">
          <i class="fa-light fa-id-card"></i>
          <span class="links_name">User Account Summery</span>
        </a>
        <span class="tooltip">User Account Summery</span>
      </li>
      @endif

      @if(auth()->user()->can('user-list') || auth()->user()->can('role-list'))
      <li>
          <a href="{{ url('/administratordashboard') }}" id="administrator_menu_link">
            <i class="fa-light fa-gears"></i>
            <span class="links_name">Administrator</span>
          </a>
          <span class="tooltip">Administrator</span>
        </li>
      <li>
      @endif

        <div class="sidenav-footer" style="position: fixed;
            bottom: 0;
            width: 100%;">
            <div class="sidenav-footer-content">
                <div style="margin-bottom: 0" class="sidenav-footer-subtitle small">Logged in as:</div>
                <div class="sidenav-footer-title small">
                    @isset(Auth::user()->name)
                    {{ Auth::user()->name }}
                    @endisset</div>
            </div>
        </div>
      </li>
    </ul>
  </div>