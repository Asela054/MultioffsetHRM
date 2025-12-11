<?php

require('config.php');
require('ssp.customized.class.php');

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

$table = 'employees';
$primaryKey = 'id';

$columns = array(
    array('db' => '`e`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`e`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
    array('db' => '`e`.`emp_etfno`', 'dt' => 'emp_etfno', 'field' => 'emp_etfno'),
    array('db' => '`e`.`emp_first_name`', 'dt' => 'emp_first_name', 'field' => 'emp_first_name'),
    array('db' => '`e`.`emp_med_name`', 'dt' => 'emp_med_name', 'field' => 'emp_med_name'),
    array('db' => '`e`.`emp_last_name`', 'dt' => 'emp_last_name', 'field' => 'emp_last_name'),
    array('db' => '`e`.`emp_fullname`', 'dt' => 'emp_fullname', 'field' => 'emp_fullname'),
    array('db' => '`e`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`e`.`emp_national_id`', 'dt' => 'emp_national_id', 'field' => 'emp_national_id'),
    array('db' => '`e`.`emp_birthday`', 'dt' => 'emp_birthday', 'field' => 'emp_birthday'),
    array('db' => '`e`.`emp_address`', 'dt' => 'emp_address', 'field' => 'emp_address'),
    array('db' => '`e`.`emp_addressT1`', 'dt' => 'emp_addressT', 'field' => 'emp_addressT1'),
    array('db' => '`job_titles`.`title`', 'dt' => 'title', 'field' => 'title'),
    array('db' => '`job_categories`.`category`', 'dt' => 'job_category', 'field' => 'category'),
    array('db' => '`departments`.`name`', 'dt' => 'dept_name', 'field' => 'name'),
    array('db' => '`e`.`emp_join_date`', 'dt' => 'emp_join_date', 'field' => 'emp_join_date'),
    array('db' => '`e`.`emp_permanent_date`', 'dt' => 'emp_permanent_date', 'field' => 'emp_permanent_date'),
    array('db' => '`payroll_profiles`.`basic_salary`', 'dt' => 'emp_basic_salary', 'field' => 'basic_salary'),
    array('db' => '`payroll_profiles`.`day_salary`', 'dt' => 'emp_daily_pay_rate', 'field' => 'day_salary'),
    array('db' => '`e`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name'),
);



$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);


$current_date_time = date('Y-m-d H:i:s');
$previous_month_date = date('Y-m-d', strtotime('-1 month'));

$extraWhere = "`e`.`deleted` = 0 AND (`e`.`is_resigned` = 0 OR (`e`.`is_resigned` = 1 AND `e`.`resignation_date` BETWEEN '$previous_month_date' AND '$current_date_time'))";

$sql = "";

if (!empty($_REQUEST['company'])) {
    $company = mysqli_real_escape_string($conn, $_REQUEST['company']);
    $extraWhere .= " AND `e`.`emp_company` = '$company'";
}
if (!empty($_REQUEST['company_branch'])) {
    $company_branch = mysqli_real_escape_string($conn, $_REQUEST['company_branch']);
    $extraWhere .= " AND `e`.`emp_location` = '$company_branch'";
}
if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $extraWhere .= " AND `departments`.`id` = '$department'";
}

$joinQuery = "FROM `employees` AS `e`
    LEFT JOIN `employment_statuses` ON `e`.`emp_status` = `employment_statuses`.`id`
    LEFT JOIN `job_titles` ON `e`.`emp_job_code` = `job_titles`.`id`
    LEFT JOIN `branches` ON `e`.`emp_location` = `branches`.`id`
    LEFT JOIN `departments` ON `e`.`emp_department` = `departments`.`id`
    LEFT JOIN `job_categories` ON `e`.`job_category_id` = `job_categories`.`id`
    LEFT JOIN `payroll_profiles` ON `e`.`id` = `payroll_profiles`.`emp_id`";

