<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Laravel PDF</title>
    <!--link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"-->
    <style type="text/css">
	@page { 
		/* size: 21cm 29.5cm portrait;  */
    size: 220mm 120mm;
		font-size:10px;
		/* margin:0.5cm; */
    margin: 5mm 15mm 5mm 5mm;
	}


  * {
      font-family: Arial, sans-serif;;
  }
  table,tr,th,td{
    font-family: Arial, sans-serif;;
    }
    
  hr{
    font-family: Arial, sans-serif;;
  }
  
	/**/
	hr.pgbrk {
		page-break-after:always;
		border:0;
	}
	
	
	
	p{
		/*
		border-top:1px solid grey;
		*/
		margin-top:1px;
		padding-top:3px;
		margin-bottom:0px;
	}
	
	table.emp_info td:nth-child(1) hr{
		top:-2px;
	}
	table.emp_info td:nth-child(3) hr{
		top:1px;
	}
	
	table.emp_info hr.hr_thin{
		height:0.5px; border:none; color:grey; background-color:grey;
		margin-top:-0.5px;
    font-family: Arial, sans-serif;;
	}
	table.emp_info hr.hr_stretch{
		width:115%;
	}
	
	table td{
		padding-left:5px;/*10px*/
		padding-right:5px;/*10px*/
	}
	
	table.emp_info tr.col_head td{
		border:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.emp_info tr.col_foot td{
		/*
		border-bottom:1px solid grey;
		*/
	}
	table.emp_info .col_foot td:nth-child(1),
	table.emp_info .col_foot td:nth-child(2){
		/*
		border-top:1px solid grey !Important;
		*/
	}
	table.emp_info tr td:first-child{
		border-left:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.emp_info td:last-child{
		border-right:1px solid grey;
    font-family: Arial, sans-serif;;
		
	}
	table.emp_info tr:last-child td{
		border-bottom:1px solid grey;
    font-family: Arial, sans-serif;;
		
	}
	
	table.sal_info, table.gen_info{
		border:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.sal_info td:nth-child(1), 
	table.sal_info td:nth-child(2){
		border-right:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.sal_info td.col_head,
	table.sal_info td.col_foot{
		border-top:1px solid grey;
		border-bottom:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	
	table.gen_info td.main_fig{
		border:1px solid grey;
		vertical-align:top;
    font-family: Arial, sans-serif;;
	}
	table.gen_info td.left_border{
		border-left:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.emp_info td.right_border{
		border-right:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	table.emp_info td.top_border{
		border-top:1px solid grey;
    font-family: Arial, sans-serif;;
	}
	
	
	table.emp_info tr td{
		height:14px;
	}
	
	tr.summary_sect td{
		border-top:1px solid grey; border-bottom:1px solid double;
    font-family: Arial, sans-serif;;
	}
	
	span.fig_val{
		/*float:right;*/
		padding-left:5px;
	}
  table.emp_info {
        border-radius: 10px; 
    }
    .top-bordered {
        border-top:1px solid grey;
    }


    table.emp_info hr.hr_headtop{
		height:0.5px; border:none; color:grey; background-color:grey;
  	width:100%;
	}
	

	</style>
  </head>
  <body>
    @php $check=0 @endphp
    
        @for ($slipcnt=0;$slipcnt<count($emp_array);$slipcnt++)
        	{{-- @if( (($check>1)&&($check%2)==1) ) 
            	@php echo '<div style="page-break-before: always;"></div>'; @endphp
            @elseif( ($check%2)==0 ) 
            	@php echo '<hr style="border:none; color:black; background-color:black; height:1px;" />'; @endphp
            @endif --}}

            @if(isset($emp_array[$slipcnt]))
            
            @php $row=$emp_array[$slipcnt] @endphp
            
            <table width="100%" border="0" cellpadding="2" cellspacing="0" class="emp_info">

              <tr class="col_head" >
                <td colspan="3" align="left"  style="border-bottom: none;  border-right:none;"><strong>{{ session("company_name") }}</strong></td>
                <td colspan="3" align="right "  style=" border-bottom: none; border-left:none;"><strong>Pay Advaice for the Month of {{$paymonth_name}}</strong> </td>
              </tr>
              
              <tr class="col_head">
                <td colspan="3" align="left" style="border-top: none; border-right:none;"><strong>{{ $company_addr }}</strong></td>
                <td colspan="3" align="right" style="border-top: none; border-left:none;">
                  <strong> DATE: {{ \Carbon\Carbon::now()->startOfMonth()->addDays(3)->format('m/d/Y') }}</strong></td>
              </tr>

              <tr align="center" class="col_head">
                <td align="left"  width="33%" colspan="2" style="border-bottom: none;">
                  <span style="margin-right:15px;">NAME</span><span class="fig_val">:  {{ $row['emp_first_name'] }}</span>
                </td>
                <td width="33%" colspan="2"><strong>RECEIVABLES</strong></td>
                <td width="33%" colspan="2"><strong>DEDUCTION</strong></td>
              </tr>

              <tr>
                <td align="left"  width="33%" colspan="2" style="border-bottom: none;" class="right_border">
                  <span style="margin-right:9px;">NIC NO</span><span class="fig_val">:  {{ $row['emp_national_id'] }}</span>
                </td>
                
                <td colspan="" style="padding-right:0px;">OVERTIME</td>

                <td class="right_border" >
                  <span style="text-align:left;">{{ number_format((float)$row['OTAMT1'], 2, '.', ',') }}</span>
                  <span style="margin-left:2px; text-align:center;">  {{ (float)$row['OTHRS1'] != 0 ? number_format((float)$row['OTHRS1'] / (float)$row['OTAMT1'], 2, '.', ',') : '00.00' }}</span>
                  <span style="margin-left:2px; text-align:right;">{{ number_format((float)$row['OTHRS1'], 2, '.', ',') }}</span>
              </td>

                <td colspan="">E.P.F. 8%:</td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['EPF8'], 2, '.', ',') }}</span><!--&nbsp;--></td>

              </tr>

              <tr>
                <td align="left"  width="33%" colspan="2" style="border-bottom: none;" class="right_border">
                  <span style="margin-right:7px;">EPF NO</span><span class="fig_val">:  {{ $row['emp_epfno'] }}</span>
                </td>

                <td colspan="" style="padding-right:0px;">HOLIDAY </td>

                <td class="right_border">
                  <span style="text-align:left;">{{ number_format((float)$row['OTAMT2'], 2, '.', ',') }}</span>
                  <span style="margin-left:2px; text-align:center; " style="align-items: center;">  {{ (float)$row['OTHRS2'] != 0 ? number_format((float)$row['OTHRS2'] / (float)$row['OTAMT2'], 2, '.', ',') : '00.00' }}</span>
                  <span style="margin-left:2px; text-align:right;" >{{ number_format((float)$row['OTHRS2'], 2, '.', ',') }}</span>
                </td>

                <td colspan=""></td>   
                <td align="right"></td>
              </tr>
             
              <tr>
                <td colspan=""><hr class="hr_thin hr_stretch" />BASIC SALARY</td>
                <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['BASIC'], 2, '.', ',') }}</span></td>
                <td colspan="">REIMB.TRAV.</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['tot_earn'], 2, '.', ',') }}</span><!--...--></td>
                <td  colspan=""></td><td class="right_border" align="right"></td>
              </tr>

              <tr>
                <td colspan="">BRA I</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['BRA_I'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="2" class="right_border"></td>
                <td colspan="2" align="right"></td>
              </tr>
              <tr>
                <td colspan="">BRA II</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['add_bra2'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="2" class="right_border"></td>
                <td colspan="2" ></td>
              </tr>
              <tr>
                <td colspan="">NO PAY</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="2" class="right_border"></td>
                <td colspan="2"></td>
              </tr>
              <tr>
                <td colspan=""><hr class="hr_thin hr_stretch" />NET BASIC</td>
                <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['tot_bnp'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="2" class="right_border"></td>
                <td colspan="2"></td>
              </tr>

              <tr>
                <td colspan="">RECEIVABLES</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['add_bra2'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="2" class="right_border"></td>
                <td colspan="2"></td>
              </tr>

              <tr>
                <td colspan=""><hr class="hr_thin hr_stretch" />GROSS PAY</td>
                <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</span><!--...--></td>
                <td class="right_border" colspan="2">&nbsp;</td>
                <!--td align="right">&nbsp;</td-->
                <td colspan="" ></td>
                <td align="right"><span class="fig_val"></span><!--&nbsp;--></td>
              </tr>

              <tr>
                <td colspan="">TOTAL DEDUCTIONS</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['tot_ded'], 2, '.', ',') }}</span></td>
                <td class="right_border" colspan="2" ></td>
                <td class="right_border" colspan="2" ></td>
              </tr>

              <tr>
                <td colspan=""><hr class="hr_thin hr_stretch" /><strong>NET SALARY</strong></td>
                <td class="right_border" align="right"><hr class="hr_thin " /><span class="fig_val">{{ number_format((float)$row['NETSAL'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan=""><hr class="hr_thin hr_stretch" />TOTAL</td>
                <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['tot_earn'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan=""><hr class="hr_thin hr_stretch" />TOTAL</td>
                <td class="right_border" align="right"><hr class="hr_thin " /><span class="fig_val">{{ number_format((float)$row['tot_ded'], 2, '.', ',') }}</span><!--&nbsp;--></td>
              </tr>

              <tr>
                <td class="right_border" colspan="2" style="text-align: center; vertical-align: middle;">
                    <hr class="hr_thin" /><span>YOUR NET SALARY AS ABOVE IS SEND TO</span>
                </td>
                <td class="right_border" colspan="2" style="text-align: center; vertical-align: middle;">
                    <hr class="hr_thin" /><strong>ATTENDANCE SUMMARY</strong>
                </td>
                <td colspan="2" style="text-align: center; vertical-align: middle;">
                    <hr class="hr_thin" /><strong>EMPLOYER</strong>
                </td>
            </tr>
          
            <tr>
              <td class="right_border" colspan="2" style="text-align: center; vertical-align: middle;"><span>BANK - BRANCH</span></td>
              <td colspan=""><hr class="hr_thin hr_stretch" />WORKING</td>
              <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['ATTBONUS'], 2, '.', ',') }}</span><!--&nbsp;--></td>
              <td colspan=""><hr class="hr_thin hr_stretch" />EPF 12%</td>
              <td class="right_border" align="right"><hr class="hr_thin" /><span class="fig_val">{{ number_format((float)$row['EPF12'], 2, '.', ',') }}</span><!--&nbsp;--></td>
            </tr>

              <tr>
                <td class="right_border" colspan="2" style="text-align: center; vertical-align: middle;"><span>ACCOUNT NO  <span >{{ $row['bank_accno'] }}</span></span></td>
                <td colspan="">NO PAY DAYS</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td colspan="">ETF 3%</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['ETF3'], 2, '.', ',') }}</span><!--&nbsp;--></td>
              </tr>

              <tr>
                <td class="right_border" colspan="2" ></td>
                <td colspan="">LATE ATTENDANCE H/M</td>
                <td class="right_border" align="right"><span class="fig_val">{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</span><!--&nbsp;--></td>
                <td class="right_border" colspan="2"></td>
              </tr>

            <tr class="col_foot">
              <td class="right_border" colspan="2" ><hr class="hr_thin" /></td>
              <td class="right_border" colspan="2" ><hr class="hr_thin" /></td>
              <td class="right_border" colspan="2" align="center"></td>
            </tr>
              <tr class="col_foot">
                <td class="right_border" colspan="2" ></td>
                <td class="right_border" colspan="2" ></td>
                <td colspan="2" align="center">............................................ <br>EMPLOYEE'S SIGNATURE</td>
              </tr>
            </table>

            {{-- <hr style="border:1px dashed grey;" />
            <table width="100%" border="0" cellpadding="2" cellspacing="0" style="font-size:8px;" class="gen_info">
              <tr>
                <td width="15%" class="main_fig">EMP NO: {{ $row['emp_epfno'] }}</td>
                <td width="15%" class="main_fig">E.P.F. NO: {{ $row['emp_epfno'] }}</td>
                <td width="30%" class="main_fig" colspan="4">NAME: {{ $row['emp_first_name'] }}</td>
                <td colspan="2" class="main_fig" width="26%">PAYSLIP FOR : {{ $paymonth_name }}</td>
                <td width="14%" rowspan="4" align="center" style="vertical-align:bottom; border-left:1px solid grey;">..................</td>
              </tr>
              <tr>
                <td colspan="2" class="main_fig" width="30%">SECTION <span class="fig_val">{{ $row['Office'] }}</span></td>
                <td width="10%">BASIC: </td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['BASIC'], 2, '.', ',') }}</span></td>
                <td width="8%" class="left_border">NO PAY</td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['NOPAY'], 2, '.', ',') }}</span></td>
                <td width="13%" class="left_border">TOTAL EARNED</td>
                <td width="15%" align="right"><span class="fig_val">{{ number_format((float)$row['tot_earn'], 2, '.', ',') }}</span></td>
                <!--td width="14%">&nbsp;</td-->
              </tr>
              <tr>
                <td colspan="2" class="main_fig">DEPARTMENT <span class="fig_val">{{ $sect_name }}</span></td>
                <td>BRA I</td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['BRA_I'], 2, '.', ',') }}</span></td>
                <td class="left_border">ARREARS</td>
                <td align="right"></td>
                <td class="left_border">TOTAL DEDUCTIONS</td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['tot_ded'], 2, '.', ',') }}</span></td>
                <!--td>&nbsp;</td-->
              </tr>
              <tr>
                <td colspan="2" rowspan="2" class="main_fig">DESIGNATION<span class="fig_val">{{ $row['emp_designation'] }}</span></td>
                <td>BRA II</td>
                <td align="right"><span class="fig_val">{{ number_format((float)$row['add_bra2'], 2, '.', ',') }}</span></td>
                <td class="left_border">&nbsp;</td>
                <td align="right"></td>
                <td class="left_border"><strong>NET SALARY</strong></td>
                <td align="right"><strong>{{ number_format((float)$row['NETSAL'], 2, '.', ',') }}</strong></td>
                <!--td>&nbsp;</td-->
              </tr>
              <tr>
                <td colspan="3" class="" style="border-top:1px solid grey;">SALARY FOR E.P.F</td>
                <td align="right" style="border-top:1px solid grey;"><span class="fig_val">{{ number_format((float)$row['tot_bnp'], 2, '.', ',') }}</span></td>
                <td colspan="2" class="main_fig">
                    <table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
                      <tr>
                        <td width="33%">8%<span class="fig_val">{{ number_format((float)$row['EPF8'], 2, '.', ',') }}</span></td>
                        <td width="33%">12%<span class="fig_val">{{ number_format((float)$row['EPF12'], 2, '.', ',') }}</span></td>
                        <td width="33%">3%<span class="fig_val">{{ number_format((float)$row['ETF3'], 2, '.', ',') }}</span></td>
                      </tr>
                    </table>
                </td>
                <td align="center">SIGNATURE</td>
              </tr>
            </table> --}}
            
            @endif
            
            @php $check++ @endphp
        	
        @endfor
      
  </body>
</html>