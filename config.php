<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.3                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Error reporting:
error_reporting(E_ALL^E_NOTICE);

// The DB connections data
require_once 'include/db.php';

// Do not go any further if install folder still exists
if (is_dir('install')) die('Please delete or rename install folder.');

if (!JAK_CACHE_DIRECTORY) die('Please define a cache directory in the db.php.');

// Start the session
$cookie_secure = true; // if you only want to receive the cookie over HTTPS
$cookie_httponly = true; // prevent JavaScript access to session cookie
$cookie_samesite = 'None';
if(PHP_VERSION_ID < 70300) {
    session_set_cookie_params(JAK_COOKIE_TIME, JAK_COOKIE_PATH.'; samesite='.$cookie_samesite, $_SERVER['HTTP_HOST'], $cookie_secure, $cookie_httponly);
} else {
    session_set_cookie_params([
        'lifetime' => JAK_COOKIE_TIME,
        'path' => JAK_COOKIE_PATH,
        'secure' => $cookie_secure,
        'httponly' => $cookie_httponly,
        'samesite' => $cookie_samesite
    ]);
}
session_start();

// Absolute Path
define('APP_PATH', dirname(__file__) . DIRECTORY_SEPARATOR);

if (isset($_SERVER['SCRIPT_NAME'])) {

    # on Windows _APP_MAIN_DIR becomes \ and abs url would look something like HTTP_HOST\/restOfUrl, so \ should be trimed too
    # @modified Chis Florinel <chis.florinel@candoo.ro>
    $app_main_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    define('_APP_MAIN_DIR', $app_main_dir);
} else {
    die('[config.php] Cannot determine APP_MAIN_DIR, please set manual and comment this line');
}

// Get the DB class
require_once 'class/class.db.php';

// Change for 3.0.3
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

// All important files
include_once 'include/functions.php';
include_once 'class/class.browser.php';
include_once 'class/class.jakbase.php';
include_once 'class/class.userlogin.php';
include_once 'class/class.user.php';
require_once 'class/phpmailer/Exception.php';
require_once 'class/phpmailer/PHPMailer.php';
require_once 'class/phpmailer/SMTP.php';
// require_once 'class/phpmailer/OAuth.php';

