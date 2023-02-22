<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("l", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Sub page available
$subpage = false;
$errors = array();

// Change for 1.0.3
use JAKWEB\JAKsql;

// Test location
if ($page1 == "c") {

	if (isset($page2) && is_numeric($page2)) {

		// Get one location
		$connect = $jakdb->get("locations", ["id", "title", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $page2]);

		if ($connect["db_host"] && $connect["db_user"] && $connect["db_pass"] && $connect["db_name"]) {

			$jakdb1 = new JAKsql([
			// required
			'database_type' => $connect["db_type"],
			'database_name' => $connect["db_name"],
			'server' => $connect["db_host"],
			'username' => $connect["db_user"],
			'password' => $connect["db_pass"],
			'charset' => 'utf8',
			'port' => $connect["db_port"],
			'prefix' => $connect["db_prefix"]
			]);

			if ($jakdb1->has("settings", ["varname" => "updated"])) {

				// We have deleted the user
				$_SESSION["successmsg"] = $jkl['e17'];
				jak_redirect($_SESSION['LCRedirect']);

			} else {

				// No data set
				$_SESSION["errormsg"] = $jkl['e16'];
				jak_redirect($_SESSION['LCRedirect']);

			}

		} else {

			// No data set
			$_SESSION["errormsg"] = $jkl['e16'];
			jak_redirect($_SESSION['LCRedirect']);

		}

	} else {

		// No permission
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect($_SESSION['LCRedirect']);

	}

}

if ($page1 == "u") {

	if (isset($page2) && is_numeric($page2)) {

		// Get one location
		$location = $jakdb->get("locations", ["id", "title"], ["AND" => ["id" => $page2, "lc3hd3" => 0]]);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (empty($_POST['title'])) {
		        $errors['e'] = $jkl['e40'];
		    }
		    
		    if (empty($_POST['lang'])) {
		        $errors['e2'] = $jkl['e41'];
		    }

		    if (empty($_POST['smsmsg'])) {
		        $errors['e3'] = $jkl['e11'];
		    }

		    if (empty($_POST['chat_dep'])) {
		        $errors['e4'] = $jkl['e11'];
		    }

		    if (empty($_POST['chat_dep_desc'])) {
		        $errors['e5'] = $jkl['e11'];
		    }

		    if (empty($_POST['cw_title'])) {
		        $errors['e6'] = $jkl['e11'];
		    }

		    if (empty($_POST['cw_template'])) {
		        $errors['e7'] = $jkl['e11'];
		    }

		    if (empty($_POST['gc_title'])) {
		        $errors['e9'] = $jkl['e11'];
		    }

		    if (count($errors) == 0) {

		    	// Update the title
		    	$jakdb->update("opsettings", ["used_value" => $_POST['title']], ["AND" => ["locid" => $page2, "varname" => "title"]]);

		    	// Update the timezone
		    	$jakdb->update("opsettings", ["used_value" => $_POST['timezone_server']], ["AND" => ["locid" => $page2, "varname" => "timezoneserver"]]);

		    	// Update the date
		    	$jakdb->update("opsettings", ["used_value" => $_POST['dateformat']], ["AND" => ["locid" => $page2, "varname" => "dateformat"]]);

		    	// Update the time
		    	$jakdb->update("opsettings", ["used_value" => $_POST['timeformat']], ["AND" => ["locid" => $page2, "varname" => "timeformat"]]);

		    	// Update the sms message
		    	$jakdb->update("opsettings", ["used_value" => $_POST['smsmsg']], ["AND" => ["locid" => $page2, "varname" => "tw_msg"]]);

		    	// Update the avatar height
		    	$jakdb->update("opsettings", ["used_value" => $_POST['avaheight']], ["AND" => ["locid" => $page2, "varname" => "useravatheight"]]);

		    	// Update the avatar width
		    	$jakdb->update("opsettings", ["used_value" => $_POST['avawidth']], ["AND" => ["locid" => $page2, "varname" => "useravatwidth"]]);

		    	if ($_POST['lang_old'] != $_POST['lang']) {
			    	// Update the language
			    	$jakdb->update("opsettings", ["used_value" => $_POST['lang']], ["AND" => ["locid" => $page2, "varname" => "lang"]]);
			    	// We update the answers
			    	$jakdb->update("answers", ["lang" => $_POST['lang']], ["AND" => ["locid" => $page2, "lang" => $_POST['lang_old']]]);
			    	// We upate the chat widget
			    	$jakdb->update("chatwidget", ["lang" => $_POST['lang']], ["AND" => ["locid" => $page2, "lang" => $_POST['lang_old']]]);
			    	// We upate the group chat
			    	$jakdb->update("groupchat", ["lang" => $_POST['lang']], ["AND" => ["locid" => $page2, "lang" => $_POST['lang_old']]]);
			    }

		    	// Update the chat department
		    	$jakdb->update("departments", ["title" => $_POST['chat_dep'], "description" => $_POST['chat_dep_desc']], ["locid" => $page2]);

		    	// Update the chat widget
		    	$jakdb->update("chatwidget", ["title" => $_POST['cw_title'], "template" => $_POST['cw_template']], ["locid" => $page2]);

		    	// Update the group chat
		    	$jakdb->update("groupchat", ["title" => $_POST['gc_title'], "description" => $_POST['gc_description']], ["locid" => $page2]);

		    	// Now the dirty work going through the foreach stuff

		    	// Answers
		    	if (isset($_POST['answer_id']) && !empty($_POST['answer_id'])) foreach ($_POST['answer_id'] as $as) {
		    		# code...
		    		if (isset($_POST['answer_title_'.$as]) && !empty($_POST['answer_title_'.$as]) && isset($_POST['answer_content_'.$as]) && !empty($_POST['answer_content_'.$as])) {
		    			$jakdb->update("answers", ["title" => $_POST['answer_title_'.$as], "message" => $_POST['answer_content_'.$as]], ["AND" => ["id" => $as, "locid" => $page2]]);
		    		}
		    	}

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect($_SESSION['LCRedirect']);

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get the settings for this location
		$opsett = array();
		$opsettings = $jakdb->select("opsettings", ["varname", "used_value"], ["locid" => $page2]);
		foreach ($opsettings as $v) {
		    $opsett[$v["varname"]] = $v["used_value"]; 
		}

		// Get the chat department
		$opdep = $jakdb->get("departments", ["title", "description"], ["locid" => $page2]);

		// Get the chat widget
		$opchatwidget = $jakdb->get("chatwidget", ["lang", "title", "template"], ["locid" => $page2]);

		// Get the group chat
		$opgroupchat = $jakdb->get("groupchat", ["title", "description", "lang"], ["locid" => $page2]);

		// Get answers
		$answers = $jakdb->select("answers", ["id", "title", "lang", "message"], ["locid" => $page2]);

		// Title and Description
		$SECTION_TITLE = $jkl['g265'];
		$SECTION_DESC = sprintf($jkl['g266'], $location["title"]);

		// Call the template
		$template = 'setupstandards.php';

		$subpage = true;

	} else {
		// No permission
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect($_SESSION['LCRedirect']);
	}	

}

// Delete location
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2)) {

		if ($jakdb->has("users", ["locationid" => $page2])) {

			// No permission
			$_SESSION["errormsg"] = $jkl['e13'];
			jak_redirect($_SESSION['LCRedirect']);

		} else {

			// Delete the admin
			$jakdb->delete("locations", ["id" => $page2]);

			// Now we delete all the entries for this location

			// Delete the chat department
			$jakdb->delete("departments", ["locid" => $page2]);

			// Delete answers
			$jakdb->delete("answers", ["locid" => $page2]);

			// Delete chatwidget
			$jakdb->delete("chatwidget", ["locid" => $page2]);

			// Delete groupchat
			$jakdb->delete("groupchat", ["locid" => $page2]);

			// We have deleted the user
			$_SESSION["successmsg"] = $jkl['g16'];
			jak_redirect($_SESSION['LCRedirect']);

		}

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
		    
		    if (empty($_POST['url'])) {
		        $errors['e1'] = $jkl['e14'];
		    }

		    if (count($errors) == 0) {

			    // We update the location
			    $result = $jakdb->update("locations", ["title" => $_POST['title'],
			    	"lc3hd3" => $_POST['lc3hd3'],
					"url" => trim($_POST['url']),
					"db_host" => trim($_POST['db_host']),
					"db_type" => trim($_POST['db_type']),
					"db_port" => trim($_POST['db_port']),
					"db_user" => trim($_POST['db_user']),
					"db_pass" => trim($_POST['db_pass']),
					"db_name" => trim($_POST['db_name']),
					"db_prefix" => trim($_POST['db_prefix']),
					"lastedit" => $jakdb->raw("NOW()")], ["id" => $page2]);

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect($_SESSION['LCRedirect']);

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get one location
		$location = $jakdb->get("locations", ["id", "title", "lc3hd3", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $page2]);

		// Title and Description
		$SECTION_TITLE = $jkl['g45'];
		$SECTION_DESC = $jkl['g46'];

		// Call the template
		$template = 'editlocation.php';

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
		    
		    if (empty($_POST['url'])) {
		        $errors['e1'] = $jkl['e14'];
		    }

		    if (isset($_POST["db_port"]) && !is_numeric($_POST["db_port"])) {
		    	$errors['e2'] = $jkl['e15'];
		    }

		    if (count($errors) == 0) {

		    	$lc3hd3 = 0;
		    	if (isset($_POST["lc3hd3"]) && $_POST["lc3hd3"] == 1) $lc3hd3 = 1;

			    // We insert the user
			    $jakdb->insert("locations", ["title" => $_POST['title'],
			    	"lc3hd3" => $lc3hd3,
					"url" => trim($_POST['url']),
					"db_host" => trim($_POST['db_host']),
					"db_type" => trim($_POST['db_type']),
					"db_port" => trim($_POST['db_port']),
					"db_user" => trim($_POST['db_user']),
					"db_pass" => trim($_POST['db_pass']),
					"db_name" => trim($_POST['db_name']),
					"db_prefix" => trim($_POST['db_prefix']),
					"lastedit" => $jakdb->raw("NOW()")]);

			    $lastid = $jakdb->id();

			    // We add the main settings for this location
			    if ($lc3hd3 == 0) {

			    	$jakdb->query("INSERT INTO ".JAKDB_PREFIX."opsettings (`locid`, `varname`, `used_value`, `default_value`) VALUES
				        (".$lastid.", 'dateformat', 'd.m.Y', 'd.m.Y'),
				        (".$lastid.", 'timeformat', 'g:i a', 'g:i a'),
				        (".$lastid.", 'timezoneserver', 'Europe/Zurich', 'Europe/Zurich'),
				        (".$lastid.", 'lang', '".JAK_LANG."', '".JAK_LANG."'),
				        (".$lastid.", 'title', 'Cloud Chat 3', 'Cloud Chat 3'),
				        (".$lastid.", 'tw_msg', 'A customer is requesting attention.', 'A customer is requesting attention.'),
				        (".$lastid.", 'useravatheight', '250', '250'),
				        (".$lastid.", 'useravatwidth', '250', '250')");

					// Chat Widget
					$jakdb->insert("chatwidget", ["locid" => $lastid, "title" => "Live Support Chat", "lang" => JAK_LANG, "hidewhenoff" => 0, "template" => "business", "created" => $jakdb->raw("NOW()")]);

					// Group Chat
					$jakdb->insert("groupchat", ["locid" => $lastid, "title" => "Weekly Support", "maxclients" => 10, "lang" => JAK_LANG, "buttonimg" => "colour_on.png", "floatpopup" => 0, "floatcss" => "bottom:20px;left:20px", "active" => 0, "created" => $jakdb->raw("NOW()")]);

					// Insert the chat department
					$jakdb->insert("departments", ["locid" => $lastid, "title" => "Chat", "description" => "About the Chat Department", "active" => 1, "dorder" => 1, "time" => $jakdb->raw("NOW()")]);
					  
					// Insert the answers
					$jakdb->insert("answers", [["locid" => $lastid, "lang" => JAK_LANG, "title" => "Enters Chat", "message" => "%operator% enters the chat.", "fireup" => 15, "msgtype" => 2, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Expired", "message" => "This session has expired!", "fireup" => 15, "msgtype" => 4, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Ended", "message" => "%client% has ended the conversation", "fireup" => 15, "msgtype" => 3, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Welcome", "message" => "Welcome %client%, a representative will be with you shortly.", "fireup" => 15, "msgtype" => 5, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Leave", "message" => "has left the conversation.", "fireup" => 15, "msgtype" => 6, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Start Page", "message" => "Please insert your name to begin, a representative will be with you shortly.", "fireup" => 15, "msgtype" => 7, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Contact Page", "message" => "None of our representatives are available right now, although you are welcome to leave a message!", "fireup" => 15, "msgtype" => 8, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Feedback Page", "message" => "We would appreciate your feedback to improve our service.", "fireup" => 15, "msgtype" => 9, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Quickstart Page", "message" => "Please type a message and hit enter to start the conversation.", "fireup" => 15, "msgtype" => 10, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Group Chat Welcome Message", "message" => "Welcome to our weekly support session, sharing experience and feedback.", "fireup" => 15, "msgtype" => 11, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Group Chat Offline Message", "message" => "The public chat is offline at this moment, please try again later.", "fireup" => 15, "msgtype" => 12, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Group Chat Full Message", "message" => "The public chat is full, please try again later.", "fireup" => 15, "msgtype" => 13, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "Select Operator", "message" => "Please select an operator of your choice and add your name and message to start a conversation.", "fireup" => 15, "msgtype" => 14, "created" => $jakdb->raw("NOW()")],
  						["locid" => $lastid, "lang" => JAK_LANG, "title" => "Expired Soft", "message" => "The chat has been ended due the inactivity, please type a message to restart again.", "fireup" => 15, "msgtype" => 15, "created" => $jakdb->raw("NOW()")],
  						["locid" => $lastid, "lang" => JAK_LANG, "title" => "Transfer Message", "message" => "We have transferred your conversation to %operator%, please hold.", "fireup" => 15, "msgtype" => 16, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "WhatsApp Online", "message" => "Please click on a operator below to connect via WhatsApp and get help immediately.", "fireup" => 15, "msgtype" => 26, "created" => $jakdb->raw("NOW()")],
					    ["locid" => $lastid, "lang" => JAK_LANG, "title" => "WhatsApp Offline", "message" => "We are currently offline however please check below for available operators in WhatsApp, we try to help you as soon as possible.", "fireup" => 15, "msgtype" => 27, "created" => $jakdb->raw("NOW()")]]);

			    }

				// We say succesful
				$_SESSION["successmsg"] = $jkl['g16'];
				jak_redirect(JAK_rewrite::jakParseurl('l', 'e', $lastid));

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Title and Description
		$SECTION_TITLE = $jkl['g41'];
		$SECTION_DESC = $jkl['g54'];

		// Call the template
		$template = 'newlocation.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

if (!$subpage) {
	// Get all locations
	$locations = $jakdb->select("locations", ["id", "title", "lc3hd3", "url", "db_host", "lastedit"]);

	// Title and Description
	$SECTION_TITLE = $jkl['g39'];
	$SECTION_DESC = $jkl['g40'];

	// Call the template
	$template = 'location.php';
}

?>