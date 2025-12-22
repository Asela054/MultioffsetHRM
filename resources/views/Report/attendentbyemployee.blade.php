<?php $page_stitle = 'Report on Employee Attendance - Multi Offset'; ?>
@extends('layouts.app')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <main>
        <div class="page-header shadow">
            <div class="container-fluid">
                @include('layouts.reports_nav_bar')
               
            </div>
        </div>

        <div class="container-fluid mt-4">
            <div class="card mb-2">
                <div class="card-body">
                    <form class="form-horizontal" id="formFilter">
                        <div class="form-row mb-1">
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Company</label>
                                <select name="company" id="company" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Department</label>
                                <select name="department" id="department" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-dark">Location</label>
                                <select name="location" id="location" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Employee</label>
                                <select name="employee" id="employee" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Date : From - To</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" id="from_date" name="from_date" class="form-control form-control-sm border-right-0" placeholder="yyyy-mm-dd"
                                    value="{{date('Y-m-d') }}"
                                           required
                                    >
                                    <input type="date" id="to_date" name="to_date" class="form-control" placeholder="yyyy-mm-dd"
                                    value="{{date('Y-m-d') }}"
                                           required
                                    >
                                </div>
                            </div>
                            <div class="col-2">
                                <label class="small font-weight-bold text-dark">Special Attendance Allow</label><br>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="specialattendanceyes" name="specialattendance" class="custom-control-input" value="1" required>
                                    <label class="custom-control-label" for="specialattendanceyes">Yes</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="specialattendanceno" name="specialattendance" class="custom-control-input" value="2" required>
                                    <label class="custom-control-label" for="specialattendanceno">No</label>
                                </div>
                            </div>
                            <div class="col">
                                <br>
                                <button type="submit" class="btn btn-primary btn-sm filter-btn" id="btn-filter"> Filter</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0 p-2">
                    <div class="info_msg">
                        <div class="alert alert-info" role="alert">
                            <span><i class="fa fa-info-circle"></i>  Records for {{date('Y-m-d')}} showing by default </span>
                        </div>
                    </div>
                     <div class="response">
                     </div>
                     <div class="response2 d-none">
                    </div>
                    <input type="hidden" name="present_count" id="present_count" value="" />
                    <input type="hidden" name="absent_count" id="absent_count" value="" />
                    {{ csrf_field() }}
                </div>
            </div>
        </div>
    </main>

@endsection

