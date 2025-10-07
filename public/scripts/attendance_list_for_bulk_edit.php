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


$where = "a.deleted_at IS NULL AND e.deleted = 0 AND st.deleted = 0";

if (!empty($_REQUEST['company'])) {
    $company = $_REQUEST['company'];
    $where .= " AND `e`.`emp_company` = '$company'";
}

if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $where .= " AND `e`.`emp_department` = '$department'";
}

if (!empty($_POST['employee'])) {
    $employee = $_POST['employee'];
    $where .= " AND `e`.`emp_id` = '$employee'";
}

if (!empty($_POST['date'])) {
    $date = $_POST['date'];
    $where .= " AND `a`.`date` = '$date'";
} 


include('get_employee_in_out_attendend.php');
   


$joinQuery = "FROM (" . $sql . ") as `u`";

$extraWhere = "";

    echo json_encode(
        SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
    );

?>
