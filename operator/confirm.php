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

// Change for 1.0.3
use JAKWEB\JAKsql;

// Already logged in, don't load it again
if (JAK_USERID) jak_redirect(BASE_URL_ADMIN);

// Login IN
if (!empty($page1) && !empty($page2) && is_numeric($page1) && is_numeric($page2)) {

    if ($jakdb->has("user_confirm", ["confirmcode" => $page2])) {

        // Ok, already activated
        $_SESSION["infomsg"] = $jkl['i8'];
        jak_redirect(BASE_URL_ADMIN);

    } else {

        // Database connection to the main site
        $jakdb1 = new JAKsql([
            // required
            'database_type' => JAKDB_MAIN_DBTYPE,
            'database_name' => JAKDB_MAIN_NAME,
            'server' => JAKDB_MAIN_HOST,
            'username' => JAKDB_MAIN_USER,
            'password' => JAKDB_MAIN_PASS,
            'charset' => 'utf8',
            'port' => JAKDB_MAIN_PORT,
            'prefix' => JAKDB_MAIN_PREFIX,
         
            // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
            'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
            ]);

        // Now get the user information
        $activeuser = $jakdb1->get("users", ["id", "email", "username", "password"], ["AND" => ["id" => $page1, "active" => 0, "confirm" => $page2]]);

        // We get the settings for the payment
        $sett = array();
        $settings = $jakdb1->select("settings", ["varname", "used_value"]);
        foreach ($settings as $v) {
            $sett[$v["varname"]] = $v["used_value"]; 
        }

        if (isset($activeuser) && !empty($activeuser)) {

            $jakdb->insert("user", [ 
                "password" => $activeuser["password"],
                "username" => $activeuser["username"],
                "name" => $activeuser["username"],
                "email" => $activeuser["email"],
                "access" => 1,
                "permissions" => "leads,leads_all,off_all,ochat,ochat_all,statistic,statistic_all,files,proactive,usrmanage,responses,departments,settings,logs,answers,widget,groupchat,blacklist,blocklist",
                "time" => $jakdb->raw("NOW()")]);

            $lastid = $jakdb->id();
        
            if ($lastid) {

                $newuserpath = APP_PATH.JAK_FILES_DIRECTORY.'/'.$lastid;
                
                if (!is_dir($newuserpath)) {
                    mkdir($newuserpath, 0755);
                    copy(APP_PATH.JAK_FILES_DIRECTORY."/index.html", $newuserpath."/index.html");
                }

                // Get the settings for this location
                $opsett = array();
                $opsettings = $jakdb1->select("opsettings", ["varname", "used_value"], ["locid" => JAK_MAIN_LOC]);
                foreach ($opsettings as $v) {
                    $opsett[$v["varname"]] = $v["used_value"]; 
                }

                // Create the settings for the operator
                $jakdb->query("INSERT INTO ".JAKDB_PREFIX."settings (`opid`, `varname`, `used_value`, `default_value`) VALUES
                    (".$lastid.", 'crating', '1', '0'),
                    (".$lastid.", 'dateformat', '".$opsett["dateformat"]."', 'd.m.Y'),
                    (".$lastid.", 'email', '".$activeuser["email"]."', '@cc3jak'),
                    (".$lastid.", 'emailcc', '', '@jakcc'),
                    (".$lastid.", 'email_block', '', NULL),
                    (".$lastid.", 'facebook', '', ''),
                    (".$lastid.", 'ip_block', '', NULL),
                    (".$lastid.", 'lang', '".$opsett["lang"]."', '".$opsett["lang"]."'),
                    (".$lastid.", 'chat_upload_standard', '0', '0'),
                    (".$lastid.", 'msg_tone', 'new_message', 'new_message'),
                    (".$lastid.", 'pro_alert', '1', '1'),
                    (".$lastid.", 'ring_tone', 'ring', 'ring'),
                    (".$lastid.", 'send_tscript', '1', '1'),
                    (".$lastid.", 'show_ips', '1', '1'),
                    (".$lastid.", 'smtp_sender', '".$activeuser["email"]."', ''),
                    (".$lastid.", 'smtphost', '', ''),
                    (".$lastid.", 'smtppassword', '', ''),
                    (".$lastid.", 'smtpport', '25', '25'),
                    (".$lastid.", 'smtpusername', '', ''),
                    (".$lastid.", 'smtp_alive', '0', '0'),
                    (".$lastid.", 'smtp_auth', '0', '0'),
                    (".$lastid.", 'smtp_mail', '0', '0'),
                    (".$lastid.", 'smtp_prefix', '', ''),
                    (".$lastid.", 'timeformat', '".$opsett["timeformat"]."', 'g:i a'),
                    (".$lastid.", 'timezoneserver', '".$opsett["timezoneserver"]."', '".$opsett["timezoneserver"]."'),
                    (".$lastid.", 'title', '".$opsett["title"]."', '".$opsett["title"]."'),
                    (".$lastid.", 'twilio_nexmo', '0', '1'),
                    (".$lastid.", 'tw_msg', '".$opsett["tw_msg"]."', '".$opsett["tw_msg"]."'),
                    (".$lastid.", 'tw_phone', '', ''),
                    (".$lastid.", 'tw_sid', '', ''),
                    (".$lastid.", 'tw_token', '', ''),
                    (".$lastid.", 'useravatheight', '".$opsett["useravatheight"]."', '113'),
                    (".$lastid.", 'useravatwidth', '".$opsett["useravatwidth"]."', '150'),
                    (".$lastid.", 'holiday_mode', '0', '0'),
                    (".$lastid.", 'client_push_not', '1', '1'),
                    (".$lastid.", 'engage_sound', 'sound/new_message3', 'sound/new_message3'),
                    (".$lastid.", 'engage_icon', 'fa fa-bells', 'fa fa-bells'),
                    (".$lastid.", 'client_sound', 'sound/hello', 'sound/hello')");

                // Insert the chat widget
                $opcw = $jakdb1->select("chatwidget", '*', ["locid" => JAK_MAIN_LOC]);
                foreach ($opcw as $rowcw) {
                    # code...
                    $jakdb->insert("chatwidget", ["opid" => $lastid, "title" => $rowcw["title"], "lang" => $opsett["lang"], "hidewhenoff" => 0, "template" => $rowcw["template"], "created" => $jakdb->raw("NOW()")]);
                }

                // Group Chat
                $opgc = $jakdb1->select("groupchat", '*', ["locid" => JAK_MAIN_LOC]);
                foreach ($opgc as $rowgc) {
                    # code...
                    $jakdb->insert("groupchat", ["opid" => $lastid, "title" => $rowgc["title"], "description" => $rowgc["description"], "opids" => 0, "maxclients" => 10, "lang" => $opsett["lang"], "buttonimg" => "colour_on.png", "floatpopup" => 0, "floatcss" => "bottom:20px;left:20px", "active" => 0, "created" => $jakdb->raw("NOW()")]);
                }

                // Insert the chat department
                $opdep = $jakdb1->select("departments", '*', ["locid" => JAK_MAIN_LOC]);
                foreach ($opdep as $rowod) {
                    # code...
                    $jakdb->insert("departments", ["opid" => $lastid, "title" => $rowod["title"], "description" => $rowod["description"], "active" => $rowod["active"], "dorder" => $rowod["dorder"], "time" => $jakdb->raw("NOW()")]);
                }

                // Insert the answers
                $opa = $jakdb1->select("answers", '*', ["locid" => JAK_MAIN_LOC]);
                foreach ($opa as $rowa) {
                    # code...
                    $jakdb->insert("answers", [["opid" => $lastid, "department" => 0, "lang" => $opsett["lang"], "title" => $rowa["title"], "message" => $rowa["message"], "fireup" => $rowa["fireup"], "msgtype" => $rowa["msgtype"], "created" => $jakdb->raw("NOW()")]]);
                }

                // Get the trial in the correct format
                $trialunix = strtotime("+".$sett["trialdays"]." day");
                $trialtime = $paidtill = date('Y-m-d H:i:s', $trialunix);

                // Insert into the local subscription table
                $jakdb->insert("subscriptions", ["opid" => $lastid, "validfor" => $sett["trialdays"], "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $trialtime, "registered" => $jakdb->raw("NOW()")]);

                // Now let's check if we have a standard package after sign up.
                if ($jakdb1->has("packages", ["AND" => ["locationid" => JAK_MAIN_LOC, "supackage" => 1, "active" => 1]])) {

                    // First we need the old subscriptions
                    $subs = $jakdb->get("subscriptions", ["id", "packageid", "chatwidgets", "groupchats", "operatorchat", "operators", "departments", "files", "chathistory", "paygateid", "subscribeid", "subscribed"], ["opid" => $lastid]);

                    // Get the package
                    $pack = $jakdb1->get("packages", ["id", "title", "amount", "currency", "chatwidgets", "groupchats", "operatorchat", "operators", "departments", "files", "copyfree", "activechats", "chathistory", "islc3", "ishd3", "validfor"], ["AND" => ["locationid" => JAK_MAIN_LOC, "supackage" => 1, "active" => 1]]);

                    // Paid unix
                    $paidunix = strtotime("+".$pack["validfor"]." days");
                    // get the nice time
                    $paidtill = date('Y-m-d H:i:s', $paidunix);
                    // Price
                    $couponprice = $pack['amount'];
                    // zero
                    $subscribed = $paygateid = $subscribeid = 0;

                    // We collect the customer id from stripe
                    $paygateid = $subs["paygateid"];
                    $subscribeid = $subs["subscribeid"];

                    // We set the current currency and amount
                    $amountopay = $pack['amount'];
                    $currencytopay = $pack['currency'];

                    // Nasty stuff starts
                    if (isset($subs) && isset($pack)) {

                        // Update the main operator subscription
                        update_main_operator($subs, $pack, $currencytopay, $couponprice, $paygateid, $subscribeid, 0, 0, "Standard User Plan", $lastid, JAK_MAIN_LOC);

                    }

                    // finally update the main database
                    $jakdb->update("users", ["paidtill" => $paidtill], ["AND" => ["opid" => $lastid, "locationid" => JAK_MAIN_LOC]]);

                    // We insert the subscription into the main table for that user.
                    $jakdb1->insert("subscriptions", ["packageid" => $pack["id"],
                        "locationid" => JAK_MAIN_LOC,
                        "userid" => $lastid,
                        "amount" => $couponprice,
                        "currency" => $currencytopay,
                        "paidfor" => $pack["title"],
                        "paidhow" => "Standard User Plan",
                        "subscribed" => 0,
                        "paygateid" => $paygateid,
                        "subscribeid" => $subscribeid,
                        "paidwhen" => $jakdb->raw("NOW()"),
                        "paidtill" => $paidtill,
                        "freeplan" => 1,
                        "active" => 1,
                        "success" => 1]);

                    // Set the correct trialtime because it is none
                    $trialtime = "1980-05-06 00:00:00";

                }

                // finally update the main database
                $jakdb1->update("users", [ 
                    "opid" => $lastid,
                    "trial" => $trialtime,
                    "paidtill" => $paidtill,
                    "welcomemsg" => 1,
                    "active" => 1,
                    "confirm" => 0], ["id" => $activeuser["id"]]);

                // So we do not need to connect to main database all the time the user clicks the link
               $jakdb->insert("user_confirm", ["opid" => $lastid, "confirmcode" => $page2, "created" => $jakdb->raw("NOW()")]);

            }

            // Something went wrong
            $_SESSION["successmsg"] = $jkl['i9'];
            jak_redirect(BASE_URL_ADMIN);

        } else {
            // Something went wrong
            $_SESSION["errormsg"] = $jkl['i3'];
            jak_redirect(BASE_URL_ADMIN);
        }
	}
}

// Something went wrong
$_SESSION["errormsg"] = $jkl['i3'];
jak_redirect(BASE_URL_ADMIN);
?>