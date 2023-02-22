<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1998 03:10:00 GMT");

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('config.php')) die('rest_api config.php not exist');
require_once 'config.php';

$userid = $loginhash = "";
if (isset($_REQUEST['userid']) && !empty($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])) $userid = $_REQUEST['userid'];
if (isset($_REQUEST['loginhash']) && !empty($_REQUEST['loginhash'])) $loginhash = $_REQUEST['loginhash'];

if (!empty($userid) && !empty($loginhash)) {

	// Let's check if we are logged in
	$usr = $jakuserlogin->jakCheckrestlogged($userid, $loginhash);

	if ($usr) {

		// Select the fields
		$jakuser = new JAK_user($usr);

		// Now we get the siblings sorted
		// Load the correct cache file
		$opcacheid = $jakuser->getVar("id");
		$mainop = true;
		if ($jakuser->getVar("opid") != 0) {
		    $opcacheid = $jakuser->getVar("opid");
		    $mainop = false;
		}

		// Ok, we have check for some data, pull it
		if (jak_get_access("leads_all", $jakuser->getVar("permissions"), $mainop)) {
			$data = $jakdb->select("sessions", ["[>]departments" => ["department" => "id"]], ["sessions.id", "sessions.usr_avatar", "sessions.name", "sessions.initiated", "sessions.operatorname", "sessions.status", "departments.title"], ["sessions.opid" => $opcacheid, "ORDER" => ["sessions.initiated" => "DESC"], "LIMIT" => 30]);
		} else {
			$data = $jakdb->select("sessions", ["[>]departments" => ["department" => "id"]], ["sessions.id", "sessions.usr_avatar", "sessions.name", "sessions.initiated", "sessions.operatorname", "sessions.status", "departments.title"], ["sessions.operatorid" => $userid, "ORDER" => ["sessions.initiated" => "DESC"], "LIMIT" => 30]);
		}

		if (isset($data) && !empty($data)) {
			die(json_encode(array('status' => true, 'data' => $data, 'filepath' => '', 'url' => BASE_URL)));
		} else {
			die(json_encode(array('status' => false, 'errorcode' => 9)));
		}

	} else {
		die(json_encode(array('status' => false, 'errorcode' => 1)));
	}
}

die(json_encode(array('status' => false, 'errorcode' => 7)));
?>