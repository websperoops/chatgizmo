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
if (!JAK_USERID || !jak_get_access("l", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Sub page available
$subpage = false;

// Delete location
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2)) {

		// Delete the admin
		$jakdb->delete("coupons", ["id" => $page2]);

		// We have deleted the user
		$_SESSION["successmsg"] = $jkl['g16'];
		jak_redirect($_SESSION['LCRedirect']);

	} else {

		// No permission
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect($_SESSION['LCRedirect']);

	}

}

// Edit location
if ($page1 == "e") {

	$errors = array();
	$updatepass = false;

	if (JAK_USERID) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['title'])) {
		        $errors['e'] = $jkl['e11'];
		    }

		    if (empty($_POST['code'])) {
		        $errors['e1'] = $jkl['e11'];
		    }

		    if (jak_field_not_exist_id($_POST['code'],$page2,"coupons","code")) {
		        $errors['e2'] = $jkl['e29'];
		    }

		    // Dateformat check valid from
			if (isset($_POST['validfrom']) && !empty($_POST['validfrom'])) {
			    if (!validateDate($_POST['validtill'], "d.m.Y")) {
			    	$errors['e3'] = $jkl['e20'];
			    }
			} else {
			   	$_POST['validfrom'] = 0;
			}

			// Dateformat check valid till
			if (isset($_POST['validtill']) && !empty($_POST['validtill'])) {
			    if (!validateDate($_POST['validtill'], "d.m.Y")) {
			    	$errors['e4'] = $jkl['e20'];
			    }
			} else {
			   	$_POST['validtill'] = 0;
			}

		    if (count($errors) == 0) {

			   	if (!isset($_POST['products'])) {
			    	$depa = 0;
			    } else {
			    	$depa = join(',', $_POST['products']);
			    }

			    // We update the package
			     $result = $jakdb->update("coupons", ["locationid" => $_POST['locationid'],
			    	"title" => $_POST['title'],
					"description" => trim($_POST['desc']),
					"code" => $_POST['code'],
					"freepackageid" => trim($_POST['freepackageid']),
					"discount" => trim($_POST["discount"]),
					"used" => trim($_POST['used']),
					"total" => trim($_POST['total']),
					"datestart" => strtotime($_POST['validfrom']),
					"dateend" => strtotime($_POST['validtill']),
					"products" => $depa,
					"active" => trim($_POST['active']),
					"lastedit" => $jakdb->raw("NOW()")], ["id" => $page2]);

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect($_SESSION['LCRedirect']);

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get one package
		$coupons = $jakdb->get("coupons", "*", ["id" => $page2]);

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"]);
		// Get all packages
		$packages = $jakdb->select("packages", ["id", "locationid", "title", "amount", "currency"]);

		// Title and Description
		$SECTION_TITLE = $jkl['g184'];
		$SECTION_DESC = $jkl['g186'];

		// Include the javascript file for results
		$js_file_footer = 'js_coupons.php';

		// Call the template
		$template = 'editcoupon.php';

		$subpage = true;

	} else {
		// No permission
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

// Create new location
if ($page1 == "n") {

	$errors = array();
	$updatepass = false;

	if (JAK_USERID) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['title'])) {
		        $errors['e'] = $jkl['e11'];
		    }

		    if (empty($_POST['code'])) {
		        $errors['e1'] = $jkl['e11'];
		    }

		    if (jak_field_not_exist_id($_POST['code'],$page2,"coupons","code")) {
		        $errors['e2'] = $jkl['e29'];
		    }

		    // Dateformat check valid from
			if (isset($_POST['validfrom']) && !empty($_POST['validfrom'])) {
			    if (!validateDate($_POST['validtill'], "d.m.Y")) {
			    	$errors['e3'] = $jkl['e20'];
			    }
			} else {
			   	$_POST['validfrom'] = 0;
			}

			// Dateformat check valid till
			if (isset($_POST['validtill']) && !empty($_POST['validtill'])) {
			    if (!validateDate($_POST['validtill'], "d.m.Y")) {
			    	$errors['e4'] = $jkl['e20'];
			    }
			} else {
			   	$_POST['validtill'] = 0;
			}

		    if (count($errors) == 0) {

			   	if (!isset($_POST['products'])) {
			    	$depa = 0;
			    } else {
			    	$depa = join(',', $_POST['products']);
			    }

			    // We insert the package
			    $jakdb->insert("coupons", ["locationid" => $_POST['locationid'],
			    	"title" => $_POST['title'],
					"description" => trim($_POST['desc']),
					"code" => $_POST['code'],
					"freepackageid" => trim($_POST['freepackageid']),
					"discount" => trim($_POST["discount"]),
					"used" => trim($_POST['used']),
					"total" => trim($_POST['total']),
					"datestart" => strtotime($_POST['validfrom']),
					"dateend" => strtotime($_POST['validtill']),
					"products" => $depa,
					"active" => trim($_POST['active']),
					"lastedit" => $jakdb->raw("NOW()"),
					"created" => $jakdb->raw("NOW()")]);

			    $lastid = $jakdb->id();

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect(JAK_rewrite::jakParseurl('co', 'e', $lastid));

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"]);
		// Get all packages
		$packages = $jakdb->select("packages", ["id", "locationid", "title", "amount", "currency"]);

		// Title and Description
		$SECTION_TITLE = $jkl['g185'];
		$SECTION_DESC = $jkl['g187'];

		// Include the javascript file for results
		$js_file_footer = 'js_coupons.php';

		// Call the template
		$template = 'newcoupon.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

if (!$subpage) {

	// Get all packages
	$coupons = $jakdb->select("coupons", ["id", "locationid", "title", "code", "discount", "total", "used", "active"]);

	// Title and Description
	$SECTION_TITLE = $jkl['g182'];
	$SECTION_DESC = $jkl['g183'];

	// Call the template
	$template = 'coupons.php';
}

?>