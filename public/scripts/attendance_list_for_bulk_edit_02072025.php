<?php

$table = 'attendances';

$primaryKey = 'id';

$columns = array(
    array('db' => '`u`.`uid`', 'dt' => 'uid', 'field' => 'uid'),
    array('db' => '`u`.`formatted_date`', 'dt' => 'date', 'field' => 'formatted_date'),
    array('db' => '`u`.`month`', 'dt' => 'month', 'field' => 'month'),
    array('db' => '`u`.`firsttimestamp`', 'dt' => 'firsttimestamp', 'field' => 'firsttimestamp'),
    array('db' => '`u`.`lasttimestamp`', 'dt' => 'lasttimestamp', 'field' => 'lasttimestamp'),
    array('db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`u`.`calling_name`', 'dt' => 'calling_name', 'field' => 'calling_name'),
    array('db' => '`u`.`location`', 'dt' => 'location', 'field' => 'location'),
    array('db' => '`u`.`dept_name`', 'dt' => 'dept_name', 'field' => 'dept_name'),
    array('db' => '`u`.`first_time_stamp`', 'dt' => 'first_time_stamp', 'field' => 'first_time_stamp'),
    array('db' => '`u`.`last_time_stamp`', 'dt' => 'last_time_stamp', 'field' => 'last_time_stamp'),
    array('db' => '`u`.`in_time_edit_status`', 'dt' => 'in_time_edit_status', 'field' => 'in_time_edit_status'),
    array('db' => '`u`.`out_time_edit_status`', 'dt' => 'out_time_edit_status', 'field' => 'out_time_edit_status')
);

require('config.php');

$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

require('ssp.customized.class.php');

$sql = "SELECT 
    `at1`.`uid`,
    `at1`.`date`,
    `at1`.`edit_status`,
    MAX(at1.`edit_status`) AS `any_edit_status`,
    DATE_FORMAT(`at1`.`date`, '%Y-%m') AS `month`,
    MIN(`at1`.`timestamp`) AS `firsttimestamp`,
    CASE WHEN MIN(`at1`.`timestamp`) = MAX(`at1`.`timestamp`) THEN NULL ELSE MAX(`at1`.`timestamp`) END AS `lasttimestamp`,
    `employees`.`emp_name_with_initial`,
    `employees`.`calling_name`,
    `branches`.`location`,
    `departments`.`name` AS `dept_name`,
    DATE_FORMAT(`at1`.`date`, '%Y-%m-%d') AS `formatted_date`,
    DATE_FORMAT(MIN(`at1`.`timestamp`), '%H:%i') AS `first_time_stamp`,
    DATE_FORMAT(CASE WHEN MIN(`at1`.`timestamp`) = MAX(`at1`.`timestamp`) THEN NULL ELSE MAX(`at1`.`timestamp`) END, '%H:%i') AS `last_time_stamp`,
    MAX(`at1`.`edit_status`) AS `out_time_edit_status`, -- 1 if any record that day was edited
    (
        SELECT `edit_status`
        FROM `attendances` AS a2
        WHERE a2.`uid` = `at1`.`uid`
          AND a2.`date` = `at1`.`date`
          AND a2.`timestamp` = MIN(`at1`.`timestamp`)
        LIMIT 1
    ) AS `in_time_edit_status`,

    `at1`.`deleted_at`,
    `at1`.`approved`
FROM `attendances` AS `at1`
JOIN `employees` ON `at1`.`uid` = `employees`.`emp_id`
LEFT JOIN `branches` ON `at1`.`location` = `branches`.`id`
LEFT JOIN `departments` ON `employees`.`emp_department` = `departments`.`id`
WHERE `at1`.`deleted_at` IS NULL AND `at1`.`approved` = '0'";

if (!empty($_REQUEST['company'])) {
    $company = $_REQUEST['company'];
    $sql .= " AND `employees`.`emp_company` = '$company'";
}

if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $sql .= " AND `employees`.`emp_department` = '$department'";
}

if (!empty($_POST['employee'])) {
    $employee = $_POST['employee'];
    $sql .= " AND `employees`.`emp_id` = '$employee'";
}

// if (!empty($_POST['location'])) {
//     $location = $_POST['location'];
//     $sql .= " AND `at1`.`location` = '$location'";
// }

if (!empty($_POST['date'])) {
    $date = $_POST['date'];
    $sql .= " AND `at1`.`date` = '$date'";
}

$sql .= " GROUP BY `at1`.`uid`, `at1`.`date`";

$joinQuery = "FROM (" . $sql . ") as `u`";

$extraWhere = "";

    echo json_encode(
        SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
    );

?>
