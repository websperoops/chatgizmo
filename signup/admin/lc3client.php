<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2018 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Sub page available
$subpage = false;

// arrays
$errors = array();

// Change for 1.0.3
use JAKWEB\JAKsql;

// Create a new user
if ($page1 == "n") {

	$errors = array();
	$updatepass = false;

	if (JAK_SUPERADMINACCESS) {

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			if (!isset($_POST['userid']) && empty($_POST['userid'])) {
		        $errors['e'] = $jkl['e4'];
		    }

		    if (empty($_POST['validtill'])) {
		        $errors['e1'] = $jkl['e20'];
		    }

		    // Dateformat check paidtill
		    if (isset($_POST['validtill']) && !empty($_POST['validtill'])) {
		    	if (!validateDate($_POST['validtill'], "d.m.Y")) {
		    		$errors['e1'] = $jkl['e20'];
		    	}
		   	}
		    
		    if ($_POST["locationid"] == 0 && !empty($_POST['userid']) && $jakdb->has("advaccess", ["userid" => $_POST['userid']])) {
		        $errors['e'] = sprintf($jkl['e21'], $_POST['userid']);
		    }

		    if (count($errors) == 0) {

		    	// We get the user information
		    	$crow = $jakdb->get("users", ["id", "opid", "username", "password", "email"], ["id" => $_POST['userid']]);

		    	// Normalise the date
		    	$paidtill = date('Y-m-d H:i:s', strtotime($_POST['validtill']));

		    	if ($_POST["locationid"] != 0) {

		    		// Get the one location
					$connect = $jakdb->get("locations", ["id", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $_POST["locationid"]]);

		    		// Insert the user into the table
		    		if ($jakdb->has("advaccess", ["AND" => ["userid" => $crow["id"], "opid" => $crow["opid"]]])) {

	                	// Update the advanced access table
			            $jakdb->update("advaccess", [
			            	"locationid" => $_POST["locationid"],
			            	"url" => $connect["url"],
			                "lastedit" => $jakdb->raw("NOW()"),
			                "paidtill" => $paidtill,
			                "lc3hd3" => $_SESSION["showlc3hd3"],
			                "paythanks" => 1], ["AND" => ["opid" => $crow["opid"], "userid" => $crow["id"]]]);
	                } else {
	                	// We insert the data
	                	$jakdb->insert("advaccess", ["locationid" => $_POST["locationid"], "url" => $connect["url"], "userid" => $crow["id"], "opid" => $crow["opid"], "lc3hd3" => $_SESSION["showlc3hd3"], "lastedit" => $jakdb->raw("NOW()"), "paythanks" => 1, "paidtill" => $paidtill, "created" => $jakdb->raw("NOW()")]);
	                }

					if ($connect["db_host"] && $connect["db_user"] && $connect["db_pass"] && $connect["db_name"]) {

						// Connect to the remove database
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

						// Valid till stuff
						$jakdb1->update("settings", ["used_value" => strtotime($_POST['validtill'])], ["varname" => "validtill"]);

						$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP

						// Get the email template
						$nlhtml = file_get_contents(APP_PATH.'email/index.html');

						// User Stuff
						if ($jakdb1->has("user", ["AND" => ["username" => $crow["username"], "email" => $crow['email']]])) {

							// Send email to customer
							$mail->AddAddress($crow["email"]);

							// Say Hello
							$webtext = '<h1>'.sprintf($sett["webhello"], $crow["username"]).'</h1>';

							// Send the operator url
							$webtext .= sprintf(($_SESSION["showlc3hd3"] == 1 ? $sett["lc3update"] : $sett["hd3update"]), '<a href="'.$connect['url'].'">'.$connect['url'].'</a>');
							
							// Change fake vars into real ones.
							$cssAtt = array('{emailcontent}', '{weburl}');
							$cssUrl   = array($webtext, $sett["webaddress"]);
							$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
						
							$body = str_ireplace("[\]", "", $nlcontent);
													
							$mail->Subject = $sett["emailtitle"];
							$mail->MsgHTML($body);
							$mail->Send();

							$_SESSION["infomsg"] = $jkl['e31'];

						} else {

							// We insert the user into the remote database
							$jakdb1->insert("user", [
							  "username" => $crow['username'],
							  "password" => $crow['password'],
							  "email" => $crow['email'],
							  "name" => $crow['username'],
							  "operatorchat" => 1,
							  "time" => $jakdb->raw("NOW()"),
							  "access" => 1]);
									
							if ($sett["smtp"] == 1) {

								$mail->IsSMTP(); // telling the class to use SMTP
								$mail->Host = $sett["smtphost"];
						        $mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
						        $mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
						        $mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
						        $mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
						        $mail->Username = $sett["smtpusername"]; // SMTP account username
						        $mail->Password = $sett["smtppass"];        // SMTP account password
						        $mail->SetFrom($sett["emailaddress"]);
														
							} else {
													
								$mail->SetFrom($sett["emailaddress"]);
													
							}

							// Send email to customer
							$mail->AddAddress($crow["email"]);

							// Say Hello
							$webtext = '<h1>'.sprintf($sett["webhello"], $crow["username"]).'</h1>';

							// Send the operator url
							$webtext .= sprintf(($_SESSION["showlc3hd3"] == 1 ? $sett["lc3confirm"] : $sett["hd3confirm"]), '<a href="'.$connect['url'].'">'.$connect['url'].'</a>');
							
							// Change fake vars into real ones.
							$cssAtt = array('{emailcontent}', '{weburl}');
							$cssUrl   = array($webtext, $sett["webaddress"]);
							$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
						
							$body = str_ireplace("[\]", "", $nlcontent);
													
							$mail->Subject = $sett["emailtitle"];
							$mail->MsgHTML($body);
							$mail->Send();

							$_SESSION["successmsg"] = $jkl['e32'];

						}

					} else {

						$_SESSION["errormsg"] = $jkl['e30'];

					}

					// We redirect
					jak_redirect(JAK_rewrite::jakParseurl('lc3'));

		    	} else {

				    // We create the email for the operator
				    $mail = new PHPMailer(); // defaults to using php "mail()"
					    	
					if ($sett["smtp"] == 1) {
							    	
						$mail->IsSMTP(); // telling the class to use SMTP
						$mail->Host = $sett["smtphost"];
						$mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
						$mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
						$mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
						$mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
						$mail->Username = $sett["smtpusername"]; // SMTP account username
						$mail->Password = $sett["smtppass"];        // SMTP account password
						$mail->SetFrom($sett["emailaddress"]);
						$mail->AddAddress($sett["emailaddress"]);
						$mail->AddReplyTo($crow["email"]);
							    		
					} else {
							    	
						$mail->SetFrom($sett["emailaddress"]);
						$mail->AddAddress($sett["emailaddress"]);
						$mail->AddReplyTo($crow["email"]);
							    	
					}
							    	
					$mail->Subject = $jkl['g153'];

					$mailadv = sprintf($jkl['g150'], $crow["username"], $crow["username"], $crow["opid"], $crow["email"], $crow["password"], strtotime($_POST['validtill']), BASE_URL_ORIG.'process/confirmadv.php?uid='.$crow["opid"]);
					$mail->MsgHTML($mailadv);
					$mail->Send();

					// We say succesful
					$_SESSION["successmsg"] = $jkl['g154'];
					jak_redirect(JAK_rewrite::jakParseurl('lc3'));

				}

			// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Title and Description
		$SECTION_TITLE = $jkl['g148'];
		$SECTION_DESC = $jkl['g149'];

		// Get all available clients
		$clients = $jakdb->select("users", ["id", "opid", "username", "email"], ["active" => 1]);

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"], ["lc3hd3" => 1]);

		// Include the javascript file for results
		$js_file_footer = 'js_newlc3client.php';

		// Call the template
		$template = 'newlc3client.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect(JAK_rewrite::jakParseurl('lc3'));
	}

}

// Edit the single client
if ($page1 == "e") {

	if (JAK_SUPERADMINACCESS) {

		// Get all available clients
		$client = $jakdb->get("advaccess", ["[>]users" => ["userid" => "id"]], ["advaccess.id", "advaccess.locationid", "advaccess.userid", "advaccess.opid", "advaccess.lc3hd3", "advaccess.url", "advaccess.paidtill", "users.username", "users.email", "users.password"], ["advaccess.id" => $page2]);

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		    if (empty($_POST['validtill'])) {
		        $errors['e1'] = $jkl['e20'];
		    }

		    // Dateformat check paidtill
		    if (isset($_POST['validtill']) && !empty($_POST['validtill'])) {
		    	if (!validateDate($_POST['validtill'], "d.m.Y")) {
		    		$errors['e1'] = $jkl['e20'];
		    	}
		   	}

		   	if (count($errors) == 0) {

		   		// Normalise the date
		    	$paidtill = date('Y-m-d H:i:s', strtotime($_POST['validtill']));

		    	// Get the one location
				$connect = $jakdb->get("locations", ["id", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $_POST["locationid"]]);

		   		// Update the advanced access table
			    $jakdb->update("advaccess", [
			        "locationid" => $_POST["locationid"],
			        "url" => $connect["url"],
			        "lastedit" => $jakdb->raw("NOW()"),
			        "paidtill" => $paidtill,
			        "paythanks" => 1], ["id" => $client["id"]]);

			    if (strtotime($_POST['validtill']) != $_POST['oldvalidtill']) {

				if ($connect["db_host"] && $connect["db_user"] && $connect["db_pass"] && $connect["db_name"]) {

					// Connect to the remove database
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

					// Valid till stuff
					$jakdb1->update("settings", ["used_value" => strtotime($_POST['validtill'])], ["varname" => "validtill"]);

					$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP

					// Get the email template
					$nlhtml = file_get_contents(APP_PATH.'email/index.html');

					// User Stuff
					if ($jakdb1->has("user", ["AND" => ["username" => $client["username"], "email" => $client['email']]])) {

						// Send email to customer
						$mail->AddAddress($client["email"]);

						// Say Hello
						$webtext = '<h1>'.sprintf($sett["webhello"], $client["username"]).'</h1>';

						// Send the operator url
						$webtext .= sprintf(($_SESSION["showlc3hd3"] == 1 ? $sett["lc3update"] : $sett["hd3update"]), '<a href="'.$connect['url'].'">'.$connect['url'].'</a>');
							
						// Change fake vars into real ones.
						$cssAtt = array('{emailcontent}', '{weburl}');
						$cssUrl   = array($webtext, $sett["webaddress"]);
						$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
						
						$body = str_ireplace("[\]", "", $nlcontent);
													
						$mail->Subject = $sett["emailtitle"];
						$mail->MsgHTML($body);
						$mail->Send();

						$_SESSION["infomsg"] = $jkl['e31'];

					} else {

						// We insert the user into the remote database
						$jakdb1->insert("user", [
							"username" => $client['username'],
							"password" => $client['password'],
							"email" => $client['email'],
							"name" => $client['username'],
							"operatorchat" => 1,
							"time" => $jakdb->raw("NOW()"),
							"access" => 1]);
									
						if ($sett["smtp"] == 1) {

							$mail->IsSMTP(); // telling the class to use SMTP
							$mail->Host = $sett["smtphost"];
						    $mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
						    $mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
						   	$mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
						    $mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
						    $mail->Username = $sett["smtpusername"]; // SMTP account username
						    $mail->Password = $sett["smtppass"];        // SMTP account password
						    $mail->SetFrom($sett["emailaddress"]);
														
						} else {
													
							$mail->SetFrom($sett["emailaddress"]);
													
						}

							// Send email to customer
							$mail->AddAddress($client["email"]);

							// Say Hello
							$webtext = '<h1>'.sprintf($sett["webhello"], $client["username"]).'</h1>';

							// Send the operator url
							$webtext .= sprintf(($_SESSION["showlc3hd3"] == 1 ? $sett["lc3confirm"] : $sett["hd3confirm"]), '<a href="'.$connect['url'].'">'.$connect['url'].'</a>');
								
							// Change fake vars into real ones.
							$cssAtt = array('{emailcontent}', '{weburl}');
							$cssUrl   = array($webtext, $sett["webaddress"]);
							$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
							
							$body = str_ireplace("[\]", "", $nlcontent);
														
							$mail->Subject = $sett["emailtitle"];
							$mail->MsgHTML($body);
							$mail->Send();

							$_SESSION["successmsg"] = $jkl['e32'];

						}

					} else {

						$_SESSION["errormsg"] = $jkl['e30'];

					}

					} else {
						$_SESSION["infomsg"] = $jkl['e34'];
					}

					// We redirect
					jak_redirect(JAK_rewrite::jakParseurl('lc3', 'e', $page2));

		   	// Output the errors
			} else {
			    $errors = $errors;
			}

		}

		// Title and Description
		$SECTION_TITLE = $jkl['g231'];
		$SECTION_DESC = $jkl['g232'];

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"], ["lc3hd3" => 1]);

		// Include the javascript file for results
		$js_file_footer = 'js_newlc3client.php';

		// Call the template
		$template = 'editlc3client.php';

		$subpage = true;

	} else {
		// User with ID1 cannot be deleted, as well yourself.
		$_SESSION["errormsg"] = $jkl['e12'];
		jak_redirect(JAK_rewrite::jakParseurl('lc3'));
	}

}

