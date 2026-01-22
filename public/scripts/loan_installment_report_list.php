<?php
use App\Helpers\EmployeeHelper;

require_once __DIR__ . '/../../app/Helpers/EmployeeHelper.php';

require('config.php');
require('ssp.customized.class.php');

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (!$conn) {
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

$table = 'employee_loan_installments';
$primaryKey = 'id';

$columns = array(
    array('db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`u`.`created_at`', 'dt' => 'date', 'field' => 'created_at',
          'formatter' => function($d, $row) {
              return date('Y-m-d', strtotime($d));
          }
    ),
    array('db' => '`u`.`installment_value`', 'dt' => 'installment', 'field' => 'installment_value'),
    array('db' => '`u`.`running_balance`', 'dt' => 'balance', 'field' => 'running_balance',
          'formatter' => function($d, $row) {
              return number_format($d, 2);
          }
    ),
    array('db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`u`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name')
);

$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);


$sql = "SELECT 
    `eli`.`id`,
    `eli`.`created_at`,
    `eli`.`installment_value`,
    (`el`.`loan_amount` - (
        SELECT IFNULL(SUM(`eli2`.`installment_value`), 0)
        FROM `employee_loan_installments` `eli2`
        WHERE `eli2`.`employee_loan_id` = `eli`.`employee_loan_id`
        AND `eli2`.`installment_cancel` = 0
        AND `eli2`.`id` <= `eli`.`id`
        AND (
            DATE(`eli2`.`created_at`) >= DATE(`el`.`loan_date`)
            OR 
            DATE(IFNULL(`eli2`.`updated_at`, `eli2`.`created_at`)) >= DATE(`el`.`loan_date`)
        )
    )) AS `running_balance`,
    `e`.`emp_name_with_initial`,
    `e`.`calling_name`,
    `el`.`loan_amount`,
    `eli`.`employee_loan_id`,
    `el`.`loan_date`
FROM `employee_loan_installments` `eli`
INNER JOIN `employee_loans` `el` ON `eli`.`employee_loan_id` = `el`.`id`
INNER JOIN `payroll_profiles` `pp` ON `el`.`payroll_profile_id` = `pp`.`id`
INNER JOIN `employees` `e` ON `pp`.`emp_id` = `e`.`id`
LEFT JOIN `departments` `d` ON `e`.`emp_department` = `d`.`id`
WHERE `eli`.`installment_cancel` = 0
AND `e`.`deleted` = 0
AND `el`.`loan_cancel` = 0
AND (
    (DATE(`eli`.`created_at`) >= DATE(`el`.`loan_date`) AND `eli`.`updated_at` IS NULL)
    OR 
    (DATE(IFNULL(`eli`.`updated_at`, `eli`.`created_at`)) >= DATE(`el`.`loan_date`) AND `eli`.`updated_at` IS NOT NULL)
)";

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
    $sql .= " AND `d`.`id` = '$department'";
}

if (!empty($_REQUEST['employee'])) {
    $employee = mysqli_real_escape_string($conn, $_REQUEST['employee']);
    $sql .= " AND `e`.`emp_id` = '$employee'";
}

$sql .= " ORDER BY `eli`.`created_at` ASC, `eli`.`id` ASC";

$joinQuery = "FROM (" . $sql . ") as `u`";

$extraWhere = "1=1";

$result = SSP::simple($_REQUEST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere);

if (!empty($_REQUEST['employee'])) {
    $employee = mysqli_real_escape_string($conn, $_REQUEST['employee']);
    
    $employeeInfoSql = "SELECT 
        `e`.`emp_name_with_initial`,
        `e`.`calling_name`,
        `e`.`emp_id`,
        `el`.`loan_amount`,
        `el`.`loan_date`,
        `el`.`loan_duration`,
        IFNULL(SUM(CASE 
            WHEN `eli`.`installment_cancel` = 0 
            AND (
                DATE(`eli`.`created_at`) >= DATE(`el`.`loan_date`)
                OR 
                DATE(IFNULL(`eli`.`updated_at`, `eli`.`created_at`)) >= DATE(`el`.`loan_date`)
            )
            THEN `eli`.`installment_value` 
            ELSE 0 
        END), 0) AS `paid_amount`,
        (`el`.`loan_amount` - IFNULL(SUM(CASE 
            WHEN `eli`.`installment_cancel` = 0 
            AND (
                DATE(`eli`.`created_at`) >= DATE(`el`.`loan_date`)
                OR 
                DATE(IFNULL(`eli`.`updated_at`, `eli`.`created_at`)) >= DATE(`el`.`loan_date`)
            )
            THEN `eli`.`installment_value` 
            ELSE 0 
        END), 0)) AS `balance`,
        COUNT(CASE 
            WHEN `eli`.`installment_cancel` = 0 
            AND (
                DATE(`eli`.`created_at`) >= DATE(`el`.`loan_date`)
                OR 
                DATE(IFNULL(`eli`.`updated_at`, `eli`.`created_at`)) >= DATE(`el`.`loan_date`)
            )
            THEN 1 
        END) AS `installments_paid`
    FROM `employees` `e`
    INNER JOIN `payroll_profiles` `pp` ON `e`.`id` = `pp`.`emp_id`
    INNER JOIN `employee_loans` `el` ON `pp`.`id` = `el`.`payroll_profile_id`
    LEFT JOIN `employee_loan_installments` `eli` ON `el`.`id` = `eli`.`employee_loan_id`
    WHERE `e`.`emp_id` = '$employee'
    AND `e`.`deleted` = 0
    AND `el`.`loan_cancel` = 0
    GROUP BY `e`.`id`, `el`.`id`
    ORDER BY `el`.`loan_date` DESC
    LIMIT 1";
    
    $employeeInfoResult = mysqli_query($conn, $employeeInfoSql);
    
    if ($employeeInfoResult && mysqli_num_rows($employeeInfoResult) > 0) {
        $employeeInfo = mysqli_fetch_assoc($employeeInfoResult);
        
        $employeeObj = (object)[
            'emp_name_with_initial' => $employeeInfo['emp_name_with_initial'],
            'calling_name' => $employeeInfo['calling_name'],
            'emp_id' => $employeeInfo['emp_id']
        ];
        
        $result['employeeInfo'] = [
            'name' => EmployeeHelper::getDisplayName($employeeObj),
            'loan_amount' => number_format($employeeInfo['loan_amount'], 2),
            'loan_date' => $employeeInfo['loan_date'],
            'loan_duration' => $employeeInfo['loan_duration'],
            'paid_amount' => number_format($employeeInfo['paid_amount'], 2),
            'balance' => number_format($employeeInfo['balance'], 2),
            'installments_paid' => $employeeInfo['installments_paid']
        ];
    } else {
        $result['employeeInfo'] = null;
    }
}

mysqli_close($conn);

echo json_encode($result);
?>