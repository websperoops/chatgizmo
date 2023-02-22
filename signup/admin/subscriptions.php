<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.2                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2017 JAKWEB All Rights Reserved # ||
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

// Delete the single client
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "users")) {

		// First get the client info
		$client = $jakdb->get("users", ["id", "opid", "locationid", "confirm"], ["id" => $page2]);

		// First let's check if that user is also set in advanced
		if ($jakdb->has("advaccess", ["userid" => $client["id"]])) {
			// No database information
			$_SESSION["errormsg"] = sprintf($jkl['e19'], $client["id"]);
			jak_redirect($_SESSION['LCRedirect']);
		}

		// User account has never been confirmed, just delete it.
		if ($client["confirm"] != 0) {
			// Delete the user
			$jakdb->delete("users", ["id" => $page2]);

			// We have deleted the user
			$_SESSION["successmsg"] = $jkl['g16'];
			jak_redirect($_SESSION['LCRedirect']);
		}

		// Get the one location
		$connect = $jakdb->get("locations", ["id", "title", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $client["locationid"]]);

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


			// Mark the user as deleted, the cron job will do the rest
			$usrdel = $jakdb1->update("user", ["autodelete" => 1], ["id" => $client["opid"]]);

			if ($usrdel) {

				// Delete the user
				$jakdb->delete("users", ["id" => $page2]);

				// Get the cron url
				$cronurl = str_replace("operator", "", $connect["url"]).'include/cron.php';

				// We have deleted the user
				$_SESSION["successmsg"] = sprintf($jkl['g119'], $client["locationid"], $cronurl);
				jak_redirect($_SESSION['LCRedirect']);

			} else {

				// No database information
				$_SESSION["errormsg"] = sprintf($jkl['e18'], $connect["id"]);
				jak_redirect($_SESSION['LCRedirect']);

			}

		} else {

			// Database information not set correctly
			$_SESSION["errormsg"] = sprintf($jkl['e18'], $connect["id"]);
			jak_redirect($_SESSION['LCRedirect']);

		}

	} else {
		// No database information
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('c'));
	}

}

// Resend the confirmation email
if ($page1 == "c") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "users")) {

		// Now get the user information
        $activeuser = $jakdb->get("users", ["id", "locationid", "email", "username"], ["AND" => ["id" => $page2, "active" => 0, "confirm[!]" => 0]]);

        // Create new password
		$usrpass = jak_password_creator(8);

		// Confirm time
		$confirm = time();

		// On the chat we have setup everything, now it is time to create the user in the local database
		$jakdb->update("users", ["password" => hash_hmac('sha256', $usrpass, DB_PASS_HASH),
			"lastedit" => $jakdb->raw("NOW()"),
			"active" => 0,
			"confirm" => $confirm], ["id" => $activeuser["id"]]);

		// Now let's get the location
		$locationurl = $jakdb->get("locations", "url", ["id" => $activeuser["locationid"]]);

		$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP
									
		if ($sett["smtp"] == 1) {

			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host = $sett["smtphost"];
		    $mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
		    $mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
		    $mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
		    $mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
		    $mail->Username = $sett["smtpusername"]; // SMTP account username
		    $mail->Password = $sett["smtppass"]; // SMTP account password
		    $mail->SetFrom($sett["emailaddress"]);
										
		} else {
									
			$mail->SetFrom($sett["emailaddress"]);
									
		}

		$mail->AddAddress($activeuser['email']);

		// Say Hello
		$userct = '<h1>'.sprintf($sett["webhello"], $activeuser["username"]).'</h1>';

		// Text
		$userct .= sprintf($sett["emailsignup"], $sett["trialdays"]);

		// Send the user password
		$userct .= sprintf($sett["emailpass"], $usrpass);

		// The only thing missing is the link to the server we have that from location
		if (JAK_USE_APACHE) {
			$userct .= '<p><a href="'.$locationurl.'/confirm/'.$activeuser["id"].'/'.$confirm.'">'.$locationurl.'/confirm/'.$activeuser["id"].'/'.$confirm.'</a></p>';
		} else {
			$userct .= '<p><a href="'.$locationurl.'/index.php?p=confirm&amp;sp='.$activeuser["id"].'&amp;ssp='.$confirm.'">'.$locationurl.'/index.php?p=confirm&amp;sp='.$activeuser["id"].'&amp;ssp='.$confirm.'</a></p>';
		}

		// Get the email template
		$nlhtml = file_get_contents('../email/index.html');
		
		// Change fake vars into real ones.
		$cssAtt = array('{emailcontent}', '{weburl}');
		$cssUrl   = array($userct, $sett["webaddress"]);
		$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
		
		$body = str_ireplace("[\]", "", $nlcontent);
									
		$mail->Subject = $sett["emailtitle"];
		$mail->MsgHTML($body);
		if ($mail->Send()) {

			// We have deleted the user
			$_SESSION["successmsg"] = sprintf($jkl['g118'], $activeuser["id"]);
			jak_redirect($_SESSION['LCRedirect']);

		} else {
			// User with ID1 cannot be deleted, as well yourself.
			$_SESSION["errormsg"] = $jkl['e5'];
			jak_redirect($_SESSION['LCRedirect']);
		}

	} else {
		// we cannot handle an unknown user
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('c'));
	}

}

