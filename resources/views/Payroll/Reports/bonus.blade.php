@extends('layouts.app')

@section('content')

				<main>
					<div class="page-header page-header-light bg-white shadow">
                        <div class="container-fluid">
                            @include('layouts.payroll_nav_bar')
                           
                        </div>
                    </div>
                    <div class="container-fluid mt-4">
                        <div class="row">
                       
                           
                            <div class="col-lg-12">
                                <div id="default">
                                    <form id="frmExport" method="post" action="{{ url('DownloadEpfEtf') }}">
                                    {{ csrf_field() }}
                                        <div class="card card-header-actions mb-4">
                                            <div class="card-header">
                                              Bonus Report
                                                <span id="lbl_duration" style="display:none; margin-right:auto; padding-left:10px;">
                                                    <span id="lbl_date_fr">&nbsp;</span> To <span id="lbl_date_to">&nbsp;</span>
                                                    (<span id="lbl_payroll_name">&nbsp;</span>)
                                                </span>
                                                <div>
                                                    <button type="button" name="find_employee" id="find_employee" class="btn btn-success btn-sm">Search</button>
                                                    <button type="button" name="print_record" id="print_record" disabled="disabled" class="btn btn-secondary btn-sm btn-light" onclick="generatePDF();">Download</button>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body">
                                                <div id="divPrint" class="datatable table-responsive" style="margin-top:0px;">
                                                    
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="formModal" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <form id="frmSearch" method="post">
                            {{ csrf_field() }}	
                                <div class="modal-content">
                                   <div class="modal-header">
                                       <h5 class="modal-title" id="formModalLabel">Filter Report Type</h5>
                                       
                                       <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span class="btn-sm btn-danger" aria-hidden="true">X</span></button>
                                   </div>
                                   <div class="modal-body">
                                        <span id="search_result"></span>
                                        <div class="row">
                                            
                                            <div class="form-group col-md-6">
                                               <label class="control-label col">Branch</label>
                                               <div class="col">
                                                   <select name="location_filter_id" id="location_filter_id" class="custom-select shipClass nest_head" style="pointer-events: none;" data-findnest="deptnest" >
                                                        <option value="" disabled="disabled" selected="selected" data-regcode="">Please Select</option>
                                                        @foreach($branch as $branches)
                                                           <option value="{{$branches->id}}" data-regcode="{{$branches->id}}">{{$branches->name}}</option>
                                                        @endforeach
                                                        
                                                   </select>
                                               </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                               <label class="control-label col">Department</label>
                                               <div class="col">
                                                   <select name="department_filter_id" id="department_filter_id" class="custom-select" style="" required>
                                                        <option value="" selected="selected">Please Select</option>
                                                        <option value="All" >All</option>
														@foreach($department as $section)
                                                           <option  value="{{$section->id}}" data-nestcode="{{$section->company_id}}" data-sectcode="{{$section->id}}">{{$section->name}}</option>
                                                        @endforeach
                                                        
                                                   </select>
                                               </div>
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                        	<div class="form-group col-md-6">
                                               <label class="control-label col" >Report type</label>
                                               <div class="col">
                                                 <select name="reporttype" id="reporttype" class="form-control" required>
                                                    <option value="" disabled="disabled" selected="selected">Please select</option>
													<option value="1" >Monthly Basis</option>
													<option value="2" >Daily Basis</option>
											
                                                 </select>
                                               </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                            	<label class="control-label col">From - To</label>
													<div class="input-group input-group-md mb-3">
														<input type="month" id="from_date" name="from_date" class="form-control form-control-md border-right-0" placeholder="yyyy-mm-dd" required>
														<input type="month" id="to_date" name="to_date" class="form-control" placeholder="yyyy-mm-dd" required>
													</div>
                                            </div>
                                        </div>
                                   </div>
                                   <div class="modal-footer" align="right">
                                       <input type="submit" name="action_button" id="action_button" class="btn btn-warning" value="View Bonus Report" />
                                       <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                   </div>
                                   
                                </div>
                            </form>
                        </div>
                    </div>


                </main>
              
@endsection


@section('script')

