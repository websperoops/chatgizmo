<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1980 03:10:00 GMT");

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Change for 3.0.3
use JAKWEB\JAKsql;

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\OAuth;
//Alias the League Google OAuth2 provider class
// use League\OAuth2\Client\Provider\Google;

// Absolute Path
define('APP_PATH', str_replace(basename(dirname(__file__))."/", "", dirname(__file__) . DIRECTORY_SEPARATOR));

if (!file_exists('../include/db.php')) die('[install.php] include/db.php not exist');
require_once '../include/db.php';

/* NO CHANGES FROM HERE */
if (!file_exists('../class/class.jaklic.php')) die('It looks like the boat has been reported as missing.');

// Get the ls DB class
require_once '../class/class.db.php';

// Get the ls DB class
require_once '../include/functions.php';

// Finally verify the license
require_once '../class/class.jaklic.php';
$jaklic = new JAKLicenseAPI();

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

if (is_numeric($_POST['step']) && $_POST['step'] == 5) {

$result = $jakdb->get("settings", "used_value", ["AND" => ["opid" => 0, "varname" => "lang"]]);
    
if ($result) {

$errors = "";

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors = 'Please insert a valid email address.<br>';
}

if (jak_field_not_exist(strtolower($_POST['email']), "user", "email")) {
  $errors .= 'This email address is already on deck, please try a different one.<br>';
}

if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $_POST['uname'])) {
  $errors .= 'Please insert a valid username (A-Z,a-z,0-9,-_).<br>';
}
        
if (jak_field_not_exist(strtolower($_POST['uname']), "user", "username")) {
  $errors .= 'This username is already on board.<br>';
}

if ($_POST['password'] == '') {
  $errors .= 'Please insert a password, it should have at least 8 characters.<br>';
}

if (!empty($_POST['onumber']) && !empty($_POST['envname'])) {
  $license_code = strip_tags(trim($_POST["onumber"]));
  $env_name = strip_tags(trim($_POST["envname"]));

  // Now let's check the license
  $activate_response = $jaklic->activate_license($license_code, $env_name);
  if (empty($activate_response)) {
    $errors .= LB_TEXT_CONNECTION_FAILED.'<br>';
  }

  if ($activate_response['status'] != true) { 
    $errors .= $activate_response['message'].'<br>';
  }

} else {
  $errors .= 'Please insert your purchase code or license number.<br>';
}

