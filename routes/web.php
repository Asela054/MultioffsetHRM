<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('auth/login');
// });

Auth::routes();

/* User Role Permission*/

Route::get('/home', 'HomeController@index')->name('home');
Route::resource('roles','RoleController');
Route::resource('permissions','PermissionController');
Route::resource('users','UserController');
Route::resource('permission','PermissionController');
Route::resource('rolepermission','RolePermissionController');
Route::resource('userrole','RoleUserController');
Route::resource('userpermission','UserPermissionController');

Route::get('/home', 'HomeController@index')->name('home');

/* Home*/
Route::get('/getdashboard_department_attendance', 'HomeController@department_attendance')->name('getdashboard_department_attendance');
Route::get('/getdashboard_department_absent', 'HomeController@department_absent')->name('getdashboard_department_absent');

Route::get('/getdashboard_AttendentChart', 'HomeController@getAttendentChart')->name('getdashboard_AttendentChart');


Route::resource('FingerprintDevice', 'FingerprintDeviceController');
//Route::post('addFingerprintDevice',['uses' => 'FingerprintDeviceController@store', 'as' => 'addFingerprintDevice']); 

Route::get('EmloyeeList',['uses' => 'EmployeeController@employeelist', 'as' => 'EmloyeeList']); 
Route::get('addEmployee',['uses' => 'EmployeeController@index', 'as' => 'addEmployee']);
Route::get('employee_list_dt',['uses' => 'EmployeeController@employee_list_dt', 'as' => 'employee_list_dt']);
Route::post('empoyeeUpdate',['uses' => 'EmployeeController@edit', 'as' => 'empoyeeUpdate']); 
Route::post('empoyeeRegister',['uses' => 'EmployeeController@store', 'as' => 'empoyeeRegister']); 
Route::post('addUserLogin',['uses' => 'EmployeeController@usercreate', 'as' => 'addUserLogin']); 
Route::get('EmployeeDestroy/destroy/{id}', 'EmployeeController@destroy');
Route::get('exportEmpoloyee', 'EmployeeController@exportempoloyee')->name('exportEmpoloyee');
Route::get('/viewEmployee/{id}',['uses' => 'EmployeeController@show', 'as' => 'viewEmployee']);
Route::get('/contactDetails/{id}',['uses' => 'EmployeeController@showcontact', 'as' => 'contactDetails']);
Route::post('contactUpdate',['uses' => 'EmployeeController@editcontact', 'as' => 'contactUpdate']);
Route::get('/viewEmergencyContacts/{id}',['uses' => 'EmployeeController@showcontact', 'as' => 'viewEmergencyContacts']);
Route::get('Employee/active/{id}', 'EmployeeController@status');

Route::post('empoyeeAttachment',['uses' => 'EmployeeAttachmentController@create', 'as' => 'empoyeeAttachment']);
Route::post('contactAttachment',['uses' => 'EmployeeAttachmentController@createcontact', 'as' => 'contactAttachment']);
Route::post('qulificationAttachment',['uses' => 'EmployeeAttachmentController@createqulification', 'as' => 'qulificationAttachment']);
Route::post('dependetAttachment',['uses' => 'EmployeeAttachmentController@createdependent', 'as' => 'dependetAttachment']);
Route::post('immigrationAttachment',['uses' => 'EmployeeImmigrationController@immigrationattacment', 'as' => 'immigrationAttachment']);


Route::resource('EmployeeSelect', 'SelectEmployeeController');
Route::get('selectEmployee',['uses' => 'SelectEmployeeController@create', 'as' => 'selectEmployee']);
Route::post('/get_select_employee_details', 'SelectEmployeeController@get_select_employee_details')->name('get_select_employee_details');
Route::post('/select_employee_post', 'SelectEmployeeController@select_employee_post')->name('select_employee_post');
Route::get('selectEmployeeIndex',['uses' => 'SelectEmployeeController@index', 'as' => 'selectEmployeeIndex']);
Route::get('select_employee_list_dt',['uses' => 'SelectEmployeeController@select_employee_list_dt', 'as' => 'select_employee_list_dt']);
Route::get('EmployeeSelect/destroy/{id}', 'SelectEmployeeController@destroy');


Route::get('/viewEmergencyContacts', function () {
    return view('Employee.viewEmergencyContacts');
});

Route::get('getDependentDetail/{id}',['uses' => 'EmployeeDependentController@edit_json', 'as' => 'getDependentDetail']);
Route::post('dependentUpdate',['uses' => 'EmployeeDependentController@update', 'as' => 'dependentUpdate']);
Route::get('dependent_delete/{id}',['uses' => 'EmployeeDependentController@destroy', 'as' => 'dependent_delete']);
Route::get('/viewDependents/{id}',['uses' => 'EmployeeDependentController@show', 'as' => 'viewDependents']);
Route::post('dependentInsert',['uses' => 'EmployeeDependentController@create', 'as' => 'dependentInsert']);
Route::get('dependentUpdate/{id}',['uses' => 'EmployeeDependentController@edit', 'as' => 'dependentUpdate']);

Route::get('/viewEmergencyContacts/{id}',['uses' => 'EmployeeEmergencyContacts@show', 'as' => 'viewEmergencyContacts']);
Route::post('emergencyContactInsert',['uses' => 'EmployeeEmergencyContacts@create', 'as' => 'emergencyContactInsert']);
Route::get('getEmergencyContactDetail/{id}',['uses' => 'EmployeeEmergencyContacts@edit_json', 'as' => 'getEmergencyContactDetail']);
Route::post('emergencyContactUpdate',['uses' => 'EmployeeEmergencyContacts@update', 'as' => 'emergencyContactUpdate']);
Route::get('emergency_contact_delete/{id}',['uses' => 'EmployeeEmergencyContacts@destroy', 'as' => 'emergency_contact_delete']);



Route::get('/viewImmigration/{id}',['uses' => 'EmployeeImmigrationController@show', 'as' => 'viewImmigration']);
Route::post('immigrationInsert',['uses' => 'EmployeeImmigrationController@create', 'as' => 'immigrationInsert']);
Route::get('getImmigrationDetail/{id}',['uses' => 'EmployeeImmigrationController@edit_json', 'as' => 'getImmigrationDetail']);
Route::post('immigrationUpdate',['uses' => 'EmployeeImmigrationController@update', 'as' => 'immigrationUpdate']);
Route::get('immigration_delete/{id}',['uses' => 'EmployeeImmigrationController@destroy', 'as' => 'immigration_delete']);
Route::get('/viewJobDetails/{id}',['uses' => 'EmployeeImmigrationController@show', 'as' => 'viewJobDetails']);


Route::get('/viewJobDetails', function () {
    return view('Employee.viewJobDetails');
});


Route::get('/viewSalaryDetails/{id}',['uses' => 'EmployeeSalaryController@show', 'as' => 'viewSalaryDetails']);


Route::get('/viewQualifications/{id}',['uses' => 'EmployeeEducationController@show', 'as' => 'viewQualifications']);
Route::get('/viewbankDetails/{id}',['uses' => 'EmployeeBankController@show', 'as' => 'viewbankDetails']);
Route::post('BankInsert',['uses' => 'EmployeeBankController@store', 'as' => 'BankInsert']);
Route::get('empBank/destroy/{id}', 'EmployeeBankController@destroy');
Route::get('empBankReport',['uses' => 'EmployeeBankController@empBankReport', 'as' => 'empBankReport']);
Route::get('bank_report_list',['uses' => 'EmployeeBankController@bank_report_list', 'as' => 'bank_report_list']);



Route::get('/viewPassport/{id}',['uses' => 'EmployeePassportController@show', 'as' => 'viewPassport']);
Route::post('passportInsert',['uses' => 'EmployeePassportController@store', 'as' => 'passportInsert']); 
Route::post('passportAttachment',['uses' => 'EmployeePassportController@passportattacment', 'as' => 'passportAttachment']);
Route::get('passportEdit/{emp_pass_id}',['uses' => 'EmployeePassportController@Edit', 'as' => 'passportEdit']);
Route::get('passportDestroy/{emp_pass_id}',['uses' => 'EmployeePassportController@Destroy', 'as' => 'passportDestroy']);
Route::post('passportUpdate',['uses' => 'EmployeePassportController@Update', 'as' => 'passportUpdate']);


Route::get('/viewEmployeeFiles/{id}',['uses' => 'EmployeeFilesController@show', 'as' => 'viewEmployeeFiles']);
Route::post('employeeAttachmentJson',['uses' => 'EmployeeAttachmentController@employeeAttachmentJson', 'as' => 'employeeAttachmentJson']);
Route::get('/download_file/{file}',['uses' => 'EmployeeAttachmentController@download_file', 'as' => 'download_file']);
Route::get('attachment/destroy/{id}', 'EmployeeAttachmentController@destroy_attachment');

