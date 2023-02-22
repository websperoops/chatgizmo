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

$where = 't1.lc3hd3 = '.$_SESSION["showlc3hd3"];

// DB table to use
$table = JAKDB_PREFIX.'advaccess AS t1';
$table2 = ' LEFT JOIN '.JAKDB_PREFIX.'users AS t2 ON (t1.userid = t2.id)';

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
	array( 'db' => 't2.username', 'dbjoin' => 'username', 'dt' => 1),
	array( 'db' => 't2.email', 'dbjoin' => 'email', 'dt' => 2),
	array( 'db' => 't1.url', 'dbjoin' => 'url', 'dt' => 3 ),
	array( 'db' => 't1.paidtill', 'dbjoin' => 'paidtill', 'dt' => 4, 'formatter' => function( $d, $row ) {
			global $sett;
			return (!empty($row["url"]) ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 't1.lastedit', 'dbjoin' => 'lastedit', 'dt' => 5, 'formatter' => function( $d, $row ) {
			global $sett;
			return ($row["lastedit"] ? JAK_base::jakTimesince($d, $sett["dateformat"], $sett["timeformat"]) : "-");
		} ),
	array( 'db' => 't1.id', 'dbjoin' => 'id', 'dt' => 6, 'formatter' => function( $d, $row ) {
			return '<a href="'.str_replace("ajax/", "", JAK_rewrite::jakParseurl("lc3", "e", $row["id"])).'"><i class="fa fa-edit"></i></a>';
		} ),
	array( 'db' => 't1.id', 'dbjoin' => 'id', 'dt' => 7, 'formatter' => function( $d, $row ) {
			return '<a href="'.str_replace("ajax/", "", JAK_rewrite::jakParseurl("lc3", "d", $row["id"])).'"><i class="fa fa-trash-o"></i></a>';
		} )
);

die(json_encode(SSP::join( $_GET, $table, $table2, $primaryKey, $columns, $where, $where )));
?>