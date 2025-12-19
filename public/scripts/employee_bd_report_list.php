<?php
use App\Helpers\EmployeeHelper;

require_once __DIR__ . '/../../app/Helpers/EmployeeHelper.php';

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
    array('db' => '`e`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`e`.`emp_birthday`', 'dt' => 'emp_birthday', 'field' => 'emp_birthday'),
    array('db' => '`departments`.`name`', 'dt' => 'dept_name', 'field' => 'name'),
    array('db' => '`e`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name'),
    array('db' => '`e`.`emp_id`', 'dt' => 'employee_display', 'field' => 'emp_id', 
        'formatter' => function($d, $row) {
            $employee = (object)[
                'emp_name_with_initial' => $row['emp_name_with_initial'],
                'calling_name' => $row['calling_name'],
                'emp_id' => $d
            ];
            
            return EmployeeHelper::getDisplayName($employee);
        }
    ),
    array('db' => '`e`.`emp_birthday`', 'dt' => 'age', 'field' => 'emp_birthday',
        'formatter' => function($d, $row) {
            if (!empty($d)) {
                $birthDate = new DateTime($d);
                $today = new DateTime();
                return $today->diff($birthDate)->y;
            }
            return null;
        }
    ),
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
if (!empty($_POST['date'])) {
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $month = date('m', strtotime($date));
    $day = date('d', strtotime($date));
    $extraWhere .= " AND MONTH(`e`.`emp_birthday`) = '$month' AND DAY(`e`.`emp_birthday`) = '$day'";
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
    
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>