/*-- Jobs Title----*/
Route::resource('WorkExprience', 'EmployeeExperienceController');
Route::post('WorkExprienceInsert',['uses' => 'EmployeeExperienceController@create', 'as' => 'WorkExprienceInsert']); 
Route::post('WorkExprience/update', 'EmployeeExperienceController@update')->name('WorkExprience.update');
Route::get('WorkExprience/destroy/{id}', 'EmployeeExperienceController@destroy');
/*-- End Jobs Title----*/

/*-- EmployeeSkill----*/
Route::resource('EmployeeSkill', 'EmployeeSkillController');
Route::post('skillInsert',['uses' => 'EmployeeSkillController@create', 'as' => 'skillInsert']);
Route::post('EmployeeSkill/update', 'EmployeeSkillController@update')->name('EmployeeSkill.update');
Route::get('EmployeeSkill/destroy/{id}', 'EmployeeSkillController@destroy');
/*-- End EmployeeSkill----*/

/*-- Education----*/
Route::resource('EmployeeEducation', 'EmployeeEducationController');
Route::post('educationInsert',['uses' => 'EmployeeEducationController@create', 'as' => 'educationInsert']); 
Route::post('EmployeeEducation/update', 'EmployeeEducationController@update')->name('EmployeeEducation.update');
Route::get('EmployeeEducation/destroy/{id}', 'EmployeeEducationController@destroy');
/*-- End Education----*/



/*-- Jobs Title----*/
Route::resource('JobTitle', 'JobTitleController');
Route::get('JobTitle',['uses' => 'JobTitleController@index', 'as' => 'JobTitle']); 
Route::post('addJobTitle',['uses' => 'JobTitleController@store', 'as' => 'addJobTitle']); 
Route::post('JobTitle/update', 'JobTitleController@update')->name('JobTitle.update');
Route::get('JobTitle/destroy/{id}', 'JobTitleController@destroy');
/*-- End Jobs Title----*/

/*-- Pay Grade Title----*/
Route::resource('PayGrade', 'PayGradeController');
Route::get('PayGrade',['uses' => 'PayGradeController@index', 'as' => 'PayGrade']); 
Route::post('addPayGrade',['uses' => 'PayGradeController@store', 'as' => 'addPayGrade']); 
Route::post('PayGrade/update', 'PayGradeController@update')->name('PayGrade.update');
Route::get('PayGrade/destroy/{id}', 'PayGradeController@destroy');
/*-- Pay Grade Title----*/

/*-- Employment Status----*/
Route::resource('EmploymentStatus', 'EmploymentStatusController');
Route::get('EmploymentStatus',['uses' => 'EmploymentStatusController@index', 'as' => 'EmploymentStatus']); 
Route::post('addEmploymentStatus',['uses' => 'EmploymentStatusController@store', 'as' => 'addEmploymentStatus']); 
Route::post('EmploymentStatus/update', 'EmploymentStatusController@update')->name('EmploymentStatus.update');
Route::get('EmploymentStatus/destroy/{id}', 'EmploymentStatusController@destroy');

/*-- Employment Status----*/

/*--  Job Category----*/
Route::resource('JobCategory', 'JobCategoryController');
Route::get('JobCategory',['uses' => 'JobCategoryController@index', 'as' => 'JobCategory']); 
Route::post('addJobCategory',['uses' => 'JobCategoryController@store', 'as' => 'addJobCategory']); 
Route::post('JobCategory/update', 'JobCategoryController@update')->name('JobCategory.update');
Route::get('JobCategory/destroy/{id}', 'JobCategoryController@destroy');
/*-- Job Category----*/


/*-- End Jobs----*/


/*-- Start Qulification----*/
/*-- Skills----*/
Route::resource('Skill', 'SkillController');
Route::get('Skill',['uses' => 'SkillController@index', 'as' => 'Skill']); 
Route::post('addSkill',['uses' => 'SkillController@store', 'as' => 'addSkill']); 
Route::post('Skill/update', 'SkillController@update')->name('Skill.update');
Route::get('Skill/destroy/{id}', 'SkillController@destroy');
/*-- Skills----*/

/*-- Education----*/
Route::resource('Education', 'EducationController');
Route::get('Education',['uses' => 'EducationController@index', 'as' => 'Education']); 
Route::post('addEducation',['uses' => 'EducationController@store', 'as' => 'addEducation']); 
Route::post('Education/update', 'EducationController@update')->name('Education.update');
//Route::post('updateEducation', 'EducationController@update')->name('updateEducation');
Route::get('Education/destroy/{id}', 'EducationController@destroy');
/*-- Education----*/
/*-- End Qulification----*/



/*-- Jobs Title----*/
Route::resource('Branch', 'BranchController');
Route::get('Branch',['uses' => 'BranchController@index', 'as' => 'Branch']); 
Route::post('addBranch',['uses' => 'BranchController@store', 'as' => 'addBranch']); 
Route::post('Branch/update', 'BranchController@update')->name('Branch.update');
Route::get('Branch/destroy/{id}', 'BranchController@destroy');
Route::get('BranchShow/{id}',['uses' => 'BranchController@index', 'as' => 'BranchShow']);
/*-- End Jobs Title----*/


Route::resource('Attendance', 'AttendanceController');
Route::get('Attendance',['uses' => 'AttendanceController@index', 'as' => 'Attendance']);
Route::get('late_attendance_by_time',['uses' => 'AttendanceController@late_attendance_by_time', 'as' => 'late_attendance_by_time']);
Route::get('late_attendance_by_time_approve',['uses' => 'AttendanceController@late_attendance_by_time_approve', 'as' => 'late_attendance_by_time_approve']);
Route::get('late_attendances_all',['uses' => 'AttendanceController@late_attendances_all', 'as' => 'late_attendances_all']);
Route::get('late_attendance_list_approved',['uses' => 'AttendanceController@late_attendance_list_approved', 'as' => 'late_attendance_list_approved']);

Route::get('late_attendance/destroy/{id}', 'AttendanceController@destroy_late_attendacne');

//Attendance.delete post
Route::post('Attendance.delete', 'AttendanceController@delete')->name('Attendance.delete');


//incomplete_attendances
Route::get('incomplete_attendances',['uses' => 'AttendanceController@incomplete_attendances', 'as' => 'incomplete_attendances']);

Route::get('attendance_by_time_report_list',['uses' => 'AttendanceController@attendance_by_time_report_list', 'as' => 'attendance_by_time_report_list']);
Route::post('lateAttendance_mark_as_late',['uses' => 'AttendanceController@lateAttendance_mark_as_late', 'as' => 'lateAttendance_mark_as_late']);

Route::get('attendance_by_time_approve_report_list',['uses' => 'AttendanceController@attendance_by_time_approve_report_list', 'as' => 'attendance_by_time_approve_report_list']);
Route::post('lateAttendance_mark_as_late_approve',['uses' => 'AttendanceController@lateAttendance_mark_as_late_approve', 'as' => 'lateAttendance_mark_as_late_approve']);

Route::get('late_types_sel2',['uses' => 'AttendanceController@late_types_sel2', 'as' => 'late_types_sel2']);
Route::get('AttendanceEdit',['uses' => 'AttendanceController@attendanceedit', 'as' => 'AttendanceEdit']);
Route::get('attendance_list_for_edit',['uses' => 'AttendanceController@attendance_list_for_edit', 'as' => 'attendance_list_for_edit']);

Route::post('attendance_add_bulk_submit',['uses' => 'AttendanceController@attendance_add_bulk_submit', 'as' => 'attendance_add_bulk_submit']);
Route::post('attendance_add_dept_wise_submit',['uses' => 'AttendanceController@attendance_add_dept_wise_submit', 'as' => 'attendance_add_dept_wise_submit']);
//post get_attendance_monthly_summery_by_emp_id
Route::post('get_attendance_monthly_summery_by_emp_id',['uses' => 'AttendanceController@get_attendance_monthly_summery_by_emp_id', 'as' => 'get_attendance_monthly_summery_by_emp_id']);


Route::get('AttendanceEditBulk',['uses' => 'AttendanceController@AttendanceEditBulk', 'as' => 'AttendanceEditBulk']);
Route::get('AttendanceApprovel',['uses' => 'AttendanceController@attendanceapprovel', 'as' => 'AttendanceApprovel']);
Route::get('attendance_list_for_approve',['uses' => 'AttendanceController@attendance_list_for_approve', 'as' => 'attendance_list_for_approve']);
Route::post('AttendentAprovelBatch', 'AttendanceController@AttendentAprovelBatch')->name('AttendentAprovelBatch');

Route::get('attendance_list_for_bulk_edit',['uses' => 'AttendanceController@attendance_list_for_bulk_edit', 'as' => 'attendance_list_for_bulk_edit']);
Route::post('AttendanceEditBulkSubmit',['uses' => 'AttendanceController@AttendanceEditBulkSubmit', 'as' => 'AttendanceEditBulkSubmit']);
Route::post('attendance_list_for_month_edit',['uses' => 'AttendanceController@attendance_list_for_month_edit', 'as' => 'attendance_list_for_month_edit']);
Route::post('attendance_update_bulk_submit',['uses' => 'AttendanceController@attendance_update_bulk_submit', 'as' => 'attendance_update_bulk_submit']);


