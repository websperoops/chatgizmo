<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.2                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2018 JAKWEB All Rights Reserved # ||
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

    if (empty($jkp['emailtitle'])) {
	   $errors['e'] = $jkl['e11'];
	}

    if (count($errors) == 0) {

        // Update the fields
        $jakdb->update("settings", ["used_value" => $jkp['welcomemsg']], ["varname" => "welcomemsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailtitle']], ["varname" => "emailtitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['webhello']], ["varname" => "webhello"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailsignup']], ["varname" => "emailsignup"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailwelcome']], ["varname" => "emailwelcome"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailpass']], ["varname" => "emailpass"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailpaid']], ["varname" => "emailpaid"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailpaidlc3']], ["varname" => "emailpaidlc3"]);
        $jakdb->update("settings", ["used_value" => $jkp['lc3confirm']], ["varname" => "lc3confirm"]);
        $jakdb->update("settings", ["used_value" => $jkp['lc3update']], ["varname" => "lc3update"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailpaidhd3']], ["varname" => "emailpaidlhd3"]);
        $jakdb->update("settings", ["used_value" => $jkp['hd3confirm']], ["varname" => "hd3confirm"]);
        $jakdb->update("settings", ["used_value" => $jkp['hd3update']], ["varname" => "hd3update"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailmoved']], ["varname" => "emailmoved"]);
        $jakdb->update("settings", ["used_value" => $jkp['emailexpire']], ["varname" => "emailexpire"]);
        $jakdb->update("settings", ["used_value" => $jkp['newtickettitle']], ["varname" => "newtickettitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['newticketmsg']], ["varname" => "newticketmsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['welcomedash']], ["varname" => "welcomedash"]);
        $jakdb->update("settings", ["used_value" => $jkp['appboxes']], ["varname" => "appboxes"]);
        $jakdb->update("settings", ["used_value" => $jkp['expiredmsgdash']], ["varname" => "expiredmsgdash"]);
        $jakdb->update("settings", ["used_value" => $jkp['trialdate']], ["varname" => "trialdate"]);
        $jakdb->update("settings", ["used_value" => $jkp['heldashpmsg']], ["varname" => "heldashpmsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['purchasedtitle']], ["varname" => "purchasedtitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['packageseltitle']], ["varname" => "packageseltitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['addoptitle']], ["varname" => "addoptitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['businesshours']], ["varname" => "businesshours"]);
        $jakdb->update("settings", ["used_value" => $jkp['addopsmsg']], ["varname" => "addopsmsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['moreopmsg']], ["varname" => "moreopmsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['opwarnmsg']], ["varname" => "opwarnmsg"]);
        $jakdb->update("settings", ["used_value" => $jkp['invoicetitle']], ["varname" => "invoicetitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['invoicecontent']], ["varname" => "invoicecontent"]);
        $jakdb->update("settings", ["used_value" => $jkp['invoicetitle']], ["varname" => "invoicetitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['invoicecontent']], ["varname" => "invoicecontent"]);
        $jakdb->update("settings", ["used_value" => $jkp['subsctitle']], ["varname" => "subsctitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['subsctext']], ["varname" => "subsctext"]);
        $jakdb->update("settings", ["used_value" => $jkp['failedtitle']], ["varname" => "failedtitle"]);
        $jakdb->update("settings", ["used_value" => $jkp['failedtext']], ["varname" => "failedtext"]);
        $jakdb->update("settings", ["used_value" => $jkp['newclient']], ["varname" => "newclient"]);

        $_SESSION["successmsg"] = $jkl['g16'];
        jak_redirect($_SESSION['LCRedirect']);

    } else {
        $errors = $errors;
    }
    
}

// Title and Description
$SECTION_TITLE = $jkl['g207'];
$SECTION_DESC = $jkl['g208'];

// Include the javascript file for results
// $js_file_footer = 'js_settings.php';

// Call the template
$template = 'language.php';

?>