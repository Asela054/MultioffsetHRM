<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title></title>
  <style>
	@page { 
    size: 220mm 120mm;
		font-size:12px;
    margin: 5mm 5mm 5mm 5mm;
	}
   * {
      font-family: Arial, sans-serif;
     }
     table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0; 
        border: 1px solid #000;
        border-radius: 5px;
        overflow: hidden;
     }
     th, td {
        padding: 2.5px;
    }
    .bodytd{
        border: 1px solid;
    }
  </style>
</head>
<body>

    @php $check=0 @endphp
    
        @for ($slipcnt=0;$slipcnt<count($emp_array);$slipcnt++)
  
            @if(isset($emp_array[$slipcnt]))
            
            @php $row=$emp_array[$slipcnt] @endphp
            
            @php 
                     $netbasicValue = ($row['BASIC'] + $row['BRA_I'] + $row['add_bra2']) - $row['NOPAY'];
                     $totalearnValue = $row['OTHRS1'] + $row['OTHRS2'] + $row['ATTBONUS_W'] + $row['INCNTV_EMP'] + $row['INCNTV_DIR'];
   
                    $netbasic = number_format((float)$netbasicValue, 2, '.', ',');
                    $totalearn = number_format((float)$totalearnValue, 2, '.', ',');
                    
                    $grosspay = number_format((float)($netbasicValue + $totalearnValue), 2, '.', ',');
             @endphp

              <table>
                  <tbody>
                    <tr>
                      <td colspan="3"><b>{{ session("company_name") }}</b></td>
                      <td colspan="3" style="text-align:right;"><b>Pay Advice for the Month of {{$paymonth_name}}</b></td>
                  </tr>
                  <tr >
                      <td  style="border-top: none; border-right:none;" colspan="3" ><b>{{ $company_addr }}</b></td>
                      <td  style="border-top: none; border-right:none;text-align:right;" colspan="3" ><b>DATE: {{ \Carbon\Carbon::now()->startOfMonth()->addDays(3)->format('d/m/Y') }}</b></td>
                  </tr>
                      <tr>
                          <td class="bodytd"  style="border-left:none; border-right:none;border-bottom:none;">NAME</td>
                          <td class="bodytd"  style="border-left:none; border-bottom:none;border-right:none;">: &nbsp;{{ $row['emp_first_name'] }}</td>
                          <td class="bodytd"  width="30%"colspan="2" style="text-align:center; border-bottom:none; border-right:none;"><b>RECEIVABLES</b></td>
                          <td class="bodytd"  width="30%"colspan="2" style="text-align:center; border-bottom:none; border-right:none;"><b>DEDUCTION</b></td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">NIC NO</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none;border-top:none; border-right:none;">: &nbsp;{{ $row['emp_national_id'] }}</td>
                          <td class="bodytd" style="border-right:none; border-bottom:none; border-right:none;" >
                              <div style="display: inline-block; width: 50%; text-align: left;   white-space: nowrap;">OVERTIME</div>
                              <div style="display: inline-block; width: 45%; text-align: right;  white-space: nowrap;">{{ number_format((float)$row['OTAMT1'], 2, '.', ',') }}</div>
                          </td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none;"> 
                              <div style="display: inline-block; width: 45%; text-align: left; white-space: nowrap;">{{ (float)$row['OTHRS1'] != 0 ? number_format((float)$row['OTHRS1'] / (float)$row['OTAMT1'], 2, '.', ',') : '00.00' }}</div>
                              <div style="display: inline-block; width: 50%; text-align: right; white-space: nowrap;">{{ number_format((float)$row['OTHRS1'], 2, '.', ',') }}</div>
                          </td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; ">EPF 8%</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; ">{{ number_format((float)$row['EPF8'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none; ">EPF NO</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none;border-top:none; border-right:none;">: &nbsp;{{ $row['emp_epfno'] }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">
                              <div style="display: inline-block; width: 50%; text-align: left;   white-space: nowrap;">HOLIDAY</div>
                              <div style="display: inline-block; width: 45%; text-align: right;  white-space: nowrap;">{{ number_format((float)$row['OTAMT2'], 2, '.', ',') }}</div>
                          </td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none;"> 
                              <div style="display: inline-block; width: 45%; text-align: left; white-space: nowrap;">{{ (float)$row['OTHRS2'] != 0 ? number_format((float)$row['OTHRS2'] / (float)$row['OTAMT2'], 2, '.', ',') : '00.00' }}</div>
                              <div style="display: inline-block; width: 50%; text-align: right; white-space: nowrap;">{{ number_format((float)$row['OTHRS2'], 2, '.', ',') }}</div>
                          </td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">FUNARAL FUND</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">{{ number_format((float)$row['ded_fund_1'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;">BASIC SALARY </td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; text-align: right;"> &nbsp;{{ number_format((float)($row['BASIC'] + $row['BRA_I'] + $row['add_bra2']), 2, '.', ',') }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">REIMB.TRAV.</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">&nbsp;{{ number_format((float)$row['ATTBONUS_W'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">LOAN</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">&nbsp;{{ number_format((float)$row['LOAN'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">NO PAY </td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"> &nbsp;{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">INCENTIVE.</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">&nbsp;{{ number_format((float)$row['INCNTV_EMP'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">APIT</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">&nbsp;{{ number_format((float)$row['PAYE'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">NET BASIC</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; text-align: right;"> &nbsp;{{ $netbasic }}
                        </td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">DIR. INCENTIVE</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">&nbsp;{{ number_format((float)$row['INCNTV_DIR'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">ADVANCE</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">&nbsp;{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">RECEIVABLES</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"> &nbsp;{{ $totalearn }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;"></td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"></td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;"></td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;"></td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">GROSS PAY</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; text-align: right;"> &nbsp;&nbsp;{{ $grosspay }}
                        </td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;"></td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"></td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;"></td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;"></td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">TOTAL DEDUCTIONS</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"> &nbsp;{{ number_format((float)$row['tot_ded'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;"></td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;"></td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;"></td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;"></td>
                      </tr>
                      <tr>
                          <td class="bodytd" style=" border-left:none; border-right:none;border-bottom:none;border-top:none;">NET SALARY</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; text-align: right;"> &nbsp;{{ number_format((float)$row['NETSAL'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">TOTAL</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none;  text-align: right;">&nbsp;{{ $totalearn }}</td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">TOTAL</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none;">{{ number_format((float)$row['tot_ded'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; text-align:center;">YOUR NET SALARY AS ABOVE IS SEND TO</td>
                          <td class="bodytd" colspan="2" style="text-align:center;  border-right:none;"><b>ATTENDANCE SUMMARY</b></td>
                          <td class="bodytd" colspan="2" style="text-align:center;  border-right:none;"><b>EMPLOYER</b></td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; border-top:none; text-align:center;">{{ $row['bank_name'] }} - {{ $row['bank_branch'] }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;  ">WORKING</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">{{ number_format((float)$row['work_tot_days'], 2, '.', ',') }}</td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">EPF 12%</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">{{ number_format((float)$row['EPF12'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; border-top:none; text-align:center;">ACCOUNT NO - {{ $row['bank_accno'] }}</td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">NO PAY DAYS</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">
                              <div style="display: inline-block; width: 45%; text-align: left; white-space: nowrap;">{{ (float)$row['NOPAY'] != 0 ? number_format((float)$row['NOPAY'] / (float)$row['NOPAYCNT'], 2, '.', ',') : '00.00' }}</div>
                              <div style="display: inline-block; width: 50%; text-align: right; white-space: nowrap;">{{ number_format((float)$row['NOPAYCNT'], 2, '.', ',') }}</div>
                          </td>
                          <td class="bodytd" style="text-align: left; border-right:none; border-bottom:none; border-top:none;">ETF 3%</td>
                          <td class="bodytd" style="text-align: right; border-left:none; border-bottom:none; border-right:none; border-top:none;">{{ number_format((float)$row['ETF3'], 2, '.', ',') }}</td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; border-top:none; text-align:center;"></td>
                          <td class="bodytd" style="border-right:none;border-bottom:none; border-right:none; border-top:none;">LATE ATTENDANCE H/M</td>
                          <td class="bodytd" style="border-left:none; border-bottom:none; border-right:none; border-top:none; text-align: right;">00.00</td>
                          <td class="bodytd" colspan="2" style="text-align: left; border-right:none; border-bottom:none; border-top:none;"></td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; "></td>
                          <td class="bodytd" colspan="2" style="text-align:center;border-bottom:none;  border-right:none;"><b></b></td>
                          <td class="bodytd" colspan="2" style="text-align:center;border-bottom:none; border-top:none; border-right:none;"></td>
                      </tr>
                      <tr>
                          <td class="bodytd" colspan="2" style=" border-left:none; border-right:none;border-bottom:none; border-top:none; padding-top:20px;"></td>
                          <td class="bodytd" colspan="2" style="text-align:center;border-bottom:none;  border-top:none; border-right:none; padding-top:20px;"><b></b></td>
                          <td class="bodytd" colspan="2" style="text-align:center;border-bottom:none; border-top:none; border-right:none; padding-top:20px;">
                            .......................................... <br>EMPLOYEE'S SIGNATURE</td>
                      </tr>
                  </tbody>
              </table>
            @endif
            
            @php $check++ @endphp
        	
        @endfor
      
  </body>
</html>