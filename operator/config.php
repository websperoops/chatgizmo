<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Do not go any further if install folder still exists
if (is_dir('../install')) die('Please delete or rename install folder.');

// The DB connections data
require_once '../include/db.php';

// Get the real stuff
require_once '../config.php';

define('BASE_URL_ADMIN', BASE_URL);
define('BASE_URL_ORIG', str_replace('/'.JAK_OPERATOR_LOC.'/', '/', BASE_URL));
define('BASE_PATH_ORIG', str_replace('/'.JAK_OPERATOR_LOC.'', '/', _APP_MAIN_DIR));

// Include some functions for the ADMIN Area
include_once 'include/admin.function.php';
include_once '../class/class.paginator.php';

// Update last activity from this user
if (JAK_USERID) $jakuserlogin->jakUpdatelastactivity(JAK_USERID);
?>