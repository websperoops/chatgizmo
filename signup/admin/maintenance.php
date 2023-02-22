<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("s", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Get the license file
require_once str_replace(JAK_ADMIN_LOC.DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.jaklic.php';
$jaklic = new JAKLicenseAPI();

// Check and validate
$verify_response = $jaklic->verify_license(false);
$licmsg = $verify_response['message'];

// Flag to select step
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jkp = $_POST;

if (isset($jkp['optimize'])) {
	
	$tables = $jakdb->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $db => $tablename) { 
        $jakdb->query('OPTIMIZE TABLE '.$tablename);
    }

    $_SESSION["successmsg"] = $jkl['g16'];
    jak_redirect($_SESSION['LCRedirect']);

}

if (isset($jkp['regLicense'])) {

	if (!empty($_POST['jak_lic']) && !empty($_POST['jak_licusr'])) {
		$license_code = strip_tags(trim($_POST["jak_lic"]));
	  	$env_name = strip_tags(trim($_POST["jak_licusr"]));

		// Now let's check the license
	  	$activate_response = $jaklic->activate_license($license_code, $env_name);
	  	if (empty($activate_response)) {
			$errors['e1'] = LB_TEXT_CONNECTION_FAILED;
	  	}

	  	if ($activate_response['status'] != true) { 
	    	$errors['e1'] = $activate_response['message'];
	  	} else {

	  		// We update the order number
	  		$jakdb->update("settings", ["used_value" => filter_var($_POST['jak_lic'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)], ["varname" => "onumber"]);

		  	$_SESSION["successmsg"] = $jkl['g16'];
	    	jak_redirect($_SESSION['LCRedirect']);
	    }

	} else {
		$errors['e1'] = $jkl['e37'];
		$errors['e2'] = $jkl['e1'];
	}

}

if (isset($jkp['deregLicense']) && JAK_SUPERADMINACCESS) {

	$deactivate_response = $jaklic->deactivate_license();
    if (empty($deactivate_response)) {
    	$errors['e1'] = LB_TEXT_CONNECTION_FAILED;
    }

    if ($deactivate_response['status'] != true) { 
	    $errors['e1'] = $deactivate_response['message'];
	} else {
		$_SESSION["successmsg"] = $jkl['g16'];
	    jak_redirect($_SESSION['LCRedirect']);
	}

}

}

// Title and Description
$SECTION_TITLE = $jkl["g225"];
$SECTION_DESC = "";

// Call the template
$template = 'maintenance.php';

?>