Route::get('/AttendentUpdate', 'AttendanceController@getAttendance');
Route::get('/AttendentView', 'AttendanceController@getAttendance');
Route::get('/getAttendanceApprovel', 'AttendanceController@getAttendanceApprovel');
Route::post('AttendentAprovel', 'AttendanceController@AttendentAprovel')->name('AttendentAprovel');

Route::post('/AttendentUpdateLive', 'AttendanceController@attendentUpdateLive');
Route::post('/AttendentInsertLive', 'AttendanceController@attendentinsertlive');
Route::post('/AttendentDeleteLive', 'AttendanceController@attendentdeletelive');
Route::get('/getAttendentChart', 'AttendanceController@getAttendentChart');
Route::get('/getBranchAttendentChart', 'AttendanceController@getBranchAttendentChart');
Route::get('/Attendentdetails/{id}/{date}',['uses' => 'AttendanceController@attendentdetails', 'as' => 'Attendentdetails']);

//get_incomplete_attendance_by_employee_data
Route::post('get_incomplete_attendance_by_employee_data', 'AttendanceController@get_incomplete_attendance_by_employee_data')->name('get_incomplete_attendance_by_employee_data');

//mark_as_no_pay
Route::post('mark_as_no_pay', 'AttendanceController@mark_as_no_pay')->name('mark_as_no_pay');



Route::post('Attendance/update', 'AttendanceController@update')->name('Attendance.update');
Route::get('Attendance/destroy/{id}', 'AttendanceController@destroy');
//Route::post('Attendance/getdevicedata', 'AttendanceController@getdevicedata');
Route::post('/Attendance/getdevicedata', 'AttendanceController@getdevicedata')->name('Attendance.getdevicedata');

//AttendanceDeviceClear
Route::get('AttendanceDeviceClear', 'AttendanceClearController@attendance_clear_list')->name('AttendanceDeviceClear');

//Attendance.cleardevicedata
Route::post('/Attendance/cleardevicedata', 'AttendanceController@cleardevicedata')->name('Attendance.cleardevicedata');

//attendance_clear_list
Route::get('attendance_clear_list_dt',['uses' => 'AttendanceClearController@attendance_clear_list_dt', 'as' => 'attendance_clear_list_dt']);

Route::get('exportAttendance', 'AttendanceController@exportattendances')->name('exportAttendance');


/*-- Attendent Type----*/
Route::resource('AttendanceType', 'AttendanceTypeController');
Route::get('AttendanceType',['uses' => 'AttendanceTypeController@index', 'as' => 'AttendanceType']); 
Route::post('addAttendanceType',['uses' => 'AttendanceTypeController@store', 'as' => 'addAttendanceType']); 
Route::post('AttendanceType/update', 'AttendanceTypeController@update')->name('AttendanceType.update');
Route::get('job/destroy/{id}', 'AttendanceTypeController@destroy');
/*-- End Attendent Type----*/


Route::resource('FingerprintUser', 'FingerprintUserController');
Route::post('addFingerprintUser',['uses' => 'FingerprintUserController@store', 'as' => 'addFingerprintUser']);
Route::get('FingerprintUser',['uses' => 'FingerprintUserController@index', 'as' => 'FingerprintUser']); 
Route::post('FingerprintUser/update', 'FingerprintUserController@update')->name('FingerprintUser.update');
Route::get('FingerprintUser/destroy/{id}', 'FingerprintUserController@destroy');
Route::get('exportFPUser', 'FingerprintUserController@exportfpuser')->name('exportFPUser');
Route::post('FingerprintUser/getdeviceuserdata', 'FingerprintUserController@getdeviceuserdata');


Route::resource('FingerprintDevice', 'FingerprintDeviceController');
Route::post('addFingerprintDevice',['uses' => 'FingerprintDeviceController@store', 'as' => 'addFingerprintDevice']); 
Route::get('FingerprintDevice',['uses' => 'FingerprintDeviceController@index', 'as' => 'FingerprintDevice']); 
Route::post('FingerprintDevice/update', 'FingerprintDeviceController@update')->name('FingerprintDevice.update');
Route::get('FingerprintDevice/destroy/{id}', 'FingerprintDeviceController@destroy');


Route::resource('LeaveType', 'LeaveTypeController');
Route::post('addLeaveType',['uses' => 'LeaveTypeController@store', 'as' => 'addLeaveType']); 
Route::get('LeaveType',['uses' => 'LeaveTypeController@index', 'as' => 'LeaveType']); 
Route::post('LeaveType/update', 'LeaveTypeController@update')->name('LeaveType.update');
Route::get('LeaveType/destroy/{id}', 'LeaveTypeController@destroy');
Route::get('LeaveBalance',['uses' => 'LeaveTypeController@LeaveBalance', 'as' => 'LeaveBalance']);
Route::get('leave_balance_list',['uses' => 'LeaveTypeController@leave_balance_list', 'as' => 'leave_balance_list']);


Route::get('run_csv',['uses' => 'DepartmentController@run_csv', 'as' => 'run_csv']);
Route::post('run_csv_post', 'DepartmentController@run_csv_post')->name('run_csv_post');


Route::resource('LeaveApply', 'LeaveController');
Route::post('addLeaveApply',['uses' => 'LeaveController@store', 'as' => 'addLeaveApply']); 
Route::get('LeaveApply',['uses' => 'LeaveController@index', 'as' => 'LeaveApply']);
Route::get('leave_list_dt',['uses' => 'LeaveController@leave_list_dt', 'as' => 'leave_list_dt']);
Route::post('LeaveApply/update', 'LeaveController@update')->name('LeaveApply.update');
Route::get('LeaveApply/destroy/{id}', 'LeaveController@destroy');
Route::post('/getEmployeeLeaveStatus', 'LeaveController@getemployeeleavestatus');

Route::post('/getEmployeeCategory', 'LeaveController@getEmployeeCategory');

Route::get('LeaveApprovel',['uses' => 'LeaveController@approvelindex', 'as' => 'LeaveApprovel']); 
Route::get('leave_approve_list_dt',['uses' => 'LeaveController@leave_approve_list_dt', 'as' => 'leave_approve_list_dt']);
Route::post('approvelupdate', 'LeaveController@approvelupdate')->name('approvelupdate');

Route::get('Leaveleavecreate', 'LeaveController@leaveleavecreate')->name('leaveleavecreate'); 


Route::get('EmpoloyeeReport',['uses' => 'Report@getemployeelist', 'as' => 'EmpoloyeeReport']);
Route::get('employee_report_list',['uses' => 'Report@employee_report_list', 'as' => 'employee_report_list']);

Route::get('AttendenceReport', 'Report@empoloyeeattendentall')->name('AttendenceReport');
Route::get('exportAttendanceReport', 'Report@exportattendances')->name('exportAttendanceReport');  
Route::get('exportEmployeeReport', 'Report@exportempoloyeereport')->name('exportEmployeeReport');  
Route::post('attendentfilter', 'Report@attendentfilter')->name('attendentfilter');  
Route::get('attendentreportbydate', function () {
    return view('Report.attendentreportbydate');
});
Route::get('attendetreportbyemployee', 'Report@attendentbyemployee')->name('attendetreportbyemployee'); 
Route::get('attendance_report_list', 'Report@attendance_report_list')->name('attendance_report_list');

//post get_attendance_by_employee_data
Route::post('get_attendance_by_employee_data', 'Report@get_attendance_by_employee_data')->name('get_attendance_by_employee_data');
//get_attendance_by_employee_data_excel
Route::post('get_attendance_by_employee_data_excel', 'Report@get_attendance_by_employee_data_excel')->name('get_attendance_by_employee_data_excel');