try {
    $data = SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere);
    
    foreach ($data['data'] as &$record) {
        $empid = $record['emp_id'];
        $like_from_date = date('Y') . '-01-01';
        $like_from_date2 = date('Y') . '-12-31';
        
        // Get annual leaves (leave_type = 1)
        $annual_leaves_query = "SELECT * FROM `leaves` 
            WHERE `emp_id` = '$empid' 
            AND `leave_from` BETWEEN '$like_from_date' AND '$like_from_date2'
            AND `leave_type` = '1' 
            AND `status` = 'Approved'";
        $annual_leaves_result = mysqli_query($conn, $annual_leaves_query);
        
        $current_year_taken_a_l = 0;
        while ($tta = mysqli_fetch_assoc($annual_leaves_result)) {
            $leave_from = $tta['leave_from'];
            $leave_to = $tta['leave_to'];
            $leave_from_year = date('Y', strtotime($leave_from));
            $leave_to_year = date('Y', strtotime($leave_to));
            
            if ($leave_from_year != $leave_to_year) {
                $lastDayOfMonth = date('Y-m-t', strtotime($leave_from));
                $diff_in_days = (strtotime($lastDayOfMonth) - strtotime($leave_from)) / 86400;
                $current_year_taken_a_l += $diff_in_days;
                
                $firstDayOfMonth = date('Y-m-01', strtotime($leave_to));
                $diff_in_days_f = (strtotime($leave_to) - strtotime($firstDayOfMonth)) / 86400;
                $current_year_taken_a_l += $diff_in_days_f;
            } else {
                $current_year_taken_a_l += $tta['no_of_days'];
            }
        }
        
        // Get casual leaves (leave_type = 2)
        $casual_leaves_query = "SELECT * FROM `leaves` 
            WHERE `emp_id` = '$empid' 
            AND `leave_from` BETWEEN '$like_from_date' AND '$like_from_date2'
            AND `leave_type` = '2' 
            AND `status` = 'Approved'";
        $casual_leaves_result = mysqli_query($conn, $casual_leaves_query);
        
        $current_year_taken_c_l = 0;
        while ($tta = mysqli_fetch_assoc($casual_leaves_result)) {
            $leave_from = $tta['leave_from'];
            $leave_to = $tta['leave_to'];
            $leave_from_year = date('Y', strtotime($leave_from));
            $leave_to_year = date('Y', strtotime($leave_to));
            
            if ($leave_from_year != $leave_to_year) {
                $lastDayOfMonth = date('Y-m-t', strtotime($leave_from));
                $diff_in_days = (strtotime($lastDayOfMonth) - strtotime($leave_from)) / 86400;
                $current_year_taken_c_l += $diff_in_days;
            } else {
                $current_year_taken_c_l += $tta['no_of_days'];
            }
        }
        
        // Get medical leaves (leave_type = 4)
        $medical_leaves_query = "SELECT * FROM `leaves` 
            WHERE `emp_id` = '$empid' 
            AND `leave_from` BETWEEN '$like_from_date' AND '$like_from_date2'
            AND `leave_type` = '4' 
            AND `status` = 'Approved'";
        $medical_leaves_result = mysqli_query($conn, $medical_leaves_query);
        
        $current_year_taken_medical = 0;
        while ($medic = mysqli_fetch_assoc($medical_leaves_result)) {
            $leave_from = $medic['leave_from'];
            $leave_to = $medic['leave_to'];
            $leave_from_year = date('Y', strtotime($leave_from));
            $leave_to_year = date('Y', strtotime($leave_to));
            
            if ($leave_from_year != $leave_to_year) {
                $lastDayOfMonth = date('Y-m-t', strtotime($leave_from));
                $diff_in_days = (strtotime($lastDayOfMonth) - strtotime($leave_from)) / 86400;
                $current_year_taken_medical += $diff_in_days;
            } else {
                $current_year_taken_medical += $medic['no_of_days'];
            }
        }
        
        $total_leaves_taken = $current_year_taken_a_l + $current_year_taken_c_l + $current_year_taken_medical;
        
        $record['emp_leave'] = $total_leaves_taken;
    }
    
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>