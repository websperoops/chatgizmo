<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.3                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

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

// The DB connections data
require_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'include/db.php';

// Get the ls DB class
require_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.db.php';

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

// Finally start the session
session_start();

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

// All important files
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'include/functions.php';
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.browser.php';
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.jakbase.php';
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/PHPMailerAutoload.php';
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.userlogin.php';
include_once str_replace("admin".DIRECTORY_SEPARATOR, "", APP_PATH).'class/class.user.php';

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

// timezone from server
date_default_timezone_set(JAK_TIMEZONESERVER);
$jakdb->query('SET time_zone = "'.date("P").'"');

// Check if https is activated
if (JAK_SITEHTTPS) {
    define('BASE_URL', 'https://' . FULL_SITE_DOMAIN . _APP_MAIN_DIR . '/');
} else {
    define('BASE_URL', 'http://' . FULL_SITE_DOMAIN . _APP_MAIN_DIR . '/');
}

define('BASE_URL_ORIG', str_replace('/admin/', '/', BASE_URL));

// Check if user is logged in
$jakuserlogin = new JAK_userlogin();
$jakuserrow = $jakuserlogin->jakChecklogged();
$jakuser = new JAK_user($jakuserrow);
if ($jakuser) {
	define('JAK_USERID', $jakuser->getVar("id"));
	$jakuserlogin->jakUpdatelastactivity(JAK_USERID);
} else {
	define('JAK_USERID', false);
}

// Get the users ip address
$ipa = get_ip_address();
?>