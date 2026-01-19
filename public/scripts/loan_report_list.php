<?php
use App\Helpers\EmployeeHelper;

require_once __DIR__ . '/../../app/Helpers/EmployeeHelper.php';

require('config.php');
require('ssp.customized.class.php');

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

$table = 'employee_loans';
$primaryKey = 'id';

$columns = array(
    array('db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`u`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
    array('db' => '`u`.`emp_etfno`', 'dt' => 'emp_etfno', 'field' => 'emp_etfno'),
    array('db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`u`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name'),
    array('db' => '`u`.`dept_name`', 'dt' => 'dept_name', 'field' => 'dept_name'),
    array('db' => '`u`.`loan_description`', 'dt' => 'loan_description', 'field' => 'loan_description'),
    array('db' => '`u`.`loan_amount`', 'dt' => 'loan_amount', 'field' => 'loan_amount'),
    array('db' => '`u`.`installment_value`', 'dt' => 'installment', 'field' => 'installment_value'),
    array('db' => '`u`.`loan_paid`', 'dt' => 'paid_amount', 'field' => 'loan_paid'),
    array('db' => '`u`.`balance`', 'dt' => 'balance', 'field' => 'balance'),
    array('db' => '`u`.`emp_id`', 'dt' => 'employee_display', 'field' => 'emp_id', 
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
    `employee_loans`.`id`,
    `employees`.`emp_id`,
    `employees`.`emp_etfno`,
    `employees`.`emp_name_with_initial`,
    `employees`.`calling_name`,
    `departments`.`name` AS `dept_name`,
    `employee_loans`.`loan_name` AS `loan_description`,
    `employee_loans`.`loan_amount`,
    `employee_loans`.`installment_value`,
    `employee_loans`.`loan_freeze`,
    IFNULL(`drv_prog`.`loan_paid`, 0) AS `loan_paid`,
    (`employee_loans`.`loan_amount` - IFNULL(`drv_prog`.`loan_paid`, 0)) AS `balance`
FROM `employee_loans`
INNER JOIN `payroll_profiles` ON `employee_loans`.`payroll_profile_id` = `payroll_profiles`.`id`
INNER JOIN `employees` ON `payroll_profiles`.`emp_id` = `employees`.`id`
INNER JOIN `companies` ON `employees`.`emp_company` = `companies`.`id`
LEFT JOIN `departments` ON `employees`.`emp_department` = `departments`.`id`
LEFT JOIN (
    SELECT `payroll_profile_id`, MAX(`emp_payslip_no`) AS `emp_payslip_no` 
    FROM `employee_payslips` 
    GROUP BY `payroll_profile_id`
) AS `employee_payslips` ON `payroll_profiles`.`id` = `employee_payslips`.`payroll_profile_id`
LEFT JOIN (
    SELECT `employee_loan_id`, MAX(`emp_payslip_no`) AS `loan_payslip_no`, SUM(`installment_value`) AS `loan_paid` 
    FROM `employee_loan_installments` 
    WHERE `installment_cancel` = 0 
    GROUP BY `employee_loan_id`
) AS `drv_prog` ON `employee_loans`.`id` = `drv_prog`.`employee_loan_id`
LEFT JOIN (
    SELECT `id`, `employee_loan_id`, `emp_payslip_no`, `installment_cancel` 
    FROM `employee_loan_installments`
) AS `employee_loan_installments` ON (
    `employee_loans`.`id` = `employee_loan_installments`.`employee_loan_id` 
    AND IFNULL(`employee_payslips`.`emp_payslip_no` + 1, 1) = `employee_loan_installments`.`emp_payslip_no`
)
WHERE `employees`.`deleted` = 0 
AND `employee_loans`.`loan_approved` = 1 
AND `employee_loans`.`loan_cancel` = 0
AND `employee_loans`.`loan_freeze` = 0
AND (
    `employee_loans`.`loan_amount` > IFNULL(`drv_prog`.`loan_paid`, 0) 
    OR IFNULL(`drv_prog`.`loan_payslip_no`, 1) >= (IFNULL(`employee_payslips`.`emp_payslip_no`, 0) + `employee_loans`.`loan_complete`)
)";

if (!empty($_REQUEST['company'])) {
    $company = mysqli_real_escape_string($conn, $_REQUEST['company']);
    $sql .= " AND `employees`.`emp_company` = '$company'";
}

if (!empty($_REQUEST['company_branch'])) {
    $company_branch = mysqli_real_escape_string($conn, $_REQUEST['company_branch']);
    $sql .= " AND `employees`.`emp_location` = '$company_branch'";
}

if (!empty($_REQUEST['department'])) {
    $department = mysqli_real_escape_string($conn, $_REQUEST['department']);
    $sql .= " AND `departments`.`id` = '$department'";
}

if (!empty($_REQUEST['employee'])) {
    $employee = mysqli_real_escape_string($conn, $_REQUEST['employee']);
    $sql .= " AND `employees`.`emp_id` = '$employee'";
}

$joinQuery = "FROM (" . $sql . ") as `u`";

$extraWhere = "1=1";

echo json_encode(SSP::simple($_REQUEST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere));
?>