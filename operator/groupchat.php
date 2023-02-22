<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5.6                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!jak_get_access("groupchat", $jakuser->getVar("permissions"), JAK_MAIN_OP)) jak_redirect(BASE_URL);

// All the tables we need for this plugin
$errors = array();
$jaktable = 'groupchat';
$jaktable1 = 'groupchatmsg';
$jaktable2 = 'groupchatuser';
$jaktable3 = 'user';

// We reset some vars
$newwidg = true;
$totalChange = 0;
$lastChange = '';

// Now start with the plugin use a switch to access all pages
switch ($page1) {

	case 'lock':

		// Check if widget exists and can be locked
		if (is_numeric($page2) && jak_row_exist($page2, $opcacheid, $jaktable, JAK_MAIN_OP)) {

			// Check what we have to do
			$datausrac = $jakdb->get($jaktable, "active", ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
			// update the table
			if ($datausrac) {

				// we turn off the public chat means we save the log into the database and remove the file.
				
				// The chat file
				$groupchatfile = APP_PATH.JAK_CACHE_DIRECTORY.'/groupchat'.$page2.'.txt';

				// Get the file
				if (file_exists($groupchatfile)) $chatfile = file_get_contents($groupchatfile);

				// we have a chatfile
				if (isset($chatfile) && !empty($chatfile)) {

					// Insert into the database
					$jakdb->insert($jaktable1, ["groupchatid" => $page2, "opid" => $opcacheid, "chathistory" => $chatfile, "operatorid" => JAK_USERID, "created" => $jakdb->raw("NOW()")]);

					// Finally remove the file and start fresh
					unlink($groupchatfile);
				}

				$result = $jakdb->update($jaktable, ["active" => 0], ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
			} else {
				$result = $jakdb->update($jaktable, ["active" => 1], ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
			}
		
		if (!$result) {

		    $_SESSION["infomsg"] = $jkl['i'];
		    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		} else {

			// Now let us delete the group chat cache file
	        $cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
	        if (file_exists($cachewidget)) {
	            unlink($cachewidget);
	        }

		    $_SESSION["successmsg"] = $jkl['g14'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		    
		} else {

		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}

	break;
	case 'delete':

		// We want to delete a chat log
		if (is_numeric($page2) && $page3 == "chatlog" && jak_row_exist($page2, $opcacheid, $jaktable, JAK_MAIN_OP)) {

			$result = $jakdb->delete($jaktable1, ["AND" => ["id" => $page2, "opid" => $opcacheid]]);

			if (!$result) {
			    $_SESSION["infomsg"] = $jkl['i'];
			    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
			} else {

				// Write the log file each time someone tries to login before
          		JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 60, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);

			    $_SESSION["successmsg"] = $jkl['g14'];
			    jak_redirect($_SESSION['LCRedirect']);
			}

		// We want to delete a chat
		} elseif (is_numeric($page2) && $page2 != 1) {

			$count = $jakdb->count($jaktable, ["AND" => ["opid" => $opcacheid]]);
		        
			// Now check how many departments we have and do the dirty work
			if ($count > 1) {

				// Delete the chat
				$result = $jakdb->delete($jaktable, ["AND" => ["id" => $page2, "opid" => $opcacheid]]);

				// Delete all the chat logs
				$jakdb->delete($jaktable1, ["AND" => ["groupchatid" => $page2, "opid" => $opcacheid]]);

			}
		
		if (!$result) {

		    $_SESSION["infomsg"] = $jkl['i'];
		    jak_redirect($_SESSION['LCRedirect']);
		} else {

			// Now let us delete the group chat cache file
	        $cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
	        if (file_exists($cachewidget)) {
	            unlink($cachewidget);
	        }

	        // Write the log file each time someone tries to login before
          	JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 57, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);

		    $_SESSION["successmsg"] = $jkl['g14'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		    
		} else {

		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}
		
	break;
	case 'edit':

		// Extra Check for CC3
		if (JAK_MAIN_OP && !jak_cc3_access($jaktable, $opcacheid, $page2)) {
			$_SESSION["errormsg"] = $jkl['i3'];
		   	jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}
	
		// Check if the user exists
		if (is_numeric($page2) && jak_row_exist($page2, $opcacheid, $jaktable, JAK_MAIN_OP)) {
		
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    $jkp = $_POST;
		
		    if (empty($jkp['title'])) {
		        $errors['e'] = $jkl['e2'];
		    }
		    
		    if (count($errors) == 0) {

		    	if (!isset($jkp['jak_opid']) OR in_array("0", $jkp['jak_opid'])) {
			    	$opids = 0;
			    } else {
			    	$opids = join(',', $jkp['jak_opid']);
			    }

			    if (!isset($jkp['jak_float'])) $jkp['jak_float'] = 0;

			    $gcpass = "";
			    if (isset($jkp['jak_password'])) $gcpass = $jkp['jak_password'];

		    	$result = $jakdb->update($jaktable, ["password" => $gcpass,
		    		"title" => $jkp['title'],
		    		"description" => $jkp['description'],
					"opids" => $opids,
					"maxclients" => $jkp['jak_maxclients'],
					"lang" => $jkp['jak_lang'],
					"buttonimg" => $jkp['jak_buttonimg'],
					"floatpopup" => $jkp['jak_float'],
					"floatcss" => $jkp['jak_floatcss']], ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
		
				if (!$result) {
				    $_SESSION["infomsg"] = $jkl['i'];
		    		jak_redirect($_SESSION['LCRedirect']);
				} else {

					$cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
			        if (file_exists($cachewidget)) {
			            unlink($cachewidget);
			        }

					// Write the log file each time someone tries to login before
          			JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 58, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);

				    $_SESSION["successmsg"] = $jkl['g14'];
		    		jak_redirect($_SESSION['LCRedirect']);
				}
		
			// Output the errors
			} else {
				$errors = $errors;
			}
		
			}
		
			// Title and Description
			$SECTION_TITLE = $jkl["m30"];
			$SECTION_DESC = "";

			// Get all operators
			$JAK_OPERATORS = $jakdb->select($jaktable3, ["id", "username"], ["OR" => ["id" => JAK_USERID, "opid" => $opcacheid], "ORDER" => ["username" => "ASC"]]);

			// Call the settings function
			$lang_files = jak_get_lang_files();

			// Get all buttons
    		$BUTTONS_ALL = jak_get_files('../'.JAK_FILES_DIRECTORY.'/buttons');
			
			// Get the data
			$JAK_FORM_DATA = jak_get_data($page2, $opcacheid, $jaktable);

			// Get the 10 latest chat histories
			$JAK_GCHISTORY = $jakdb->select($jaktable1, ["id", "created"], ["AND" => ["groupchatid" => $page2, "opid" => $opcacheid], "ORDER" => ["created" => "DESC"], "LIMIT" => 10]);

			// Include the javascript file for results
			$js_file_footer = 'js_editwidget.php';
			$template = 'editgroupchat.php';
		
		} else {
		    
		   	$_SESSION["errormsg"] = $jkl['i2'];
		    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}
		
	break;
	case 'view':

		// Extra Check for CC3
		if (JAK_MAIN_OP && !jak_cc3_access($jaktable1, $opcacheid, $page2)) {
			$_SESSION["errormsg"] = $jkl['i3'];
		   	jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}

		// Check if the user exists
		if (is_numeric($page2) && jak_row_exist($page2, $opcacheid, $jaktable1, JAK_MAIN_OP)) {

			$datagc = $jakdb->get($jaktable1, ["groupchatid", "chathistory", "created"], ["AND" => ["id" => $page2, "opid" => $opcacheid]]);

			// Each line
			$chatfile = explode(":!n:", $datagc["chathistory"]);

			$chatmsg = "";

			// include the PHP library (if not autoloaded)
			require('../class/class.emoji.php');

			// Get the absolute url for the image
			$ava_url = str_replace(JAK_OPERATOR_LOC.'/', '', BASE_URL);

			if (isset($chatfile) && is_array($chatfile)) foreach ($chatfile as $v) {

				$chatline = jak_string_encrypt_decrypt($v, false);
				
				// We will go trough each file
				$chatline = explode(":#!#:", $chatline);

				// Message format: time:#!#:userid:#!#:name:#!#:avatar:#!#:message:#!#:quote;

				// We want everything except mod
				if ($chatline[0] && $chatline[2] != "*mod*") {

					// Convert urls
					$messagedisp = nl2br(replace_urls($chatline[4]));

					// Convert emotji
					$messagedisp = Emojione\Emojione::toImage($messagedisp);

					// We have a quoted message
					$quoted = "";
					if (isset($chatline[5]) && !empty($chatline[5])) {
						// Convert urls
						$quotemsg = nl2br(replace_urls($chatline[5]));

						// Convert emotji
						$quotemsg = Emojione\Emojione::toImage($quotemsg);

						$quoted = '<blockquote class="blockquote"><i class="fa fa-reply"></i> '.$quotemsg.'</blockquote>';
					}

					$chatmsg .= '<div class="media"><img class="align-self-start mr-3" src="'.$ava_url.JAK_FILES_DIRECTORY.$chatline[3].'" width="30" alt="'.$chatline[2].'"><div class="media-body"><h5 class="mt-0 mb-0">'.$chatline[2].' <span class="small chat-timestamp">'.JAK_base::jakTimesince($chatline[0], JAK_DATEFORMAT, JAK_TIMEFORMAT).'</span></h5><p>'.$quoted.stripcslashes($messagedisp).'</p></div></div>';

				}
			}

			if (isset($chatmsg) && !empty($chatmsg)) {

				// Get the data
				$JAK_FORM_DATA = jak_get_data($datagc["groupchatid"], $opcacheid, $jaktable);

				// Title and Description
				$SECTION_TITLE = $JAK_FORM_DATA["title"].' - '.$jkl["g310"];
				$SECTION_DESC = JAK_base::jakTimesince($JAK_FORM_DATA["created"], JAK_DATEFORMAT, JAK_TIMEFORMAT);

				// Call the template
				$template = 'viewgroupchat.php';
			} else {
			   	$_SESSION["errormsg"] = $jkl['i2'];
			    jak_redirect($_SESSION['LCRedirect']);
			}

		} else {
		   	$_SESSION["errormsg"] = $jkl['i2'];
		    jak_redirect(JAK_rewrite::jakParseurl('groupchat'));
		}

	break;
	default:

		// Get all responses
		$GROUPCHAT_ALL = jak_get_page_info($jaktable, $opcacheid);

		// Let's check if we can add more widgets
		$totalAll = count($GROUPCHAT_ALL);
		if ($jakosub['groupchats'] <= $totalAll) $newwidg = false;

		// We have a form
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_groupchat'])) {
		    $jkp = $_POST;

		    // Limit has been reached, abort
			if (!$newwidg) {
				$_SESSION["errormsg"] = $jkl['i6'];
		    	jak_redirect($_SESSION['LCRedirect']);
			}
		    
		    if (empty($jkp['title'])) {
		        $errors['e'] = $jkl['e2'];
		    }
		        
		   	if (count($errors) == 0) {

		   		if (!isset($jkp['jak_opid']) OR in_array("0", $jkp['jak_opid'])) {
			    	$opids = 0;
			    } else {
			    	$opids = join(',', $jkp['jak_opid']);
			    }

			    $gcpass = "";
			    if (isset($jkp['jak_password'])) $gcpass = $jkp['jak_password'];

		        $jakdb->insert($jaktable, ["opid" => $opcacheid,
		        	"password" => $gcpass,
		        	"title" => $jkp['title'],
					"opids" => $opids,
					"maxclients" => $jkp['jak_maxclients'],
					"lang" => $jkp['jak_lang'],
					"buttonimg" => "colour_on.png",
					"created" => $jakdb->raw("NOW()")]);

		        $lastid = $jakdb->id();

		    	if (!$lastid) {

		    		$_SESSION["infomsg"] = $jkl['i'];
		    		jak_redirect(JAK_rewrite::jakParseurl('groupchat'));

		    	} else {

		    		// Write the log file each time someone tries to login before
          			JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 59, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);

		    		$_SESSION["successmsg"] = $jkl['g14'];
		    		jak_redirect($_SESSION['LCRedirect']);
		    	}
		    
		    // Output the errors
		    } else {
		    
		        $errors = $errors;
		    }  
   
		 }

		// Get all operators
		$JAK_OPERATORS = $jakdb->select($jaktable3, ["id", "username"], ["OR" => ["id" => JAK_USERID, "opid" => $opcacheid], "ORDER" => ["username" => "ASC"]]);

		// Call the settings function
		$lang_files = jak_get_lang_files();

		// Now we have a downgrade
		if (!$newwidg && $totalAll > 1 && $totalAll > $jakosub['groupchats']) {

			if ($jakosub['groupchats'] == 0) {
				$delW = $totalAll - 1;
			} else {
				$delW = $totalAll - $jakosub['groupchats'];
			}
		}

		// How often we had changes
	    $totalChange = $jakdb->count("whatslog", ["AND" => ["opid" => $opcacheid, "whatsid" => [57,58,59,60]]]);

	    // Last Edit
	    if ($totalChange != 0) {
	      $lastChange = $jakdb->get("whatslog", "time", ["AND" => ["opid" => $opcacheid, "whatsid" => [57,58,59,60]], "ORDER" => ["time" => "DESC"], "LIMIT" => 1]);
	    }
		
		// Title and Description
		$SECTION_TITLE = $jkl["m29"];
		$SECTION_DESC = "";
		
		// Include the javascript file for results
		$js_file_footer = 'js_widget.php';
		 
		// Call the template
		$template = 'groupchat.php';
}
?>