Route::post('/employee/fetch_data', 'Report@employee_fetch_data')->name('employee.fetch_data');
Route::get('employee_list_from_attendance_sel2', 'Report@employee_list_from_attendance_sel2')->name('employee_list_from_attendance_sel2');
Route::get('location_list_from_attendance_sel2', 'Report@location_list_from_attendance_sel2')->name('location_list_from_attendance_sel2');
Route::post('/employee/fetch_leave_data', 'Report@fetch_leave_data')->name('employee.fetch_leave_data');
Route::post('/employee/fetch_attend_data', 'Report@fetch_attend_data')->name('employee.fetch_attend_data');
Route::post('attendentbyemployeefilter', 'Report@attendentbyemployeefilter')->name('attendentbyemployeefilter');  
Route::post('leavedatafilter', 'Report@leavedatafilter')->name('leavedatafilter');  
Route::post('atenddatafilter', 'Report@atenddatafilter')->name('atenddatafilter');  
Route::get('/leaveReport', 'Report@leavereport')->name('leaveReport'); 
Route::get('/leave_report_list', 'Report@leave_report_list')->name('leave_report_list');
Route::get('/employee_list_from_leaves_sel2', 'Report@employee_list_from_leaves_sel2')->name('employee_list_from_leaves_sel2');
Route::get('/attendetreport', 'Report@attendetreport')->name('attendetreport');
Route::get('/daterange', 'Report@daterange');
Route::post('/daterange/fetch_data', 'Report@fetch_data')->name('daterange.fetch_data');
Route::post('/daterange/filter_data', 'Report@filter_data')->name('daterange.filter_data');
Route::get('LateAttendance',['uses' => 'Report@lateattendent', 'as' => 'LateAttendance']);
Route::get('late_attendance_report_list',['uses' => 'Report@late_attendance_report_list', 'as' => 'late_attendance_report_list']);
Route::get('exportLateAttend', 'Report@exportLateattend')->name('exportLateAttend');
Route::get('/LateAttendentView', 'AttendanceController@getlateAttendance');
//ot_approve
Route::get('/ot_approve', 'AttendanceController@ot_approve')->name('ot_approve');
Route::get('/ot_check', 'AttendanceController@ot_check')->name('ot_check');
//get_ot_details post
Route::post('/get_ot_details', 'AttendanceController@get_ot_details')->name('get_ot_details');
Route::post('/get_checked_ot_details', 'AttendanceController@get_checked_ot_details')->name('get_checked_ot_details');
//ot_approve_post
Route::post('/ot_check_post', 'AttendanceController@ot_check_post')->name('ot_check_post');
Route::post('/ot_approve_post', 'AttendanceController@ot_approve_post')->name('ot_approve_post');

Route::get('ot_report', 'Report@ot_report')->name('ot_report');
Route::get('ot_report_list',['uses' => 'Report@ot_report_list', 'as' => 'ot_report_list']);
Route::get('ot_report_list_month',['uses' => 'Report@ot_report_list_month', 'as' => 'ot_report_list_month']);
//ot_report_list_view_more post
Route::post('ot_report_list_view_more', 'Report@ot_report_list_view_more')->name('ot_report_list_view_more');

//ot_approved
Route::get('/ot_approved', 'AttendanceController@ot_approved')->name('ot_approved');
//ot_approved_list
Route::get('/ot_approved_list', 'AttendanceController@ot_approved_list')->name('ot_approved_list');

Route::get('/ot_approved_list_monthly', 'AttendanceController@ot_approved_list_monthly')->name('ot_approved_list_monthly');

//ot_approved_delete post
Route::post('/ot_approved_delete', 'AttendanceController@ot_approved_delete')->name('ot_approved_delete');
//ot_check_delete post
Route::post('/ot_checked_delete', 'AttendanceController@ot_checked_delete')->name('ot_checked_delete');


Route::get('no_pay_report', 'Report@no_pay_report')->name('no_pay_report');
Route::get('no_pay_report_list_month',['uses' => 'Report@no_pay_report_list_month', 'as' => 'no_pay_report_list_month']);
Route::post('no_pay_days_data',['uses' => 'Report@no_pay_days_data', 'as' => 'no_pay_days_data']);

Route::get('/copy_att_to_employee_work_rates', 'AttendanceController@copy_att_to_employee_work_rates');

Route::get('/attendance_list_ajax', 'AttendanceController@attendance_list_ajax');


Route::resource('ShiftType', 'ShiftTypeController');
Route::post('addShiftType',['uses' => 'ShiftTypeController@store', 'as' => 'addShiftType']); 
Route::get('ShiftType',['uses' => 'ShiftTypeController@index', 'as' => 'ShiftType']); 
Route::post('ShiftType/update', 'ShiftTypeController@update')->name('ShiftType.update');
Route::get('ShiftType/destroy/{id}', 'ShiftTypeController@destroy');


Route::resource('Shift', 'ShiftController');
Route::post('addShift',['uses' => 'ShiftController@store', 'as' => 'addShift']); 
Route::get('Shift',['uses' => 'ShiftController@index', 'as' => 'Shift']); 
Route::get('shift_list_dt',['uses' => 'ShiftController@shift_list_dt', 'as' => 'shift_list_dt']);
Route::post('Shift/update', 'ShiftController@update')->name('Shift.update');
Route::get('Shift/destroy/{id}', 'ShiftController@destroy');
Route::post('/Shiftupdate', 'ShiftController@Shiftupdate');
Route::get('/Getshift', 'ShiftController@getshift');

Route::resource('AdditionalShift', 'AdditionalShiftController');
Route::post('addAdditionalShift',['uses' => 'AdditionalShiftController@store', 'as' => 'addAdditionalShift']);
Route::post('AdditionalShift/update', 'AdditionalShiftController@update')->name('AdditionalShift.update');
Route::get('AdditionalShift/destroy/{id}', 'AdditionalShiftController@destroy');
Route::get('AdditionalShiftEdit/{id}', 'AdditionalShiftController@edit');
Route::get('branch_list', 'AdditionalShiftController@branch_list');
Route::get('additional_shift_list_dt',['uses' => 'AdditionalShiftController@additional_shift_list_dt', 'as' => 'additional_shift_list_dt']);

Route::resource('Holiday', 'HolidayController');
Route::post('addHoliday',['uses' => 'HolidayController@store', 'as' => 'addHoliday']); 
Route::get('Holiday',['uses' => 'HolidayController@index', 'as' => 'Holiday']); 
Route::post('Holiday/update', 'HolidayController@update')->name('Holiday.update');
Route::get('Holiday/destroy/{id}', 'HolidayController@destroy');
Route::post('get_holidays_for_calendar','HolidayController@get_holidays_for_calendar')->name('get_holidays_for_calendar');
Route::get('HolidayCalendar','HolidayController@HolidayCalendar')->name('HolidayCalendar');
Route::post('HolidayGet','HolidayController@HolidayGet')->name('HolidayGet');

Route::resource('Worklevel', 'WorkLevelController');
Route::post('addWorklevel',['uses' => 'WorkLevelController@store', 'as' => 'addWorklevel']); 
Route::get('Worklevel',['uses' => 'WorkLevelController@index', 'as' => 'Worklevel']); 
Route::post('Worklevel/update', 'WorkLevelController@update')->name('Worklevel.update');
Route::get('Worklevel/destroy/{id}', 'WorkLevelController@destroy');

/*-- Company Info----*/
Route::resource('Company', 'CompanyController');
Route::get('Company',['uses' => 'CompanyController@index', 'as' => 'Company']); 
Route::post('addCompany',['uses' => 'CompanyController@store', 'as' => 'addCompany']); 
Route::post('Company/update', 'CompanyController@update')->name('Company.update');
Route::get('Company/destroy/{id}', 'CompanyController@destroy');
Route::get('company_list_sel2', 'CompanyController@company_list_sel2');
/*-- End Company Info----*/

/*-- Department Info----*/
Route::resource('Department', 'DepartmentController');
Route::get('DepartmentShow/{id}',['uses' => 'DepartmentController@index', 'as' => 'DepartmentShow']);
Route::post('addDepartment',['uses' => 'DepartmentController@store', 'as' => 'addDepartment']);
Route::post('Department/update', 'DepartmentController@update')->name('Department.update');
Route::get('Department/destroy/{id}', 'DepartmentController@destroy');
Route::get('department_list_sel2', 'DepartmentController@department_list_sel2');

/*-- End Department Info----*/

/*-- Bank Info----*/
Route::resource('Bank', 'BankController');
Route::get('Bank',['uses' => 'BankController@index', 'as' => 'Bank']);
Route::post('addBank',['uses' => 'BankController@store', 'as' => 'addBank']);
Route::post('Bank/update', 'BankController@update')->name('Bank.update');
Route::get('Bank/destroy/{id}', 'BankController@destroy');
Route::get('bank_list', 'BankController@bank_list');
Route::get('banks_list_dt',['uses' => 'BankController@banks_list_dt', 'as' => 'banks_list_dt']);
/*-- End Bank Info----*/

/*-- bank_branch Info----*/
Route::resource('bank_branch', 'BankBranchController');
Route::get('bank_branch_show/{id}',['uses' => 'BankBranchController@index', 'as' => 'bank_branch_show']);
Route::post('addBankBranch',['uses' => 'BankBranchController@store', 'as' => 'addBankBranch']);
Route::post('BankBranch/update', 'BankBranchController@update')->name('BankBranch.update');
Route::get('BankBranch/destroy/{id}', 'BankBranchController@destroy');
Route::get('BankBranchEdit/{id}', 'BankBranchController@edit');
Route::get('branch_list', 'BankBranchController@branch_list');
Route::get('bank_branches_list_dt',['uses' => 'BankBranchController@bank_branches_list_dt', 'as' => 'bank_branches_list_dt']);

