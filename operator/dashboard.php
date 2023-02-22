<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Reset
$opmain = '';
$count = 0;

// Change for 3.0.3
use JAKWEB\JAKsql;

// Statistics
$sessCtotal = $commCtotal = $statsCtotal = $visitCtotal = 0;
if (jak_get_access("statistic_all", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	// Get the stats
	$sessCtotal = $jakdb->count("sessions", ["opid" => $opcacheid]);
	$commCtotal = $jakdb->count("transcript", ["[>]sessions" => ["convid" => "id"]], "transcript.id", ["sessions.opid" => $opcacheid]);
	$statsCtotal = $jakdb->count("user_stats", ["OR" => ["userid" => JAK_USERID, "userid" => $opcacheid]]);
	$visitCtotal = $jakdb->count("buttonstats", ["opid" => $opcacheid]);
		
} else {

	// Get the stats
	$sessCtotal = $jakdb->count("sessions", ["AND" => ["opid" => $opcacheid, "operatorid" => JAK_USERID]]);
	// Get all convid into an array
	$sessids = $jakdb->select("sessions", "id", ["AND" => ["opid" => $opcacheid, "operatorid" => JAK_USERID]]);
	// Get all messages from the convids
	if ($sessids) {
		$commCtotal = $jakdb->count("transcript", ["convid" => $sessids]);
		$statsCtotal = $jakdb->count("user_stats", ["OR" => ["userid" => JAK_USERID, "userid" => $opcacheid]]);
		$visitCtotal = $jakdb->count("buttonstats", ["AND" => ["opid" => $opcacheid, "depid" => [$jakuser->getVar("departments")]]]);
	}

}

// Get the open chats for this operator
$statschat = false;
$openChats = 0;
if (jak_get_access("leads", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	if (jak_get_access("leads_all", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

		$openChats = $jakdb->select("sessions", ["id", "name", "initiated"], ["opid" => $opcacheid, "ORDER" => ["id" => "DESC"], "LIMIT" => 10]);

	} else {

		$openChats = $jakdb->select("sessions", ["id", "name", "initiated"], ["AND" => ["opid" => $opcacheid, "operatorid" => $jakuser->getVar("id")], "ORDER" => ["id" => "DESC"], "LIMIT" => 10]);

	}

}

// Get the offline messages if allowed
$statscontact = false;
$openContacts = 0;
if (jak_get_access("off_all", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	$openContacts = $jakdb->select("contacts", ["id", "name", "email", "sent"], ["opid" => $opcacheid, "ORDER" => ["id" => "DESC"], "LIMIT" => 10]);

}

// Get the country list
$ctl = $jakdb->pdo->prepare("SELECT COUNT(id) AS total_country, countrycode, country FROM ".JAKDB_PREFIX."buttonstats WHERE opid = ".$opcacheid." AND countrycode != '' GROUP BY countrycode ORDER BY total_country DESC LIMIT 8");

$ctl->execute();

$ctlres = $ctl->fetchAll();

// Get the public operator chat, check if we have access
if ($jakosub['groupchats']) {
	$gcarray = array();
	$JAK_PUBLICCHAT = $jakdb->select("groupchat", ["id", "title", "opids", "lang"], ["AND" => ["opid" => $opcacheid, "active" => 1]]);
	if (isset($JAK_PUBLICCHAT) && !empty($JAK_PUBLICCHAT)) foreach ($JAK_PUBLICCHAT as $gc) {
		// Let's check if we have access
		if ($gc["opids"] == 0 || in_array(JAK_USERID, explode(",", $gc["opids"]))) {
			$gcarray[] = $gc;
		}
	}
}

// Title and Description
$SECTION_TITLE = $jkl['m'];
$SECTION_DESC = "";

// Include the javascript file for results
$js_file_footer = 'js_dashboard.php';
// Call the template
$template = 'dashboard.php';

?>