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
$table = JAKDB_PREFIX.'users';

// Table's primary key
$primaryKey = 'id';

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
	array( 'db' => 'id', 'dbjoin' => 'id', 'dt' => 0 ),
	array( 'db' => 'username', 'dbjoin' => 'username', 'dt' => 1, 'formatter' => function( $d, $row ) {
			return '<a href="'.str_replace('ajax/', '', JAK_rewrite::jakParseurl('c', 'e', $row['id'])).'">'.$d.'</a>';
		} ),
	array( 'db' => 'email', 'dbjoin' => 'email', 'dt' => 2),
	array( 'db' => 'locationid', 'dbjoin' => 'locationid', 'dt' => 3 ),
	array( 'db' => 'paidtill', 'dbjoin' => 'paidtill', 'dt' => 4, 'formatter' => function( $d, $row ) {
			global $sett;
			return ($row["confirm"] == 0 ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 'lastedit', 'dbjoin' => 'lastedit', 'dt' => 5, 'formatter' => function( $d, $row ) {
			global $sett;
			return ($row["lastedit"] ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 'confirm', 'dbjoin' => 'confirm', 'dt' => 6, 'formatter' => function( $d, $row ) {
			return ($d ? '<i class="fa fa-exclamation-triangle"></i> <a href="'.str_replace("ajax/", "", JAK_rewrite::jakParseurl("c", "c", $row["id"])).'"><i class="fa fa-envelope-o"></i></a>' : '<i class="fa fa-check"></i>');
		} ),
	array( 'db' => 'id', 'dbjoin' => 'id', 'dt' => 7, 'formatter' => function( $d, $row ) {
			return '<a href="'.str_replace("ajax/", "", JAK_rewrite::jakParseurl("c", "e", $row["id"])).'"><i class="fa fa-edit"></i></a>';
		} ),
	array( 'db' => 'id', 'dbjoin' => 'id', 'dt' => 8, 'formatter' => function( $d, $row ) {
			return ($row["id"] != 1 ? '<a href="'.str_replace("ajax/", "", JAK_rewrite::jakParseurl("c", "d", $row["id"])).'"><i class="fa fa-trash-o"></i></a>' : '');
		} )
);

die(json_encode(SSP::simple( $_GET, $table, $primaryKey, $columns, $where, $where )));
?>