// Windows Fix if !isset REQUEST_URI
if (!isset($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
}

// Now launch the rewrite class, depending on the settings in db.
$_SERVER['REQUEST_URI'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES);
$getURL = New JAK_rewrite($_SERVER['REQUEST_URI']);

// We are not using apache so take the ugly urls
$tempp = $getURL->jakGetseg(0);
$tempp1 = $getURL->jakGetseg(1);
$tempp2 = $getURL->jakGetseg(2);
$tempp3 = $getURL->jakGetseg(3);
$tempp4 = $getURL->jakGetseg(4);
$tempp5 = $getURL->jakGetseg(5);
$tempp6 = $getURL->jakGetseg(6);
$tempp7 = $getURL->jakGetseg(7);

// Check if we want caching
if (!is_dir(APP_PATH.JAK_CACHE_DIRECTORY)) mkdir(APP_PATH.JAK_CACHE_DIRECTORY, 0755);

// define file better for caching
$cachedefinefile = APP_PATH.JAK_CACHE_DIRECTORY.'/define.php';

if (!file_exists($cachedefinefile)) {

$allsettings = "<?php\n";

// Get the general settings out the database
$datasett = $jakdb->select("settings",["varname", "used_value"], ["opid" => 0]);
foreach ($datasett as $row) {
    // Now check if sting contains html and do something about it!
    if (strlen($row['used_value']) != strlen(filter_var($row['used_value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS))) {
    	$defvar  = 'htmlspecialchars_decode("'.htmlspecialchars($row['used_value']).'")';
    } else {
    	$defvar = "'".$row["used_value"]."'";
    }
    	
    $allsettings .= "define('JAK_".strtoupper($row['varname'])."', ".$defvar.");\n";
}
    
$allsettings .= "?>";
        
JAK_base::jakWriteinCache($cachedefinefile, $allsettings, '');

}

// Check if https is activated
if (JAK_SITEHTTPS) {
    define('BASE_URL', 'https://' . FULL_SITE_DOMAIN . _APP_MAIN_DIR . '/');
} else {
    define('BASE_URL', 'http://' . FULL_SITE_DOMAIN . _APP_MAIN_DIR . '/');
}

// Get the users ip address
$ipa = get_ip_address();

// Set all users to not logged in
$mainop = false;
$opcacheid = 0;

// Check if user is logged in
$jakuserlogin = new JAK_userlogin();
$jakuserrow = $jakuserlogin->jakChecklogged();
$jakuser = new JAK_user($jakuserrow);
if ($jakuserrow && is_numeric($jakuser->getVar("id"))) {
    define('JAK_USERID', $jakuser->getVar("id"));

    // Now we get the siblings sorted
    $opcacheid = $jakuser->getVar("id");

    // Check if a sibling has logged in
    if ($jakuser->getVar("opid") != 0) {
        $opcacheid = $jakuser->getVar("opid");
    } else {
        $mainop = true;
    }

} else {
    define('JAK_USERID', false);
}

// All the pages
$page = ($tempp ? jak_url_input_filter($tempp) : '');
$page1 = ($tempp1 ? jak_url_input_filter($tempp1) : '');
$page2 = ($tempp2 ? jak_url_input_filter($tempp2) : '');
$page3 = ($tempp3 ? jak_url_input_filter($tempp3) : '');
$page4 = ($tempp4 ? jak_url_input_filter($tempp4) : '');
$page5 = ($tempp5 ? jak_url_input_filter($tempp5) : '');
$page6 = ($tempp6 ? jak_url_input_filter($tempp6) : '');
$page7 = ($tempp7 ? jak_url_input_filter($tempp7) : '');

// Default
if (!isset($widgetid)) $widgetid = 1;
$widgetlang = "";

// We have the main chat call
if (isset($page) && $page == 'lc') {
    // Write the chat widget id
    if (isset($page2) && is_numeric($page2)) $widgetid = $page2;
    // Write the chat language
    if (isset($page3) && !empty($page3)) $widgetlang = $page3;
}

// Ok we have a link, set the sessions.
if (isset($page) && $page == 'link') {
    // Write the chat widget id
    if (isset($page1) && is_numeric($page1)) $widgetid = $page1;
    // Write the chat language
    if (isset($page2) && !empty($page2)) $widgetlang = $page2;
}

// Set the group chat language
if (strpos($_SERVER['REQUEST_URI'], JAK_OPERATOR_LOC) === false && isset($page) && $page == 'groupchat') {
    // Write the chat language
    if (isset($page2) && !empty($page2)) $widgetlang = $page2;
    // Set the opcacheid
    $opcacheid = $jakdb->get("groupchat", "opid", ["id" => $page1]);
    // Set the groupchat session id
    $_SESSION['groupchatid'] = $page1;
}

// We need to load the correct files
if ($opcacheid == 0) {
    $opcacheid = $jakdb->get("chatwidget", "opid", ["id" => $widgetid]);
}

// Get the operator settings
if (isset($opcacheid) && $opcacheid != 0) {

    $cacheopid = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';

    if (!file_exists($cacheopid)) {

        $opsett = "<?php\n";

        $datasett = $jakdb->select("settings",["varname", "used_value"], ["opid" => $opcacheid]);
        foreach ($datasett as $row) {
            // Now check if sting contains html and do something about it!
            if (strlen($row['used_value']) != strlen(filter_var($row['used_value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS))) {
                $defvar = 'htmlspecialchars_decode("'.htmlspecialchars($row['used_value']).'")';
            } else {
                $defvar = "'".$row["used_value"]."'";
            }
                
            $opsett .= "define('JAK_".strtoupper($row['varname'])."', ".$defvar.");\n";
        }

        $datasettmain = $jakdb->select("settings",["varname", "used_value"], ["varname" => ["allowedo_files", "allowed_files", "updated", "version", "native_app_token", "native_app_key", "o_number", "live_online_status", "client_expired", "client_left", "push_reminder", "proactive_time", "openop", "version", "updated"]]);
        foreach ($datasettmain as $row) {
            // Now check if sting contains html and do something about it!
            if (strlen($row['used_value']) != strlen(filter_var($row['used_value'], FILTER_SANITIZE_FULL_SPECIAL_CHARS))) {
                $defvar  = 'htmlspecialchars_decode("'.htmlspecialchars($row['used_value']).'")';
            } else {
                $defvar = "'".$row["used_value"]."'";
            }
                
            $opsett .= "define('JAK_".strtoupper($row['varname'])."', ".$defvar.");\n";
        }

        $opsett .= "\n\$jakwidget = array();\n";

        // Get the chat widget out the database
        $reswidgarray = $jakdb->select("chatwidget", "*", ["opid" => $opcacheid]);
        if (isset($reswidgarray) && !empty($reswidgarray)) foreach ($reswidgarray as $reswidg) {

            $opsett .= "\$jakwidget['".$reswidg['id']."']['id'] = ".$reswidg['id'].";\n\$jakwidget['".$reswidg['id']."']['opid'] = ".$reswidg['opid'].";\n\$jakwidget['".$reswidg['id']."']['title'] = '".addslashes($reswidg['title'])."';\n\$jakwidget['".$reswidg['id']."']['depid'] = '".$reswidg['depid']."';\n\$jakwidget['".$reswidg['id']."']['singleopid'] = ".$reswidg['singleopid'].";\n\$jakwidget['".$reswidg['id']."']['lang'] = '".stripcslashes($reswidg['lang'])."';\n\$jakwidget['".$reswidg['id']."']['hidewhenoff'] = ".$reswidg['hidewhenoff'].";\n\$jakwidget['".$reswidg['id']."']['dsgvo'] = '".(isset($reswidg['dsgvo']) ? stripcslashes($reswidg['dsgvo']) : "")."';\n\$jakwidget['".$reswidg['id']."']['redirect_url'] = '".(isset($reswidg['redirect_url']) ? stripcslashes($reswidg['redirect_url']) : "")."';\n\$jakwidget['".$reswidg['id']."']['redirect_active'] = '".$reswidg['redirect_active']."';\n\$jakwidget['".$reswidg['id']."']['redirect_after'] = '".$reswidg['redirect_after']."';\n\$jakwidget['".$reswidg['id']."']['feedback'] = '".$reswidg['feedback']."';\n\$jakwidget['".$reswidg['id']."']['hidewhenoff'] = '".$reswidg['hidewhenoff']."';\n\$jakwidget['".$reswidg['id']."']['onlymembers'] = '".$reswidg['onlymembers']."';\n\$jakwidget['".$reswidg['id']."']['template'] = '".stripcslashes($reswidg['template'])."';\n\$jakwidget['".$reswidg['id']."']['avatarset'] = '".stripcslashes($reswidg['avatarset'])."';\n\$jakwidget['".$reswidg['id']."']['btn_tpl'] = '".stripcslashes($reswidg['btn_tpl'])."';\n\$jakwidget['".$reswidg['id']."']['start_tpl'] = '".stripcslashes($reswidg['start_tpl'])."';\n\$jakwidget['".$reswidg['id']."']['chat_tpl'] = '".stripcslashes($reswidg['chat_tpl'])."';\n\$jakwidget['".$reswidg['id']."']['contact_tpl'] = '".stripcslashes($reswidg['contact_tpl'])."';\n\$jakwidget['".$reswidg['id']."']['profile_tpl'] = '".stripcslashes($reswidg['profile_tpl'])."';\n\$jakwidget['".$reswidg['id']."']['feedback_tpl'] = '".stripcslashes($reswidg['feedback_tpl'])."';\n";

            // Get the chat settings for this widget
            $reswidg2 = $jakdb->select("chatsettings", ["lang", "settname", "settvalue"], ["AND" => ["widgetid" => $reswidg["id"], "opid" => $reswidg['opid']]]);
            if (isset($reswidg2) && !empty($reswidg2)) {

                foreach ($reswidg2 as $row3) {

                    $opsett .= "\$widgetsettings['".$reswidg['id']."']['".$row3['settname']."'] = '".addslashes($row3['settvalue'])."';\n";

                }

            }

        }

        // Get the subscription out the database
        $opsett .= "\n\$jakosub = array();\n";
        $dataosub = $jakdb->get("subscriptions", "*", ["opid" => $opcacheid]);
        if (isset($dataosub) && !empty($dataosub)) {

            $opsett .= "\$jakosub['id'] = ".$dataosub['id'].";\n\$jakosub['packageid'] = ".$dataosub['packageid'].";\n\$jakosub['opid'] = ".$dataosub['opid'].";\n\$jakosub['chatwidgets'] = ".$dataosub['chatwidgets'].";\n\$jakosub['groupchats'] = ".$dataosub['groupchats'].";\n\$jakosub['operatorchat'] = ".$dataosub['operatorchat'].";\n\$jakosub['operators'] = ".$dataosub['operators'].";\n\$jakosub['extraoperators'] = ".$dataosub['extraoperators'].";\n\$jakosub['departments'] = ".$dataosub['departments'].";\n\$jakosub['files'] = ".$dataosub['files'].";\n\$jakosub['copyfree'] = ".$dataosub['copyfree'].";\n\$jakosub['activechats'] = ".$dataosub['activechats'].";\n\$jakosub['validfor'] = ".$dataosub['validfor'].";\n\$jakosub['trial'] = ".$dataosub['trial'].";\n\$jakosub['active'] = ".$dataosub['active'].";\n\$jakosub['paidtill'] = '".$dataosub['paidtill']."';\n";
        }

        // empty vars
        $answergrid = $responsegrid = $autoproactivegrid = $botgrid = $blacklistgrid = $filesgrid = array();

        $opsett .= "\n";

        // Get the general settings out the database
        $datafiles = $jakdb->select("files",["id", "path", "name"], ["opid" => $opcacheid]);
        if (isset($datafiles) && !empty($datafiles)) foreach ($datafiles as $rowf) {
            $filesgrid[] = $rowf;
        }
            
        // Get the answers out the database
        $dataansw = $jakdb->select("answers", ["id", "opid", "department", "lang", "message", "fireup", "msgtype"], ["opid" => $reswidg['opid']]);
        if (isset($dataansw) && !empty($dataansw)) foreach ($dataansw as $rowa) {
            $answergrid[] = $rowa;
        }

        // Get the url black list
        $databl = $jakdb->select("urlblacklist", "path", ["opid" => $opcacheid]);
        if (isset($databl) && !empty($databl)) foreach ($databl as $rowb) {
            $blacklistgrid[] = $rowb;
        }
            
        // Get the responses settings out the database
        $datares = $jakdb->select("responses", ["id", "opid", "department", "title", "short_code", "message"], ["opid" => $opcacheid]);
        if (isset($datares) && !empty($datares)) foreach ($datares as $rowr) {
            $responsegrid[] = $rowr;
        }

        // Get the chat bot out of the database
        $databot = $jakdb->select("bot_question", ["id", "opid", "widgetids", "depid", "lang", "question", "answer"], ["AND" => ["active" => 1, "opid" => $opcacheid]]);
        if (isset($databot) && !empty($databot)) foreach ($databot as $rowba) {
            $botgrid[] = $rowba;
        }

        // Get the departments
        $datadep = $jakdb->select("departments", ["id", "title", "email", "faq_url"], ["AND" => ["opid" => $opcacheid, "active" => 1], "ORDER" => ["dorder" => "ASC"]]);
        if (isset($datadep) && !empty($datadep)) foreach ($datadep as $rowd) {
            $departmentgrid[] = $rowd;
        }
            
        // Get the auto proactive out the database
        $dataproact = $jakdb->select("autoproactive", ["opid", "path", "title", "imgpath", "message", "btn_confirm", "btn_cancel", "showalert", "soundalert", "timeonsite", "visitedsites"], ["opid" => $opcacheid]);
        if (isset($dataproact) && !empty($dataproact)) foreach ($dataproact as $rowap) {
            $autoproactivegrid[] = $rowap;
        }
            
        if (!empty($answergrid)) $opsett .= "\$answergserialize = '".base64_encode(gzcompress(serialize($answergrid)))."';\n\n\$LC_ANSWERS = unserialize(gzuncompress(base64_decode(\$answergserialize)));\n";

        if (!empty($blacklistgrid)) $opsett .= "\$blacklistserialize = '".base64_encode(gzcompress(serialize($blacklistgrid)))."';\n\n\$LC_BLACKLIST = unserialize(gzuncompress(base64_decode(\$blacklistserialize)));\n";
            
        if (!empty($responsegrid)) $opsett .= "\$responsegserialize = '".base64_encode(gzcompress(serialize($responsegrid)))."';\n\n\$LC_RESPONSES = unserialize(gzuncompress(base64_decode(\$responsegserialize)));\n";

        if (!empty($botgrid)) $opsett .= "\$botserialize = '".base64_encode(gzcompress(serialize($botgrid)))."';\n\n\$JAK_BOT_ANSWER = unserialize(gzuncompress(base64_decode(\$botserialize)));\n";

        if (!empty($filesgrid)) $opsett .= "\$filesgserialize = '".base64_encode(gzcompress(serialize($filesgrid)))."';\n\n\$LC_FILES = unserialize(gzuncompress(base64_decode(\$filesgserialize)));\n";

        if (!empty($departmentgrid)) $opsett .= "\$departmentgserialize = '".base64_encode(gzcompress(serialize($departmentgrid)))."';\n\n\$LC_DEPARTMENTS = unserialize(gzuncompress(base64_decode(\$departmentgserialize)));\n";

        if (!empty($autoproactivegrid)) $opsett .= "\$autoproactiveserialize = '".base64_encode(gzcompress(serialize($autoproactivegrid)))."';\n\n\$LC_PROACTIVE = unserialize(gzuncompress(base64_decode(\$autoproactiveserialize)));\n";

        // Get the general settings out the database
        $resgc = $jakdb->select("groupchat", "*", ["AND" => ["opid" => $opcacheid, "active" => 1]]);
        if (isset($resgc) && !empty($resgc)) {
            $opsett .= "\n\$groupchat = array();\n";

            foreach ($resgc as $rowgc) {

                $opsett .= "\$groupchat['".$rowgc['id']."']['id'] = '".$rowgc['id']."';\n\$groupchat['".$rowgc['id']."']['opid'] = '".stripcslashes($rowgc['opid'])."';\n\$groupchat['".$rowgc['id']."']['password'] = '".$rowgc['password']."';\n\$groupchat['".$rowgc['id']."']['title'] = '".addslashes($rowgc['title'])."';\n\$groupchat['".$rowgc['id']."']['description'] = '".(!empty($rowgc['description']) ? stripcslashes($rowgc['description']) : "")."';\n\$groupchat['".$rowgc['id']."']['opids'] = '".stripcslashes($rowgc['opids'])."';\n\$groupchat['".$rowgc['id']."']['maxclients'] = ".$rowgc['maxclients'].";\n\$groupchat['".$rowgc['id']."']['lang'] = '".stripcslashes($rowgc['lang'])."';\n\$groupchat['".$rowgc['id']."']['buttonimg'] = '".stripcslashes($rowgc['buttonimg'])."';\n\$groupchat['".$rowgc['id']."']['floatpopup'] = ".$rowgc['floatpopup'].";\n\$groupchat['".$rowgc['id']."']['floatcss'] = '".(!empty($rowgc['floatcss']) ? stripcslashes($rowgc['floatcss']) : "")."';\n\$groupchat['".$rowgc['id']."']['active'] = ".$rowgc['active'].";\n";
               
            }

        }

        // Finally close the cache file
        $opsett .= "?>";

        JAK_base::jakWriteinCache($cacheopid, $opsett, '');

    }

    // Now include the created definefile
    include_once $cacheopid;

    // Your copyright link
    $JAK_PCOPYRIGHT_LINK = '<a href="https://www.jakweb.ch/cloud-chat-3">MyChat</a> / ';
    // Remove the copyright when customer has paid for
    if (isset($jakosub['copyfree']) && $jakosub['copyfree'] == 1) $JAK_PCOPYRIGHT_LINK = "";
    // Copyright do only remove or change with a valid copyright free link license
    define('JAK_COPYRIGHT_LINK', $JAK_PCOPYRIGHT_LINK.'<a href="https://www.jakweb.ch/cloud-chat-3">Powered by Cloud Chat 3</a>');

} else {

    // Now include the created definefile
    include_once $cachedefinefile;
}

// include_once APP_PATH.JAK_CACHE_DIRECTORY.'/opcache2.php';

if (defined('JAK_LANG') && !isset($BT_LANGUAGE)) $BT_LANGUAGE = JAK_LANG;

// Define the MAIN_OP
define('JAK_MAIN_OP', $mainop);

// Current date
$loc_date_now = new DateTime();
$JAK_CURRENT_DATE = $loc_date_now->format('Y-m-d H:i:s');

// timezone from server
if (defined('JAK_TIMEZONESERVER')) date_default_timezone_set(JAK_TIMEZONESERVER);
$jakdb->query('SET time_zone = "'.date("P").'"');
?>