<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// prevent direct php access
define('JAK_ADMIN_PREVENT_ACCESS', 1);

if (!file_exists('config.php')) die('[index.php] config.php not found');
require_once 'config.php';

$page = ($tempp ? jak_url_input_filter($tempp) : '');
$page1 = ($tempp1 ? jak_url_input_filter($tempp1) : '');
$page2 = ($tempp2 ? jak_url_input_filter($tempp2) : '');
$page3 = ($tempp3 ? jak_url_input_filter($tempp3) : '');
$page4 = ($tempp4 ? jak_url_input_filter($tempp4) : '');
$page5 = ($tempp5 ? jak_url_input_filter($tempp5) : '');
$page6 = ($tempp6 ? jak_url_input_filter($tempp6) : '');

// Reset vars
$JAK_SPECIALACCESS = $JAK_UNDELETABLE = $js_file_footer = $JAK_PAGINATE = false;
// Reset Title, Description and Errors
$SECTION_TITLE = $SECTION_DESC = $errors = '';

// Get the redirect into a sessions for better login handler
if ($page && $page != '404' && $page != 'js' && !in_array($page1, array("d","t","c"))) $_SESSION['LCRedirect'] = $_SERVER['REQUEST_URI'];

// Define for template the real request
$realrequest = substr($getURL->jakRealrequest(), 1);
define('JAK_PARSE_REQUEST', $realrequest);

// We need the template folder, title, author and lang as template variable
define('JAK_PAGINATE_ADMIN', 1);

// Get the language for the operator
$USER_LANGUAGE = '';
if (JAK_USERID) $USER_LANGUAGE = strtolower($jakuser->getVar("language"));

// Import the language file
if ($USER_LANGUAGE && file_exists(APP_PATH.'lang/'.$USER_LANGUAGE.'.php')) {
    include_once(APP_PATH.'lang/'.$USER_LANGUAGE.'.php');
    $_SESSION['jak_lcp_lang'] = $USER_LANGUAGE;
} else {
    include_once(APP_PATH.'lang/'.JAK_LANG.'.php');
}

// Ok, user is logged in let's get the settings table
$sett = array();
$settings = $jakdb->select("settings", ["varname", "used_value"]);
foreach ($settings as $v) {
    $sett[$v["varname"]] = $v["used_value"]; 
}

// Set the version
define('JAK_VERSION', $sett["version"]);

// First check if the user is logged in
if (JAK_USERID) {

    // Only the SuperAdmin in the config file see everything
    if (defined("JAK_SUPERADMIN") && in_array(JAK_USERID, explode(",", JAK_SUPERADMIN))) {
        $JAK_SPECIALACCESS = true;
        $JAK_UNDELETABLE = true;
        define('JAK_SUPERADMINACCESS', true);
    } else {
        define('JAK_SUPERADMINACCESS', false);
    }

    // We have access to the operator panel
    define('JAK_ADMINACCESS', true);

    // Get the name from the user for the welcome message
    $JAK_WELCOME_NAME = $jakuser->getVar("name");

} else {
	define('JAK_ADMINACCESS', false);
    define('JAK_SUPERADMINACCESS', false);
}

$checkp = 0;

if (!isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = '';
}

// home
if ($page == '') {
    #show login page only if the admin is not logged in
    #else show homepage
    if (!JAK_USERID) {
        require_once 'login.php';
    } else {
        require_once 'dashboard.php';
        $JAK_PAGE_ACTIVE = 1;
        $checkp = 1; 
    }
    $checkp = 1;
    }
if ($page == 'logout') {
    $checkp = 1;
    if (JAK_USERID) {
        $jakuserlogin->jakLogout(JAK_USERID);
        $_SESSION["successmsg"] = $jkl['g11'];
    }
    jak_redirect(BASE_URL);
}
// Forgot password request
if ($page == 'rfp' && !JAK_USERID) {
    require_once 'login.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// forgot password
if ($page == 'fp') {
    
    if (JAK_USERID || !is_numeric($page1) || !$jakuserlogin->jakForgotactive($page1)) {
        $_SESSION["errormsg"] = $jkl["e5"];
        jak_redirect(BASE_URL);
    }
    	
    // select user
    $row = $jakdb->get("admins", ["id", "name", "email"], ["forgot" => $page1]);
    	
    // create new password
    $password = jak_password_creator();
    $passcrypt = hash_hmac('sha256', $password, DB_PASS_HASH);
    	
    // update table
    $result = $jakdb->update("admins", ["password" => $passcrypt, "forgot" => "0"], ["id" => $row['id']]);
    	
    if (!$result) {
    		
    	$_SESSION["errormsg"] = $jkl["e5"];
    	// redirect back to home
    	jak_redirect(BASE_URL);
    		   
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
    	 

        $mail->AddAddress($row["email"]);  
    	$body = sprintf($jkl['g7'], $row["name"], $password, $sett["title"]);
    	$mail->MsgHTML($body);
    	$mail->AltBody = strip_tags($body);
        $mail->Subject = 'Live Chat PHP Server Admin - '.$jkl['g6'];
    		
    	if ($mail->Send()) {
    		$_SESSION["infomsg"] = $jkl["g8"];
    		jak_redirect(BASE_URL);  	
    	}
    		
    }
    	
    $_SESSION["errormsg"] = $jkl["e5"];
    jak_redirect(BASE_URL);
}
// Locations
if ($page == 'l') {
    require_once 'location.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Clients
if ($page == 'c') {
    require_once 'client.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// LC3 Clients
if ($page == 'lc3') {
    require_once 'lc3client.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// HD3 Clients
if ($page == 'hd3') {
    require_once 'hd3client.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Admins
if ($page == 'u') {
    require_once 'user.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Settings
if ($page == 's') {
    require_once 'settings.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Language Phrases
if ($page == 'lt') {
    require_once 'language.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Subscriptions
if ($page == 'su') {
    require_once 'subscriptions.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Prices and Gateways
if ($page == 'p') {
    require_once 'paygate.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Coupons
if ($page == 'co') {
    require_once 'coupons.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Packages
if ($page == 'pa') {
    require_once 'packages.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Support tickets
if ($page == 't') {
    require_once 'tickets.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
// Support tickets
if ($page == 'm') {
    require_once 'maintenance.php';
    $JAK_PAGE_ACTIVE = 1;
    $checkp = 1;
}
if ($page == '404') {
    // No access
    if (!JAK_USERID) jak_redirect(BASE_URL);
    // Go to the 404 Page
    $SECTION_TITLE = '404 / '.$sett["title"].' Admin';
    $SECTION_DESC = "";
    $template = '404.php';
    $checkp = 1;
}
     
// if page not found
if ($checkp == 0) {
    jak_redirect(JAK_rewrite::jakParseurl('404'));
}

if (isset($template) && $template != '') {
	include_once APP_PATH.'template/'.$template;
}

// Reset success and errors session for next use
unset($_SESSION["successmsg"]);
unset($_SESSION["errormsg"]);
unset($_SESSION["infomsg"]);
?>