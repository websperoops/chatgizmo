<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.6                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('../config.php')) die('ajax/[clients.php] config.php not exist');
require_once '../config.php';

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) die("Nothing to see here");

if (!file_exists('../../class/ssp.class.php')) die('ajax/[ssp.class.php] config.php not exist');
require_once '../../class/ssp.class.php';

$superadmin = false;
if (JAK_USERID == 1) $superadmin = true; 

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("c", $jakuser->getVar("permissions"), $superadmin)) jak_redirect(BASE_URL);

$where = '';

// DB table to use
$table = JAKDB_PREFIX.'subscriptions AS t1';
$table2 = ' LEFT JOIN '.JAKDB_PREFIX.'users AS t2 ON (t1.userid = t2.opid AND t1.locationid = t2.locationid)';

// Table's primary key
$primaryKey = 't1.id';

// Ok, user is logged in let's get the settings table
$sett = array();
$settings = $jakdb->select("settings", ["varname", "used_value"]);
foreach ($settings as $v) {
    $sett[$v["varname"]] = $v["used_value"]; 
}

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 't1.id', 'dbjoin' => 'id', 'dt' => 0),
	array( 'db' => 't2.username', 'dbjoin' => 'username', 'dt' => 1, 'formatter' => function( $d, $row ) {
			return '<a href="'.str_replace('ajax/', '', JAK_rewrite::jakParseurl('c', 'e', $row['userid'])).'">'.$row["username"].'</a>';
		} ),
	array( 'db' => 't1.amount', 'dbjoin' => 'amount', 'dt' => 2, 'formatter' => function( $d, $row ) {
			return $row["amount"].' '.$row["currency"];
		} ),
	array( 'db' => 't1.subscribed', 'dbjoin' => 'subscribed', 'dt' => 3, 'formatter' => function( $d, $row ) {
			return (isset($d) && $d != 0 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
		} ),
	array( 'db' => 't1.paidhow', 'dbjoin' => 'paidhow', 'dt' => 4),
	array( 'db' => 't1.paidwhen', 'dbjoin' => 'paidwhen', 'dt' => 5, 'formatter' => function( $d, $row ) {
			global $sett;
			return ($row["paidwhen"] != "1980-05-06 00:00:00" ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 't1.paidtill', 'dbjoin' => 'paidtill', 'dt' => 6, 'formatter' => function( $d, $row ) {
			global $sett;
			return ($row["paidtill"] != "1980-05-06 00:00:00" ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 't1.active', 'dbjoin' => 'active', 'dt' => 7, 'formatter' => function( $d, $row ) {
			return (isset($d) && $d != 0 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
		} ),
	array( 'db' => 't1.success', 'dbjoin' => 'success', 'dt' => 8, 'formatter' => function( $d, $row ) {
			return (isset($d) && $d != 0 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');
		} ),
	array( 'db' => 't1.currency', 'dbjoin' => 'currency', 'dt' => 'tdc' ),
	array( 'db' => 't1.userid', 'dbjoin' => 'userid', 'dt' => 'tdcu' )
);

die(json_encode(SSP::join( $_GET, $table, $table2, $primaryKey, $columns, $where, $where )));
?>