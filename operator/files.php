<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Check if the user has access to this file
if (!jak_get_access("files", $jakuser->getVar("permissions"), JAK_MAIN_OP)) jak_redirect(BASE_URL);

// All the tables we need for this plugin
$errors = array();
$jaktable = 'files';

// Now start with the plugin use a switch to access all pages
switch ($page1) {

	case 'delete':
		 
		// Check if the file can be deleted
		if (is_numeric($page2)) {
		
			$path = $jakdb->get($jaktable, "path", ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
		        
			// Now delete the record from the database
			$result = $jakdb->delete($jaktable, ["AND" => ["id" => $page2, "opid" => $opcacheid]]);

			// Now let us delete the file
			if (isset($path) && !empty($path)) {
				$filedel = CLIENT_UPLOAD_DIR.$path;
				if (file_exists($filedel)) {
					unlink($filedel);
				}
			}
		
		if (!$result) {
		    $_SESSION["infomsg"] = $jkl['i'];
		    jak_redirect($_SESSION['LCRedirect']);
		} else {
			
			// Now let us delete the define cache file
			$cachestufffile = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
			if (file_exists($cachestufffile)) {
				unlink($cachestufffile);
			}

			// Write the log file each time someone tries to login before
          	JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 84, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);
			
		    $_SESSION["successmsg"] = $jkl['g14'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		    
		} else {
		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		
	break;
	case 'deletef':
		 
		// Check if the file can be deleted
		if (!is_numeric($page2)) {
			
			// Now let us delete the file
			$filedel = APP_PATH.JAK_FILES_DIRECTORY.'/user/'.$page2;
			if (file_exists($filedel)) {
				unlink($filedel);
			}

			// Write the log file each time someone tries to login before
          	JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 84, 0, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);
			
		    $_SESSION["successmsg"] = $jkl['g14'];
		    jak_redirect($_SESSION['LCRedirect']);
		    
		} else {
		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		
	break;
	case 'deletefo':
		 
		// Check if the file can be deleted
		if (!is_numeric($page2)) {
			
			// Now let us delete the file
			$filedel = APP_PATH.JAK_FILES_DIRECTORY.'/operator/'.$page2;
			if (file_exists($filedel)) {
				unlink($filedel);
			}

			// Write the log file each time someone tries to login before
          	JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 84, 0, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);
			
		    $_SESSION["successmsg"] = $jkl['g14'];
		    jak_redirect($_SESSION['LCRedirect']);
		    
		} else {
		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		
	break;
	case 'edit':

		// Extra Check for CC3
		if (JAK_MAIN_OP && !jak_cc3_access($jaktable, $opcacheid, $page2)) {
			$_SESSION["errormsg"] = $jkl['i3'];
		   	jak_redirect(JAK_rewrite::jakParseurl('files'));
		}
	
		// Check if the user exists
		if (is_numeric($page2) && jak_row_exist($page2, $opcacheid, $jaktable, JAK_MAIN_OP)) {
		
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		    $jkp = $_POST;
		
		    if (empty($jkp['name'])) {
		        $errors['e'] = $jkl['e7'];
		    }
		    
		    if (count($errors) == 0) {

		    	$result = $jakdb->update($jaktable, ["name" => $jkp['name'], "description" => $jkp['description']], ["AND" => ["id" => $page2, "opid" => $opcacheid]]);
		
				if (!$result) {
				    $_SESSION["infomsg"] = $jkl['i'];
		    		jak_redirect($_SESSION['LCRedirect']);
				} else {
					
					// Now let us delete the stuff cache file
					$cachestufffile = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
					if (file_exists($cachestufffile)) {
						unlink($cachestufffile);
					}

					// Write the log file each time someone tries to login before
          			JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 82, $page2, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);
					
				    $_SESSION["successmsg"] = $jkl['g14'];
		    		jak_redirect($_SESSION['LCRedirect']);
				}
		
			// Output the errors
			} else {
			
			    $errors = $errors;
			}
			
			}
		
			$JAK_FORM_DATA = jak_get_data($page2, $opcacheid, $jaktable);
			
			// Title and Description
			$SECTION_TITLE = $jkl["m15"];
			$SECTION_DESC = "";

			// Include the javascript file for results
			$js_file_footer = 'js_editfile.php';
			
			$template = 'editfile.php';
		
		} else {
		   	$_SESSION["errormsg"] = $jkl['i3'];
		    jak_redirect($_SESSION['LCRedirect']);
		}
		
	break;
	default:
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['insert_response'])) {
		    $jkp = $_POST;

		    if ($jakosub['trial']) {

	            $_SESSION["infomsg"] = $jkl['i12'];
	            jak_redirect(JAK_rewrite::jakParseurl('files'));
	        }

	        if (!$jakosub['files']) {

	            $_SESSION["infomsg"] = $jkl['i13'];
	            jak_redirect(JAK_rewrite::jakParseurl('files'));
	        }
		        
		        if (empty($_FILES['uploadedfile']['name'])) {
		            $errors['e'] = $jkl['e13'];
		        }
		        
		        if (empty($jkp['name'])) {
		            $errors['e1'] = $jkl['e7'];
		        }

		        // Check if the extension is valid
		        if (count($errors) == 0) {
			        $ls_xtension = pathinfo($_FILES['uploadedfile']['name']);
					$allowedf = explode(',', JAK_ALLOWEDO_FILES);
					if (!in_array(".".$ls_xtension['extension'], $allowedf)) {
						$errors['e'] = $jkl['e13'];
			        }

			        // if mime type is valid
			        $mime_type = jak_mime_content_type($_FILES['uploadedfile']['name'], $ls_xtension['extension']);
			        if (!$mime_type) {
			        	$errors['e'] = $jkl['e13'];
			        }
			    }
		        
		        if (count($errors) == 0) {

		        	// Get the file
		        	$tempFile = $_FILES['uploadedfile']['tmp_name'];
		        	// Rename the file name
				    $name_space = strtolower($_FILES['uploadedfile']['name']);
				    $middle_name = str_replace(" ", "_", $name_space);
				    $middle_name = filter_var($middle_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		        	$glnrrand = rand(10, 9999);
	    			$filename = str_replace(".", "_" . time() . $glnrrand . ".", $middle_name);
		        	
		        	$target_path = CLIENT_UPLOAD_DIR.'/'.$opcacheid.'/'.$filename;
		        	$db_path = '/'.$opcacheid.'/'.$filename;

		        	$createfilesf = CLIENT_UPLOAD_DIR.'/'.$opcacheid;
                
		            if (!is_dir($createfilesf)) {
		                mkdir($createfilesf, 0755);
		            }

		            // Create the htaccess file for extra security
		            jak_create_htaccess($createfilesf);
		        	
		        	if (move_uploaded_file($tempFile, $target_path)) {

		        		// For security we log all file uploads into the database
		        		$jakdb->insert("files_archive", ["opid" => JAK_USERID, "path" => $db_path, "orig_name" => $middle_name, "email" => $jakuser->getVar("email"), "name" => $jkp['name'], "ip" => $ipa, "mime_type" => $mime_type, "created" => $jakdb->raw("NOW()")]);

		        		// Store the files in the files table
		        		$result = $jakdb->insert($jaktable, ["opid" => $opcacheid, "path" => $db_path, "orig_name" => $middle_name, "name" => $jkp['name'], "description" => $jkp['description'], "mime_type" => $mime_type, "created" => $jakdb->raw("NOW()")]);
		    		
		    		}
		    
		    		if (!$result) {
		    		    $_SESSION["infomsg"] = $jkl['i'];
		    			jak_redirect($_SESSION['LCRedirect']);
		    		} else {
		    			
		    			// Now let us delete the stuff cache file
		    			$cachestufffile = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$opcacheid.'.php';
		    			if (file_exists($cachestufffile)) {
		    				unlink($cachestufffile);
		    			}

		    			// Write the log file each time someone tries to login before
          				JAK_base::jakWhatslog('', $opcacheid, JAK_USERID, 0, 83, $lastid, (isset($_COOKIE['WIOgeoData']) ? $_COOKIE['WIOgeoData'] : ''), $jakuser->getVar("username"), $_SERVER['REQUEST_URI'], $ipa, $valid_agent);
		    			
		    		    $_SESSION["successmsg"] = $jkl['g14'];
		    			jak_redirect($_SESSION['LCRedirect']);
		    		}
		    
		    // Output the errors
		    } else {
		    
		        $errors = $errors;
		    }
		    
   
		}

		// Get all answers
		$totalAll = $jakdb->count($jaktable, ["opid" => $opcacheid]);
 
		if ($totalAll != 0) {

			// Paginator
			$logs = new JAK_Paginator;
			$logs->items_total = $totalAll;
			$logs->mid_range = 10;
			$logs->items_per_page = 20;
			$logs->jak_get_page = $page1;
			$logs->jak_where = JAK_rewrite::jakParseurl('files');
			$logs->paginate();
			$JAK_PAGINATE = $logs->display_pages();
					
			// Ouput all logs, well with paginate of course	
			$FILES_ALL = jak_get_page_info($jaktable, $opcacheid, $logs->limit);
		}
		 
		$JAK_USER_FILES = jak_get_files(APP_PATH.JAK_FILES_DIRECTORY.'/user');
		$JAK_OPERATOR_FILES = jak_get_files(APP_PATH.JAK_FILES_DIRECTORY.'/operator');
		 
		$FILES_ALL = jak_get_page_info($jaktable, $opcacheid);
		
		// Title and Description
		$SECTION_TITLE = $jkl["m2"];
		$SECTION_DESC = "";
		
		// Include the javascript file for results
		$js_file_footer = 'js_pages.php';
		
		// Call the template
		$template = 'files.php';
}
?>