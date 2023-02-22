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

// No access
if (!JAK_USERID) jak_redirect(BASE_URL); 

// Sub page available
$subpage = false;

// Delete the user
if ($page1 == "d") {

	if (JAK_SUPERADMINACCESS && $page2 != JAK_USERID) {

		// Delete the admin
		$jakdb->delete("admins", ["id" => $page2]);

		// We have deleted the user
		$_SESSION["successmsg"] = $jkl['g16'];
		jak_redirect($_SESSION['LCRedirect']);

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e6'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

// Edit the user
if ($page1 == "e") {

	$errors = array();
	$updatepass = false;

	if (JAK_SUPERADMINACCESS || $page2 == JAK_USERID) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['name'])) {
		        $errors['e'] = $jkl['e11'];
		    }
		    
		    if ($_POST['email'] == '' || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		        $errors['e1'] = $jkl['e2'];
		    }
		    
		    if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST['username'])) {
		    	$errors['e2'] = $jkl['e1'];
		    }
		    
		    if (jak_field_not_exist_id($_POST['username'],$page2,"admins","username")) {
		        $errors['e3'] = $jkl['e3'];
		    }

		    if (jak_field_not_exist_id($_POST['email'],$page2,"admins","email")) {
		        $errors['e4'] = $jkl['e10'];
		    }
		    
		    if (!empty($_POST['pass']) || !empty($_POST['passc'])) {    
			    if ($_POST['pass'] != $_POST['passc']) {
			    	$errors['e5'] = $jkl['e8'];
			    } elseif (strlen($_POST['pass']) <= '7') {
			    	$errors['e6'] = $jkl['e9'];
			    } else {
			    	$updatepass = true;
			    }
		    }

		    if (count($errors) == 0) {

		    	if (!isset($_POST['permissions'])) {
			    	$perm = "";
			    } else {
			    	$perm = join(',', $_POST['permissions']);
			    }

			    // We update the user
			    if (JAK_USERID == 1) {
				    $result = $jakdb->update("admins", ["name" => $_POST['name'],
					"username" => trim($_POST['username']),
					"email" => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
					"language" => trim($_POST['jak_lang']),
					"picture" => $_POST['avatar'],
					"permissions" => $perm], ["id" => $page2]);
				} else {
					$result = $jakdb->update("admins", ["name" => $_POST['name'],
					"username" => trim($_POST['username']),
					"email" => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
					"language" => trim($_POST['jak_lang']),
					"picture" => $_POST['avatar']], ["id" => $page2]);
				}

				// We update the password
				if ($updatepass) $jakdb->update("admins", ["password" => hash_hmac('sha256', $_POST['pass'], DB_PASS_HASH)], ["id" => $page2]);

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect($_SESSION['LCRedirect']);

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get one user
		$user = $jakdb->get("admins", ["id", "name", "username", "email", "picture", "language", "permissions"], ["id" => $page2]);

		// Call the settings function
		$lang_files = jak_get_lang_files();

		// Title and Description
		$SECTION_TITLE = $jkl['g29'];
		$SECTION_DESC = $jkl['g30'];

		// Include the javascript file for results
		$js_file_footer = 'js_edituser.php';

		// Call the template
		$template = 'edituser.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be edited, as well yourself.
		$_SESSION["errormsg"] = $jkl['e7'];
		jak_redirect(JAK_rewrite::jakParseurl('u'));
	}

}

// Create a new user
if ($page1 == "n") {

	$errors = array();
	$updatepass = false;

	if (JAK_SUPERADMINACCESS) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['name'])) {
		        $errors['e'] = $jkl['e11'];
		    }
		    
		    if ($_POST['email'] == '' || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		        $errors['e1'] = $jkl['e2'];
		    }
		    
		    if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST['username'])) {
		    	$errors['e2'] = $jkl['e1'];
		    }
		    
		    if (jak_field_not_exist_id($_POST['username'],$page2,"admins","username")) {
		        $errors['e3'] = $jkl['e3'];
		    }

		    if (jak_field_not_exist_id($_POST['email'],$page2,"admins","email")) {
		        $errors['e4'] = $jkl['e10'];
		    }
		    
		    if (empty($_POST['pass']) && empty($_POST['passc'])) {    
			    if ($_POST['pass'] != $_POST['passc']) {
			    	$errors['e5'] = $jkl['e8'];
			    } elseif (strlen($_POST['pass']) <= '7') {
			    	$errors['e6'] = $jkl['e9'];
			    }
		    }

		    if (count($errors) == 0) {

		    	if (!isset($_POST['permissions'])) {
			    	$perm = "";
			    } else {
			    	$perm = join(',', $_POST['permissions']);
			    }

			    // We insert the user
			    $jakdb->insert("admins", ["name" => $_POST['name'],
					"username" => trim($_POST['username']),
					"email" => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
					"picture" => $_POST['avatar'],
					"language" => JAK_LANG,
					"password" => hash_hmac('sha256', $_POST['pass'], DB_PASS_HASH),
					"permissions" => $perm,
					"access" => 1]);

			    $lastid = $jakdb->id();

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect(JAK_rewrite::jakParseurl('u', 'e', $lastid));

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Title and Description
		$SECTION_TITLE = $jkl['g28'];
		$SECTION_DESC = $jkl['g38'];

		// Include the javascript file for results
		$js_file_footer = 'js_edituser.php';

		// Call the template
		$template = 'newuser.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e7'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

if (!$subpage) {

	// Get the license file
	require_once str_replace(JAK_ADMIN_LOC.DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.jaklic.php';
	$jaklic = new JAKLicenseAPI();

	// Check and validate
    $verify_response = $jaklic->verify_license(true);
    if ($verify_response['status'] != true) {
        if (JAK_SUPERADMINACCESS) {
            jak_redirect(JAK_rewrite::jakParseurl('m'));
        } else {
            $_SESSION["errormsg"] = $jkl['e37'];
            jak_redirect(BASE_URL);
        }
    }

	if (JAK_SUPERADMINACCESS) {
		// Get all users
		$users = $jakdb->select("admins", ["id", "name", "username", "email", "lastactivity"]);
	} else {
		// Get only one user
		$users = $jakdb->select("admins", ["id", "name", "username", "email", "lastactivity"], ["id" => JAK_USERID]);
	}

	// Title and Description
	$SECTION_TITLE = $jkl['g22'];
	$SECTION_DESC = $jkl['g23'];

	// Call the template
	$template = 'user.php';
}

?>