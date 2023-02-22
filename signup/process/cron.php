<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

$cron_url_orig = dirname(__file__) . DIRECTORY_SEPARATOR;
$cron_url = str_replace("process/", "", $cron_url_orig);

if (!file_exists($cron_url.'include/db.php')) die('cron.php] db.php not exist');
require_once $cron_url.'include/db.php';

if (!file_exists($cron_url.'class/class.db.php')) die('process/[cron.php] class.db.php not exist');
require_once $cron_url.'class/class.db.php';

// For sending emails
include_once $cron_url.'class/PHPMailerAutoload.php';

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

// Check if we have a database connection
if ($jakdb) {

	// Ok, user is logged in let's get the settings table
    $sett = array();
    $settings = $jakdb->select("settings", ["varname", "used_value"]);
    foreach ($settings as $v) {
        $sett[$v["varname"]] = $v["used_value"]; 
    }

	// Select all accounts that need a welcome email.
	$welcomeusr = $jakdb->select("users", ["id", "email", "username", "locationid"], ["welcomemsg" => 1]);

	if (isset($welcomeusr) && !empty($welcomeusr) && is_array($welcomeusr)) foreach ($welcomeusr as $row) {
		# code...
		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("users", ["welcomemsg" => 0], ["id" => $row["id"]]);

		// Now we get the location id url to point the user into the correct location.
		$locurl = $jakdb->get("locations", "url", ["id" => $row["locationid"]]);

		// A few resets
		$body =  $webtext = '';

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

		// Send email to customer
		$mail->AddAddress($row["email"]);

		// Say Hello
		$webtext .= '<h1>'.sprintf($sett["webhello"], $row["username"]).'</h1>';

		// Send the operator url
		$webtext .= $sett["emailwelcome"];

		// Url to login
		$webtext .= '<p><a href="'.$locurl.'">'.$locurl.'</a></p>';

		// Get the email template
		$nlhtml = file_get_contents($cron_url.'email/index.html');
		
		// Change fake vars into real ones.
		$cssAtt = array('{emailcontent}', '{weburl}');
		$cssUrl   = array($webtext, $sett["webaddress"]);
		$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
		
		$body = str_ireplace("[\]", "", $nlcontent);
									
		$mail->Subject = $sett["emailtitle"];
		$mail->MsgHTML($body);
		$mail->Send();

	}

	// Select all accounts that an email reminder for the tickets.
	$newticket = $jakdb->select("users", ["id", "email", "username", "locationid", "paidtill"], ["newticket" => 1]);

	if (isset($newticket) && !empty($newticket) && is_array($newticket)) foreach ($newticket as $row5) {
		# code...
		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("users", ["newticket" => 0], ["id" => $row5["id"]]);

		// Now we get the location id url to point the user into the correct location.
		$locurl = $jakdb->get("locations", "url", ["id" => $row5["locationid"]]);

		// A few resets
		$body =  $webtext = '';

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

		// Send email to customer
		$mail->AddAddress($row5["email"]);

		// Say Hello
		$webtext .= '<h1>'.$sett["newtickettitle"].'</h1>';

		// Send the operator url
		$webtext .= sprintf($sett["newticketmsg"], $row5["username"]);

		// Url to login
		$webtext .= '<p><a href="'.$locurl.'">'.$locurl.'</a></p>';

		// Get the email template
		$nlhtml = file_get_contents($cron_url.'email/index.html');
		
		// Change fake vars into real ones.
		$cssAtt = array('{emailcontent}', '{weburl}');
		$cssUrl   = array($webtext, $sett["webaddress"]);
		$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
		
		$body = str_ireplace("[\]", "", $nlcontent);
									
		$mail->Subject = $sett["emailtitle"];
		$mail->MsgHTML($body);
		$mail->Send();

	}

	// Select all accounts that need a thank you email for their payments.
	$paidusr = $jakdb->select("users", ["id", "opid", "email", "username", "locationid", "paidtill"], ["paythanks" => 1]);

	if (isset($paidusr) && !empty($paidusr) && is_array($paidusr)) foreach ($paidusr as $row2) {
		# code...
		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("users", ["paythanks" => 0], ["id" => $row2["id"]]);

		// Now we get the location id url to point the user into the correct location.
		$locurl = $jakdb->get("locations", "url", ["id" => $row2["locationid"]]);

		// Now we get the location id url to point the user into the correct location.
		$amount = $jakdb->get("subscriptions", "amount", ["AND" => ["locationid" => $row2["locationid"], "userid" => $row2["opid"], "active" => 1]]);

		// A few resets
		$body =  $webtext = '';

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

		// Send email to customer
		$mail->AddAddress($row2["email"]);

		// Say Hello
		$webtext .= '<h1>'.sprintf($sett["webhello"], $row2["username"]).'</h1>';

		// Send the operator url
		$webtext .= sprintf($sett["emailpaid"], $amount.' '.$sett["currency"], $row2["paidtill"]);

		// Url to login
		$webtext .= '<p><a href="'.$locurl.'">'.$locurl.'</a></p>';

		// Get the email template
		$nlhtml = file_get_contents($cron_url.'email/index.html');
		
		// Change fake vars into real ones.
		$cssAtt = array('{emailcontent}', '{weburl}');
		$cssUrl   = array($webtext, $sett["webaddress"]);
		$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
		
		$body = str_ireplace("[\]", "", $nlcontent);
									
		$mail->Subject = $sett["emailtitle"];
		$mail->MsgHTML($body);
		$mail->Send();

	}

	// Select all advanced accounts that need a thank you email for their payments.
	$paidusradv = $jakdb->select("advaccess", ["id", "userid"], ["paythanks" => 1]);

	if (isset($paidusradv) && !empty($paidusradv) && is_array($paidusradv)) foreach ($paidusradv as $row3) {
		# code...
		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("advaccess", ["paythanks" => 0], ["id" => $row3["id"]]);

		// We will need the user email and username
		$userinfo = $jakdb->select("users", ["email", "username", "locationid"], ["id" => $row3["userid"]]);

		// Now we get the location id url to point the user into the correct location.
		$locurl = $jakdb->get("locations", "url", ["id" => $userinfo["locationid"]]);

		// Now we get the location id url to point the user into the correct location.
		$amount = $jakdb->get("subscriptions", "amount", ["id" => $userinfo["locationid"]]);

		// A few resets
		$body =  $webtext = '';

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

		// Send email to customer
		$mail->AddAddress($userinfo["email"]);

		// Say Hello
		$webtext .= '<h1>'.sprintf($sett["webhello"], $userinfo["username"]).'</h1>';

		// Send the operator url
		$webtext .= sprintf($sett["emailpaidlc3"], $amount.' '.$sett["currency"], $row2["paidtill"]);

		// Url to login
		$webtext .= '<p><a href="'.$locurl.'">'.$locurl.'</a></p>';

		// Get the email template
		$nlhtml = file_get_contents($cron_url.'email/index.html');
		
		// Change fake vars into real ones.
		$cssAtt = array('{emailcontent}', '{weburl}');
		$cssUrl   = array($webtext, $sett["webaddress"]);
		$nlcontent = str_replace($cssAtt, $cssUrl, $nlhtml);
		
		$body = str_ireplace("[\]", "", $nlcontent);
									
		$mail->Subject = $sett["emailtitle"];
		$mail->MsgHTML($body);
		$mail->Send();

	}

	// Get the current date one week in the future.
	$payreminder = date('Y-m-d H:i:s', strtotime("+1 week"));

	// Now let's check if we have payment reminder.
	$paymsg = $jakdb->select("users", ["id", "email", "username", "locationid"], ["AND" => ["active" => 1, "payreminder" => 0, "paidtill[<]" => $payreminder]]);

	if (isset($paymsg) && !empty($paymsg) && is_array($paymsg)) foreach ($paymsg as $row1) {
		# code...
		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("users", ["payreminder" => 1], ["id" => $row1["id"]]);

		// Now we get the location id url to point the user into the correct location.
		$locurlpay = $jakdb->get("locations", "url", ["id" => $row1["locationid"]]);

		// A few resets
		$bodypay =  $webtext = '';

		$mailpay = new PHPMailer(); // defaults to using php "mail()" or optional SMTP
									
		if ($sett["smtp"] == 1) {

			$mailpay->IsSMTP(); // telling the class to use SMTP
			$mailpay->Host = $sett["smtphost"];
	        $mailpay->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
	        $mailpay->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
	        $mailpay->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
	        $mailpay->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
	        $mailpay->Username = $sett["smtpusername"]; // SMTP account username
	        $mailpay->Password = $sett["smtppass"];        // SMTP account password
	        $mailpay->SetFrom($sett["emailaddress"]);
										
		} else {
									
			$mailpay->SetFrom($sett["emailaddress"]);
									
		}

		// Send email to customer
		$mailpay->AddAddress($row1["email"]);

		// Say Hello
		$webtext .= '<h1>'.sprintf($sett["webhello"], $row1["username"]).'</h1>';

		// Send the operator url
		$webtext .= $sett["emailexpire"];

		// Url to login
		$webtext .= '<p><a href="'.$locurlpay.'">'.$locurlpay.'</a></p>';

		// Get the email template
		$nlhtmlpay = file_get_contents($cron_url.'email/index.html');
		
		// Change fake vars into real ones.
		$cssAttpay = array('{emailcontent}', '{weburl}');
		$cssUrlpay   = array($webtext, $sett["webaddress"]);
		$nlcontent = str_replace($cssAttpay, $cssUrlpay, $nlhtmlpay);
		
		$bodypay = str_ireplace("[\]", "", $nlcontent);
									
		$mailpay->Subject = $sett["emailtitle"];
		$mailpay->MsgHTML($bodypay);
		$mailpay->Send();

	}
					
}
?>