/*-- End bank_branch Info----*/

//resource OccupationGroup
Route::resource('OccupationGroup', 'OccupationGroupController');
//occupation_group_list_dt
Route::get('occupation_group_list_dt',['uses' => 'OccupationGroupController@occupation_group_list_dt', 'as' => 'occupation_group_list_dt']);
//OccupationGroup.update_manual
Route::post('OccupationGroup.update_manual', 'OccupationGroupController@update_manual')->name('OccupationGroup.update_manual');
//OccupationGroup.fetch_single
Route::get('OccupationGroup.fetch_single', 'OccupationGroupController@fetch_single')->name('OccupationGroup.fetch_single');

/*-- common routes --*/
Route::get('employee_list_sel2', 'EmployeeController@employee_list_sel2')->name('employee_list_sel2');
Route::get('leave_approve_person_sel2', 'EmployeeController@leave_approve_person_sel2')->name('leave_approve_person_sel2');
Route::get('location_list_sel2', 'EmployeeController@location_list_sel2')->name('location_list_sel2');
Route::post('get_dept_emp_list', 'EmployeeController@get_dept_emp_list')->name('get_dept_emp_list');

Route::get('employee_list_sel3', 'EmployeeController@employee_list_sel3')->name('employee_list_sel3');


// employee recrument report
Route::get('employee_recirument_report', 'RecruitmentReportController@index')->name('employee_recirument_report');
Route::get('/filterrecruitment-report', 'RecruitmentReportController@filter')->name('filterRecruitmentReport');
Route::get('/filterrecruitmentinterviwer-report', 'RecruitmentReportController@interviwerfilter')->name('filterRecruitmentinterviwerReport');

//department wise reports
Route::get('departmentwise_otreport', 'DepartmentwisereportController@index')->name('departmentwise_otreport');
Route::get('departmentwise_leavereport', 'DepartmentwisereportController@leavereport')->name('departmentwise_leavereport');
Route::get('departmentwise_attendancereport', 'DepartmentwisereportController@attendancereport')->name('departmentwise_attendancereport');

Route::get('/departmentwise_generateotreport', 'DepartmentwisereportController@generateotreport')->name('departmentwise_generateotreport');
Route::get('/departmentwise_gettotlaotemployee', 'DepartmentwisereportController@gettotlaotemployee')->name('departmentwise_gettotlaotemployee');

Route::get('/departmentwise_generateleavereport', 'DepartmentwisereportController@generateleavereport')->name('departmentwise_generateleavereport');
Route::get('/departmentwise_gettotalleaveemployee', 'DepartmentwisereportController@gettotalleaveemployee')->name('departmentwise_gettotalleaveemployee');

Route::get('/departmentwise_generateattendentreport', 'DepartmentwisereportController@generateattendreport')->name('departmentwise_generateattendentreport');
Route::get('/departmentwise_gettotalattendemployee', 'DepartmentwisereportController@gettotalattendentemployee')->name('departmentwise_gettotalattendemployee');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('attendance_upload_txt_submit',['uses' => 'AttendanceController@attendance_upload_txt_submit', 'as' => 'attendance_upload_txt_submit']);

Route::resource('JobCategory', 'JobCategoryController');
Route::get('JobCategory',['uses' => 'JobCategoryController@index', 'as' => 'JobCategory']);
Route::post('addJobCategory',['uses' => 'JobCategoryController@store', 'as' => 'addJobCategory']);
Route::post('JobCategory/update', 'JobCategoryController@update')->name('JobCategory.update');
Route::get('JobCategory/destroy/{id}', 'JobCategoryController@destroy');

Route::resource('peTask', 'PeTaskController');
Route::get('peTaskList',['uses' => 'PeTaskController@index', 'as' => 'peTaskList']);
Route::post('addPeTask',['uses' => 'PeTaskController@store', 'as' => 'addPeTask']);
Route::post('peTask/update', 'PeTaskController@update')->name('peTask.update');
Route::get('peTask/destroy/{id}', 'PeTaskController@destroy');
Route::get('pe_task_list_dt',['uses' => 'PeTaskController@pe_task_list_dt', 'as' => 'pe_task_list_dt']);
Route::post('approvePeTasks',['uses' => 'PeTaskController@approvePeTasks', 'as' => 'approvePeTasks']);

Route::get('pe_employee_list_dt',['uses' => 'PeTaskController@pe_employee_list_dt', 'as' => 'pe_employee_list_dt']);

Route::get('peTaskEmployeeList',['uses' => 'PeTaskEmployeeController@index', 'as' => 'peTaskEmployeeList']);
Route::get('pe_task_employee_list_dt',['uses' => 'PeTaskEmployeeController@pe_task_employee_list_dt', 'as' => 'pe_task_employee_list_dt']);

Route::post('/pe_emp_marks_save', 'PeTaskEmployeeController@pe_emp_marks_save')->name('pe_emp_marks_save');

Route::get('peTaskEmployeeMarksList',['uses' => 'PeTaskEmployeeController@peTaskEmployeeMarksList', 'as' => 'peTaskEmployeeMarksList']);

Route::get('pe_task_employee_marks_approve_list_dt',['uses' => 'PeTaskEmployeeController@pe_task_employee_marks_approve_list_dt', 'as' => 'pe_task_employee_marks_approve_list_dt']);

Route::post('/pe_emp_marks_approve', 'PeTaskEmployeeController@pe_emp_marks_approve')->name('pe_emp_marks_approve');

Route::post('work_hours_save', 'WorkHoursController@work_hours_save')->name('work_hours_save');

Route::get('pe_task_list_sel2', 'PeTaskController@pe_task_list_sel2')->name('pe_task_list_sel2');

Route::get('PaySheetReport', 'PaySheetReportController@PaySheetReport')->name('PaySheetReport');
Route::post('PaySheetReportPrint', 'PaySheetReportController@PaySheetReportPrint')->name('PaySheetReportPrint');

Route::post('/allowance_approved_delete', 'EmployeeAllowanceController@allowance_approved_delete')->name('allowance_approved_delete');

Route::resource('workCategory', 'WorkCategoryController');
Route::get('workCategoryList',['uses' => 'WorkCategoryController@index', 'as' => 'workCategoryList']);
Route::post('addWorkCategory',['uses' => 'WorkCategoryController@store', 'as' => 'addWorkCategory']);
Route::post('workCategory/update', 'WorkCategoryController@update')->name('workCategory.update');
Route::get('workCategory/destroy/{id}', 'WorkCategoryController@destroy');
Route::get('work_category_list_dt',['uses' => 'WorkCategoryController@work_category_list_dt', 'as' => 'work_category_list_dt']);

Route::get('work_category_list_sel2', 'WorkCategoryController@work_category_list_sel2')->name('work_category_list_sel2');

// Resignation Report
Route::get('employee_resign_report', 'EmployeeResignController@employee_resign_report')->name('employee_resign_report');
Route::get('get_resign_employees', 'EmployeeResignController@get_resign_employees')->name('get_resign_employees');


// User account Details
Route::put('/employees/{id}/update-image', 'UserAccountController@updateImage')->name('employees.update-image');
Route::get('useraccountsummery', 'UserAccountController@useraccountsummery_list')->name('useraccountsummery');
Route::post('get_employee_monthlysummery', 'UserAccountController@get_employee_monthlysummery')->name('get_employee_monthlysummery');
Route::get('user_leave_list',['uses' => 'UserAccountController@leave_list_dt', 'as' => 'user_leave_list']);
Route::get('userlogininformation', 'UserAccountController@userlogininformation_list')->name('userlogininformation');

Route::post('/get_employee_salarysheet', 'UserAccountController@downloadSalarySheet')->name('get_employee_salarysheet');
Route::post('/getEmployeeLeaveStatusSummary', 'UserAccountController@getemployeeleavestatus');
Route::post('EmployeeLeaveApply',['uses' => 'UserAccountController@leaveapply', 'as' => 'EmployeeLeaveApply']); 
Route::put('/employees/{id}/update-image', 'UserAccountController@updateImage')->name('employees.update-image');

// Absent Report
Route::get('employee_absent_report', 'EmployeeAbsentController@employee_absent_report')->name('employee_absent_report');
Route::get('get_absent_employees', 'EmployeeAbsentController@get_absent_employees')->name('get_absent_employees');

/* Home*/
Route::get('/getdashboard_department_lateattendance', 'HomeController@department_lateattendance')->name('getdashboard_department_lateattendance');
Route::get('/getdashboard_department_yesterdayattendance', 'HomeController@department_yesterdayattendance')->name('getdashboard_department_yesterdayattendance');
Route::get('/getdashboard_department_yesterdayabsent', 'HomeController@department_yesterdayabsent')->name('getdashboard_department_yesterdayabsent');
Route::get('/getdashboard_department_yesterdaylateattendance', 'HomeController@department_yesterdaylateattendance')->name('getdashboard_department_yesterdaylateattendance');