if (!$errors) {

// Sanitize Email
$semail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  
$jakdb->update("settings", ["used_value" => $semail], ["varname" => "email"]);
$jakdb->update("settings", ["used_value" => $semail], ["varname" => "smtp_sender"]);
$jakdb->update("settings", ["used_value" => filter_var($_POST['onumber'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)], ["varname" => "o_number"]);

@$jakdb->query('ALTER DATABASE '.JAKDB_NAME.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');

// phpmailer
require_once '../class/phpmailer/Exception.php';
require_once '../class/phpmailer/PHPMailer.php';

$email_body = 'URL: '.FULL_SITE_DOMAIN.'<br>Email: '.$semail.'<br>License: '.filter_var($_POST['onumber'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Send the email to the customer
$mail = new PHPMailer(); // defaults to using php "mail()"
$body = str_ireplace("[\]", "", $email_body);
$mail->SetFrom($semail);
$mail->AddReplyTo($semail);
$mail->AddAddress('lic@jakweb.ch');
$mail->Subject = "Install - Cloud Chat 3 / 3.1.1";
$mail->AltBody = 'HTML Format';
$mail->MsgHTML($body);
$mail->Send();

// Now let us delete all cache files
$cacheallfiles = '../'.JAK_CACHE_DIRECTORY.'/';
$msfi = glob($cacheallfiles."*.php");
if ($msfi) foreach ($msfi as $filen) {
    if (file_exists($filen)) unlink($filen);
}

// Now let's update the admin panel.

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

// The new password encrypt with hash_hmac
// Decode the password so we can actually use it
$pass = base64_decode($_POST['password']);
$passcrypt = hash_hmac('sha256', $pass, DB_PASS_HASH);
$subject = "Install - Cloud Chat 3 - Administration (3.1.1)";
 
$jakdb1->insert("admins", [
  "username" => filter_var($_POST['uname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
  "password" => $passcrypt,
  "email" => $semail,
  "name" => filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
  "language" => "en",
  "time" => $jakdb->raw("NOW()"),
  "access" => 1]);
  
$jakdb1->update("settings", ["used_value" => $semail], ["varname" => "emailaddress"]);
$jakdb1->update("settings", ["used_value" => filter_var($_POST['onumber'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)], ["varname" => "onumber"]);
$jakdb1->update("settings", ["used_value" => filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)], ["varname" => "title"]);
$jakdb1->update("settings", ["used_value" => SIGN_UP_URL], ["varname" => "webaddress"]);

// We update the location
$result = $jakdb1->update("locations", ["title" => filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS),
  "url" => trim("https://".FULL_SITE_DOMAIN."/operator"),
  "db_host" => trim(JAKDB_HOST),
  "db_type" => trim(JAKDB_DBTYPE),
  "db_port" => trim(JAKDB_PORT),
  "db_user" => trim(JAKDB_USER),
  "db_pass" => trim(JAKDB_PASS),
  "db_name" => trim(JAKDB_NAME),
  "db_prefix" => trim(JAKDB_PREFIX),
  "lastedit" => $jakdb->raw("NOW()")], ["id" => 1]);

@$jakdb1->query('ALTER DATABASE '.JAKDB_MAIN_NAME.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci');

// Finish installation of the admin panel

// Now let's create the first super operator

// Clean Username
$cleanusername = filter_var($_POST['uname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Clean Name
$cleanname = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

$trialunix = strtotime("+5 year");
$trialtime = date('Y-m-d H:i:s', $trialunix);
$paidtime = date('Y-m-d H:i:s', strtotime($trialtime));

// The client in the Admin database
$jakdb1->insert("users", [ 
  "locationid" => 1,
  "email" => $semail,
  "username" => strtolower($cleanusername),
  "password" => $passcrypt,
  "signup" => $jakdb1->raw("NOW()"),
  "trial" => $trialtime,
  "paidtill" => $paidtime,
  "lastedit" => $jakdb1->raw("NOW()"),
  "active" => 1]);

$clientid = $jakdb1->id();
 
$jakdb->insert("user", [
  "username" => strtolower($cleanusername),
  "password" => $passcrypt,
  "email" => $semail,
  "name" => $cleanname,
  "operatorchat" => 1,
  "time" => $jakdb->raw("NOW()"),
  "access" => 1]);

$opid = $jakdb->id();

  if ($opid) {

      $newuserpath = APP_PATH.JAK_FILES_DIRECTORY.'/'.$opid;
      
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
          (".$opid.", 'crating', '1', '0'),
          (".$opid.", 'dateformat', '".$opsett["dateformat"]."', 'd.m.Y'),
          (".$opid.", 'email', '".$semail."', '@cc3jak'),
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
          (".$opid.", 'smtp_sender', '".$semail."', ''),
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
      $opcw = $jakdb1->select("chatwidget", '*', ["locid" => 1]);
      foreach ($opcw as $rowcw) {
          # code...
          $jakdb->insert("chatwidget", ["opid" => $opid, "title" => $rowcw["title"], "lang" => $opsett["lang"], "hidewhenoff" => 0, "template" => $rowcw["template"], "created" => $jakdb->raw("NOW()")]);
      }

      // Group Chat
      $opgc = $jakdb1->select("groupchat", '*', ["locid" => 1]);
      foreach ($opgc as $rowgc) {
          # code...
          $jakdb->insert("groupchat", ["opid" => $opid, "title" => $rowgc["title"], "description" => $rowgc["description"], "opids" => 0, "maxclients" => 10, "lang" => $opsett["lang"], "buttonimg" => "colour_on.png", "floatpopup" => 0, "floatcss" => "bottom:20px;left:20px", "active" => 0, "created" => $jakdb->raw("NOW()")]);
      }

      // Insert the chat department
      $opdep = $jakdb1->select("departments", '*', ["locid" => 1]);
      foreach ($opdep as $rowod) {
          # code...
          $jakdb->insert("departments", ["opid" => $opid, "title" => $rowod["title"], "description" => $rowod["description"], "active" => $rowod["active"], "dorder" => $rowod["dorder"], "time" => $jakdb->raw("NOW()")]);
      }

      // Insert the answers
      $opa = $jakdb1->select("answers", '*', ["locid" => 1]);
      foreach ($opa as $rowa) {
          # code...
          $jakdb->insert("answers", [["opid" => $opid, "department" => 0, "lang" => $opsett["lang"], "title" => $rowa["title"], "message" => $rowa["message"], "fireup" => $rowa["fireup"], "msgtype" => $rowa["msgtype"], "created" => $jakdb->raw("NOW()")]]);
      }

      // Insert into the local subscription table
      $jakdb->insert("subscriptions", ["opid" => $opid, "validfor" => $trialunix, "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $trialtime, "registered" => $jakdb->raw("NOW()")]);

      // finally update the main database
      $jakdb1->update("users", [ 
          "opid" => $opid,
          "active" => 1], ["id" => $clientid]);

  }

  $email_body = 'URL: '.FULL_SITE_DOMAIN.'<br />Email: '.$semail;

  // Send the email to the customer
  $mail = new PHPMailer(); // defaults to using php "mail()"
  $body = str_ireplace("[\]", "", $email_body);
  $mail->SetFrom($semail);
  $mail->AddReplyTo($semail);
  $mail->AddAddress('lic@jakweb.ch');
  $mail->Subject = $subject;
  $mail->AltBody = 'HTML Format';
  $mail->MsgHTML($body);
  // $mail->Send();
	
	die(json_encode(array("status" => 1)));

} else {
  die(json_encode(array("status" => 0, "errors" => $errors)));
}

} else {
	die(json_encode(array("status" => 0)));
}

} else {
	die(json_encode(array("status" => 0)));
}
?>