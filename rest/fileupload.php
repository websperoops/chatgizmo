<?php

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1998 03:10:00 GMT");

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('config.php')) die('rest_api config.php not exist');
require_once 'config.php';

$userid = $loginhash = $chatid = "";
if (isset($_REQUEST['userid']) && !empty($_REQUEST['userid']) && is_numeric($_REQUEST['userid'])) $userid = $_REQUEST['userid'];
if (isset($_REQUEST['loginhash']) && !empty($_REQUEST['loginhash'])) $loginhash = $_REQUEST['loginhash'];
if (isset($_REQUEST['chatid']) && !empty($_REQUEST['chatid'])) $chatid = $_REQUEST['chatid'];

if (!empty($userid) && !empty($loginhash) && !empty($chatid) && is_numeric($chatid)) {

	// Let's check if we are logged in
	$usr = $jakuserlogin->jakCheckrestlogged($userid, $loginhash);

	if ($usr) {

		if (empty($_FILES['fileupload']['name'])) die(json_encode(array('status' => false, 'errorcode' => 2)));

		// Select the user fields
		$jakuser = new JAK_user($usr);

		// Now we get the siblings sorted
		$opcacheid = $jakuser->getVar("id");
		if ($jakuser->getVar("opid") != 0) $opcacheid = $jakuser->getVar("opid");

		// User has no permission to upload files, abort
		if (!$jakuser->getVar("files")) die(json_encode(array('status' => false, 'errorcode' => 8)));

		if (!empty($_FILES['fileupload']['name'])) {

			$filename = $_FILES['fileupload']['name']; // original filename
			$jak_xtension = pathinfo($_FILES['fileupload']['name']);
	
			// Check if the extension is valid
			$allowedf = explode(',', JAK_ALLOWEDO_FILES);
			if (in_array(".".$jak_xtension['extension'], $allowedf)) {

				// Get the maximum upload or set to 2
				$postmax = (ini_get('post_max_size') ? filter_var(ini_get('post_max_size'), FILTER_SANITIZE_NUMBER_INT) : "2");
	
				if ($_FILES['fileupload']['size'] <= ($postmax * 1000000)) {

		    		// first get the target path
		    		$targetPathd = CLIENT_UPLOAD_DIR.'/'.$opcacheid.'/';
					$targetPath =  str_replace("//", "/", $targetPathd);
		    	
			    	$tempFile = $_FILES['fileupload']['tmp_name'];
				    $name_space = strtolower($_FILES['fileupload']['name']);
				    $middle_name = str_replace(" ", "_", $name_space);
				    $middle_name = filter_var($middle_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				    $glnrrand = rand(10, 9999);
				    $ufile = str_replace(".", "_" . $glnrrand . ".", $middle_name);

				    $targetFile =  str_replace('//','/',$targetPath).$ufile;
	    			$message = $dbfile.':#:'.$middle_name.':#:'.$mime_type;
				    	
				    // Move file     
				    move_uploaded_file($tempFile, $targetFile);

				    $jakdb->insert("transcript", [ 
						"name" => $jakuser->getVar("name"),
						"message" => $message,
						"user" => $userid.'::'.$jakuser->getVar("username"),
						"operatorid" => $userid,
						"convid" => $chatid,
						"class" => "download",
						"time" => $jakdb->raw("NOW()")]);

				    $jakdb->update("checkstatus", ["newc" => 1, "typeo" => 0], ["convid" => $chatid]);

				    die(json_encode(array('status' => true)));
		    	     		
		    	} else {
						die(json_encode(array('status' => false, 'errorcode' => 3)));
				}
		    } else {
					die(json_encode(array('status' => false, 'errorcode' => 2)));
			}
		}
	} else {
		die(json_encode(array('status' => false, 'errorcode' => 1)));
	}
}

die(json_encode(array('status' => false, 'errorcode' => 7)));
?>