Route::post('employeeresignation', 'EmployeeController@employeeresignation')->name('employeeresignation');

Route::get('AttendanceEditApprovel', 'AttendanceController@AttendanceEditApprovel')->name('AttendanceEditApprovel');
Route::get('attendance_list_for_edit_approvel',['uses' => 'AttendanceController@attendance_list_for_edit_approvel', 'as' => 'attendance_list_for_edit_approvel']);
Route::get('/get_attendentedit_details', 'AttendanceController@get_attendentedit_details');
Route::post('/attendance_edit_approval', 'AttendanceController@attendance_edit_approval')->name('attendance_edit_approval');
Route::post('/attendance_edit_reject', 'AttendanceController@attendance_edit_reject')->name('attendance_edit_reject');

// Dashboard routes
Route::post('/dashboard_attend_update' ,'HomeController@dashboard_attend_update')->name('dashboard_attend_update');

// Payroll Routes

/*-- Remunerations ----*/
Route::resource('Remuneration', 'RemunerationController');
Route::get('RemunerationList',['uses' => 'RemunerationController@index', 'as' => 'RemunerationList']); 
Route::post('addRemuneration',['uses' => 'RemunerationController@store', 'as' => 'addRemuneration']); 
Route::post('Remuneration/update', 'RemunerationController@update')->name('Remuneration.update');
Route::get('Remuneration/destroy/{id}', 'RemunerationController@destroy');
/*
Route::post('/RemunerationListUpdateLive', 'RemunerationController@attendentUpdateLive');
Route::post('/RemunerationListInsertLive', 'RemunerationController@attendentinsertlive');
Route::post('/RemunerationListDeleteLive', 'RemunerationController@attendentdeletelive');
*/
/*-- Remunerations ----*/

/*-- RemunerationsEligibility ----*/
Route::resource('RemunerationEligibilityDay', 'RemunerationEligibilityDayController');
Route::post('RemunerationEligibilityDay/update', 'RemunerationEligibilityDayController@update')->name('RemunerationEligibilityDay.update');
/*-- RemunerationsEligibility ----*/

/*-- PayrollProfiles ----*/
Route::resource('PayrollProfile', 'PayrollProfileController');
Route::get('PayrollProfileList',['uses' => 'PayrollProfileController@index', 'as' => 'PayrollProfileList']); 
Route::post('addPayrollProfile',['uses' => 'PayrollProfileController@store', 'as' => 'addPayrollProfile']); 
Route::post('PayrollProfile/update', 'PayrollProfileController@update')->name('PayrollProfile.update');
Route::get('employeeData/getData', 'PayrollProfileController@getEmployeeData')->name('employeeData.getData');
/*-- PayrollProfiles ----*/

/*-- RemunerationProfiles ----*/
Route::resource('RemunerationProfile', 'RemunerationProfileController');
//Route::get('RemunerationProfileList',['uses' => 'RemunerationProfileController@index', 'as' => 'RemunerationProfileList']); 
Route::post('addRemunerationProfile',['uses' => 'RemunerationProfileController@store', 'as' => 'addRemunerationProfile']); 
Route::post('RemunerationProfile/update', 'RemunerationProfileController@update')->name('RemunerationProfile.update');
Route::get('RemunerationProfile/destroy/{id}', 'RemunerationProfileController@destroy');
/*-- RemunerationProfiles ----*/

/*-- EmployeeLoans --*/
Route::resource('EmployeeLoan', 'EmployeeLoanController');
Route::get('EmployeeLoanList',['uses' => 'EmployeeLoanController@index', 'as' => 'EmployeeLoanList']); 
Route::post('addEmployeeLoan',['uses' => 'EmployeeLoanController@store', 'as' => 'addEmployeeLoan']); 
Route::get('EmployeeLoan/{id}/review', 'EmployeeLoanController@reviewLoanList')->name('EmployeeLoan.review');
Route::post('EmployeeLoan/update', 'EmployeeLoanController@update')->name('EmployeeLoan.update');
Route::post('/freezeEmployeeLoan', 'EmployeeLoanController@freeze');//->name('EmployeeLoan.freeze');
Route::get('EmployeeLoan/destroy/{id}', 'EmployeeLoanController@destroy');
/*-- EmployeeLoans --*/

/*-- EmployeeLoanApproval --*/
//Route::resource('EmployeeLoanApproval', 'EmployeeLoanApprovalController');
Route::get('EmployeeLoanAdmin',['uses' => 'EmployeeLoanApprovalController@index', 'as' => 'EmployeeLoanAdmin']); 
Route::post('viewLoanApplicants',['uses' => 'EmployeeLoanApprovalController@applicantsinfo', 'as' => 'viewLoanApplicants']); 
Route::get('LoanApplicationList/{id}/review', 'EmployeeLoanApprovalController@reviewApplicationList')->name('LoanApplicationList.review');
Route::post('EmployeeLoanApprove/update',['uses' => 'EmployeeLoanApprovalController@update', 'as' => 'EmployeeLoanApprove.update']); 
Route::get('EmployeeLoanApprove/{id}/edit', 'EmployeeLoanApprovalController@edit')->name('EmployeeLoanApprove.edit');
/*-- EmployeeLoanApproval --*/

/*-- EmployeeTermPayments --*/
Route::resource('EmployeeTermPayment', 'EmployeeTermPaymentController');
Route::get('EmployeeTermPaymentList',['uses' => 'EmployeeTermPaymentController@index', 'as' => 'EmployeeTermPaymentList']); 
Route::post('addTermPayment',['uses' => 'EmployeeTermPaymentController@store', 'as' => 'addTermPayment']); 
Route::get('EmployeeTermPayment/{id}/review', 'EmployeeTermPaymentController@reviewPaymentList')->name('EmployeeTermPayment.review');
Route::post('/freezeTermPayment', 'EmployeeTermPaymentController@freeze');
Route::post('/checkTermPayment', 'EmployeeTermPaymentController@checkPayment');
Route::post('EmployeeTermPayment/update', 'EmployeeTermPaymentController@update')->name('EmployeeTermPayment.update');

Route::post('uploadTermPayment',['uses' => 'EmployeeTermPaymentController@uploadFromFile', 'as' => 'uploadTermPayment']); 

Route::post('DownloadTermPaymentPdf',['uses' => 'EmployeeTermPaymentController@downloadTermPayment', 'as' => 'DownloadTermPaymentPdf']);
/*-- EmployeeTermPayments --*/

/* SalaryIncrements */
Route::resource('SalaryIncrement', 'SalaryIncrementController');
Route::get('SalaryIncrementList',['uses' => 'SalaryIncrementController@index', 'as' => 'SalaryIncrementList']);
Route::post('uploadSalaryIncrement',['uses' => 'SalaryIncrementController@uploadFromFile', 'as' => 'uploadSalaryIncrement']); 
Route::get('incrementData/getData', 'SalaryIncrementController@getIncrementData')->name('incrementData.getData');
Route::get('SalaryIncrement/destroy/{id}', 'SalaryIncrementController@destroy');
/* SalaryIncrements */

/*-- OtherFacilities ----*/
Route::resource('OtherFacility', 'OtherFacilityController');
//Route::get('OtherFacilityList',['uses' => 'OtherFacilityController@index', 'as' => 'OtherFacilityList']); 
Route::post('addOtherFacility',['uses' => 'OtherFacilityController@store', 'as' => 'addOtherFacility']); 
/*-- OtherFacilities ----*/

/* OtherFacilityPayments */
Route::resource('OtherFacilityPayment', 'OtherFacilityPaymentController');
Route::get('OtherFacilityPaymentList',['uses' => 'OtherFacilityPaymentController@index', 'as' => 'OtherFacilityPaymentList']);
Route::post('allocateOtherFacility',['uses' => 'OtherFacilityPaymentController@store', 'as' => 'allocateOtherFacility']); 
Route::post('uploadOtherFacilities',['uses' => 'OtherFacilityPaymentController@uploadFromFile', 'as' => 'uploadOtherFacilities']); 
Route::get('facilitiesData/getData', 'OtherFacilityPaymentController@getFacilityData')->name('facilitiesData.getData');
Route::post('/freezeOtherFacilityPayment', 'OtherFacilityPaymentController@freeze');
Route::get('OtherFacilities/destroy/{id}', 'OtherFacilityPaymentController@destroy');
/* OtherFacilityPayments */

