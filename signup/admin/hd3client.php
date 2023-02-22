<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 1.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2017 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!JAK_USERID || !jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// Title and Description
$SECTION_TITLE = $jkl['g102'];
$SECTION_DESC = $jkl['g97'];

// Session
$_SESSION["showlc3hd3"] = 2;

// Include the javascript file for results
$js_file_footer = 'js_lc3client.php';

// Call the template
$template = 'lc3client.php';

?>