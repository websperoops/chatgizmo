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
if (!JAK_USERID || !jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Sub page available
$subpage = false;

$errors = array();

// Change for 1.0.3
use JAKWEB\JAKsql;

// Delete the single client
if ($page1 == "n") {

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		if ($_POST['email'] == '' || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		    $errors['e'] = $jkl['e2'];
		}
		    
		if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST['username'])) {
		   	$errors['e1'] = $jkl['e1'];
		}

		if (jak_field_not_exist(strtolower($_POST['email']), "users", "email")) {
			$errors['e1'] = $jkl['e10'];
		}
        
		if (jak_field_not_exist(strtolower($_POST['username']), "users", "username")) {
			$errors['e1'] = $jkl['e3'];
		}

		if (empty($_POST['pass']) || empty($_POST['passc'])) {    
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
		    $trialunix = strtotime($_POST['paidtill']);
		    $paidtime = date('Y-m-d H:i:s', strtotime($_POST['paidtill']));
		} else {
			$trialunix = strtotime("+".$sett["trialdays"]." day");
            $trialtime = date('Y-m-d H:i:s', $trialunix);
			$paidtime = date('Y-m-d H:i:s', strtotime($trialtime));
		}

		// Dateformat check trial
		if (isset($_POST['trial']) && !empty($_POST['trial'])) {
		   	if (!validateDate($_POST['trial'], "d.m.Y")) {
		    	$errors['e7'] = $jkl['e20'];
		    }
		    $trialtime = $trialtime = date('Y-m-d H:i:s', strtotime($_POST['trial']));
		} else {
			$trialtime = "1980-05-06 00:00:00";
		}

		if (count($errors) == 0) {

			// Let's check if we have the maximum reached
			if (JAK_MAX_CLIENTS != 0) {

				$totalu = $jakdb->count("users", ["active" => 1]);

				if ($totalu >= JAK_MAX_CLIENTS) {

					// No database information
					$_SESSION["errormsg"] = $jkl['e27'];
					jak_redirect($_SESSION['LCRedirect']);

				}

			}

		    // Get the one location
			$connect = $jakdb->get("locations", ["id", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $_POST["locationid"]]);

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

				// Get the username
				$cleanusername = filter_var(trim($_POST['username']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

				// Get the password
				$hashedpass = hash_hmac('sha256', $_POST['pass'], DB_PASS_HASH);

				// Sanitize the email address
				$client_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

				// On the chat we have setup everything, now it is time to create the user in the local database
				$jakdb->insert("users", [ 
					"locationid" => $_POST["locationid"],
					"email" => $client_email,
					"username" => strtolower($cleanusername),
					"password" => $hashedpass,
					"signup" => $jakdb->raw("NOW()"),
					"trial" => $trialtime,
					"paidtill" => $paidtime,
					"lastedit" => $jakdb->raw("NOW()"),
					"active" => 1]);

				$userid = $jakdb->id();

				// Now we create the user in the remote database
				$jakdb1->insert("user", [ 
	                "password" => $hashedpass,
	                "username" => $cleanusername,
	                "name" => $cleanusername,
	                "email" => $client_email,
	                "validtill" => $trialunix,
	                "access" => 1,
	                "permissions" => "leads,leads_all,off_all,ochat,ochat_all,statistic,statistic_all,files,proactive,usrmanage,responses,departments,settings,logs,answers,widget,groupchat,blacklist,blocklist",
	                "time" => $jakdb->raw("NOW()")]);

            	$opid = $jakdb1->id();

            	// Update the main user entry
                $jakdb->update("users", ["opid" => $opid], ["id" => $userid]);

                // Get the settings for this location
				$opsett = array();
				$opsettings = $jakdb->select("opsettings", ["varname", "used_value"], ["locid" => $connect["id"]]);
				foreach ($opsettings as $v) {
				    $opsett[$v["varname"]] = $v["used_value"]; 
				}
				
				// Create the settings for the operator
                $jakdb1->query("INSERT INTO ".$connect["db_prefix"]."settings (`opid`, `varname`, `used_value`, `default_value`) VALUES
                    (".$opid.", 'crating', '1', '0'),
                    (".$opid.", 'dateformat', '".$opsett["dateformat"]."', 'd.m.Y'),
                    (".$opid.", 'email', '".$client_email."', '@cc3jak'),
                    (".$opid.", 'emailcc', '', '@jakcc'),
                    (".$opid.", 'email_block', '', NULL),
                    (".$opid.", 'facebook', '', ''),
                    (".$opid.", 'ip_block', '', NULL),
                    (".$opid.", 'lang', '".$opsett["lang"]."', '".$opsett["lang"]."'),
                    (".$opid.", 'chat_upload_standard', '0', '0'),
                    (".$opid.", 'msg_tone', 'new_message', 'new_message'),
                    (".$opid.", 'pro_alert', '1', '1'),
                    (".$opid.", 'ring_tone', 'ring', 'ring'),
                    (".$opid.", 'send_tscript', '1', '1'),
                    (".$opid.", 'show_ips', '1', '1'),
                    (".$opid.", 'smtp_sender', '".$client_email."', ''),
                    (".$opid.", 'smtphost', '', ''),
                    (".$opid.", 'smtppassword', '', ''),
                    (".$opid.", 'smtpport', '25', '25'),
                    (".$opid.", 'smtpusername', '', ''),
                    (".$opid.", 'smtp_alive', '0', '0'),
                    (".$opid.", 'smtp_auth', '0', '0'),
                    (".$opid.", 'smtp_mail', '0', '0'),
                    (".$opid.", 'smtp_prefix', '', ''),
                    (".$opid.", 'timeformat', '".$opsett["timeformat"]."', 'g:i a'),
                    (".$opid.", 'timezoneserver', '".$opsett["timezoneserver"]."', '".$opsett["timezoneserver"]."'),
                    (".$opid.", 'title', '".$opsett["title"]."', '".$opsett["title"]."'),
                    (".$opid.", 'twilio_nexmo', '0', '1'),
                    (".$opid.", 'tw_msg', '".$opsett["tw_msg"]."', '".$opsett["tw_msg"]."'),
                    (".$opid.", 'tw_phone', '', ''),
                    (".$opid.", 'tw_sid', '', ''),
                    (".$opid.", 'tw_token', '', ''),
                    (".$opid.", 'useravatheight', '".$opsett["useravatheight"]."', '113'),
                    (".$opid.", 'useravatwidth', '".$opsett["useravatwidth"]."', '150'),
                    (".$opid.", 'holiday_mode', '0', '0'),
                    (".$opid.", 'client_push_not', '1', '1'),
                    (".$opid.", 'engage_sound', 'sound/new_message3', 'sound/new_message3'),
                    (".$opid.", 'engage_icon', 'fa fa-bells', 'fa fa-bells'),
                    (".$opid.", 'client_sound', 'sound/hello', 'sound/hello')");

                // Insert the chat widget
				$opcw = $jakdb->select("chatwidget", '*', ["locid" => $connect["id"]]);
                foreach ($opcw as $rowcw) {
                	# code...
                	$jakdb1->insert("chatwidget", ["opid" => $opid, "title" => $rowcw["title"], "lang" => $opsett["lang"], "hidewhenoff" => 0, "template" => $rowcw["template"], "created" => $jakdb->raw("NOW()")]);
                }

                // Group Chat
				$opgc = $jakdb->select("groupchat", '*', ["locid" => $connect["id"]]);
                foreach ($opgc as $rowgc) {
                	# code...
                	$jakdb1->insert("groupchat", ["opid" => $opid, "title" => $rowgc["title"], "description" => $rowgc["description"], "opids" => 0, "maxclients" => 10, "lang" => $opsett["lang"], "buttonimg" => "colour_on.png", "floatpopup" => 0, "floatcss" => "bottom:20px;left:20px", "active" => 0, "created" => $jakdb->raw("NOW()")]);
                }

                // Insert the chat department
                $opdep = $jakdb->select("departments", '*', ["locid" => $connect["id"]]);
                foreach ($opdep as $rowod) {
                	# code...
                	$jakdb1->insert("departments", ["opid" => $opid, "title" => $rowod["title"], "description" => $rowod["description"], "active" => $rowod["active"], "dorder" => $rowod["dorder"], "time" => $jakdb->raw("NOW()")]);
                }

                // Insert the answers
                $opa = $jakdb->select("answers", '*', ["locid" => $connect["id"]]);
               	foreach ($opa as $rowa) {
                	# code...
	                $jakdb1->insert("answers", [["opid" => $opid, "department" => 0, "lang" => $opsett["lang"], "title" => $rowa["title"], "message" => $rowa["message"], "fireup" => $rowa["fireup"], "msgtype" => $rowa["msgtype"], "created" => $jakdb->raw("NOW()")]]);
	            }

                // Insert into the local subscription table
                $jakdb1->insert("subscriptions", ["opid" => $opid, "validfor" => $sett["trialdays"], "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $trialtime, "registered" => $jakdb->raw("NOW()")]);

                // Send login details
				if (isset($_POST['send_login']) && $_POST['send_login'] == 1) {

					$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP

					// Get the email template
					$nlhtml = file_get_contents(str_replace(JAK_ADMIN_LOC.'/', '', APP_PATH).'email/index.html');

					if ($sett["smtp"] == 1) {

						$mail->IsSMTP(); // telling the class to use SMTP
						$mail->Host = $sett["smtphost"];
						$mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
						$mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
						$mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
						$mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
						$mail->Username = $sett["smtpusername"]; // SMTP account username
						$mail->Password = $sett["smtppass"];        // SMTP account password
														
					}

					$mail->SetFrom($sett["emailaddress"]);
					// Send email to customer
					$mail->AddAddress(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));

					// Say Hello
					$webtext = '<h1>'.sprintf($sett["webhello"], $cleanusername).'</h1>';

					// Send the operator url
					$webtext .= sprintf($sett["newclient"], '<a href="'.$connect['url'].'">'.$connect['url'].'</a>', $cleanusername, $_POST['pass']);
							
					// Change fake vars into real ones.
					$cssAtt = array('{emailcontent}', '{weburl}');
					$cssUrl   = array($webtext, $sett["webaddress"]);
					$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
						
					$body = str_ireplace("[\]", "", $nlcontent);
													
					$mail->Subject = $sett["emailtitle"];
					$mail->MsgHTML($body);
					$mail->Send();

					$_SESSION["infomsg"] = $jkl['e32'];


				}

				// We have deleted the user
				$_SESSION["successmsg"] = $jkl['g238'];
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

	// Get all locations
	$locations = $jakdb->select("locations", ["id", "title"], ["lc3hd3" => 0]);

	// Title and Description
	$SECTION_TITLE = $jkl['g233'];
	$SECTION_DESC = $jkl['g234'];

	// Include the javascript file for results
	$js_file_footer = 'js_editclient.php';

	// Call the template
	$template = 'newclient.php';

	$subpage = true;


}

// Delete the single client
if ($page1 == "d") {

	if (isset($page2) && is_numeric($page2) && jak_row_exist($page2, "users")) {

		// First get the client info
		$client = $jakdb->get("users", ["id", "opid", "locationid", "confirm"], ["id" => $page2]);

		// We don't allow to delete the client with ID1
		if ($client["opid"] == 1) {
			// No database information
			$_SESSION["errormsg"] = sprintf($jkl['e35'], $client["id"]);
			jak_redirect($_SESSION['LCRedirect']);
		}

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
		$nlhtml = file_get_contents(str_replace(JAK_ADMIN_LOC.'/', '', APP_PATH).'email/index.html');
		
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

					// Mark the user to update the profile and empty the cache, the cron job will do the rest
					$jakdb1->update("user", ["autoupdate" => 1], ["id" => $_POST["opid"]]);

					// Update the total operators, each operator will be valid for 30 days
					if (isset($_POST['extraop']) && is_numeric($_POST['extraop']) && $_POST['extraop'] > 0) {

						// Mark the user as deleted, the cron job will do the rest
						$jakdb1->update("subscriptions", ["extraoperators[+]" => $_POST['extraop']], ["opid" => $_POST["opid"]]);

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
			                "freeplan" => 1,
			                "paidwhen" => $jakdb->raw("NOW()"),
			                "paidtill" => $paidtill,
			                "success" => 1]);

						$_SESSION["infomsg"] = sprintf($jkl['g146'], $_POST['extraop']);

					}

					// Update the total operators, each operator will be valid for 30 days
					if (isset($_POST['extraop']) && is_numeric($_POST['extraop']) && $_POST['extraop'] < 0) {

						// Mark the user as deleted, the cron job will do the rest
						$jakdb1->update("subscriptions", ["extraoperators[-]" => $_POST['extraop']], ["opid" => $_POST["opid"]]);

						// Payment details insert
		                $jakdb->insert("subscriptions", [ 
			                "locationid" => $connect["id"],
			                "userid" => $_POST["opid"],
			                "amount" => 0,
			                "currency" => $sett["currency"],
			                "paidfor" => "Canceled Operator(s) / ".$_POST['extraop'],
			                "paidhow" => "Admin Panel",
			                "freeplan" => 1,
			                "paidwhen" => $jakdb->raw("NOW()"),
			                "paidtill" => "NOW()",
			                "success" => 1]);

						$_SESSION["infomsg"] = sprintf($jkl['g198'], $_POST['extraop']);

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

					// Now let's check if we assign a package
	                if (isset($_POST["jak_package"]) && is_numeric($_POST["jak_package"]) && jak_row_exist($_POST["jak_package"], "packages")) {

	                	// First we need the old subscriptions
						$subs = $jakdb1->get("subscriptions", ["id", "packageid", "chatwidgets", "groupchats", "operatorchat", "operators", "departments", "files", "chathistory", "paygateid", "subscribeid", "subscribed"], ["opid" => $_POST["opid"]]);

	                	// Get the package
						$pack = $jakdb->get("packages", ["id", "title", "amount", "currency", "chatwidgets", "groupchats", "operatorchat", "operators", "departments", "files", "copyfree", "activechats", "chathistory", "islc3", "ishd3", "validfor", "isfree"], ["AND" => ["id" => $_POST['jak_package'], "active" => [1,2]]]);

						// Paid unix
						$paidunix = strtotime("+".$pack["validfor"]." days");
						// get the nice time
						$paidtill = date('Y-m-d H:i:s', $paidunix);
						// Price
						$couponprice = $pack['amount'];
						// zero
						$subscribed = $paygateid = $subscribeid = 0;

						// We collect the customer id from stripe
						if (isset($subs["subscribeid"]) && isset($subs["paygateid"])) {
							$paygateid = $subs["paygateid"];
							$subscribeid = $subs["subscribeid"];
						}

						// Nasty stuff starts
						if (isset($subs) && isset($pack)) {

							// Update the main operator subscription
							update_main_operator($subs, $pack, $sett["currency"], $couponprice, $paygateid, $subscribeid, $subscribed, "Assigned Plan", $_POST["opid"], $connect["id"]);

						}

						// Update old subscriptions to none active
		                $jakdb->update("subscriptions", ["active" => 0], ["AND" => ["locationid" => $connect["id"], "userid" => $_POST["opid"]]]);

		                // We insert the subscription into the main table for that user.
						$jakdb->insert("subscriptions", ["packageid" => $pack["id"],
							"locationid" => $connect["id"],
							"userid" => $_POST["opid"],
							"amount" => $couponprice,
							"currency" => $sett["currency"],
							"paidfor" => $pack["title"],
							"paidhow" => "Free Plan",
							"subscribed" => 0,
							"paygateid" => $paygateid,
							"subscribeid" => $subscribeid,
							"paidwhen" => $jakdb->raw("NOW()"),
							"paidtill" => $paidtill,
							"freeplan" => ($pack["isfree"] ? 1 : 0),
							"active" => 1,
							"success" => 1]);

						// finally update the main database
						$jakdb->update("users", ["trial" => "1980-05-06 00:00:00", "paidtill" => $paidtill], ["AND" => ["opid" => $_POST["opid"], "locationid" => $connect["id"]]]);

						// Inform the admin
						$_SESSION["infomsg"] = $jkl['g241'];

					}

					// We have an advanced payment
	                if (isset($_POST["islc3hd3"]) && $_POST["islc3hd3"] != 0) {

	                	// 1 stands for LC3
	                	$islc3hd3 = 1;
	                	if ($_POST["islc3hd3"] == 2) $islc3hd3 = 2;

	                	if ($jakdb->has("advaccess", ["AND" => ["userid" => $page2, "opid" => $_POST["opid"]]])) {

	                		// Update the advanced access table
			                $jakdb->update("advaccess", [ 
			                    "lastedit" => $jakdb->raw("NOW()"),
			                    "paidtill" => $paidtill,
			                    "lc3hd3" => $islc3hd3,
			                    "paythanks" => 1], ["AND" => ["opid" => $_POST["opid"], "userid" => $page2]]);
	                	} else {
	                		$jakdb->insert("advaccess", ["userid" => $page2, "opid" => $_POST["opid"], "lc3hd3" => $islc3hd3, "lastedit" => $jakdb->raw("NOW()"), "paythanks" => 1, "paidtill" => $paidtill, "created" => $jakdb->raw("NOW()")]);
	                	}

	                	// Ok, we have removed the old stuff and now we update the user subscription table
						$jakdb1->update("subscriptions", ["packageid" => $pack["id"], "operators" => $pack["operators"], "departments" => $pack["departments"], "files" => $pack["files"], "activechats" => $pack["activechats"], "chathistory" => $pack["chathistory"], "islc3" => $pack["islc3"], "ishd3" => $pack["ishd3"], "validfor" => $pack["validfor"], "paygateid" => $paygateid, "subscribed" => $subscribed, "amount" => $couponprice, "currency" => $sett["currency"], "paidhow" => "Free Package - Coupon", "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $paidtill, "trial" => 0], ["opid" => $_POST["opid"]]);
					

						// We get the user information
		    			$crow = $jakdb->get("users", ["id", "opid", "username", "password", "email"], ["id" => $page2]);

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

						$mailadv = sprintf($jkl['g150'], $crow["username"], $crow["username"], $crow["opid"], $crow["email"], $crow["password"], $paidunix, BASE_URL_ORIG.'process/confirmadv.php?uid='.$crow["opid"]);
						$mail->MsgHTML($mailadv);
						$mail->Send();

						// Inform the admin
						$_SESSION["infomsg"] = $jkl['g154'];

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
						$nlhtml = file_get_contents(str_replace(JAK_ADMIN_LOC.'/', '', APP_PATH).'email/index.html');
						
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

		// Get the packages
		$packages = $jakdb->select("packages", ["id", "title", "description", "amount", "currency"], ["active" => [1,2], "ORDER" => ["lastedit" => "DESC"]]);

		// Get his payments
		$subscriptions = $jakdb->select("subscriptions", ["[>]packages" => ["packageid" => "id"]], ["subscriptions.id", "subscriptions.locationid", "subscriptions.packageid", "subscriptions.amount", "subscriptions.paidfor", "subscriptions.paidhow", "subscriptions.paidwhen", "subscriptions.paidtill", "subscriptions.success", "packages.title"], ["userid" => $client["opid"], "ORDER" => ["id" => "DESC"]]);

		// Get all locations
		$locations = $jakdb->select("locations", ["id", "title"], ["lc3hd3" => 0]);

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
	$SECTION_TITLE = $jkl['g55'];
	$SECTION_DESC = $jkl['g59'];

	// Include the javascript file for results
	$js_file_footer = 'js_client.php';

	// Call the template
	$template = 'client.php';
}

?>