<script>
$(document).ready(function(){
	$('#payroll_menu_link').addClass('active');
    $('#payroll_menu_link_icon').addClass('active');
    $('#payroll_ReportList').addClass('navbtnactive');

	var companyId = '{{ session("company_id") }}';
    var companyName = '{{ session("company_name") }}';

    if (companyId && companyName) {
        $('#location_filter_id').val(companyId).trigger('change');
		var findNest = $('#location_filter_id').data('findnest');
		var regCode = $('#location_filter_id option:selected').data('regcode');
    }
	
	
	$("#frmSearch").on('submit', function(event){
	  event.preventDefault();

      var formData = {
        company: $('#location_filter_id').val(),
        department: $('#department_filter_id').val(),
        reporttype: $('#reporttype').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val()
    };


	  $.ajax({
	   url: '{{ route("bonusreportgenerate") }}',
        type: 'POST',
        data: formData, 
        dataType: 'JSON',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
	   success:function(data){
		var html = '';
		if(data.errors){
			html = '<div class="alert alert-danger">';
			for(var count = 0; count < data.errors.length; count++){
			  html += '<p>' + data.errors[count] + '</p>';
			}
			html += '</div>';
			$('#search_result').html(html);
		}else{

            $('#formModal').modal('hide');

            if (formData.reporttype == '1')
             {
                bonustablemonth(data);
             }else{
                bonustableweekly(data);
             }

			$("#print_record").prop('disabled', false);
			$("#print_record").removeClass('btn-light');
			
			
		}
	   }
	  })
	});
	

	$('#find_employee').click(function(){
		$('#formModal').modal('show');
	});
	

});


function bonustablemonth(data) {
    var tableHtml = '<table class="table table-bordered table-hover nowarp" id="emptable" width="100%" cellspacing="0">' +
        '<thead>' +
            '<tr>' +
                '<th>EPF NO</th>' +
                '<th>Name</th>' +
                '<th>Salary</th>' +
                '<th>Nopay</th>' +
                '<th>Bonus</th>' +
            '</tr>' +
        '</thead>' +
        '<tbody>';

    // Populate the table with the employee data
    $.each(data.data, function(index, row) {
        tableHtml += '<tr>' +
            '<td>' + row.emp_etfno + '</td>' +
            '<td>' + row.emp_name + '</td>' +
            '<td>' + row.basic_salary + '</td>' +
            '<td>' + row.total_no_pay + '</td>' +
            '<td>' + row.total_bonus + '</td>' +
        '</tr>';
    });

    tableHtml += '</tbody>';
    tableHtml += '<tr>' +
                    '<td colspan="4" style="text-align:right;"><strong>Total Bonus:</strong></td>' +
                    '<td>' + data.total_bonus_all_employees + '</td>' +
                '</tr>';

    tableHtml += '</table>';

    $('#divPrint').html(tableHtml);

    // Initialize DataTable
    $('#emptable').DataTable({
        "order": [0, 'asc'],
    });
}



function bonustableweekly(data) {
    var tableHtml = '<table class="table table-bordered table-hover nowarp" id="emptable" width="100%" cellspacing="0">' +
        '<thead>' +
            '<tr>' +
                '<th >EPF NO</th>' +
                '<th >Name</th>' +
                '<th>Total Pay Salary</th>' +
                '<th>Bonus</th>' +
            '</tr>' +
        '</thead>' +
        '<tbody>';

    $.each(data.data, function(index, row) {
        tableHtml += '<tr>' +
            '<td>' + row.emp_etfno + '</td>' +
            '<td>' + row.emp_name + '</td>' +
            '<td>' + row.basic_salary + '</td>' +
            '<td>' + row.total_bonus + '</td>' +
        '</tr>';
    });

    tableHtml += '</tbody>' +
        '<tfoot>' +
            '<tr>' +
                '<td colspan="3" style="text-align:right;"><strong>Total Bonus:</strong></td>' +
                '<td>' + data.total_bonus_all_employees + '</td>' +
            '</tr>' +
        '</tfoot>' +
    '</table>';

    $('#divPrint').html(tableHtml);

    $('#emptable').DataTable({
        "order": [0, 'asc'],
    });
}


function centerText(text, y, doc) {
    var pageWidth = doc.internal.pageSize.getWidth();
    var textWidth = doc.getTextWidth(text);
    doc.text(text, (pageWidth - textWidth) / 2, y);
}

function rightAlignText(text, y, doc, margin = 22) {
    var pageWidth = doc.internal.pageSize.getWidth();
    var textWidth = doc.getTextWidth(text);
    var xCoordinate = pageWidth - textWidth - margin; 

    doc.text(text, xCoordinate, y);
}

function centerTextWithUnderline(text, y, doc) {
    var pageWidth = doc.internal.pageSize.getWidth();
    var textWidth = doc.getTextWidth(text);
    var startX = (pageWidth - textWidth) / 2;
    
    doc.text(text, startX, y);

    doc.line(startX, y + 1, startX + textWidth, y + 1); 
}


