<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1998 03:10:00 GMT");

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('config.php')) die('rest_api config.php not exist');
require_once 'config.php';

$username = $userpass = $token = $device = "";
if (isset($_REQUEST['username']) && !empty($_REQUEST['username'])) $username = $_REQUEST['username'];
if (isset($_REQUEST['password']) && !empty($_REQUEST['password'])) $userpass = $_REQUEST['password'];
if (isset($_REQUEST['device']) && !empty($_REQUEST['device'])) $device = $_REQUEST['device'];
if (isset($_REQUEST['token']) && !empty($_REQUEST['token'])) $token = $_REQUEST['token'];
if (isset($_REQUEST['appversion']) && !empty($_REQUEST['appversion'])) $appversion = $_REQUEST['appversion'];
if (isset($_REQUEST['appname']) && !empty($_REQUEST['appname'])) $appname = $_REQUEST['appname']; // (LC3 or HD3)

if (!empty($username) && !empty($userpass)) {

	// A few information from the device
	$valid_agent = filter_var($_REQUEST['device'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$valid_ip = filter_var($ipa, FILTER_VALIDATE_IP);
	$valid_username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$userpass = filter_var($userpass, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	    
	// Write the log file each time someone tries to login before
	JAK_base::jakWhatslog('', 0, 0, 0, 1, 0, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $valid_username, $device, $valid_ip, $valid_agent);

	$user_check = $jakuserlogin->jakCheckuserdata($valid_username, $userpass);
	if ($user_check == true) {
	    
	    // Now login in the user and return tha data
	    $user = $jakuserlogin->jakrestLogin($user_check, $userpass);

	    // Get the user details
	    $jakuser = new JAK_user($user);

	    // Now we get the siblings sorted
		$opcacheid = $jakuser->getVar("id");
		if ($jakuser->getVar("opid") != 0) $opcacheid = $jakuser->getVar("opid");

	    // finally update the push notification table if we need to do so, max 2 devices (One for Android and One for IOS)
	    if (!empty($device) && !empty($token)) $jakuserlogin->jakWriteDeviceToken($jakuser->getVar("id"), $opcacheid, $device, $token, $appname, $appversion);

		// Only the SuperAdmin in the config file see everything
		if ($jakuser->jakSuperadminaccess($jakuser->getVar("id"))) {
			$superadmin = true;
		} else {
			$superadmin = false;
		}

		// Write the log file each time someone login after to show success
	    JAK_base::jakWhatslog('', $opcacheid, $user_check["id"], 0, 2, 0, '', $user_check["username"], '', $valid_ip, '');

		// Get the maximum upload or set to 2
		$postmax = (ini_get('post_max_size') ? filter_var(ini_get('post_max_size'), FILTER_SANITIZE_NUMBER_INT) : "2");

		// Get the details from the cache file
		$cacheopid = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
    	if (file_exists($cacheopid)) include_once $cacheopid;

    	$sharefiles = 0;
    	if ($jakuser->getVar("files") && $jakosub['files']) $sharefiles = 1;

    	// timezone from server
		if (defined('JAK_TIMEZONESERVER')) date_default_timezone_set(JAK_TIMEZONESERVER);
		$jakdb->query('SET time_zone = "'.date("P").'"');
	      
	    // Return the json object 
	    die(json_encode(array('status' => true, 'userid' => $jakuser->getVar("id"), 'name' => $jakuser->getVar("name"), 'username' => $jakuser->getVar("username"), 'hash' => $jakuser->getVar("idhash"), 'email' => $jakuser->getVar("email"), 'picture' => JAK_FILES_DIRECTORY.$jakuser->getVar("picture"), 'responses' => $jakuser->getVar("responses"), 'files' => $sharefiles, 'transfer' => $jakuser->getVar("transferc"), 'permissions' => $jakuser->getVar("permissions"), 'superadmin' => $superadmin, 'postmaxsize' => $postmax, 'filetypes' => JAK_ALLOWEDO_FILES, 'urlabout' => "https://jakweb.ch", 'urlterms' => "https://jakweb.ch/terms-condition", 'urlprivacy' => "https://jakweb.ch/privacy")));
    }
}

die(json_encode(array('status' => false, 'errorcode' => 4)));
?>