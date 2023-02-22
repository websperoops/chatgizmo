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

// Absolute Path
define('APP_PATH', str_replace(basename(dirname(__file__))."/", "", dirname(__file__) . DIRECTORY_SEPARATOR));

/* NO CHANGES FROM HERE */
if (!file_exists('../class/class.jaklic.php')) die('It looks like the boat has been reported as missing.');

// Get the ls DB class
require_once '../class/class.db.php';

// Get the ls DB class
require_once '../include/functions.php';

// We will need a string
$alphanumstring = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

// DB Pass Hash
$dbpasshash = substr(str_shuffle($alphanumstring), 0, 12);

// Zero Errors
$errors = "";

// Check if db exists
$check_db = true;
// check if db content exists
$check_db_content = false;
// DB Error Message
$db_error_msg = "";

// We will need to set the database file for the server
$dbserver = APP_PATH.'include/db.php';

// We will need to set the database file for the administration panel
$dbadmin = APP_PATH.'signup/include/db.php';

if (is_numeric($_POST['step']) && $_POST['step'] == 2) {

  if (empty($_POST['dbhost1'])) {
      $errors = 'Please insert a valid host for the database connection 1.<br>';
  }

  if (empty($_POST['dbport1'])) {
      $errors .= 'Please insert a valid database port for the database connection 1.<br>';
  }

  if (empty($_POST['dbuser1'])) {
      $errors .= 'Please insert a valid database user for the database connection 1.<br>';
  }

  if (empty($_POST['dbpass1'])) {
      $errors .= 'Please insert a valid database password for the database connection 1.<br>';
  }

  if (empty($_POST['dbname1'])) {
      $errors .= 'Please insert a valid database name for the database connection 1.<br>';
  }

  if (empty($_POST['dbhost2'])) {
      $errors = 'Please insert a valid host for the database connection 2.<br>';
  }

  if (empty($_POST['dbport2'])) {
      $errors .= 'Please insert a valid database port for the database connection 2.<br>';
  }

  if (empty($_POST['dbuser2'])) {
      $errors .= 'Please insert a valid database user for the database connection 2.<br>';
  }

  if (empty($_POST['dbpass2'])) {
      $errors .= 'Please insert a valid database password for the database connection 2.<br>';
  }

  if (empty($_POST['dbname2'])) {
      $errors .= 'Please insert a valid database name for the database connection 2.<br>';
  }

  if ($_POST['dbname1'] == $_POST['dbname2']) {
      $errors .= 'You will need two different databases.<br>';
  }

  // Ok we have no errors from the field, let's check if we can connect to the database
  if (!$errors) {

    // Check the database connection 1
    $dsn1 = 'mysql:dbname='.$_POST['dbname1'].';host='.$_POST['dbhost1'];

    try {
      $dbh1 = new PDO($dsn1, $_POST['dbuser1'], $_POST['dbpass1']);
      $dbh1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      try {
        $dbh1->query("SELECT title FROM cc3_chatwidget WHERE id = 1 LIMIT 1");
      } catch (Exception $e) {
        // We got an exception == table not found
        $fresh_install = true;
      }

    } catch (PDOException $e) {
        $check_db = false;
        $db_error_msg = "Database 1 (Server) ".$e->getMessage()."<br>";
    }

    // Check the database connection 2
    $dsn2 = 'mysql:dbname='.$_POST['dbname2'].';host='.$_POST['dbhost2'];

    try {
      $dbh2 = new PDO($dsn2, $_POST['dbuser2'], $_POST['dbpass2']);
      $dbh2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      try {
        $dbh2->query("SELECT title FROM packages WHERE id = 1 LIMIT 1");
      } catch (Exception $e2) {
        // We got an exception == table not found
        $fresh_install = true;
      }

    } catch (PDOException $e2) {
        $check_db = false;
        $db_error_msg .= "Database 2 (Admin) ".$e2->getMessage()."<br>";
    }

    if ($check_db) {

      if ($fresh_install) {

      // We have a connection, so we can create the database connection files

        if (!file_exists($dbserver)) {

          $dbservercontent = "<?php\n";

          $dbservercontent .= "define('JAKDB_HOST', '".$_POST['dbhost1']."');\n";
          $dbservercontent .= "define('JAKDB_DBTYPE', 'mysql');\n";
          $dbservercontent .= "define('JAKDB_PORT', ".$_POST['dbport1'].");\n";
          $dbservercontent .= "define('JAKDB_USER', '".$_POST['dbuser1']."');\n";
          $dbservercontent .= "define('JAKDB_PASS', '".$_POST['dbpass1']."');\n";
          $dbservercontent .= "define('JAKDB_NAME', '".$_POST['dbname1']."');\n";
          $dbservercontent .= "define('JAKDB_PREFIX', 'cc3_');\n\n";

          $dbservercontent .= "define('JAKDB_MAIN_HOST', '".$_POST['dbhost2']."');\n";
          $dbservercontent .= "define('JAKDB_MAIN_DBTYPE', 'mysql');\n";
          $dbservercontent .= "define('JAKDB_MAIN_PORT', ".$_POST['dbport2'].");\n";
          $dbservercontent .= "define('JAKDB_MAIN_USER', '".$_POST['dbuser2']."');\n";
          $dbservercontent .= "define('JAKDB_MAIN_PASS', '".$_POST['dbpass2']."');\n";
          $dbservercontent .= "define('JAKDB_MAIN_NAME', '".$_POST['dbname2']."');\n";
          $dbservercontent .= "define('JAKDB_MAIN_PREFIX', '');\n\n";

          $dbservercontent .= "define('JAK_MAIN_LOC', 1);\n\n";

          $dbservercontent .= "define('DB_PASS_HASH', '".$dbpasshash."');\n\n";

          $dbservercontent .= "define('FULL_SITE_DOMAIN', '".$_POST['cc3domain']."');\n\n";

          $dbservercontent .= "define('SIGN_UP_URL', 'https://".$_POST['cc3domain']."/signup/');\n\n";

          $dbservercontent .= "define('JAK_USE_APACHE', ".$_POST['rewrite'].");\n\n";

          $dbservercontent .= "define('JAK_SITEHTTPS', ".$_POST['ssl'].");\n\n";

          $dbservercontent .= "define('CLIENT_UPLOAD_DIR', '".$_POST['upath']."');\n\n";

          $dbservercontent .= "define('JAK_FILE_SECRET_KEY', '".substr(str_shuffle($alphanumstring), 0, 8)."');\n";
          $dbservercontent .= "define('JAK_FILE_SECRET_IV', '".substr(str_shuffle($alphanumstring), 0, 8)."');\n\n";

          $dbservercontent .= "define('JAK_STRING_SECRET_KEY', '".substr(str_shuffle($alphanumstring), 0, 8)."');\n";
          $dbservercontent .= "define('JAK_STRING_SECRET_IV', '".substr(str_shuffle($alphanumstring), 0, 8)."');\n\n";

          $dbservercontent .= "define('JAK_OPERATOR_LOC', 'operator');\n\n";

          $dbservercontent .= "define('JAK_COOKIE_PATH', '/');\n";
          $dbservercontent .= "define('JAK_COOKIE_TIME', 2592000);\n\n";

          $dbservercontent .= "define('JAK_CACHE_DIRECTORY', 'cache');\n\n";

          $dbservercontent .= "define('JAK_FILES_DIRECTORY', 'files');\n\n";

          $dbservercontent .= "define('OPERATOR_CHAT_EXPIRE', '7200');\n\n";

          $dbservercontent .= "define('JAK_SUPERADMIN', '1');\n";

          $dbservercontent .= "?>";

          file_put_contents($dbserver, $dbservercontent, LOCK_EX);

        }

        if (!file_exists($dbadmin)) {

          $dbadmincontent = "<?php\n";

          $dbadmincontent .= "define('JAKDB_HOST', '".$_POST['dbhost2']."');\n";
          $dbadmincontent .= "define('JAKDB_DBTYPE', 'mysql');\n";
          $dbadmincontent .= "define('JAKDB_PORT', ".$_POST['dbport2'].");\n";
          $dbadmincontent .= "define('JAKDB_USER', '".$_POST['dbuser2']."');\n";
          $dbadmincontent .= "define('JAKDB_PASS', '".$_POST['dbpass2']."');\n";
          $dbadmincontent .= "define('JAKDB_NAME', '".$_POST['dbname2']."');\n";
          $dbadmincontent .= "define('JAKDB_PREFIX', '');\n\n";

          $dbadmincontent .= "define('DB_PASS_HASH', '".$dbpasshash."');\n\n";

          $dbadmincontent .= "define('FULL_SITE_DOMAIN', '".$_POST['cc3domain']."');\n\n";

          $dbadmincontent .= "define('JAK_USE_APACHE', ".$_POST['rewrite'].");\n\n";

          $dbadmincontent .= "define('JAK_SITEHTTPS', ".$_POST['ssl'].");\n\n";

          $dbadmincontent .= "define('JAK_COOKIE_PATH', '/');\n";
          $dbadmincontent .= "define('JAK_COOKIE_TIME', 2592000);\n\n";

          $dbadmincontent .= "define('JAK_TIMEZONESERVER', 'Europe/Zurich');\n\n";

          $dbadmincontent .= "define('JAK_LANG', 'en');\n\n";

          $dbadmincontent .= "define('JAK_ADMIN_LOC', 'admin');\n\n";

          $dbadmincontent .= "define('JAK_SUPERADMIN', '1');\n";

          $dbadmincontent .= "define('JAK_MAX_CLIENTS', 0);\n";

          $dbadmincontent .= "?>";

          file_put_contents($dbadmin, $dbadmincontent, LOCK_EX);

        }

        if (file_exists($dbserver) && file_exists($dbadmin)) {

          die(json_encode(array("status" => 1)));

        } else {

          $db_error_msg .= '<strong style="color:red">DB Files cannot be created, please change folder permission for /include and signup/include to (0777).</strong>';
          die(json_encode(array("status" => 0, "errors" => $db_error_msg)));

        }

      }

    } else {
      $db_error_msg .= '<strong style="color:red">Could not connect to the database!</strong>';
      die(json_encode(array("status" => 0, "errors" => $db_error_msg)));
    }


} else {
	die(json_encode(array("status" => 0)));
}

} else {
	die(json_encode(array("status" => 0)));
}
?>