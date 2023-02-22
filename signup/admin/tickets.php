<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Sub page available
$subpage = false;

// Change for 1.0.3
use JAKWEB\JAKsql;

// All the tables we need for this plugin
$errors = $success = array();

// Delete the single client
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "support_tickets")) {

		// Delete the ticket
		$jakdb->delete("support_tickets", ["id" => $page2]);

		// We have deleted the ticket
		$_SESSION["successmsg"] = $jkl['g16'];
		jak_redirect($_SESSION['LCRedirect']);

	} else {
		// No database information
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('t'));
	}

}

// Answer the ticket
if ($page1 == "a") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "support_tickets")) {

		// Now get the user information
        $ticket = $jakdb->get("support_tickets", ["id", "userid", "opid", "subject", "content", "status", "sent"], ["id" => $page2]);

        if ($ticket["status"] == 3) {
        	// Set status from old ticket as solved
			$jakdb->update("support_tickets", ["readtime" => time()], ["id" => $page2]);
        }

        $usr = $jakdb->get("users", ["username", "email", "paidtill"], ["opid" => $ticket["opid"]]);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    $jkp = $_POST;
		    
		    if (isset($jkp['save'])) {

		    	if (empty($jkp['subject'])) {
				    $errors['e'] = $jkl['e24'];
				}
		    
		        if ($jkp['content'] == '') { 
		        	$errors['e1'] = $jkl['e25'];
		        }

		        if (count($errors) == 0) {

		        	$timenow = time();

			        $phold = array("%username%","%email%","%paidtill%");
					$replace   = array($usr["username"], $usr["email"], $usr["paidtill"]);
					$content = str_replace($phold, $replace, $jkp['content']);
			        			
			        // Insert the news for user(s)
					$jakdb->insert("support_tickets", ["userid" => $ticket["userid"],
						"opid" => $ticket["opid"],
						"ticketid" => $page2,
						"username" => $usr["username"],
						"subject" => trim($jkp['subject']),
						"content" => trim($content),
						"isnews" => 0,
						"sent" => $timenow]);

					// Set status from old ticket as solved
					$jakdb->update("support_tickets", ["status" => 2], ["id" => $page2]);

					// Send email to client with the cron job
					$jakdb->update("users", ["newticket" => 1], ["id" => $ticket["userid"]]);

					// We go for success
				    $_SESSION["successmsg"] = $jkl['g142'];
			        jak_redirect(JAK_rewrite::jakParseurl('t'));

			    } else {
			        $errors = $errors;
			    }

		    }
		}

        // Title and Description
		$SECTION_TITLE = $jkl['g132'];
		$SECTION_DESC = $jkl['g130'];

		// Include the javascript file for results
		$js_file_footer = 'js_tickets.php';

		// Call the template
		$template = 'tickets.php';

		$subpage = true;

	} else {
		// No database information
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('t'));
	}

}

// Reat the ticket
if ($page1 == "r") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "support_tickets")) {

		// Now get the user information
        $ticket = $jakdb->get("support_tickets", ["id", "subject", "content"], ["id" => $page2]);

        // Set status from new ticket to read for operator
		$jakdb->update("support_tickets", ["status" => 1], ["id" => $page2]);

        // Title and Description
		$SECTION_TITLE = $jkl['g132'];
		$SECTION_DESC = $jkl['g130'];

		// Include the javascript file for results
		$js_file_footer = 'js_tickets.php';

		// Call the template
		$template = 'tickets.php';

		$subpage = true;

	} else {
		// No database information
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('t'));
	}

}

if (!$subpage) {

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    $jkp = $_POST;
	    
	    if (isset($jkp['save'])) {

	    	if (empty($jkp['subject'])) {
			    $errors['e'] = $jkl['e24'];
			}
	    
	        if ($jkp['content'] == '') { 
	        	$errors['e1'] = $jkl['e25'];
	        }

	        if (count($errors) == 0) {

	        	$timenow = time();

	        	// Only for some user(s)
		        if (isset($jkp['userid']) && is_array($jkp['userid']) && !in_array(0, $jkp['userid'])) {

		        	foreach ($jkp['userid'] as $v) {

		        		$usr = $jakdb->get("users", ["opid", "username", "email", "paidtill"], ["id" => $v]);

		        		$phold = array("%username%","%email%","%paidtill%");
						$replace   = array($usr["username"], $usr["email"], $usr["paidtill"]);
						$content = str_replace($phold, $replace, $jkp['content']);
		        			
		        		// Insert the news for user(s)
						$jakdb->insert("support_tickets", ["userid" => $v,
							"opid" => $usr["opid"],
							"username" => $usr["username"],
							"subject" => trim($jkp['subject']),
							"content" => trim($content),
							"isnews" => 0,
							"sent" => $timenow]);

						// Send email to client with the cron job
						$jakdb->update("users", ["newticket" => 1], ["id" => $v]);

		        	}

		        	$_SESSION["successmsg"] = $jkl['g142'];
		        	jak_redirect($_SESSION['LCRedirect']);

		        // News for all set 0 for isnews
		        } elseif (isset($jkp['userid']) && is_array($jkp['userid']) && in_array(0, $jkp['userid'])) {

		        	// Insert the news for user(s)
					$jakdb->insert("support_tickets", ["subject" => trim($jkp['subject']),
						"content" => trim($jkp['content']),
						"isnews" => 1,
						"readtime" => $timenow,
						"sent" => $timenow]);

					$_SESSION["successmsg"] = $jkl['g16'];
		        	jak_redirect($_SESSION['LCRedirect']);

			    }

		    } else {
		        $errors = $errors;
		    }

	    }
	}

	// Title and Description
	$SECTION_TITLE = $jkl['g129'];
	$SECTION_DESC = $jkl['g130'];

	// Get all available clients
	$clients = $jakdb->select("users", ["id", "opid", "username", "email"]);

	// Include the javascript file for results
	$js_file_footer = 'js_tickets.php';

	// Call the template
	$template = 'tickets.php';
}

?>