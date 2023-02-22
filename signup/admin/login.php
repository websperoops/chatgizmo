<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Login
if ($_SERVER["REQUEST_METHOD"] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'login') {
	
	$lcookies = false;
    $username = $_POST['username'];
    $userpass = $_POST['password'];
    if (isset($_POST['lcookies'])) $lcookies = $_POST['lcookies'];
    
    // Security fix
    $valid_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $valid_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    
    // Write the log file each time someone tries to login before
    $jakuserlogin->jakWriteloginlog($username, $_SERVER['REQUEST_URI'], $valid_ip, $valid_agent, 0);

    $user_check = $jakuserlogin->jakCheckuserdata($username, $userpass);
    if ($user_check == true) {
    
    	// Now login in the user
        $jakuserlogin->jakLogin($user_check, $userpass, $lcookies);
        
        // Write the log file each time someone login after to show success
        $jakuserlogin->jakWriteloginlog($user_check, '', $valid_ip, '', 1);
        
        // Unset the recover message
        if (isset($_SESSION['password_recover'])) unset($_SESSION['password_recover']);
        
        if (isset($_SESSION['LCRedirect'])) {
        	jak_redirect($_SESSION['LCRedirect']);
        } else {
        	jak_redirect(BASE_URL);
        }

    } else {
        $errors = $jkl['e'];
    }
}

// Forgot password
 if ($_SERVER["REQUEST_METHOD"] == 'POST' && !empty($_POST['action']) && $_POST['action'] == 'forgot') {
 	$jkp = $_POST;
 
 	if ($jkp['lsE'] == '' || !filter_var($jkp['lsE'], FILTER_VALIDATE_EMAIL)) {
 	    $errormsg = $jkl['e2'];
 	}
 	
 	// transform user email
    $femail = filter_var($_POST['lsE'], FILTER_SANITIZE_EMAIL);
    $fwhen = time();
 	
 	// Check if this user exist
    $user_check = $jakuserlogin->jakForgotpassword($femail, $fwhen);
     
    if (!$user_check) {
        $errormsg = $jkl['e4'];
    } else {
         

        $mail = new PHPMailer(); // defaults to using php "mail()"

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

        // Get user details
        $oname = $jakdb->get("admins", "name", ["AND" => ["email" => $femail, "access" => 1]]);
         	
        $mail->AddAddress($femail);
         	
        $mail->Subject = 'Cloud Chat 3 - '.$jkl['g4'];
        $body = sprintf($jkl['g5'], $oname, '<a href="'.JAK_rewrite::jakParseurl('fp', $fwhen).'">'.JAK_rewrite::jakParseurl('fp', $fwhen).'</a>', $sett["title"]);
         	
        $mail->MsgHTML($body);
        $mail->AltBody = strip_tags($body);
         	
        if ($mail->Send()) {
            $_SESSION["infomsg"] = $jkl["g9"];
            jak_redirect(BASE_URL);	
        }

    }
    
    // Nothing good, show error
    $errors = $errormsg;
}

if ($page == "rfp") {

    // Title and Description
    $SECTION_TITLE = $jkl['g4'];
    $SECTION_DESC = "";

    $template = 'forgot.php';

} else {

    // Title and Description
    $SECTION_TITLE = $jkl['g'];
    $SECTION_DESC = "";

    $template = 'login.php';
}

?>