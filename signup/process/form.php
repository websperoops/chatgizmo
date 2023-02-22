<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// No errors let's proceed with the sign up.
if (!file_exists('../include/db.php')) die('process/[db.php] config.php not exist');
require_once '../include/db.php';

// Get the language file
include_once('../admin/lang/'.JAK_LANG.'.php');

if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !isset($_POST["location"])) die("Nothing to do here.");

// Reset vars
$errors = array();

// Empty email address
if (!isset($_POST["signup"]) || empty($_POST["signup"]) || !filter_var($_POST["signup"], FILTER_VALIDATE_EMAIL)) {

	$errors['signup'] = $jkl['e2'];

}

// Empty Username and not an email address
if (!isset($_POST["username"]) || empty($_POST["username"]) || !preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST["username"])) {

	$errors['username'] = $jkl['e1'];

}

// Prohibited usernames
$prohun = array("administrator", "admin", "test", "tester", "demo", "demos", "asdf", "abc", "dummy");
if (isset($_POST["username"]) && in_array($_POST["username"], $prohun)) {

	$errors['username'] = $jkl['e3'];

}

// dsgvo agreement
if (!isset($_POST["dsgvo"]) && empty($_POST["dsgvo"])) {

	$errors['dsgvo'] = $jkl['e38'];

}

if (!empty($errors)) {

	/* Outputtng the error messages */
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
				
		header('Cache-Control: no-cache');
		die('{"status":0, "errors":'.json_encode($errors).'}');
					
	} else {
		die('{"status":0, "errors":'.json_encode($errors).'}');
	}
}

if (!file_exists('../class/class.db.php')) die('process/[class.db.php] config.php not exist');
require_once '../class/class.db.php';

include_once '../class/PHPMailerAutoload.php';

// Password generator
function jak_password_creator($length = 8) {
	return substr(md5(rand().rand()), 0, $length);
}

// Change for 1.0.3
use JAKWEB\JAKsql;

// Database connection
$jakdb = new JAKsql([
    // required
    'database_type' => JAKDB_DBTYPE,
    'database_name' => JAKDB_NAME,
    'server' => JAKDB_HOST,
    'username' => JAKDB_USER,
    'password' => JAKDB_PASS,
    'charset' => 'utf8',
    'port' => JAKDB_PORT,
    'prefix' => JAKDB_PREFIX,
 
    // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
    ]);

// Get the one location
$connect = $jakdb->get("locations", ["id", "title", "url", "db_host", "db_type", "db_port", "db_user", "db_pass", "db_name", "db_prefix"], ["id" => $_POST["location"]]);

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

}

// Check if we have a database connection
if ($jakdb && $jakdb1) {

	// Let's check if we have the maximum reached
	if (JAK_MAX_CLIENTS != 0) {

		$totalu = $jakdb->count("users", ["active" => 1]);

		if ($totalu >= JAK_MAX_CLIENTS) {

			/* Outputtng the success messages */
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
							
				header('Cache-Control: no-cache');
				die('{"status":1, "txt":'.json_encode($jkl['e27']).'}');
								
			} else {
				die('{"status":1, "txt":'.json_encode($jkl['e27']).'}');
			}

		}

	}

	// Is the email address already taken
	if ($jakdb->has("users", ["email" => $_POST["signup"]]) || $jakdb1->has("user", ["email" => $_POST["signup"]])) {

		$errors['signup'] = $jkl['e10'];

		/* Outputtng the error messages */
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					
			header('Cache-Control: no-cache');
			die('{"status":0, "errors":'.json_encode($errors).'}');
						
		} else {
			die('{"status":0, "errors":'.json_encode($errors).'}');
		}

	}

	// Is the username already taken
	if ($jakdb->has("users", ["username" => $_POST["username"]]) || $jakdb1->has("user", ["username" => $_POST["username"]])) {

		$errors['username'] = $jkl['e3'];

		/* Outputtng the error messages */
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
					
			header('Cache-Control: no-cache');
			die('{"status":0, "errors":'.json_encode($errors).'}');
						
		} else {
			die('{"status":0, "errors":'.json_encode($errors).'}');
		}

	}

	// Now let's get the location
	$loc = $jakdb->get("locations", "*", ["id" => $_POST["location"]]);

	// Ok, we have a location let's proceed
	if (isset($loc) && !empty($loc)) {

		// Ok, user is clear let's get the settings table
	    $sett = array();
	    $settings = $jakdb->select("settings", ["varname", "used_value"]);
	    foreach ($settings as $v) {
	        $sett[$v["varname"]] = $v["used_value"]; 
	    }

		// Create new password
		$usrpass = jak_password_creator(8);

		// Confirm time
		$confirm = time();

		// Get the pseudo username
		$parts = explode("@", $_POST["signup"]);
		$username = $parts[0];
		$cleanusername = filter_var(trim($_POST['username']), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

		// On the chat we have setup everything, now it is time to create the user in the local database
		$jakdb->insert("users", [ 
			"locationid" => $loc["id"],
			"email" => filter_var($_POST['signup'], FILTER_SANITIZE_EMAIL),
			"username" => strtolower($cleanusername),
			"password" => hash_hmac('sha256', $usrpass, DB_PASS_HASH),
			"signup" => $jakdb->raw("NOW()"),
			"lastedit" => $jakdb->raw("NOW()"),
			"active" => 0,
			"confirm" => $confirm]);

		$userid = $jakdb->id();

		// We are almost done, let's send an email to the customer.
		if ($userid) {

			$mail = new PHPMailer(); // defaults to using php "mail()" or optional SMTP
									
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

			$mail->AddAddress($_POST['signup']);

			// Say Hello
			$userct = '<h1>'.sprintf($sett["webhello"], $cleanusername).'</h1>';

			// Text
			$userct .= sprintf($sett["emailsignup"], $sett["trialdays"]);

			// Send the user password
			$userct .= sprintf($sett["emailpass"], $usrpass);

			// The only thing missing is the link to the server we have that from location
			if (JAK_USE_APACHE) {
				$userct .= '<p><a href="'.$loc["url"].'/confirm/'.$userid.'/'.$confirm.'">'.$loc["url"].'/confirm/'.$userid.'/'.$confirm.'</a></p>';
			} else {
				$userct .= '<p><a href="'.$loc["url"].'/index.php?p=confirm&amp;sp='.$userid.'&amp;ssp='.$confirm.'">'.$loc["url"].'/index.php?p=confirm&amp;sp='.$userid.'&amp;ssp='.$confirm.'</a></p>';
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
			$mail->Send();

		}

	}

}

/* Outputtng the success messages */
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
				
	header('Cache-Control: no-cache');
	die('{"status":1, "txt":'.json_encode($sett["welcomemsg"]).'}');
					
} else {
	die('{"status":1, "txt":'.json_encode($sett["welcomemsg"]).'}');
}
?>