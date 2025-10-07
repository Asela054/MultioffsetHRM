<?php
// DB table to use
$table = 'leave_request';

// Table's primary key
$primaryKey = 'id';

    $columns = array(
        array('db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id'),
        array('db' => '`u`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
        array('db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
        array('db' => '`u`.`name`', 'dt' => 'name', 'field' => 'name'),
        array('db' => '`u`.`leave_type`', 'dt' => 'leave_type', 'field' => 'leave_type'),
        array('db' => '`u`.`leave_from`', 'dt' => 'leave_from', 'field' => 'leave_from'),
        array('db' => '`u`.`leave_to`', 'dt' => 'leave_to', 'field' => 'leave_to'),
        array('db' => '`u`.`half_short`', 'dt' => 'half_short', 'field' => 'half_short'),
        array('db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status'),
        array('db' => '`u`.`approvestatus`', 'dt' => 'approvestatus', 'field' => 'approvestatus'),
        array('db' => '`u`.`leave_category`', 'dt' => 'leave_category', 'field' => 'leave_category'),
        array('db' => '`u`.`reason`', 'dt' => 'reason', 'field' => 'reason'),
    );

// SQL server connection information
require('config.php');
$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

require('ssp.customized.class.php');

    $sql = "SELECT 
    `leave_request`.`id`,
    `emp`.`emp_id`,
    `emp`.`emp_name_with_initial`,
    `departments`.`name`,
    `leave_types`.`leave_type`,
    `leave_request`.`from_date` AS `leave_from`,
    `leave_request`.`to_date` AS `leave_to`,
    `leaves`.`half_short`,
    `leaves`.`status`,
    `leave_request`.`request_approve_status` AS `approvestatus`,
    `leave_request`.`leave_category`,
    `leave_request`.`reason`
    FROM `leave_request`
    JOIN `employees` AS `emp` ON `leave_request`.`emp_id` = `emp`.`emp_id`
    LEFT JOIN `departments` ON `emp`.`emp_department` = `departments`.`id`
    LEFT JOIN `leaves` ON `leave_request`.`id` = `leaves`.`request_id`
    LEFT JOIN `leave_types` ON `leaves`.`leave_type` = `leave_types`.`id`
    WHERE `leave_request`.`status` = 1";

    if (!empty($_REQUEST['company'])) {
        $company = $_REQUEST['company'];
        $sql .= " AND `emp`.`emp_company` = '$company'";
    }

    if (!empty($_REQUEST['department'])) {
        $department = $_REQUEST['department'];
        $sql .= " AND `departments`.`id` = '$department'";
    }

    if (!empty($_REQUEST['employee'])) {
        $employee = $_REQUEST['employee'];
        $sql .= " AND `emp`.`emp_id` = '$employee'";
    }
     if (!empty($_POST['location'])) {
        $location = $_POST['location'];
        $sql .= " AND `emp`.`emp_location` = '$location'";
    }

    if (!empty($_REQUEST['from_date']) && !empty($_REQUEST['to_date'])) {
        $from_date = $_REQUEST['from_date'];
        $to_date = $_REQUEST['to_date'];
        $sql .= " AND `leave_request`.`from_date` BETWEEN '$from_date' AND '$to_date'";
    }

    $joinQuery = "FROM (" . $sql . ") as `u`";
    $extraWhere = "";


    echo json_encode(SSP::simple($_REQUEST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere));
    ?>