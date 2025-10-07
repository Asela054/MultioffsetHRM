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
$table = 'attendances';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => '`u`.`id`', 'dt' => 'id', 'field' => 'id' ),
	array( 'db' => '`u`.`emp_id`', 'dt' => 'emp_id', 'field' => 'emp_id' ),
	array( 'db' => '`u`.`uid`', 'dt' => 'uid', 'field' => 'uid' ),
	array( 'db' => '`u`.`emp_name_with_initial`', 'dt' => 'emp_name_with_initial', 'field' => 'emp_name_with_initial' ),
	array( 'db' => '`u`.`location`', 'dt' => 'location', 'field' => 'location' ),
	array( 'db' => '`u`.`dep_name`', 'dt' => 'dep_name', 'field' => 'dep_name' ),
	array( 'db' => '`u`.`formatted_date`',   'dt' => 'formatted_date', 'field' => 'formatted_date' ),
	array( 'db' => '`u`.`firsttimestamp`',   'dt' => 'firsttimestamp', 'field' => 'firsttimestamp' ),
	array( 'db' => '`u`.`lasttimestamp`',   'dt' => 'lasttimestamp', 'field' => 'lasttimestamp' ),
	array( 'db' => '`u`.`first_time_stamp`',   'dt' => 'first_time_stamp', 'field' => 'first_time_stamp' ),
	array( 'db' => '`u`.`last_time_stamp`',   'dt' => 'last_time_stamp', 'field' => 'last_time_stamp' )
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

$sql="SELECT `at1`.`id`, `at1`.`emp_id`, `at1`.`uid`, `employees`.`emp_name_with_initial`, `branches`.`location`, `departments`.`name` AS `dep_name`, `at1`.`date`, DATE_FORMAT(`at1`.`date`, '%Y-%m-%d') AS `formatted_date`, MIN(`at1`.`timestamp`) AS `firsttimestamp`, CASE WHEN MIN(`at1`.`timestamp`) = MAX(`at1`.`timestamp`) THEN NULL ELSE MAX(`at1`.`timestamp`) END AS `lasttimestamp`, DATE_FORMAT(MIN(`at1`.`timestamp`), '%H:%i') AS `first_time_stamp`, DATE_FORMAT(CASE WHEN MIN(`at1`.`timestamp`) = MAX(`at1`.`timestamp`) THEN NULL ELSE MAX(`at1`.`timestamp`) END, '%H:%i') AS `last_time_stamp` FROM `attendances` AS `at1` JOIN `employees` ON `at1`.`uid` = `employees`.`emp_id` LEFT JOIN `branches` ON `at1`.`location` = `branches`.`id` LEFT JOIN `departments` ON `departments`.`id` = `employees`.`emp_department` WHERE `at1`.`deleted_at` IS NULL";

if (!empty($_POST['department'])) {
    $department = $_POST['department'];
    $sql .= " AND `departments`.`id` = '$department'";
}
if (!empty($_POST['employee'])) {
    $employee = $_POST['employee'];
    $sql .= " AND `employees`.`emp_id` = '$employee'";
}
// if (!empty($_POST['location'])) {
//     $location = $_POST['location'];
//     $sql .= " AND `at1`.`location` = '$location'";
// }
if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $sql .= " AND `at1`.`date` BETWEEN '$from_date' AND '$to_date'";
}

$sql.=" GROUP BY `at1`.`uid`, `at1`.`date`";

$joinQuery = "FROM (".$sql.") as `u`";

$extraWhere = "";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);
?>