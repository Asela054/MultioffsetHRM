<?php

// DB table to use
$table = 'attendances';

// Table's primary key
$primaryKey = 'id';

$columns = array(
    array('db' => '`at1`.`id`', 'dt' => 'at_id', 'field' => 'id'),
    array('db' => '`at1`.`uid`', 'dt' => 'uid', 'field' => 'uid'),
    array('db' => '`employees`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => 'MIN(`at1`.`timestamp`)', 'dt' => 'firsttimestamp', 'field' => 'firsttimestamp', 'as' => 'firsttimestamp'),
    array('db' => '`at1`.`date`', 'dt' => 'date_row', 'field' => 'date'),
    array('db' => '`at1`.`date`', 'dt' => 'date', 'field' => 'date'),
    array('db' => 'MAX(`at1`.`timestamp`)', 'dt' => 'lasttimestamp', 'field' => 'lasttimestamp', 'as' => 'lasttimestamp'),
    array('db' => '`branches`.`location`', 'dt' => 'location', 'field' => 'location'),
    array('db' => '`departments`.`name`', 'dt' => 'dep_name', 'field' => 'name'),
    array('db' => '`shift_types`.`shift_name`', 'dt' => 'shift_name', 'field' => 'shift_name')
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

$extraWhere = "at1.deleted_at IS NULL";

if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $extraWhere .= " AND departments.id = '" . $department . "' ";
}

if (!empty($_POST['employee'])) {
    $employee = $_POST['employee'];
    $extraWhere .= " AND employees.emp_id = '" . $employee . "' ";
}

if (!empty($_POST['location'])) {
    $location = $_POST['location'];
    $extraWhere .= " AND at1.location = '" . $location . "' ";
}

if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $extraWhere .= " AND at1.date BETWEEN '" . $from_date . "' AND '" . $to_date . "' ";
}

$groupBy = "`at1`.`uid`, `at1`.`date`"; 

$having = "COUNT(at1.timestamp) < 2";

$joinQuery = "FROM `attendances` as `at1`
    INNER JOIN `employees` ON `at1`.`uid` = `employees`.`emp_id`
    LEFT JOIN `shift_types` ON `employees`.`emp_shift` = `shift_types`.`id`
    LEFT JOIN `branches` ON `at1`.`location` = `branches`.`id`
    LEFT JOIN `departments` ON `departments`.`id` = `employees`.`emp_department`";

try {
    $data = SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy, $having);
    
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as &$row) {
            $row['date'] = date('Y-m-d', strtotime($row['date']));
            $row['firsttimestamp'] = !empty($row['firsttimestamp']) ? date('H:i', strtotime($row['firsttimestamp'])) : '';
            $row['lasttimestamp'] = !empty($row['lasttimestamp']) ? date('H:i', strtotime($row['lasttimestamp'])) : '';
            
            if (isset($row['firsttimestamp']) && isset($row['begining_checkout'])) {
                $firstTime = !empty($row['firsttimestamp']) ? date('G:i', strtotime($row['firsttimestamp'])) : '';
                $checkoutTime = !empty($row['begining_checkout']) ? date('G:i', strtotime($row['begining_checkout'])) : '';
                
                $row['btn_in'] = ($firstTime && $checkoutTime) ? ($firstTime < $checkoutTime) : false;
                $row['btn_out'] = !$row['btn_in'];
            } else {
                $row['btn_in'] = false;
                $row['btn_out'] = false;
            }
        }
    }
    
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}