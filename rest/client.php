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

$userid = $loginhash = $chatid = "";
$sendform = false;
if (isset($_REQUEST['userid']) && !empty($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])) $userid = $_REQUEST['userid'];
if (isset($_REQUEST['loginhash']) && !empty($_REQUEST['loginhash'])) $loginhash = $_REQUEST['loginhash'];
if (isset($_REQUEST['chatid']) && !empty($_REQUEST['chatid'])) $chatid = $_REQUEST['chatid'];
if (isset($_REQUEST['sendform']) && !empty($_REQUEST['sendform'])) $sendform = $_REQUEST['sendform'];

if (!empty($userid) && !empty($loginhash) && !empty($chatid) && is_numeric($chatid)) {

	// Let's check if we are logged in
	$usr = $jakuserlogin->jakCheckrestlogged($userid, $loginhash);

	if ($usr) {

		// Select the user fields
		$jakuser = new JAK_user($usr);
		// Now we get the siblings sorted
		$opcacheid = $jakuser->getVar("id");
		if ($jakuser->getVar("opid") != 0) $opcacheid = $jakuser->getVar("opid");

		if ($sendform) {

			$jakdb->update("transcript", ["name" => $_REQUEST['name']], ["AND" => ["convid" => $chatid, "class" => "user"]]);

			$jakdb->update("checkstatus", ["datac" => 1], ["convid" => $chatid]);

			$email = $phone = $note = "";
			if (isset($_REQUEST['email']) && !empty($_REQUEST['email'])) $email = $_REQUEST['email'];
			if (isset($_REQUEST['phone']) && !empty($_REQUEST['phone'])) $phone = $_REQUEST['phone'];
			if (isset($_REQUEST['note']) && !empty($_REQUEST['note'])) $note = $_REQUEST['note'];
			$result = $jakdb->update("sessions", ["name" => $_REQUEST['name'], "email" => $email, "phone" => $phone, "notes" => $note], ["AND" => ["id" => $chatid, "opid" => $opcacheid]]);

			// Form has been sent, let's send the success status
			die(json_encode(array('status' => true)));

		}

		// Let's check if the chat exists and pull the data
		$data = $jakdb->get("sessions", ["[>]buttonstats" => ["session" => "session"]], ["sessions.id", "sessions.usr_avatar", "sessions.name", "sessions.email", "sessions.phone", "sessions.country", "sessions.city", "sessions.latitude", "sessions.longitude", "sessions.notes", "sessions.countrycode", "sessions.initiated", "sessions.operatorname", "buttonstats.referrer", "buttonstats.agent"], ["AND" => ["sessions.id" => $chatid, "sessions.opid" => $opcacheid]]);

		if (isset($data) && !empty($data)) {
			die(json_encode(array('status' => true, 'data' => $data)));
		} else {
			die(json_encode(array('status' => false, 'errorcode' => 9)));
		}

	} else {
		die(json_encode(array('status' => false, 'errorcode' => 1)));
	}
}

die(json_encode(array('status' => false, 'errorcode' => 7)));
?>