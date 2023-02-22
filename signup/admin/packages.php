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

// Delete Package
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2)) {

		// Delete the admin
		$jakdb->delete("packages", ["id" => $page2]);

		// Delete the package_gateway assocation
		$jakdb->delete("package_gateways", ["packageid" => $page2]);

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
	$supackage = 0;

	if (JAK_USERID) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['title'])) {
		        $errors['e'] = $jkl['e11'];
		    }

		    if (count($errors) == 0) {

		    	// if we have a new standard package we remove the old one
			    if (isset($_POST['supackage']) && $_POST['supackage'] == 1) {
			    	$jakdb->update("packages", ["supackage" => 0], ["supackage" => 1]);

			    	$supackage = 1;
			    }

			    // We update the package
			    $result = $jakdb->update("packages", ["locationid" => $_POST['locationid'],
			    	"title" => $_POST['title'],
					"description" => trim($_POST['desc']),
					"previmg" => $_POST['previmg'],
					"amount" => trim($_POST['amount']),
					"currency" => trim($sett["currency"]),
					"chatwidgets" => trim($_POST['chatwidgets']),
					"groupchats" => trim($_POST['groupchats']),
					"operatorchat" => trim($_POST['operatorchat']),
					"operators" => trim($_POST['operators']),
					"departments" => trim($_POST['departments']),
					"files" => trim($_POST['files']),
					"copyfree" => trim($_POST['copyfree']),
					"activechats" => trim($_POST['activechats']),
					"chathistory" => trim($_POST['chathistory']),
					"islc3" => trim($_POST['islc3']),
					"ishd3" => trim($_POST['ishd3']),
					"validfor" => trim($_POST['validfor']),
					"multipleuse" => trim($_POST['multipleuse']),
					"isfree" => trim($_POST['isfree']),
					"active" => trim($_POST['active']),
					"supackage" => trim($supackage),
					"lastedit" => $jakdb->raw("NOW()")], ["id" => $page2]);

			    // Update the  payment gateways
			    if (isset($_POST['paygate']) && !empty($_POST['paygate'])) updatePaygate($_POST['locationid'], $page2, $_POST['paygate']);

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect($_SESSION['LCRedirect']);

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get one package
		$package = $jakdb->get("packages", "*", ["id" => $page2]);

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"]);

		// Get all payment gateways
		$paygate = $jakdb->select("payment_gateways", ["id", "title"], ["ORDER" => ["title" => "DESC"], "GROUP" => ["id"]]);

		// Get the selected payment gateways
		$paysel = $jakdb->select("package_gateways", "paygateid", ["packageid" => $package["id"]]);

		// Title and Description
		$SECTION_TITLE = $jkl['g166'];
		$SECTION_DESC = $jkl['g167'];

		// Include the javascript file for results
		$js_file_footer = 'js_package.php';

		// Call the template
		$template = 'editpackage.php';

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

		    if (count($errors) == 0) {

		    	// if we have a new standard package we remove the old one
			    if ($_POST['supackage'] == 1) {
			    	$jakdb->update("packages", ["supackage" => 0], ["supackage" => 1]);
			    }

			    // We insert the package
			    $jakdb->insert("packages", ["locationid" => $_POST['locationid'],
			    	"title" => $_POST['title'],
					"description" => trim($_POST['desc']),
					"previmg" => $_POST['previmg'],
					"amount" => trim($_POST['amount']),
					"currency" => trim($sett["currency"]),
					"chatwidgets" => trim($_POST['chatwidgets']),
					"groupchats" => trim($_POST['groupchats']),
					"operatorchat" => trim($_POST['operatorchat']),
					"operators" => trim($_POST['operators']),
					"departments" => trim($_POST['departments']),
					"files" => trim($_POST['files']),
					"copyfree" => trim($_POST['copyfree']),
					"activechats" => trim($_POST['activechats']),
					"chathistory" => trim($_POST['chathistory']),
					"islc3" => trim($_POST['islc3']),
					"ishd3" => trim($_POST['ishd3']),
					"validfor" => trim($_POST['validfor']),
					"multipleuse" => trim($_POST['multipleuse']),
					"isfree" => trim($_POST['isfree']),
					"active" => trim($_POST['active']),
					"supackage" => trim($_POST['supackage']),
					"lastedit" => $jakdb->raw("NOW()"),
					"created" => $jakdb->raw("NOW()")]);

			    $lastid = $jakdb->id();

			    // Update the  payment gateways
			    updatePaygate($_POST['locationid'], $lastid, $_POST['paygate']);

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect(JAK_rewrite::jakParseurl('pa', 'e', $lastid));

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"]);

		// Get all payment gateways
		$paygate = $jakdb->select("payment_gateways", ["id", "title"]);

		// Title and Description
		$SECTION_TITLE = $jkl['g158'];
		$SECTION_DESC = $jkl['g159'];

		// Include the javascript file for results
		$js_file_footer = 'js_package.php';

		// Call the template
		$template = 'newpackage.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

if (!$subpage) {
	// Get all packages
	$packages = $jakdb->select("packages", ["id", "locationid", "title", "amount", "currency", "operators", "departments", "files", "copyfree", "active"]);

	// Title and Description
	$SECTION_TITLE = $jkl['g111'];
	$SECTION_DESC = $jkl['g112'];

	// Call the template
	$template = 'packages.php';
}

?>