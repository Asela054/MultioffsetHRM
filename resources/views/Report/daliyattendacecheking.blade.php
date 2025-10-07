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
                                <label class="small font-weight-bold text-dark">Location</label>
                                <select name="location" id="location" class="form-control form-control-sm" style="pointer-events: none;" readonly>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Department</label>
                                <select name="department" id="department" class="form-control form-control-sm">
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-dark">Date </label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="date" id="from_date" name="from_date" class="form-control form-control-sm border-right-0" placeholder="yyyy-mm-dd" value="{{date('Y-m-d') }}" required>
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
                    <div class="row">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-sm btn-outline-danger pdf-btn"
                                onclick="generatePDF();"> Download PDF
                            </button><br><br>
                        </div>
                        <div class="col-12">
                            <div class="center-block fix-width scroll-inner">
                                <table class="table table-striped table-bordered table-sm small nowrap" style="width: 100%" id="dailyattendance_report_table">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            <th>Attendace</th>
                                            <th>H/D</th>
                                            <th>S/L</th>
                                            <th>INF</th>
                                            <th>Remark</th>
                                            <th>Late Arrival</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" style="text-align:right"><strong>Absent  :  </strong></th>
                                            <th></th>
                                            <th colspan="3" style="text-align:right"><strong>Letnes  :  </strong></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                        </div>
                    </div>
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

            var branchId = '{{ session("company_branch_id") }}';
            var branchName = '{{ session("company_branch_name") }}';

            if (branchId && branchName) {
                var option = new Option(branchName, branchId, true, true);
                location.append(option).trigger('change');
            }


            function load_dt(department, location, from_date) {
                let element = $('.filter-btn');
                element.attr('disabled', true);
                element.html('<i class="fa fa-spinner fa-spin"></i> Loading...');

                // Initialize or destroy existing DataTable
                if ($.fn.DataTable.isDataTable('#dailyattendance_report_table')) {
                    $('#dailyattendance_report_table').DataTable().destroy();
                }

                $('#dailyattendance_report_table tbody').empty();

                $.ajax({
                    url: "{{ route('dailyattendacechekingreport') }}",
                    method: "POST",
                    data: {
                        department: department,
                        location: location,
                        from_date: from_date,
                        _token: '{{csrf_token()}}'
                    },
                    success: function(response) {
                        element.html('Filter');
                        element.prop('disabled', false);

                        // Initialize DataTable
                        $('#dailyattendance_report_table').DataTable({
                            data: response.data,
                            columns: [
                                { data: 'emp_id', title: 'No' },
                                { data: 'emp_name', title: 'Name' },
                                { data: 'status', title: 'Attendance' },
                                { data: 'half_day', title: 'H/D', defaultContent: '' },
                                { data: 'short_leave', title: 'S/L', defaultContent: '' },
                                { data: 'informal', title: 'INF', defaultContent: '' },
                                { data: 'remark', title: 'Remark', defaultContent: '' },
                                { data: 'late_arrival', title: 'Late Arrival', defaultContent: '' },
                                { data: 'duration', title: 'Duration', defaultContent: '' }
                            ],
                            responsive: true,
                            "lengthMenu": [
                                [10, 25, 50],
                                [10, 25, 50]
                            ],
                            footerCallback: function() {
                                var api = this.api();
                                $(api.column(4).footer()).html(
                                    '<strong> '+response.total_absent+'</strong>'
                                );
                                $(api.column(8).footer()).html(
                                    '<strong>'+response.total_late+'</strong>'
                                );
                            }
                        });
                    }
                });
            }


            $('#formFilter').on('submit',function(e) {
                e.preventDefault();
                let department = $('#department').val();
                let location = $('#location').val();
                let from_date = $('#from_date').val();
                $('.info_msg').html('');
                load_dt(department, location, from_date);
            });

        });


        async function generatePDF() {
            const { jsPDF } = window.jspdf;
            const { autoTable } = window.jspdf;

            const doc = new jsPDF({
                format: 'a4',
                unit: 'mm'
            });
            const margins = {
                top: 35
            };

            var companyName = '{{ session("company_name") }}';
            var reportDate = $("#from_date").val();
            
            // Get current datetime for printed date
            const now = new Date();
            const printedDate = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

            // Draw the table
            doc.autoTable({
                html: '#dailyattendance_report_table',
                theme: 'grid',
                margin: margins,
                styles: {
                    fontSize: 8,
                    cellPadding: 2,
                    lineWidth: 0.2,
                    lineColor: [0, 0, 0],
                    fillColor: [255, 255, 255],
                    textColor: [0, 0, 0]
                },
                headStyles: {
                    fillColor: [255, 255, 255],
                    textColor: [0, 0, 0],
                    fontStyle: 'bold',
                },
                didDrawPage: function (data) {
                    // Header content
                    doc.setFontSize(12);
                    doc.setFont('Helvetica', 'normal');
                    centerTextWithUnderline('DAILY ATTENDANCE CHECKING REPORT', 15, doc);

                    doc.setFontSize(14);
                    doc.setTextColor(40);
                    doc.setFont('Helvetica', 'bold');
                    centerText(companyName || "Your Company Name", 22, doc);

                    // Dates in same row
                    doc.setFontSize(10);
                    doc.setFont('Helvetica', 'normal');
                    doc.text('Report Date: ' + reportDate, 16, 32);
                    doc.text('Printed Date: ' + printedDate, 100, 32);
                }
            });

            // Get final Y position after table
                const endPosY = doc.lastAutoTable.finalY || margins.top + 20;

                // Add signature section exactly as per the image
                const sectionY = endPosY + 10;

                // Column X positions
                const col1X = 14;   // Short Leave
                const col2X = 75;   // Half Day
                const sigXLabel = 140; // Signatures: Label column
                const sigXLine = sigXLabel + 35; // Signatures: Dotted line column

                // Section Titles
                doc.setFontSize(11);
                doc.setFont('Helvetica', 'bold');
                doc.text('Short Leave', col1X, sectionY);
                doc.text('Half Day', col2X, sectionY);
                doc.text('', sigXLabel, sectionY);

                // Leave entries
                doc.setFont('Helvetica', 'normal');
                for (let i = 0; i < 4; i++) {
                    const offsetY = sectionY + 5 + (i * 5);
                    doc.text(`${i + 1}. ........................`, col1X + 2, offsetY); // Short Leave
                    doc.text(`${i + 1}. ........................`, col2X + 2, offsetY); // Half Day
                }

                // Signature labels vertically
                const signatureLabels = ['Supervisor', 'Authorised Officer', 'Accountant'];
                signatureLabels.forEach((label, index) => {
                    const y = sectionY + 5 + (index * 8);
                    doc.text(label, sigXLabel, y);
                    doc.text('.......................', sigXLine, y); // Dotted line
                });


            doc.save('Daily Attendance Checking Report ' + reportDate + '.pdf');
        }

    // Helper functions remain the same
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
        doc.line(x, y + 2, x + textWidth, y + 2);
    }
    </script>

@endsection