@section('script')
<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
<script src="https://unpkg.com/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.js"></script>
    <script>
        $(document).ready(function () {

            $('#report_menu_link').addClass('active');
            $('#report_menu_link_icon').addClass('active');
            $('#employeereportmaster').addClass('navbtnactive');

            let company = $('#company');
            let department = $('#department');
            let employee = $('#employee');
            let location = $('#location');

            var companyId = '{{ session("company_id") }}';
            var companyName = '{{ session("company_name") }}';
            
            if (companyId && companyName) {
                var option = new Option(companyName, companyId, true, true);
                company.append(option).trigger('change');
            }


            department.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("department_list_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                            company: company.val()
                        }
                    },
                    cache: true
                }
            });

            employee.select2({
                placeholder: 'Select...',
                width: '100%',
                allowClear: true,
                ajax: {
                    url: '{{url("employee_list_from_attendance_sel2")}}',
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });

            var branchId = '{{ session("company_branch_id") }}';
            var branchName = '{{ session("company_branch_name") }}';

            if (branchId && branchName) {
                var option = new Option(branchName, branchId, true, true);
                location.append(option).trigger('change');
            }

        

            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            let specialatten = $('input[name="specialattendance"]:checked').val();

            load_dt('', '', '', from_date, to_date,specialatten);

            function load_dt(department, employee, location, from_date, to_date,specialatten) {

                $('.response').html('');
                $('.response2').html('');

                let element = $('.filter-btn');
                element.attr('disabled', true);
                element.html('<i class="fa fa-spinner fa-spin"></i>');

                //add loading to element button
                $(element).val('<i class="fa fa-spinner fa-spin"></i>');
                //disable
                $(element).prop('disabled', true);

                $.ajax({
                    url: "{{ route('get_attendance_by_employee_data') }}",
                    method: "POST",
                    data: {
                        department: department,
                        employee: employee,
                        location: location,
                        from_date: from_date,
                        to_date: to_date,
                        specialatten: specialatten,
                        _token: '{{csrf_token()}}'
                    },
                    success: function (res) {

                        element.html('Filter');
                        element.prop('disabled', false);

                        $('.response').html(res.html);
                        $('.response2').html(res.html2);
                        $('#present_count').val(res.present_count);
                        $('#absent_count').val(res.absent_count);
                    }
                });

            }

            $('#formFilter').on('submit',function(e) {
                e.preventDefault();
                let department = $('#department').val();
                let employee = $('#employee').val();
                let location = $('#location').val();
                let from_date = $('#from_date').val();
                let to_date = $('#to_date').val();
                let specialatten = $('input[name="specialattendance"]:checked').val();

                $('.info_msg').html('');

                load_dt(department, employee, location, from_date, to_date,specialatten);
            });

            //document .excel-btn click event
            $(document).on('click', '.excel-btn', function(e) {
                e.preventDefault();
                let department = $('#department').val();
                let employee = $('#employee').val();
                let location = $('#location').val();
                let from_date = $('#from_date').val();
                let to_date = $('#to_date').val();

                let data = {
                    department: department,
                    employee: employee,
                    location: location,
                    from_date: from_date,
                    to_date: to_date,
                    _token: '{{csrf_token()}}'
                };

                download_excel(data);

            });

            async function download_excel(data) {

                let save_btn=$(".excel-btn");
                //save_btn.prop("disabled", true);
                save_btn.html('<i class="fa fa-spinner fa-spin"> </i> &nbsp; Generating Excel...' );

                let url = "{{ route('get_attendance_by_employee_data_excel') }}";
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    xhrFields: {
                        responseType: 'blob' // to avoid binary data being mangled on charset conversion
                    },
                    success: function(blob, status, xhr) {

                        save_btn.prop("disabled", false);
                        save_btn.html('<i class="fa fa-file-excel"> </i> &nbsp; Download Excel');

                        // check for a filename
                        var filename = "";
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                            var matches = filenameRegex.exec(disposition);
                            if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                        }

                        if (typeof window.navigator.msSaveBlob !== 'undefined') {
                            // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for which they were created. These URLs will no longer resolve as the data backing the URL has been freed."
                            window.navigator.msSaveBlob(blob, filename);
                        } else {
                            var URL = window.URL || window.webkitURL;
                            var downloadUrl = URL.createObjectURL(blob);

                            if (filename) {
                                // use HTML5 a[download] attribute to specify filename
                                var a = document.createElement("a");
                                // safari doesn't support this yet
                                if (typeof a.download === 'undefined') {
                                    window.location.href = downloadUrl;
                                } else {
                                    a.href = downloadUrl;
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                }
                            } else {
                                window.location.href = downloadUrl;
                            }

                            setTimeout(function () {
                                URL.revokeObjectURL(downloadUrl);
                            }, 100); // cleanup
                        }
                    }
                });
            }

        });


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


// async function generatePDF() {
//     const { jsPDF } = window.jspdf; 
//     const { autoTable } = window.jspdf; 

//     const doc = new jsPDF({
//         format: 'a4',
//         unit: 'mm'
//     });
// 	const margins = { top: 35};

//     var companyId = '{{ session("company_id") }}';
//     var companyName = '{{ session("company_name") }}';

// 	var from_date=$("#from_date").val();
// 	var to_date=$("#to_date").val();
//     var present_count=$("#present_count").val();
//     var absent_count=$("#absent_count").val();

//     var date='';
//     if(from_date == to_date){
//         date=from_date;
//     }else{
//         date=from_date+' - '+to_date;
//     }
//     let endPosY;

