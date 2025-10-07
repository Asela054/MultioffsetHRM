
<div class="row nowrap" style="padding-top: 5px;padding-bottom: 5px;">

  @if(auth()->user()->can('EmployeePayment-list'))
    <div class="dropdown">
      <a  role="button" data-toggle="dropdown" class="btn navbtncolor" data-target="#" href="#" id="payroll_Remunerations">
          Policy Management <span class="caret"></span></a>
  
          <ul class="dropdown-menu multi-level dropdownmenucolor" role="menu" aria-labelledby="dropdownMenu">
  
           
              <li><a class="dropdown-item" href="{{ route('RemunerationList')}}" id="travelrequest_link">Facilities</a></li>
              {{-- <li><a class="dropdown-item" href="{{ route('rankpayrollprofile')}}" id="boardingfees_link">Rank Payroll Profile</a></li> --}}
              <li><a class="dropdown-item" href="{{ route('PayrollProfileList')}}" id="boardingfees_link">Payroll Profile</a></li>

              <li><a class="dropdown-item" href="{{ url('EmployeeLoanList') }}">Loans</a></li>
              @can('Loan Approve')
              <li><a class="dropdown-item" href="{{ url('EmployeeLoanAdmin') }}">Loan Approval</a></li>
              @endcan
   
              <li><a class="dropdown-item" href="{{ route('EmployeeLoanInstallmentList')}}" id="boardingfees_link">Loan Settlement</a></li>                  
              <li><a class="dropdown-item" href="{{ route('EmployeeTermPaymentList')}}" id="travelrequest_link">Addition & Deduction</a></li>
              <li><a class="dropdown-item" href="{{ route('OtherFacilityPaymentList')}}" id="travelrequest_link">Other Facilities</a></li>       
              <li><a class="dropdown-item" href="{{ route('SalaryIncrementList')}}" id="travelrequest_link">Salary Increments</a></li>
              <li><a class="dropdown-item" href="{{ route('SalaryProcessSchedule')}}" id="travelrequest_link">Salary Schedule</a></li>
              <li><a class="dropdown-item" href="{{ route('EmployeeWorkSummary')}}" id="travelrequest_link">Work Summary</a></li>
              {{-- <li><a class="dropdown-item" href="{{ route('shiftsalarypreparation')}}" id="travelrequest_link">Shift Salary Preperation</a></li> --}}
              <li><a class="dropdown-item" href="{{ route('EmployeePayslipList')}}" id="travelrequest_link">Salary Preperation</a></li>
              <li><a class="dropdown-item" href="{{ route('PayslipRegistry')}}" id="travelrequest_link">Payslip List</a></li>

          </ul>
      </div>

      <div class="dropdown">
        <a  role="button" data-toggle="dropdown" class="btn navbtncolor" data-target="#" href="#" id="payroll_ReportList">
            Reports <span class="caret"></span></a>
    
            <ul class="dropdown-menu multi-level dropdownmenucolor" role="menu" aria-labelledby="dropdownMenu">
    
                <li><a class="dropdown-item" href="{{ route('ReportPayRegister')}}" id="travelrequest_link">Pay Register</a></li>
                <li><a class="dropdown-item" href="{{ route('ReportEmpOvertime')}}" id="boardingfees_link">OT Report</a></li>
                <li><a class="dropdown-item" href="{{ route('ReportEpfEtf')}}" id="travelrequest_link">EPF and ETF</a></li>
                <li><a class="dropdown-item" href="{{ route('ReportSalarySheet')}}" id="travelrequest_link">Salary Sheet</a></li>                
                <li><a class="dropdown-item" href="{{ route('ReportSalarySheetBankSlip')}}" id="travelrequest_link">Salary Sheet - Bank Slip</a></li>                
                <li><a class="dropdown-item" href="{{ route('ReportHeldSalaries')}}" id="travelrequest_link">Salary Sheet - Held Payments</a></li>               
                <li><a class="dropdown-item" href="{{ route('ReportSixMonth')}}" id="travelrequest_link">Six Month Report</a></li>               
                <li><a class="dropdown-item" href="{{ route('ReportAddition')}}" id="travelrequest_link">Additions Report</a></li>       
                <li><a class="dropdown-item" href="{{ route('bonusreport')}}" id="travelrequest_link">Bonus Report</a></li>    
                <li><a class="dropdown-item" href="{{ route('leavebalancereport')}}" id="travelrequest_link">Leave Balance Report</a></li>           
               
            </ul>
        </div>

        <div class="dropdown">
          <a  role="button" data-toggle="dropdown" class="btn navbtncolor" data-target="#" href="#" id="payroll_statments">
            Statements <span class="caret"></span></a>
      
              <ul class="dropdown-menu multi-level dropdownmenucolor" role="menu" aria-labelledby="dropdownMenu">
      
                  <li><a class="dropdown-item" href="{{ route('EmpSalaryPayVoucher')}}" id="travelrequest_link">Employee Salary (Payment Voucher)</a></li>
                  <li><a class="dropdown-item" href="{{ route('EmpIncentivePayVoucher')}}" id="boardingfees_link">Employee Incentive (Payment Voucher)</a></li>
                  <li><a class="dropdown-item" href="{{ route('ReportBankAdvice')}}" id="travelrequest_link">Bank Advice</a></li>
                  <li><a class="dropdown-item" href="{{ route('ReportPaySummary')}}" id="travelrequest_link">Pay Summary</a></li>                
                  <li><a class="dropdown-item" href="{{ route('EmpSalaryJournalVoucher')}}" id="travelrequest_link">Employee Salary (Journal Voucher)</a></li>                
                  <li><a class="dropdown-item" href="{{ route('EmpEpfEtfJournalVoucher')}}" id="travelrequest_link">EPF and ETF (Journal Voucher)</a></li>                            
                 
              </ul>
          </div>
  @endif

</div>