/*-- EmployeeLoanInstallments --*/
Route::resource('EmployeeLoanInstallment', 'EmployeeLoanInstallmentController');
Route::get('EmployeeLoanInstallmentList',['uses' => 'EmployeeLoanInstallmentController@index', 'as' => 'EmployeeLoanInstallmentList']); 
Route::get('EmployeeLoanInstallment/{id}/review', 'EmployeeLoanInstallmentController@reviewPaymentList')->name('EmployeeLoanInstallment.review');
Route::post('EmployeeLoanInstallment/update', 'EmployeeLoanInstallmentController@update')->name('EmployeeLoanInstallment.update');
Route::post('/freezeLoanInstallment', 'EmployeeLoanInstallmentController@freeze');
Route::post('/checkLoanInstallment', 'EmployeeLoanInstallmentController@checkInstallment');
/*-- EmployeeLoanInstallments --*/

/*-- PaymentPeriods --*/
Route::resource('PaymentPeriod', 'PaymentPeriodController');
Route::get('SalaryProcessSchedule',['uses' => 'PaymentPeriodController@index', 'as' => 'SalaryProcessSchedule']); 
Route::post('addSchedule',['uses' => 'PaymentPeriodController@store', 'as' => 'addSchedule']); 
Route::post('PaymentPeriod/update', 'PaymentPeriodController@update')->name('PaymentPeriod.update');

Route::post('get_entitle_work_hours',['uses' => 'PaymentPeriodController@get_entitle_work_hours', 'as' => 'get_entitle_work_hours']); 
/*-- PaymentPeriods --*/

/*-- EmployeePayslips --*/
Route::resource('EmployeePayslip', 'EmployeePayslipController');
Route::get('EmployeePayslipList',['uses' => 'EmployeePayslipController@index', 'as' => 'EmployeePayslipList']); 
Route::get('PayslipRegistry',['uses' => 'EmployeePayslipController@preview', 'as' => 'PayslipRegistry']); 
Route::post('/checkAttendance', 'EmployeePayslipController@checkAttendance');
Route::post('/checkPayslipListByDept', 'EmployeePayslipController@checkPayslipList');
Route::post('/checkEmpEpfEtf', 'EmployeePayslipController@checkPayPeriodEpfEtf');
Route::post('/checkPayslipListBankSlip', 'PaySlipBank@checkPayslipListBankSlip');
Route::post('/freezePayslip', 'EmployeePayslipController@freeze');
Route::post('/deletePayslip', 'EmployeePayslipController@clearPaidInfo');
Route::post('/holdPayment', 'EmployeePayslipController@holdPayment');
Route::post('/approvePayment', 'EmployeePayslipController@approvePayment');

Route::post('/checkPayslipList', 'PaySlipBank@checkPayslipList');
Route::post('/checkPayslipListBankSlip', 'PaySlipBank@checkPayslipListBankSlip');
Route::get('ReportSalarySheetBankSlip',['uses' => 'PaySlipBank@reportSalarySheetBankSlip', 'as' => 'ReportSalarySheetBankSlip']);
/*-- EmployeePayslips --*/

/*-- EmployeeSalaryRemarks --*/
Route::resource('EmployeeSalaryRemark', 'EmployeeSalaryRemarkController');
Route::post('setRemark',['uses' => 'EmployeeSalaryRemarkController@store', 'as' => 'setRemark']); 
/*-- EmployeeSalaryRemarks --*/

/*-- EmployeeWorkRates --*/
Route::resource('EmployeeWorkRate', 'EmployeeWorkRateController');
Route::get('EmployeeWorkSummary',['uses' => 'EmployeeWorkRateController@index', 'as' => 'EmployeeWorkSummary']); 
Route::post('addWorkSummary',['uses' => 'EmployeeWorkRateController@store', 'as' => 'addWorkSummary']);
/*-- EmployeeWorkRates --*/

//Route::get('admin/customers','CustomerController@index');
//Route::get('clist',['uses' => 'CustomerController@index', 'as' => 'clist']);
//Route::get('ajaxdata', 'AjaxdataController@index')->name('ajaxdata');
//Route::get('ajaxdata/getdata', 'AjaxdataController@getdata')->name('ajaxdata.getdata');

/*-- PayrollReports --*/
Route::get('ReportEpfEtf',['uses' => 'EmployeePayslipController@reportEpfEtf', 'as' => 'ReportEpfEtf']); 
Route::post('DownloadEpfEtf',['uses' => 'EmployeePayslipController@downloadEpfEtf', 'as' => 'DownloadEpfEtf']); 
Route::get('ReportSignatureSheet',['uses' => 'EmployeePayslipController@reportSignatureSheet', 'as' => 'ReportSignatureSheet']); 
Route::post('DownloadSignatureSheet',['uses' => 'EmployeePayslipController@downloadSignatureSheet', 'as' => 'DownloadSignatureSheet']);
Route::get('ReportSalarySheet',['uses' => 'EmployeePayslipController@reportSalarySheet', 'as' => 'ReportSalarySheet']); 
Route::post('DownloadSalarySheet',['uses' => 'EmployeePayslipController@downloadSalarySheet', 'as' => 'DownloadSalarySheet']);
Route::get('ReportPayRegister',['uses' => 'EmployeePayslipController@reportPayRegister', 'as' => 'ReportPayRegister']); 
Route::post('DownloadPayRegister',['uses' => 'EmployeePayslipController@downloadPayRegister', 'as' => 'DownloadPayRegister']);
Route::post('/checkPayRegister', 'EmployeePayslipController@checkPayRegister');
Route::post('/checkPayslipListBankSlip', 'PaySlipBank@checkPayslipListBankSlip');

Route::get('ReportSixMonth',['uses' => 'EmployeePayrollReport@reportSixMonth', 'as' => 'ReportSixMonth']); 
Route::post('/previewSixMonth', 'EmployeePayrollReport@previewSixMonth');

Route::get('ReportAddition',['uses' => 'EmployeePayrollReport@reportAddition', 'as' => 'ReportAddition']); 
//Route::post('/previewAddition', 'EmployeePayrollReport@previewAddition');//get-data-from-checkPayslipList-for-testing
Route::get('ReportHeldSalaries',['uses' => 'EmployeePayrollReport@reportHeldSalaries', 'as' => 'ReportHeldSalaries']);
Route::post('/checkHeldSalaryList', 'EmployeePayrollReport@checkHeldSalaryList');
Route::post('/DownloadHeldSalaries', 'EmployeePayrollReport@downloadHeldSalaryList');

Route::get('ReportEmpOvertime',['uses' => 'EmployeePayrollReport@reportEmpOT', 'as' => 'ReportEmpOvertime']); 
Route::post('checkEmpOvertime',['uses' => 'EmployeePayrollReport@previewEmpOT', 'as' => 'checkEmpOvertime']); 
Route::post('DownloadEmpOvertime',['uses' => 'EmployeePayrollReport@downloadEmpOT', 'as' => 'DownloadEmpOvertime']);
/*-- PayrollReports --*/
/*-- PayrollStatements --*/
Route::get('EmpSalaryPayVoucher',['uses' => 'EmployeePayrollStatement@reportEmpSalaryVoucher', 'as' => 'EmpSalaryPayVoucher']); 
Route::get('EmpIncentivePayVoucher',['uses' => 'EmployeePayrollStatement@reportEmpIncentiveVoucher', 'as' => 'EmpIncentivePayVoucher']); 

Route::get('ReportBankAdvice',['uses' => 'EmployeePayrollStatement@reportBankAdvice', 'as' => 'ReportBankAdvice']); 
Route::post('/previewBankAdvice', 'EmployeePayrollStatement@previewBankAdvice');
Route::get('ReportPaySummary',['uses' => 'EmployeePayrollStatement@reportPaySummary', 'as' => 'ReportPaySummary']); 
Route::post('/previewPaySummary', 'EmployeePayrollStatement@previewPaySummary');

Route::get('EmpSalaryJournalVoucher',['uses' => 'EmployeePayrollStatement@glEmpSalaryVoucher', 'as' => 'EmpSalaryJournalVoucher']); 
Route::get('EmpEpfEtfJournalVoucher',['uses' => 'EmployeePayrollStatement@glEmpEpfEtfVoucher', 'as' => 'EmpEpfEtfJournalVoucher']); 
/*-- PayrollStatements --*/

// rank payroll profile controller
Route::get('/rankpayrollprofile' ,'RankpayrollprofileController@index')->name('rankpayrollprofile');
Route::post('/rankpayrollprofileinsert' ,'RankpayrollprofileController@insert')->name('rankpayrollprofileinsert');
Route::get('/rankpayrollprofilelist' ,'RankpayrollprofileController@profilelist')->name('rankpayrollprofilelist');
Route::post('/rankpayrollprofileedit' ,'RankpayrollprofileController@edit')->name('rankpayrollprofileedit');
Route::post('/rankpayrollprofileupdate' ,'RankpayrollprofileController@Update')->name('rankpayrollprofileupdate');
Route::post('/rankpayrollprofiledelete' ,'RankpayrollprofileController@delete')->name('rankpayrollprofiledelete');
Route::get('/rankpayrollprofilestatus/{id}/{stasus}','RankpayrollprofileController@status')->name('rankpayrollprofilestatus');
Route::post('/rankpayrollprofiledeletelist' ,'RankpayrollprofileController@deletelist')->name('rankpayrollprofiledeletelist');
Route::post('/rankpayrollprofileapprove' ,'RankpayrollprofileController@approve')->name('rankpayrollprofileapprove');