// 	doc.autoTable({ 
// 		html: '#dailyattendance_report_table',
// 		theme: 'grid',
// 		margin: margins,
// 		headStyles: {
//         fillColor: [255, 255, 255], 
//         textColor: [0, 0, 0],      
//         fontStyle: 'bold',   
// 		},
// 		styles: {
// 			lineWidth: 0.3,             
// 			lineColor: [0, 0, 0]     
// 		},
//         didDrawCell: function (data) {
//             // Capture the Y position after each cell is drawn
//             endPosY = data.cell.section === 'body' ? data.cell.y + data.cell.height : endPosY;
//         },
// 		didDrawPage: function (data) {
//         doc.setFontSize(12);
//         doc.setFont('Helvetica', 'normal');
//         centerTextWithUnderline('Attendance Report', 15, doc);

//         doc.setFontSize(14);
//         doc.setTextColor(40);
//         doc.setFont('Helvetica', 'bold');
//         centerText(companyName || "Your Company Name", 22, doc);

//         doc.setFontSize(9);
//         doc.setFont('Helvetica', 'normal');
//         centerText("No 345, Negombo Road, Mukalangamuwa Seeduwa", 27, doc);

//         doc.setFontSize(11);
//         doc.setFont('Helvetica', 'normal');
//         doc.text('Date : '+date, 16, 32);

//       }
// 	 })
//      const lastPage = doc.internal.getNumberOfPages();

//         doc.setFontSize(11);
//         doc.setFont('Helvetica', 'normal');
//         doc.text('No Of Present : '+present_count, 14, endPosY + 10); 
//         doc.text('No Of Absent  : '+absent_count, 14, endPosY + 18); 

//         const signPageWidth = doc.internal.pageSize.getWidth();
//         const signColWidth = signPageWidth / 4;
//         const signText1 = '...............................';
//         const signText2 = '...............................';
//         const signText3 = '...............................';
//         const signYPosition = endPosY + 33; 

//         doc.text(signText1, 14, signYPosition);
//         doc.text(signText2, 14 + signColWidth, signYPosition);
//         doc.text(signText3, 14 + 2 * signColWidth, signYPosition);

//         doc.setFontSize(11);
//         doc.setFont('Helvetica', 'normal');
//         const colWidth = signPageWidth / 4;
//         const text1 = 'Supervisor';
//         const text2 = 'Authorized Officer';
//         const text3 = 'Accountant';
//         const titleYPosition = signYPosition + 5; 

//         doc.text(text1, 14, titleYPosition);
//         doc.text(text2, 14 + colWidth, titleYPosition);
//         doc.text(text3, 14 + 2 * colWidth, titleYPosition);

// 	doc.save('Attendance Report '+date+'.pdf')

//   }

