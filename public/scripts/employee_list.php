<?php

$table = 'employees';
$primaryKey = 'id';

$columns = array(
    array('db' => '`e`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`e`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
    array('db' => '`e`.`emp_fp_id`', 'dt' => 'emp_fp_id', 'field' => 'emp_fp_id'),
    array('db' => '`e`.`emp_etfno`', 'dt' => 'emp_etfno', 'field' => 'emp_etfno'),
    array('db' => '`e`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`e`.`emp_join_date`', 'dt' => 'emp_join_date', 'field' => 'emp_join_date'),
    array('db' => '`employment_statuses`.`emp_status`', 'dt' => 'emp_status', 'field' => 'emp_status'),
    array('db' => '`branches`.`location`', 'dt' => 'location', 'field' => 'location'),
    array('db' => '`job_titles`.`title`', 'dt' => 'title', 'field' => 'title'),
    array('db' => '`departments`.`name`', 'dt' => 'dep_name', 'field' => 'name'),
    array('db' => '`e`.`is_resigned`', 'dt' => 'is_resigned', 'field' => 'is_resigned'),
    array('db' => '`e`.`emp_national_id`', 'dt' => 'emp_national_id', 'field' => 'emp_national_id'),
    array('db' => '`job_categories`.`category`', 'dt' => 'category', 'field' => 'category'),
    array('db' => '`e`.`status`', 'dt' => 'status', 'field' => 'status'),
);

require('config.php');

$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

require('ssp.customized.class.php');

$current_date_time = date('Y-m-d H:i:s');
$previous_month_date = date('Y-m-d', strtotime('-1 month'));

$extraWhere = "`e`.`deleted` = 0 AND (`e`.`is_resigned` = 0 OR (`e`.`is_resigned` = 1 AND `e`.`resignation_date` BETWEEN '$previous_month_date' AND '$current_date_time'))";

if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $extraWhere .= " AND `departments`.`id` = '$department'";
}
if (!empty($_POST['employee'])) {
    $employee = $_POST['employee'];
    $extraWhere .= " AND `e`.`emp_id` = '$employee'";
}
if (!empty($_POST['company'])) {
    $company = $_POST['company'];
    $extraWhere .= " AND `e`.`emp_company` = '$company'";
}
if (!empty($_POST['location'])) {
    $location = $_POST['location'];
    $extraWhere .= " AND `e`.`emp_location` = '$location'";
}
if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $extraWhere .= " AND `e`.`emp_join_date` BETWEEN '$from_date' AND '$to_date'";
}

$joinQuery = "FROM `employees` AS `e`
    LEFT JOIN `employment_statuses` ON `e`.`emp_status` = `employment_statuses`.`id`
    LEFT JOIN `job_titles` ON `e`.`emp_job_code` = `job_titles`.`id`
    LEFT JOIN `branches` ON `e`.`emp_location` = `branches`.`id`
    LEFT JOIN `departments` ON `e`.`emp_department` = `departments`.`id`
    LEFT JOIN `job_categories` ON `e`.`job_category_id` = `job_categories`.`id`";

try {
    $data = SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere);
    
    foreach ($data['data'] as &$row) {
        $row['emp_id_link'] = '<a href="viewEmployee/'.$row['id'].'">'.$row['emp_id'].'</a>';
        $row['emp_name_link'] = '<a href="viewEmployee/'.$row['id'].'">'.$row['emp_name_with_initial'].'</a>';
        $row['emp_status_label'] = '<span class="text-success">'.$row['emp_status'].'</span>';
    }
    
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>