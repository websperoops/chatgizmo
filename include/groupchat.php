<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1998 03:10:00 GMT");

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5.3                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_GET['id']) && !is_numeric($_GET['id'])) die(json_encode(array('status' => false, 'error' => "No valid ID.")));

if (!file_exists('../config.php')) die('include/[groupchat.php] config.php not exist');
require_once '../config.php';

// We do not load any widget code if we are on hosted and and expiring date is true.
if ((isset($jakosub['groupchats']) && $jakosub['groupchats'] == 0) || (isset($jakosub['active']) && $jakosub['active'] == 0)) die(json_encode(array('status' => false, 'error' => "Account expired or no access to group chats.")));

// Some reset
$widgethtml = $floatstyle = '';

// Get the client browser
$ua = new Browser();

// Is a robot just die
if ($ua->isRobot()) die(json_encode(array('status' => false, 'error' => "Robots do not need a live chat.")));

// Now check the button id
if (file_exists($cacheopid)) {

	// Group Chat is online show it
	if (isset($groupchat[$_GET['id']]["active"]) && $groupchat[$_GET['id']]["active"] == 1) {

		// Float button? Position
		$floatstyle = '';
		if ($groupchat[$_GET['id']]['floatpopup'] && !empty($groupchat[$_GET['id']]['floatcss'])) $floatstyle = ' style="position:fixed;z-index:9999;'.$groupchat[$_GET['id']]['floatcss'].'"';

		$widgethtml = '<a href="'.str_replace('include/', '', JAK_rewrite::jakParseurl('groupchat', $_GET['id'], $groupchat[$_GET['id']]['lang'])).'" target="_blank"'.$floatstyle.'><img src="'.str_replace('include/', '', BASE_URL).JAK_FILES_DIRECTORY.'/buttons/'.$groupchat[$_GET['id']]['buttonimg'].'"></a>';

		die(json_encode(array('status' => true, 'title' => $groupchat[$_GET['id']]['title'], 'widgethtml' => $widgethtml)));

	// Chat is offline show nothing
	} else {
		die(json_encode(array('status' => false, 'error' => "Group Chat is offline")));
	}

} else {
	die(json_encode(array('status' => false, 'error' => "No Group Chat available with this ID.")));
}
?>