// shift salary prep
Route::get('/shiftsalarypreparation' ,'ShiftpayrollController@index')->name('shiftsalarypreparation');
Route::post('/getshiftsalarypreparation' ,'ShiftpayrollController@create')->name('getshiftsalarypreparation');
Route::post('/shiftsalarypreparationinsert' ,'ShiftpayrollController@insert')->name('shiftsalarypreparationinsert');

/*-- LocationSummaryReports --*/
Route::get('LocationPaySummary',['uses' => 'LocationPayrollReport@reportPaySummary', 'as' => 'LocationPaySummary']);
Route::post('/checkPaySummary', 'LocationPayrollReport@checkPaySummary');//PAY-SUMMARY, MASTER-SUMMARY, SALARY-REC
Route::get('LocationMasterSummary',['uses' => 'LocationPayrollReport@reportMasterSummary', 'as' => 'LocationMasterSummary']);
//Route::post('/checkPaySummary', 'LocationPayrollReport@checkPaySummary');//MASTER-SUMMARY
Route::get('EmployeeGratuity',['uses' => 'LocationPayrollReport@reportEmpGratuity', 'as' => 'EmployeeGratuity']);
Route::post('/checkEmpGratuity', 'LocationPayrollReport@checkEmpGratuity');//EMP-GRATUITY, PRO-GRATUITY
Route::get('GratuityProvision',['uses' => 'LocationPayrollReport@reportGratuityProvision', 'as' => 'GratuityProvision']);
//Route::post('/checkEmpGratuity', 'LocationPayrollReport@checkEmpGratuity');//PRO-GRATUITY
Route::get('SalaryReconciliation',['uses' => 'LocationPayrollReport@reportSalaryReconciliation', 'as' => 'SalaryReconciliation']);
//Route::post('/checkPaySummary', 'LocationPayrollReport@checkPaySummary');//SALARY-REC
/*-- LocationSummaryReports --*/

Route::get('/', 'LoginController@index')->name('showLoginForm');
Route::post('/getbranch', 'LoginController@getbranch')->name('getbranch');

Route::get('/corporatedashboard' ,'CorporatedashboardController@index')->name('corporatedashboard');
Route::get('/employeemanagementdashboard' ,'EmployeemanagementdashboardController@index')->name('employeemanagementdashboard');
Route::get('/shiftmanagementdashboard' ,'ShiftdashboardController@index')->name('shiftmanagementdashboard');
Route::get('/attendenceleavedashboard' ,'AttendenceleavedashboardController@index')->name('attendenceleavedashboard');
Route::get('/reportdashboard' ,'ReportdashboardController@index')->name('reportdashboard');
Route::get('/payrolldashboard' ,'PayrolldashboardController@index')->name('payrolldashboard');
Route::get('/administratordashboard' ,'AdministratordashboardController@index')->name('administratordashboard');

// Employee Recurement details 
Route::get('/viewEmployeeRequrement/{id}',['uses' => 'EmployeementrequrementController@index', 'as' => 'viewEmployeeRequrement']);
Route::post('/EmployeeRequrementinsert' ,'EmployeementrequrementController@insert')->name('EmployeeRequrementinsert');

// leave request controller routes
Route::get('/leaverequest' ,'LeaverequestController@index')->name('leaverequest');
Route::post('/leaverequestinsert' ,'LeaverequestController@insert')->name('leaverequestinsert');
Route::get('/leaverequest_list' ,'LeaverequestController@leavereuest_list')->name('leaverequest_list');
Route::post('/leaverequestedit' ,'LeaverequestController@edit')->name('leaverequestedit');
Route::post('/leaverequestupdate' ,'LeaverequestController@update')->name('leaverequestupdate');
Route::post('/leaverequestdelete' ,'LeaverequestController@delete')->name('leaverequestdelete');
Route::post('/leaverequestapprove' ,'LeaverequestController@approve')->name('leaverequestapprove');

Route::post('/employeeleaverequest' ,'LeaverequestController@getemployeeleaverequest')->name('employeeleaverequest');
Route::post('/employeeleaverequestdetails' ,'LeaverequestController@getrequestreorddetails')->name('employeeleaverequestdetails');

// Special attendance controller routes
Route::get('/speecialattendace' ,'AttendanceSpecialController@index')->name('speecialattendace');
Route::post('/speecialattendaceinsert' ,'AttendanceSpecialController@insert')->name('speecialattendaceinsert');

// bONUS Report controller routes
Route::get('/bonusreport' ,'BonusreportController@index')->name('bonusreport');
Route::post('/bonusreportgenerate' ,'BonusreportController@generatereport')->name('bonusreportgenerate');

// Leave balance Report controller routes
Route::get('/leavebalancereport' ,'LeavebalancereportController@index')->name('leavebalancereport');
Route::post('/leavebalancereportreportgenerate' ,'LeavebalancereportController@generatereport')->name('leavebalancereportreportgenerate');

Route::get('/checkloanguranteemployee' ,'EmployeeLoanController@checkloangurante')->name('checkloanguranteemployee');

Route::post('/generatepaysummarypdf' ,'PaysummaryPDFController@paysummarypdf')->name('generatepaysummarypdf');
Route::post('lateAttendance_mark_as_reject',['uses' => 'AttendanceController@lateAttendance_mark_as_reject', 'as' => 'lateAttendance_mark_as_reject']);
Route::post('late_leave_balance',['uses' => 'AttendanceController@late_leave_balance', 'as' => 'late_leave_balance']);



Route::get('/getdashboard_emp_work_days', 'HomeController@emp_work_days')->name('getdashboard_emp_work_days');
Route::get('/getdashboard_today_birthday', 'HomeController@today_birthday')->name('getdashboard_today_birthday');
Route::get('/getdashboard_thisweek_birthday', 'HomeController@thisweek_birthday')->name('getdashboard_thisweek_birthday');
Route::get('/getdashboard_thismonth_birthday', 'HomeController@thismonth_birthday')->name('getdashboard_thismonth_birthday');

// daily attendace cheking Report controller routes
Route::get('/dailyattendacecheking' ,'DailyattendacereportController@index')->name('dailyattendacecheking');
Route::post('/dailyattendacechekingreport' ,'DailyattendacereportController@generatedailyattendacereport')->name('dailyattendacechekingreport');


// Payroll chart of account controller routes
Route::get('/payrollchartaccount' ,'PayrollchartAccountController@index')->name('payrollchartaccount');
Route::post('/payrollchartaccountcreate' ,'PayrollchartAccountController@store')->name('payrollchartaccountcreate');
Route::post('/payrollchartaccountedit' ,'PayrollchartAccountController@edit')->name('payrollchartaccountedit');
Route::post('/payrollchartaccountupdate' ,'PayrollchartAccountController@update')->name('payrollchartaccountupdate');
Route::post('/payrollchartaccountdelete' ,'PayrollchartAccountController@delete')->name('payrollchartaccountdelete');


// Route::get('/get-accounts', 'PayrollchartAccountController@getAllAccounts')->name('getchartsaccounts');

// Employee Birthday Report
Route::get('EmpoloyeeBdReport',['uses' => 'EmployeeBdReportController@getemployeeBdlist', 'as' => 'EmpoloyeeBdReport']);
Route::get('employee_bd_report_list',['uses' => 'EmployeeBdReportController@employee_bd_report_list', 'as' => 'employee_bd_report_list']);

/*-- Special notes----*/
Route::get('SpecialNote',['uses' => 'SpecialNoteController@index', 'as' => 'SpecialNote']); 
Route::post('addSpecialNote',['uses' => 'SpecialNoteController@store', 'as' => 'addSpecialNote']); 
Route::get('SpecialNote/{id}/edit', 'SpecialNoteController@edit')->name('SpecialNote.edit');  
Route::post('SpecialNote/update', 'SpecialNoteController@update')->name('SpecialNote.update');
Route::get('SpecialNote/destroy/{id}', 'SpecialNoteController@destroy');
Route::get('SpecialNote/{id}/view', 'SpecialNoteController@viewEmployees')->name('SpecialNote.view');
Route::post('SpecialNote/{id}/getEmployees', 'SpecialNoteController@getEmployeesForEdit')->name('SpecialNote.getEmployees');
Route::post('get_employee_department', 'SpecialNoteController@getEmployeeDepartment');
/*-- End Special notes----*/

/*-- loan report----*/
Route::get('/loanReport', 'LoanReportController@loanReport')->name('loanReport'); 
Route::get('/loanInstallmentReport', 'LoanReportController@loanInstallmentReport')->name('loanInstallmentReport'); 
/*-- End loan report----*/

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    
    return "Cache cleared successfully!";
});
