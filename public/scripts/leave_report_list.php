<?php
use App\Helpers\EmployeeHelper;

require_once __DIR__ . '/../../app/Helpers/EmployeeHelper.php';

require('config.php');
require('ssp.customized.class.php');

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

$table = 'leaves';
$primaryKey = 'id';

$columns = array(
    array('db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`u`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
    array('db' => '`u`.`emp_etfno`', 'dt' => 'emp_etfno', 'field' => 'emp_etfno'),
    array('db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`u`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name'),
    array('db' => '`u`.`leave_type_name`', 'dt' => 'leave_type_name', 'field' => 'leave_type_name'),
    array('db' => '`u`.`emp_covering_name`', 'dt' => 'emp_covering_name', 'field' => 'emp_covering_name'),
    array('db' => '`u`.`dept_name`', 'dt' => 'dept_name', 'field' => 'dept_name'),
    array('db' => '`u`.`leave_from`', 'dt' => 'leave_from', 'field' => 'leave_from'),
    array('db' => '`u`.`leave_to`', 'dt' => 'leave_to', 'field' => 'leave_to'),
    array('db' => '`u`.`half_short`', 'dt' => 'half_short', 'field' => 'half_short'),
    array('db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status'),
    array('db' => '`u`.`reson`', 'dt' => 'reson', 'field' => 'reson'),
    array('db' => '`u`.`comment`', 'dt' => 'comment', 'field' => 'comment'),
    array('db' => 'employees.emp_id', 'dt' => 'employee_display', 'field' => 'emp_id', 
          'formatter' => function($d, $row) {
              $employee = (object)[
                  'emp_name_with_initial' => $row['emp_name_with_initial'],
                  'calling_name' => $row['calling_name'],
                  'emp_id' => $row['emp_id']
              ];
              
              return EmployeeHelper::getDisplayName($employee);
          }
    )
);

$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

$sql = "SELECT 
    `leaves`.`id`,
    `leaves`.`emp_id`,
    `e`.`emp_etfno`,
    `e`.`emp_name_with_initial`,
    `e`.`calling_name`,
    `leave_types`.`leave_type` AS `leave_type_name`,
    `ec`.`emp_name_with_initial` AS `emp_covering_name`,
    `departments`.`name` AS `dept_name`,
    `leaves`.`leave_from`,
    `leaves`.`leave_to`,
    `leaves`.`half_short`,
    `leaves`.`status`,
    `leaves`.`reson`,
    `leaves`.`comment`
FROM `leaves`
JOIN `leave_types` ON `leaves`.`leave_type` = `leave_types`.`id`
LEFT JOIN `employees` AS `e` ON `leaves`.`emp_id` = `e`.`emp_id`
LEFT JOIN `employees` AS `ec` ON `leaves`.`emp_covering` = `ec`.`emp_id`
LEFT JOIN `branches` ON `e`.`emp_location` = `branches`.`id`
LEFT JOIN `departments` ON `e`.`emp_department` = `departments`.`id`
WHERE `e`.`deleted` = 0";

if (!empty($_REQUEST['company'])) {
    $company = mysqli_real_escape_string($conn, $_REQUEST['company']);
    $sql .= " AND `e`.`emp_company` = '$company'";
}

if (!empty($_REQUEST['company_branch'])) {
    $company_branch = mysqli_real_escape_string($conn, $_REQUEST['company_branch']);
    $sql .= " AND `e`.`emp_location` = '$company_branch'";
}

if (!empty($_REQUEST['department'])) {
    $department = mysqli_real_escape_string($conn, $_REQUEST['department']);
    $sql .= " AND `departments`.`id` = '$department'";
}

if (!empty($_REQUEST['employee'])) {
    $employee = mysqli_real_escape_string($conn, $_REQUEST['employee']);
    $sql .= " AND `e`.`emp_id` = '$employee'";
}

if (!empty($_REQUEST['location'])) {
    $location = mysqli_real_escape_string($conn, $_REQUEST['location']);
    $sql .= " AND `e`.`emp_location` = '$location'";
}

if (!empty($_REQUEST['leave_type'])) {
    $leave_type = mysqli_real_escape_string($conn, $_REQUEST['leave_type']);
    $sql .= " AND `leaves`.`leave_type` = '$leave_type'";
}

if (!empty($_REQUEST['covering_employee'])) {
    $covering_employee = mysqli_real_escape_string($conn, $_REQUEST['covering_employee']);
    $sql .= " AND `leaves`.`emp_covering` = '$covering_employee'";
}

if (!empty($_REQUEST['from_date'])) {
    $from_date = mysqli_real_escape_string($conn, $_REQUEST['from_date']);
    $sql .= " AND `leaves`.`leave_from` >= '$from_date'";
}

if (!empty($_REQUEST['to_date'])) {
    $to_date = mysqli_real_escape_string($conn, $_REQUEST['to_date']);
    $sql .= " AND `leaves`.`leave_to` <= '$to_date'";
    if (!empty($_REQUEST['to_date'])) {
        $sql .= " AND (`e`.`is_resigned` = 0 OR (`e`.`is_resigned` = 1 AND `e`.`resignation_date` >= '$to_date'))";
    } else {
        $sql .= " AND `e`.`is_resigned` = 0";
    }
}

$sql .= " GROUP BY `e`.`emp_id`, `leaves`.`leave_from`, `leaves`.`leave_type`";

$joinQuery = "FROM (" . $sql . ") as `u` LEFT JOIN `employees` ON `u`.`emp_id` = `employees`.`emp_id`";

$extraWhere = "1=1";

echo json_encode(SSP::simple($_REQUEST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere));
?>