async function generatePDF() {
    const { jsPDF } = window.jspdf; 
    const { autoTable } = window.jspdf; 

    const doc = new jsPDF({
        format: 'a4',
        unit: 'mm'
    });
    const margins = { top: 35 };

    var companyId = '{{ session("company_id") }}';
    var companyName = '{{ session("company_name") }}';

    var from_date = $("#from_date").val();
    var to_date = $("#to_date").val();
    var present_count = $("#present_count").val();
    var absent_count = $("#absent_count").val();

    var date = '';
    if (from_date == to_date) {
        date = from_date;
    } else {
        date = from_date + ' - ' + to_date;
    }

    // --- NEW: DATA EXTRACTION & SORTING ---
    // Extract rows from the HTML table
    const tableCustom = document.getElementById('dailyattendance_report_table');
    const rows = Array.from(tableCustom.querySelectorAll('tbody tr'));

    const tableData = rows.map(row => {
        return Array.from(row.cells).map(cell => cell.innerText.trim());
    });

    // Sort by the first column (Index 0) ASC
    tableData.sort((a, b) => {
        // Use localeCompare for strings, or simple subtraction for numbers
        return a[0].localeCompare(b[0], undefined, {numeric: true, sensitivity: 'base'});
    });
    
    let endPosY;

    doc.autoTable({ 
        // html: '#dailyattendance_report_table',
        head: [Array.from(tableCustom.querySelectorAll('thead th')).map(th => th.innerText)],
        body: tableData,
        theme: 'grid',
        margin: margins,
        headStyles: {
            fillColor: [255, 255, 255], 
            textColor: [0, 0, 0],      // Dark text for headers
            fontStyle: 'bold',
            lineWidth: 0.1,            // Light border for headers
            lineColor: [200, 200, 200] // Light gray border
        },
        bodyStyles: {
            textColor: [0, 0, 0],      // Dark text for body
            lineWidth: 0.1,            // Light border for cells
            lineColor: [200, 200, 200]  // Light gray border
        },
        styles: {
            lineWidth: 0.1,             // Light border width
            lineColor: [200, 200, 200], // Light gray border color
            textColor: [0, 0, 0],       // Dark text color
            fontSize: 10                // Base font size
        },
        didDrawCell: function (data) {
            endPosY = data.cell.section === 'body' ? data.cell.y + data.cell.height : endPosY;
        },
        didDrawPage: function (data) {
            doc.setFontSize(12);
            doc.setFont('Helvetica', 'normal');
            centerTextWithUnderline('Attendance Report', 15, doc);

            doc.setFontSize(14);
            doc.setTextColor(40);
            doc.setFont('Helvetica', 'bold');
            centerText(companyName || "Your Company Name", 22, doc);

            doc.setFontSize(9);
            doc.setFont('Helvetica', 'normal');
            centerText("No 345, Negombo Road, Mukalangamuwa Seeduwa", 27, doc);

            doc.setFontSize(11);
            doc.setFont('Helvetica', 'normal');
            doc.text('Date : ' + date, 16, 32);
        }
    });

    const lastPage = doc.internal.getNumberOfPages();

    doc.setFontSize(11);
    doc.setFont('Helvetica', 'normal');
    doc.text('No Of Present : ' + present_count, 14, endPosY + 10); 
    doc.text('No Of Absent  : ' + absent_count, 14, endPosY + 18); 

    const signPageWidth = doc.internal.pageSize.getWidth();
    const signColWidth = signPageWidth / 4;
    const signText1 = '...............................';
    const signText2 = '...............................';
    const signText3 = '...............................';
    const signYPosition = endPosY + 33; 

    doc.text(signText1, 14, signYPosition);
    doc.text(signText2, 14 + signColWidth, signYPosition);
    doc.text(signText3, 14 + 2 * signColWidth, signYPosition);

    doc.setFontSize(11);
    doc.setFont('Helvetica', 'normal');
    const colWidth = signPageWidth / 4;
    const text1 = 'Supervisor';
    const text2 = 'Authorized Officer';
    const text3 = 'Accountant';
    const titleYPosition = signYPosition + 5; 

    doc.text(text1, 14, titleYPosition);
    doc.text(text2, 14 + colWidth, titleYPosition);
    doc.text(text3, 14 + 2 * colWidth, titleYPosition);

    doc.save('Attendance Report ' + date + '.pdf');
}

// Helper functions (make sure these are defined)
function centerText(text, y, doc) {
    const pageWidth = doc.internal.pageSize.getWidth();
    const textWidth = doc.getStringUnitWidth(text) * doc.internal.getFontSize() / doc.internal.scaleFactor;
    const x = (pageWidth - textWidth) / 2;
    doc.text(text, x, y);
}

function centerTextWithUnderline(text, y, doc) {
    centerText(text, y, doc);
    const pageWidth = doc.internal.pageSize.getWidth();
    const textWidth = doc.getStringUnitWidth(text) * doc.internal.getFontSize() / doc.internal.scaleFactor;
    const x = (pageWidth - textWidth) / 2;
    doc.setDrawColor(0);
    doc.setLineWidth(0.5);
    doc.line(x, y + 1, x + textWidth, y + 1);
}
    </script>

@endsection