async function generatePDF() {
    const { jsPDF } = window.jspdf;
    const { autoTable } = window.jspdf;

    const doc = new jsPDF();
    const margins = { top: 35 };

    var companyId = '{{ session("company_id") }}';
    var companyName = '{{ session("company_name") }}';
    var companyAddress = '{{ session("company_address") }}';

    var workfrom = $("#from_date").val();
    var workto = $("#to_date").val();

    var date = new Date(workto);
    var formattedDate = date.toLocaleString('default', { month: 'long', year: 'numeric' });

    doc.autoTable({
        html: '#emptable',
        theme: 'grid',
        margin: margins,
        headStyles: {
            fillColor: [255, 255, 255],
            textColor: [0, 0, 0],
            fontStyle: 'bold'
        },
        styles: {
            lineWidth: 0,
            lineColor: [0, 0, 0]
        },
        columnStyles: {
            0: { halign: 'left' },
            1: { halign: 'left' },
            2: { halign: 'left' },
            3: { halign: 'left' },
            4: { halign: 'right' } 
        },
        didParseCell: function (data) {
            if (data.column.index === 4) {
                var plainText = $(data.cell.raw).text();
                if (!isNaN(plainText) && plainText !== '') {
                    const formattedValue = parseFloat(plainText).toFixed(2)
                        .toString()
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    data.cell.text = [formattedValue];
                }
            }
        },
        didDrawCell: function (data) {
            if (data.row.index === data.table.body.length - 1) {
                if (data.column.index === 0) {
                    const y = data.cell.y;
                    doc.line(doc.internal.pageSize.getWidth() * 0.05, y - 1, doc.internal.pageSize.getWidth() - doc.internal.pageSize.getWidth() * 0.05, y - 1); 
                }

                if (data.column.index === 4) {
                    data.cell.styles.halign = 'right';
                    data.cell.styles.fillColor = [255, 255, 255];
                }

                if (data.column.index === 0) {
                    data.cell.text = 'Total Bonus:';
                    data.cell.styles.halign = 'right'; 
                }

                if (data.column.index === 0 && data.row.index === data.table.body.length - 1) {
                    const lastRowY = data.cell.y + data.cell.height; 
                    doc.line(doc.internal.pageSize.getWidth() * 0.05, lastRowY + 1, doc.internal.pageSize.getWidth() - doc.internal.pageSize.getWidth() * 0.05, lastRowY + 1); 
                    doc.line(doc.internal.pageSize.getWidth() * 0.05, lastRowY + 4, doc.internal.pageSize.getWidth() - doc.internal.pageSize.getWidth() * 0.05, lastRowY + 4);
                }
            }
        },
        didDrawPage: function (data) {
            doc.setFontSize(12);
            doc.setFont('Helvetica', 'normal');
            centerTextWithUnderline('Bonus Report', 15, doc);

            doc.setFontSize(14);
            doc.setTextColor(40);
            doc.setFont('Helvetica', 'bold');
            centerText(companyName || "Your Company Name", 22, doc);

            doc.setFontSize(9);
            doc.setFont('Helvetica', 'normal');
            rightAlignText('NO : ....................', 22, doc);

            doc.setFontSize(9);
            doc.setFont('Helvetica', 'normal');
            centerText(companyAddress, 27, doc);

            doc.setFontSize(9);
            doc.setFont('Helvetica', 'normal');
            rightAlignText('Date : ' + workto, 27, doc);

            const signyPosition = data.cursor.y + 20;
            doc.setFontSize(11);
            const signpageWidth = doc.internal.pageSize.getWidth();
            const signcolWidth = signpageWidth / 4;
            const signtext1 = '..........................';
            const signtext2 = '..........................';
            const signtext3 = '..........................';
            const signtext4 = '..........................';
            doc.text(signtext1, 14, signyPosition);
            doc.text(signtext2, 14 + signcolWidth, signyPosition);
            doc.text(signtext3, 14 + 2 * signcolWidth, signyPosition);
            doc.text(signtext4, 14 + 3 * signcolWidth, signyPosition);

            doc.setFontSize(11);
            const colWidth = signpageWidth / 4;
            const text1 = 'Asst Accountant';
            const text2 = 'J L Folio';
            const text3 = 'Manager';
            const text4 = 'Director';
            const yPosition = signyPosition + 5;
            doc.text(text1, 14, yPosition);
            doc.text(text2, 14 + colWidth, yPosition);
            doc.text(text3, 14 + 2 * colWidth, yPosition);
            doc.text(text4, 14 + 3 * colWidth, yPosition);
        }
    });

    doc.save('Bonus Report ' + workfrom + ' to ' + workto + '.pdf');
}

</script>

@endsection