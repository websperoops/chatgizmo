<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

$cron_url_orig = dirname(__file__) . DIRECTORY_SEPARATOR;
$cron_url = str_replace("include".DIRECTORY_SEPARATOR, "", $cron_url_orig);

if (!file_exists($cron_url.'include/db.php')) die('[cron.php] db.php not exist');
require_once $cron_url.'include/db.php';

if (!file_exists($cron_url.'class/class.db.php')) die('class/[cron.php] class.db.php not exist');
require_once $cron_url.'class/class.db.php';

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

// Check if we have a database connection
if ($jakdb) {

	// Select all accounts that need a welcome email.
	$upduser = $jakdb->select("user", ["id", "email", "username"], ["AND" => ["opid" => 0, "autoupdate" => 1, "autodelete" => 0]]);

	if (isset($upduser) && !empty($upduser) && is_array($upduser)) foreach ($upduser as $row) {
		# code...

		// First we update the status back to zero so we do not send emails twice
		$jakdb->update("user", ["autoupdate" => 0], ["id" => $row["id"]]);

		// Database connection to main site
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

        // Now get the user information to update the table
        $activeuser = $jakdb1->get("users", ["id", "email", "username", "password", "paidtill"], ["AND" => ["opid" => $row["id"], "locationid" => JAK_MAIN_LOC]]);

        if (isset($activeuser) && !empty($activeuser)) {

        	// let's update the user credential
        	$jakdb->update("user", [ 
                "password" => $activeuser["password"],
                "username" => $activeuser["username"],
                "email" => $activeuser["email"]], ["id" => $row["id"]]);

        	// let's update the membership
        	$jakdb->update("subscriptions", ["paidtill" => $activeuser["paidtill"]], ["opid" => $row["id"]]);

        	// Now let us delete the define cache file
			$cachewidget = $cron_url.JAK_CACHE_DIRECTORY.'/opcache'.$row["id"].'.php';
			if (file_exists($cachewidget)) {
				@unlink($cachewidget);
			}

		}

	}

	// Select all accounts that need to be removed
	$deluser = $jakdb->select("user", ["id", "email", "username"], ["AND" => ["opid" => 0, "autoupdate" => 0, "autodelete" => 1]]);

	if (isset($deluser) && !empty($deluser) && is_array($deluser)) foreach ($deluser as $row2) {
		# code...

		// Now check how many languages are installed and do the dirty work
		$jakdb->delete("settings", ["opid" => $row2["id"]]);
		$jakdb->delete("chatwidget", ["opid" => $row2["id"]]);
		$jakdb->delete("chatcustomfields", ["opid" => $row2["id"]]);
   		$jakdb->delete("chatsettings", ["opid" => $row2["id"]]);
		$gcid = $jakdb->get("groupchat", "id", ["opid" => $row2["id"]]);
		$jakdb->delete("groupchat", ["opid" => $row2["id"]]);
		$jakdb->delete("groupchatmsg", ["groupchatid" => $gcid]);
		$jakdb->delete("operatorchat", ["opid" => $row2["id"]]);
		$sessionid = $jakdb->get("sessions", "id", ["operatorid" => $row2["id"]]);
    	$jakdb->delete("transcript", ["convid" => $sessionid]);
   		$jakdb->delete("sessions", ["operatorid" => $row2["id"]]);
   		$contactid = $jakdb->get("contacts", "id", ["operatorid" => $row2["id"]]);
    	$jakdb->delete("contactsreply", ["contactid" => $contactid]);
   		$jakdb->delete("contacts", ["operatorid" => $row2["id"]]);
   		$jakdb->delete("answers", ["opid" => $row2["id"]]);
   		$jakdb->delete("bot_question", ["opid" => $row2["id"]]);
   		$jakdb->delete("responses", ["opid" => $row2["id"]]);
   		$jakdb->delete("autoproactive", ["opid" => $row2["id"]]);
   		$jakdb->delete("subscriptions", ["opid" => $row2["id"]]);
   		$jakdb->delete("departments", ["opid" => $row2["id"]]);
   		$jakdb->delete("checkstatus", ["convid" => $sessionid]);
   		$jakdb->delete("push_notification_devices", ["userid" => $row2["id"]]);
   		$jakdb->delete("whatslog", ["opid" => $row2["id"]]);
   		$jakdb->delete("user", ["opid" => $row2["id"]]);
		$result = $jakdb->delete("user", ["id" => $row2["id"]]);

		// Delete uploaded files
		$targetPathf = CLIENT_UPLOAD_DIR.$row2["id"];
		$removedoublef =  str_replace("//","/",$targetPathf);
		foreach(glob($removedoublef.'*.*') as $jak_unlinkf) {

			@unlink($jak_unlinkf);
				
			@unlink($targetPathf);
				
		}
				
		// Delete Avatar and folder
		$targetPath = $cron_url.JAK_FILES_DIRECTORY.'/'.$row2["id"].'/';
		$removedouble =  str_replace("//","/",$targetPath);
		foreach(glob($removedouble.'*.*') as $jak_unlink) {

			@unlink($jak_unlink);
				
			@unlink($targetPath);
				
		}

		// Delete buttons
		$targetPathb = $cron_url.JAK_FILES_DIRECTORY.'/buttons/'.$row2["id"].'/';
		$removedoubleb =  str_replace("//","/",$targetPathb);
		foreach(glob($removedoubleb.'*.*') as $jak_unlinkb) {

			@unlink($jak_unlinkb);
				
			@unlink($targetPathb);
				
		}

		// Delete slideup images
		$targetPaths = $cron_url.JAK_FILES_DIRECTORY.'/slideimg/'.$row2["id"].'/';
		$removedoubles =  str_replace("//","/",$targetPaths);
		foreach(glob($removedoubles.'*.*') as $jak_unlinks) {

			@unlink($jak_unlinks);
				
			@unlink($targetPaths);
				
		}

		// Delete the widget
		$cachewidget = $cron_url.JAK_CACHE_DIRECTORY.'/opcache'.$row2["id"].'.php';
		if (file_exists($cachewidget)) {
			@unlink($cachewidget);
		}

	}

	// Delete entries older than
	$subs = $jakdb->select("subscriptions", ["opid", "chatwidgets", "groupchats", "operatorchat", "departments", "chathistory", "subscribed", "paygateid", "paidhow", "subscribeid", "paidwhen", "paidtill"], ["active" => 1]);

	if (isset($subs) && !empty($subs)) {

		// Current date
		$loc_date_now = new DateTime();
		$JAK_CURRENT_DATE = $loc_date_now->format('Y-m-d H:i:s');

		foreach ($subs as $v) {
			# code...

			// We make a clean up for expired stuff.
			if ($v["paidtill"] < $JAK_CURRENT_DATE && $v["paidhow"] != "expired") {
				$jakdb->update("subscriptions", ["packageid" => 0, "chatwidgets" => 1, "groupchats" => 0, "operatorchat" => 0, "operators" => 1, "departments" => 1, "files" => 0, "activechats" => 3, "chathistory" => 30, "islc3" => 0, "ishd3" => 0, "validfor" => 0, "paygateid" => $v["paygateid"], "subscribeid" => 0, "subscribed" => 0, "amount" => 0, "currency" => "", "paidhow" => "expired", "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $v["paidtill"], "trial" => 0, "active" => 0], ["opid" => $v["opid"]]);

				if ($v["subscribed"] && $v["subscribeid"]) {

					// Database connection to main site
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

					$jakdb1->update("subscriptions", ["subscribeid" => 0, "subscribed" => 0, "active" => 0], ["AND" => ["locationid" => JAK_MAIN_LOC, "userid" => $v["opid"], "subscribeid" => $v["subscribeid"]]]);
				}
			}

			// The time we have to go back
			$deleteold = strtotime("-".$v['chathistory']." days");

			// Delete Leads older then
			$sessionid = $jakdb->select("sessions", "id", ["AND" => ["opid" => $v['opid'], "ended[<]" => $deleteold]]);
			if (isset($sessionid) && !empty($sessionid)) foreach ($sessionid as $s) {
				// Remove stuff
		    	$jakdb->delete("transcript", ["convid" => $s]);
		    	$jakdb->delete("checkstatus", ["convid" => $s]);
		   		$jakdb->delete("sessions", ["id" => $s]);
		   	}

		   	// Delete old Operator Chats private and public
		   	$jakdb->delete("operatorchat", ["sent[<]" => $deleteold]);

		   	// Mysql nice format for other tables
		   	$deleteoldmysql = date('Y-m-d H:i:s', $deleteold);
		   	
		   	// Delete Contacts older then
		   	$contactid = $jakdb->select("contacts", "id", ["AND" => ["opid" => $v['opid'], "sent[<]" => $deleteoldmysql]]);
		   	if (isset($contactid) && !empty($contactid)) foreach ($contactid as $c) {
			   	$jakdb->delete("contactsreply", ["contactid" => $c]);
		       	$jakdb->delete("contacts", ["id" => $c]);
		    }

	       	// We need online user list clean up
	       	$jakdb->delete("buttonstats", ["AND" => ["opid" => $v['opid'], "lasttime[<]" => $deleteoldmysql]]);

	       	// We need to clean up the push notifications table if entries are older than one month
	       	$jakdb->delete("push_notification_devices", ["lastedit[<]" => $deleteoldmysql]);

	       	// Remove all expired accounts older than 1 Month and subtract it from the op settings.
	       	if ($jakdb->has("subscriptions", "id", ["AND" => ["opid" => $v['opid'], "extraoperators[!]" => 0]])) {
	       		
	       		// The time we have to go back
				$deleteoldop = strtotime("-1 month");
				// Mysql nice format for other tables
		   		$deleteoldmysqlop = date('Y-m-d H:i:s', $deleteoldop);

		       	$oldops = $jakdb->select("user", ["id", "opid"], ["AND" => ["extraop" => 1, "validtill[<]" => $deleteoldmysqlop, "opid" => $v['opid']]]);
				if (isset($oldops) && !empty($oldops)) foreach ($oldops as $o) {
					// Remove and update stuff
			    	$jakdb->delete("user", ["id" => $o["id"]]);
			    	$jakdb->delete("push_notification_devices", ["userid" => $o["id"]]);
			    	$jakdb->delete("user_stats", ["userid" => $o["id"]]);
			    	$jakdb->update("subscriptions", ["extraoperators[-]" => 1], ["opid" => $o["opid"]]);

			    	// Delete the cache file
					$cachewidget = $cron_url.JAK_CACHE_DIRECTORY.'/opcache'.$o["opid"].'.php';
					if (file_exists($cachewidget)) {
						@unlink($cachewidget);
					}
			   	}
		   	}

		   	// We remove old entries due a downgrade
		   	$twidget = $jakdb->count("chatwidget", ["opid" => $v["opid"]]);
		   	$tgroupwidget = $jakdb->count("groupchat", ["opid" => $v["opid"]]);
		   	$tdepartment = $jakdb->count("departments", ["opid" => $v["opid"]]);

		   	// We remove the chat widgets if
		   	if (isset($twidget) && $twidget > 1 && $v["chatwidgets"] < $twidget) {

		   		// We calculate how many we have to delete
		   		if ($v['chatwidgets'] == 0) {
					$delW = $twidget - 1;
				} else {
					$delW = $twidget - $v['chatwidgets'];
				}
				// We delete the newest ones
		   		$jakdb->delete("chatwidget", ["opid" => $v["opid"], "ORDER" => ["created" => "DESC"], "LIMIT" => $delW]);
		   	}

		   	// We remove the group chats if
		   	if (isset($tgroupwidget) && $tgroupwidget > 1 && $v["groupchats"] < $tgroupwidget) {

		   		// We calculate how many we have to delete
		   		if ($v['groupchats'] == 0) {
					$delGW = $tgroupwidget - 1;
				} else {
					$delGW = $tgroupwidget - $v['groupchats'];
				}
				// We delete the newest ones
		   		$jakdb->delete("groupchat", ["opid" => $v["opid"], "ORDER" => ["created" => "DESC"], "LIMIT" => $delGW]);
		   	}

		   	// We remove the chat widgets if
		   	if (isset($tdepartment) && $tdepartment > 1 && $v["departments"] < $tdepartment) {

		   		// We calculate how many we have to delete
		   		if ($v['departments'] == 0) {
					$delDep = $tdepartment - 1;
				} else {
					$delDep = $tdepartment - $v['departments'];
				}
				// We delete the newest ones
		   		$jakdb->delete("departments", ["opid" => $v["opid"], "ORDER" => ["time" => "DESC"], "LIMIT" => $delDep]);
		   	}
		}
	}

	// Finally run the optimisation of all tables
	$tables = $jakdb->query('SHOW TABLES')->fetchAll();

    foreach ($tables as $db => $tablename) { 
        $jakdb->query('OPTIMIZE TABLE '.$tablename[0]); 
    }
					
}

?>