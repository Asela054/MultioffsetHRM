<?php


// DB table to use
$table = 'leave_request';

// Table's primary key
$primaryKey = 'id';

$columns = array(
    array('db' => '`leave_request`.`id`', 'dt' => 'id', 'field' => 'id'),
    array('db' => '`emp`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id'),
    array('db' => '`emp`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial'),
    array('db' => '`departments`.`name`', 'dt' => 'name', 'field' => 'name'),
    array('db' => '`leave_types`.`leave_type`', 'dt' => 'leave_type', 'field' => 'leave_type'),
    array('db' => '`leave_request`.`from_date`', 'dt' => 'leave_from', 'field' => 'from_date'),
    array('db' => '`leave_request`.`to_date`', 'dt' => 'leave_to', 'field' => 'to_date'),
    array('db' => '`leaves`.`half_short`', 'dt' => 'half_or_short', 'field' => 'half_short'),
    array('db' => '`leaves`.`status`', 'dt' => 'status', 'field' => 'status'),
    array('db' => '`leave_request`.`request_approve_status`', 'dt' => 'approvestatus', 'field' => 'request_approve_status'),
    array('db' => '`leave_request`.`leave_category`', 'dt' => 'leave_category', 'field' => 'leave_category'),
    array('db' => '`leave_request`.`reason`', 'dt' => 'reason', 'field' => 'reason'),
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

// Base condition
$extraWhere = "`leave_request`.`status` = 1";

// Add filters
if (!empty($_REQUEST['department'])) {
    $department = $_REQUEST['department'];
    $extraWhere .= " AND `departments`.`id` = '$department'";
}

if (!empty($_REQUEST['employee'])) {
    $employee = $_REQUEST['employee'];
    $extraWhere .= " AND `emp`.`emp_id` = '$employee'";
}

if (!empty($_REQUEST['from_date']) && !empty($_REQUEST['to_date'])) {
    $from_date = $_REQUEST['from_date'];
    $to_date = $_REQUEST['to_date'];
    $extraWhere .= " AND `leave_request`.`from_date` BETWEEN '$from_date' AND '$to_date'";
}

$joinQuery = "FROM `leave_request`
    JOIN `employees` AS `emp` ON `leave_request`.`emp_id` = `emp`.`emp_id`
    LEFT JOIN `departments` ON `emp`.`emp_department` = `departments`.`id`
    LEFT JOIN `leaves` ON `leave_request`.`id` = `leaves`.`request_id`
    LEFT JOIN `leave_types` ON `leaves`.`leave_type` = `leave_types`.`id`";

try {
    $data = SSP::simple($_REQUEST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere);
    
    foreach ($data['data'] as &$row) {
        // Format leave category
        if ($row['leave_category'] == 0.25) {
            $row['leave_category'] = 'Short Leave';
        } elseif ($row['leave_category'] == 0.5) {
            $row['leave_category'] = 'Half Day';
        } else {
            $row['leave_category'] = 'Full Day';
        }
        

        $row['approvestatus'] = ($row['approvestatus'] == 0) ? 'Not Approved' : 'Approved';
        
   
        if ($row['half_or_short'] == 0.25) {
            $row['half_or_short'] = 'Short Leave';
        } elseif ($row['half_or_short'] == 0.5) {
            $row['half_or_short'] = 'Half Day';
        } elseif ($row['half_or_short'] == 1) {
            $row['half_or_short'] = 'Full Day';
        } else {
            $row['half_or_short'] = '';
        }
        
        // Ensure leave type is displayed properly
        $row['leave_type'] = $row['leave_type'] ?? ' ';
    }
    
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
	















$query2 = 'FROM `attendances` as `at1` ';
$query2 .= 'inner join `employees` on `at1`.`uid` = `employees`.`emp_id` ';
$query2 .= 'left join `shift_types` on `employees`.`emp_shift` = `shift_types`.`id` ';
$query2 .= 'left join `branches` on `at1`.`location` = `branches`.`id` ';
$query2 .= 'left join `departments` on `departments`.`id` = `employees`.`emp_department` ';
$query2 .= 'WHERE 1 = 1 AND at1.deleted_at IS NULL ';
//$searchValue = 'Breeder Farm';
if ($searchValue != '') {
    $query2 .= 'AND ';
    $query2 .= '( ';
    $query2 .= 'employees.emp_id like "' . $searchValue . '%" ';
    $query2 .= 'OR employees.emp_name_with_initial like "' . $searchValue . '%" ';
    $query2 .= 'OR at1.timestamp like "' . $searchValue . '%" ';
    $query2 .= 'OR branches.location like "' . $searchValue . '%" ';
    $query2 .= ') ';
}

if ($department != '') {
    $query2 .= 'AND departments.id = "' . $department . '" ';
}

if ($employee != '') {
    $query2 .= 'AND employees.emp_id = "' . $employee . '" ';
}

if ($location != '') {
    $query2 .= 'AND at1.location = "' . $location . '" ';
}

if ($from_date != '' && $to_date != '') {
    $query2 .= 'AND at1.date BETWEEN "' . $from_date . '" AND "' . $to_date . '" ';
}

$query6 = 'group by `at1`.`uid`, `at1`.`date` ';
$query6 .= 'having count(timestamp) < 2 ';
$query5 = 'LIMIT ' . (string)$start . ' , ' . $rowperpage . ' ';
$query7 = 'ORDER BY ' . $columnName . ' ' . $columnSortOrder . ' ';

$query3 = 'select `shift_types`.*, `at1`.*, at1.id as at_id, Max(at1.timestamp) as lasttimestamp, Min(at1.timestamp) as firsttimestamp,
        `employees`.`emp_name_with_initial`, `branches`.`location` as b_location, departments.name as dep_name ';

$records = DB::select($query3 . $query2 . $query6 . $query7 . $query5);