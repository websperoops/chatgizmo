<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// prevent direct php access
define('JAK_PREVENT_ACCESS', 1);

if (!file_exists('config.php')) die('[index.php] config.php not exist');
require_once 'config.php';

// Language
$BT_LANGUAGE = JAK_LANG;

// Get the language file if different from settings
if (isset($widgetlang) && !empty($widgetlang) && $widgetlang != JAK_LANG) $BT_LANGUAGE = $widgetlang;

// Import the language file
if ($BT_LANGUAGE && file_exists(APP_PATH.'lang/'.strtolower($BT_LANGUAGE).'.php')) {
    include_once(APP_PATH.'lang/'.strtolower($BT_LANGUAGE).'.php');
} else {
    include_once(APP_PATH.'lang/'.JAK_LANG.'.php');
}

// If Referer Zero go to the session url
if (!isset($_SERVER['HTTP_REFERER'])) {
	if (isset($_SESSION['jaklastURL'])) {
    	$_SERVER['HTTP_REFERER'] = $_SESSION['jaklastURL'];
    } else {
    	$_SERVER['HTTP_REFERER'] = BASE_URL;
    }
}

// Lang and pages file for template
define('JAK_SITELANG', JAK_LANG);

// Assign Pages to template
define('JAK_PAGINATE_ADMIN', 0);

// Define the avatarpath in the settings
define('JAK_FILEPATH_BASE', BASE_URL.JAK_FILES_DIRECTORY);

// Define the real request
$realrequest = substr($getURL->jakRealrequest(), 1);
define('JAK_PARSE_REQUEST', $realrequest);

// Check if the ip or range is blocked, if so redirect to offline page with a message
$USR_IP_BLOCKED = false;
if (JAK_IP_BLOCK) {
	$blockedips = explode(',', JAK_IP_BLOCK);
	// Do we have a range
	if (is_array($blockedips)) foreach ($blockedips as $bip) {
		$blockedrange = explode(':', $bip);
		
		if (is_array($blockedrange)) {
		
			$network=ip2long($blockedrange[0]);
			$mask=ip2long($blockedrange[1]);
			$remote=ip2long($ipa);
			
			if (($remote & $mask) == $network) {
			    $USR_IP_BLOCKED = $jkl['e11'];
			}	
		}
	}
	// Now let's check if we have another match
	if (in_array($ipa, $blockedips)) {
		$USR_IP_BLOCKED = $jkl['e11'];
	}
}

// Now get the available departments
$online_op = false;
if (JAK_HOLIDAY_MODE != 0) {
	$online_op = false;
} else {
	if (isset($widgetid)) $online_op = online_operators($opcacheid, $LC_DEPARTMENTS, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['singleopid']);
}

// Set the check page to 0
$JAK_CHECK_PAGE = 0;
	
	// Link we need a redirect
	if ($page == 'link') {
		$_SESSION['islinked'] = true;
		// We set the session for this linked chat
		create_session_id($opcacheid, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid'], $ipa);
		// We redirect to the chat and make it open
		jak_redirect(JAK_rewrite::jakParseurl('lc', 'open', $widgetid, $widgetlang));
	}
	// The chat class
	if ($page == 'lc') {
		require_once 'lc.php';
		$JAK_CHECK_PAGE = 1;
		$PAGE_SHOWTITLE = 1;
	}
	// Group Chat
	if ($page == 'groupchat') {
		require_once 'groupchat.php';
		$JAK_CHECK_PAGE = 1;
		$PAGE_SHOWTITLE = 1;
	}
    // Get the 404 page
   	if ($page == '404') {
   	    $PAGE_TITLE = '404 ';
   	    require_once '404.php';
   	    $JAK_CHECK_PAGE = 1;
   	    $PAGE_SHOWTITLE = 1;
   	}

// if page not found
if ($JAK_CHECK_PAGE == 0) jak_redirect(BASE_URL."signup/");
?>