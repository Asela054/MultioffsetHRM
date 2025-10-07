<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'leaves';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id' ),
	array( 'db' => '`u`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id' ),
	array( 'db' => '`u`.`leave_from`', 'dt' => 'leave_from', 'field' => 'leave_from' ),
	array( 'db' => '`u`.`leave_to`', 'dt' => 'leave_to', 'field' => 'leave_to' ),
	array( 'db' => '`u`.`no_of_days`', 'dt' => 'no_of_days', 'field' => 'no_of_days' ),
	array( 'db' => '`u`.`half_short`', 'dt' => 'half_short', 'field' => 'half_short' ),
	array( 'db' => '`u`.`reson`', 'dt' => 'reson', 'field' => 'reson' ),
	array( 'db' => '`u`.`comment`', 'dt' => 'comment', 'field' => 'comment' ),
	array( 'db' => '`u`.`leave_approv_person`', 'dt' => 'leave_approv_person', 'field' => 'leave_approv_person' ),
	array( 'db' => '`u`.`status`', 'dt' => 'status', 'field' => 'status' ),
	array( 'db' => '`ec`.`covering_emp`', 'dt' => 'covering_emp', 'field' => 'covering_emp' ),
	array( 'db' => '`lt`.`leave_type`', 'dt' => 'leave_type', 'field' => 'leave_type' ),
	array( 'db' => '`e`.`emp_name_with_initial`', 'dt' => 'emp_name', 'field' => 'emp_name_with_initial' ),
	array( 'db' => '`br`.`b_location`', 'dt' => 'b_location', 'field' => 'b_location' ),
	array( 'db' => '`dep`.`dep_name`', 'dt' => 'dep_name', 'field' => 'dep_name')
);


// SQL server connection information
require('config.php');
$sql_details = array(
	'user' => $db_username,
	'pass' => $db_password,
	'db'   => $db_name,
	'host' => $db_host
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

// require( 'ssp.class.php' );
require('ssp.customized.class.php' );

$department = $_POST['department'];
$employee = $_POST['employee'];
$location = $_POST['location'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];


$joinQuery = "FROM `leaves` AS `u` 
            LEFT JOIN `leave_types` AS `lt` ON `lt`.`id` = u.leave_type
            LEFT JOIN (SELECT `id`, `emp_id`,`emp_name_with_initial` AS `covering_emp` FROM `employees`)  AS ec ON u.emp_covering = ec.emp_id
			LEFT JOIN employees AS e ON u.emp_id = e.emp_id
			LEFT JOIN (SELECT `id`, `location` AS `b_location` FROM `branches`) AS br  ON e.emp_location = br.id
			LEFT JOIN (SELECT `id`, `company_id`, `name` AS `dep_name` FROM `departments`) AS dep ON e.emp_department = dep.id";

$extraWhere = "1=1"; 

if ($department != '') {
    $extraWhere .= " AND `dep`.`id` = '$department'";
}

if ($employee != '') {
    $extraWhere .= " AND `e`.`emp_id` = '$employee'";
}

if ($location != '') {
    $extraWhere .= " AND `e`.`emp_location` = '$location'";
}

if ($from_date != '' && $to_date != '') {
    $extraWhere .= " AND `u`.`leave_from` BETWEEN '$from_date' AND '$to_date'";
}


	


echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);
