<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.6                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// No access
if (!JAK_USERID || !jak_get_access("s", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// All the tables we need for this plugin
$errors = $success = array();

// Let's go on with the script
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jkp = $_POST;
    
    if (isset($jkp['save'])) {

    	if (empty($jkp['title'])) {
		    $errors['e'] = $jkl['e11'];
		}
    
        if ($jkp['emailaddress'] == '' || !filter_var($jkp['emailaddress'], FILTER_VALIDATE_EMAIL)) { 
        	$errors['e1'] = $jkl['e2'];
        }

        if (count($errors) == 0) {

        // Update the fields
        $jakdb->update("settings", ["used_value" => $jkp['title']], ["varname" => "title"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailaddress']], ["varname" => "emailaddress"]);
        $jakdb->update("settings", ["used_value" => $jkp['webaddress']], ["varname" => "webaddress"]);
        $jakdb->update("settings", ["used_value" => $jkp['dateformat']], ["varname" => "dateformat"]);
        $jakdb->update("settings", ["used_value" => $jkp['timeformat']], ["varname" => "timeformat"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtp']], ["varname" => "smtp"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtphost']], ["varname" => "smtphost"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtpauth']], ["varname" => "smtpauth"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtpprefix']], ["varname" => "smtpprefix"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtpalive']], ["varname" => "smtpalive"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtpport']], ["varname" => "smtpport"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtpusername']], ["varname" => "smtpusername"]);
        $jakdb->update("settings", ["used_value" => $jkp['smtppass']], ["varname" => "smtppass"]);

        $_SESSION["successmsg"] = $jkl['g16'];
        jak_redirect($_SESSION['LCRedirect']);

    } else {
        $errors = $errors;
    }
    
    } else {
    
    	$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
    
    	// Send email the smpt way or else the mail way
    	if ($sett["smtp"] == 1) {
    		
    		try {
        		$mail->IsSMTP(); // telling the class to use SMTP
        		$mail->Host = $sett["smtphost"];
        		$mail->SMTPAuth = ($sett["smtpauth"] ? true : false); // enable SMTP authentication
        		$mail->SMTPSecure = $sett["smtpprefix"]; // sets the prefix to the server
        		$mail->SMTPKeepAlive = ($sett["smtpalive"] ? true : false); // SMTP connection will not close after each email sent
        		$mail->Port = $sett["smtpport"]; // set the SMTP port for the GMAIL server
        		$mail->Username = $sett["smtpusername"]; // SMTP account username
        		$mail->Password = $sett["smtppass"];        // SMTP account password
        		$mail->SetFrom($sett["emailaddress"]);
        		$mail->AddReplyTo($sett["emailaddress"]);
        		$mail->AddAddress($sett["emailaddress"]);
        		$mail->AltBody = sprintf($jkl["g94"], 'SMTP.'); // optional, comment out and test
        		$mail->Subject = $jkl["g71"];
        		$mail->MsgHTML(sprintf($jkl["g94"], 'SMTP.'));
        		$mail->Send();
        		$success['e'] = sprintf($jkl["g94"], 'SMTP.');
        	} catch (phpmailerException $e) {
    	    	$errors['e'] = $e->errorMessage(); //Pretty error messages from PHPMailer
        	} catch (Exception $e) {
        		$errors['e'] = $e->getMessage(); //Boring error messages from anything else!
        	}
    		
    	} else {
    	
    		try {
        		$mail->SetFrom($sett["emailaddress"]);
        		$mail->AddReplyTo($sett["emailaddress"]);
        		$mail->AddAddress($sett["emailaddress"]);
        		$mail->AltBody = sprintf($jkl["g94"], 'SMTP.'); // optional, comment out and test
        		$mail->Subject = $jkl["g72"];
        		$mail->MsgHTML(sprintf($jkl["g94"], 'Mail().'));
        		$mail->Send();
        		$success['e'] = sprintf($jkl["g94"], 'Mail().');
    		} catch (phpmailerException $e) {
    			$errors['e'] = $e->errorMessage(); //Pretty error messages from PHPMailer
    		} catch (Exception $e) {
    		  	$errors['e'] = $e->getMessage(); //Boring error messages from anything else!
    		}
    	
    	}
    
    }
    
}

// Title and Description
$SECTION_TITLE = $jkl['g66'];
$SECTION_DESC = $jkl['g67'];

// Include the javascript file for results
$js_file_footer = 'js_settings.php';

// Call the template
$template = 'settings.php';

?>