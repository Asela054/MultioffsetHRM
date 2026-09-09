<?php

$table = 'manpower_employee_details';

$primaryKey = 'id';

require('../config.php');
require('../ssp.customized.class.php');

$columns = array(
    array( 'db' => '`m`.`id`', 'dt' => 'id', 'field' => 'id' ),
    array( 'db' => '`mc`.`card_no`', 'dt' => 'card_no', 'field' => 'card_no' ),
    array( 'db' => '`m`.`date`', 'dt' => 'date', 'field' => 'date' ),
    array( 'db' => '`m`.`employee`', 'dt' => 'employee', 'field' => 'employee' ),
    array( 'db' => '`m`.`national_id`', 'dt' => 'national_id', 'field' => 'national_id' ),
    array( 'db' => '`m`.`off_next_day`', 'dt' => 'off_next_day', 'field' => 'off_next_day' ),
    array( 'db' => '`m`.`company`', 'dt' => 'company', 'field' => 'company' ),
    array( 'db' => '`m`.`phone`', 'dt' => 'phone', 'field' => 'phone' ),
    array( 'db' => '`m`.`status`', 'dt' => 'status', 'field' => 'status' )
);

$sql_details = array(
    'user' => $db_username,
    'pass' => $db_password,
    'db'   => $db_name,
    'host' => $db_host
);

$joinQuery = "FROM `manpower_employee_details` AS `m` LEFT JOIN `manpower_cards` AS `mc` ON `mc`.`id` = `m`.`card_id`";

$extraWhere = "`m`.`status` IN (1,2)";

echo json_encode(
	SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere)
);

?>