// Delete the single client
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2)) {

		// First get the client info
		$advclient = $jakdb->get("advaccess", ["userid", "url"], ["id" => $page2]);

		$client = $jakdb->get("users", ["id", "opid", "locationid", "confirm"], ["id" => $advclient["userid"]]);

		// User account has never been confirmed, just delete it.
		if ($client["confirm"] != 0) {
			// Delete the user
			$jakdb->delete("users", ["id" => $page2]);
			$jakdb->delete("advaccess", ["id" => $page2]);

			// We have deleted the user
			$_SESSION["successmsg"] = $jkl['g16'];
			jak_redirect($_SESSION['LCRedirect']);
			
		} else {

			// Only delete the advanced access
			$jakdb->delete("advaccess", ["id" => $page2]);

			// We have deleted the user
			$_SESSION["successmsg"] = sprintf($jkl['e22'], $client["id"], $advclient["url"]);
			jak_redirect($_SESSION['LCRedirect']);

		}

	} else {
		// No database information
		$_SESSION["errormsg"] = $jkl['e5'];
		jak_redirect($_SESSION['LCRedirect']);
	}

}

if (!$subpage) {

	$_SESSION["showlc3hd3"] = 1;

	// Title and Description
	$SECTION_TITLE = $jkl['g96'];
	$SECTION_DESC = $jkl['g97'];

	// Include the javascript file for results
	$js_file_footer = 'js_lc3client.php';

	// Call the template
	$template = 'lc3client.php';
}

?>