// Edit the client
if ($page1 == "e") {

	$errors = array();
	$updatepass = false;

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "users")) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    
		    if ($_POST['email'] == '' || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		        $errors['e'] = $jkl['e2'];
		    }
		    
		    if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST['username'])) {
		    	$errors['e1'] = $jkl['e1'];
		    }
		    
		    if (jak_field_not_exist_id($_POST['username'],$page2,"users","username")) {
		        $errors['e2'] = $jkl['e3'];
		    }

		    if (jak_field_not_exist_id($_POST['email'],$page2,"users","email")) {
		        $errors['e3'] = $jkl['e10'];
		    }
		    
		    if (!empty($_POST['pass']) || !empty($_POST['passc'])) {    
			    if ($_POST['pass'] != $_POST['passc']) {
			    	$errors['e4'] = $jkl['e8'];
			    } elseif (strlen($_POST['pass']) <= '7') {
			    	$errors['e5'] = $jkl['e9'];
			    } else {
			    	$updatepass = true;
			    }
		    }

		    // Dateformat check paidtill
		    if (isset($_POST['paidtill']) && !empty($_POST['paidtill'])) {
		    	if (!validateDate($_POST['paidtill'], "d.m.Y")) {
		    		$errors['e6'] = $jkl['e20'];
		    	}
		   	}

		   	// Dateformat check trial
		    if (isset($_POST['trial']) && !empty($_POST['trial'])) {
		    	if (!validateDate($_POST['trial'], "d.m.Y")) {
		    		$errors['e7'] = $jkl['e20'];
		    	}
		   	}

		    if (count($errors) == 0) {

		    	// First let's check if that user is also set in advanced
				if ($jakdb->has("advaccess", ["userid" => $page2])) {
					// No database information
					$_SESSION["errormsg"] = sprintf($jkl['e21'], $page2);
					jak_redirect($_SESSION['LCRedirect']);
				}

		    	// Get the one location
				$connect = $jakdb->get("locations", ["id", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $_POST["oldlocationid"]]);

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
					'prefix' => $connect["db_prefix"],
					]);

					// Mark the user as deleted, the cron job will do the rest
					$jakdb1->update("user", ["autoupdate" => 1], ["id" => $_POST["opid"]]);

					// Update the total operators, each operator will be valid for 30 days
					if (isset($_POST['extraop']) && is_numeric($_POST['extraop']) && $_POST['extraop'] > 0) {

						// Mark the user as deleted, the cron job will do the rest
						$jakdb1->update("op_settings", ["totalops[+]" => $_POST['extraop']], ["opid" => $_POST["opid"]]);

						// get the nice time
		        		$paidtill = date('Y-m-d H:i:s', strtotime("+1 month"));

						// Payment details insert
		                $jakdb->insert("subscriptions", [ 
			                "locationid" => $connect["id"],
			                "userid" => $_POST["opid"],
			                "amount" => 0,
			                "currency" => $sett["currency"],
			                "paidfor" => "Free Operator(s) / ".$_POST['extraop'],
			                "paidhow" => "Admin Panel",
			                "paidwhen" => $jakdb->raw("NOW()"),
			                "paidtill" => $paidtill,
			                "success" => 1]);

						$_SESSION["infomsg"] = sprintf($jkl['g146'], $_POST['extraop']);

					}

					// Extend membership
					$paidunix = strtotime($_POST['paidtill']);

					// get the nice time
			        $paidtill = date('Y-m-d H:i:s', $paidunix);

			        $trial = "1980-05-06 00:00:00";
			        if (isset($_POST['trial']) && !empty($_POST['trial'])) {
				        // Extend trial
						$trialunix = strtotime($_POST['trial']);

						// get the nice time
				        $trial = date('Y-m-d H:i:s', $trialunix);
				    }

					// We update the user
			    	$jakdb->update("users", ["username" => trim($_POST['username']),
						"email" => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
						"trial" => $trial,
						"paidtill" => $paidtill,
						"lastedit" => $jakdb->raw("NOW()")], ["id" => $page2]);

			    	// We update the password
					if ($updatepass) $jakdb->update("users", ["password" => hash_hmac('sha256', $_POST['pass'], DB_PASS_HASH)], ["id" => $page2]);

					// We have a changed date, we will need to send an email.
					if ($paidunix != $_POST["oldpaid"]) {
						$jakdb->update("users", ["paythanks" => 1], ["id" => $page2]);
					}

					// We have a location change, we will need to send an email to the customer that he needs to verify again and start the process all over.
					if ($_POST["locationid"] != $_POST["oldlocationid"]) {

					// Delete the user on the old server
					$usrdel = $jakdb1->update("user", ["autodelete" => 1], ["id" => $_POST["opid"]]);

					// Confirm time
					$confirm = time();

					// Update the user to the new location and the confirm code
					$jakdb->update("users", ["locationid" => $_POST["locationid"], "confirm" => $confirm], ["id" => $page2]);

					// Send an email to the user that he has to confirm again
					$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP
									
					if ($sett["smtp"] == 1) {

						$mail->IsSMTP(); // telling the class to use SMTP
						$mail->Host = $sett["smtphost"];
						$mail->SMTPAuth = $sett["smtpauth"]; // enable SMTP authentication
						$mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
						$mail->SMTPKeepAlive = $sett["smtpalive"]; // SMTP connection will not close after each email sent
						$mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
						$mail->Username = $sett["smtpuser"]; // SMTP account username
						$mail->Password = $sett["smtppass"]; // SMTP account password
						$mail->SetFrom($sett["emailaddress"]);
														
					} else {
													
						$mail->SetFrom($sett["emailaddress"]);
													
					}

						$mail->AddAddress($_POST['email']);

						// Say Hello
						$userct = '<h1>'.sprintf($sett["webhello"], $_POST['username']).'</h1>';

						// Text
						$userct .= $sett["emailmoved"];

						// The only thing missing is the link to the server we have that from location
						if (JAK_USE_APACHE) {
							$userct .= '<p><a href="'.$connect["url"].'/confirm/'.$page2.'/'.$confirm.'">'.$connect["url"].'/confirm/'.$page2.'/'.$confirm.'</a></p>';
						} else {
							$userct .= '<p><a href="'.$connect["url"].'/index.php?p=confirm&amp;sp='.$page2.'&amp;ssp='.$confirm.'">'.$connect["url"].'/index.php?p=confirm&amp;sp='.$page2.'&amp;ssp='.$confirm.'</a></p>';
						}

						// Get the email template
						$nlhtml = file_get_contents(APP_PATH.'email/index.html');
						
						// Change fake vars into real ones.
						$cssAtt = array('{emailcontent}', '{weburl}');
						$cssUrl   = array($userct, $sett["webaddress"]);
						$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
						
						$body = str_ireplace("[\]", "", $nlcontent);
													
						$mail->Subject = $sett["emailtitle"];
						$mail->MsgHTML($body);
						$mail->Send();

					}

					// Get the cron url
					$cronurl = str_replace("operator", "", $connect["url"]).'include/cron.php';

					// We have deleted the user
					$_SESSION["successmsg"] = sprintf($jkl['g120'], $_POST["locationid"], $cronurl);
					jak_redirect($_SESSION['LCRedirect']);

				} else {

					// Database information not set correctly
					$_SESSION["errormsg"] = sprintf($jkl['e18'], $connect["id"]);
					jak_redirect($_SESSION['LCRedirect']);

				}

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Get one user
		$client = $jakdb->get("users", ["id", "locationid", "opid", "username", "email", "trial", "paidtill"], ["id" => $page2]);

		// Get his payments
		$subscriptions = $jakdb->select("subscriptions", ["id", "locationid", "amount", "paidfor", "paidhow", "paidwhen", "paidtill", "success"], ["userid" => $client["opid"], "ORDER" => ["id" => "DESC"]]);

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"]);

		// Title and Description
		$SECTION_TITLE = $jkl['g57'];
		$SECTION_DESC = $jkl['g58'];

		// Include the javascript file for results
		$js_file_footer = 'js_editclient.php';

		// Call the template
		$template = 'editclient.php';

		$subpage = true;

	} else {
		// User does not exist
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect(JAK_rewrite::jakParseurl('c'));
	}

}

if (!$subpage) {

	// Title and Description
	$SECTION_TITLE = $jkl['g125'];
	$SECTION_DESC = $jkl['g168'];

	// Include the javascript file for results
	$js_file_footer = 'js_subscriptions.php';

	// Call the template
	$template = 'subscriptions.php';
}

?>