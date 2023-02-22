<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.3                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// Reset
$opmain = '';
$count = 0;

use JAKWEB\JAKsql;

// Include the payment class
include_once('../class/class.payment.php');

use YooKassa\Client;

// Now we finally initate the payment module
$JAK_payment = new JAK_payment();

// Now if we have multi site we have fully automated process
if (!empty(JAKDB_MAIN_NAME) && JAK_MAIN_LOC && JAK_MAIN_OP) {
	
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

	// We get the user data from the main table
	$opmain = $jakdb1->get("users", ["id", "signup", "trial", "paidtill", "active"], ["AND" => ["opid" => JAK_USERID, "locationid" => JAK_MAIN_LOC]]);

	// Now we get the packages
	$packages = $jakdb1->select("packages", ["id", "title", "description", "previmg", "amount", "currency", "operators", "departments", "files", "chatwidgets", "copyfree", "activechats", "chathistory", "islc3", "ishd3", "validfor", "multipleuse", "isfree"], ["AND" => ["locationid" => JAK_MAIN_LOC, "active" => 1], "GROUP" => "id", "ORDER" => ["lastedit" => "DESC"]]);

	//  We will need the payment gateways for each pacakge
	$paygate = $jakdb1->select("package_gateways", ["[>]payment_gateways" => ["paygateid" => "id"], "[>]packages" => ["packageid" => "id"], "[>]currency_conversations" => ["packageid" => "packageid"]], ["package_gateways.packageid", "payment_gateways.id", "payment_gateways.paygateid", "payment_gateways.currency(pcurrency)", "payment_gateways.title", "payment_gateways.secretkey_one", "payment_gateways.emailkey", "payment_gateways.bank_info", "payment_gateways.sandbox", "packages.amount", "packages.currency", "currency_conversations.id(ccid)", "currency_conversations.fromcurrency", "currency_conversations.tocurrency", "currency_conversations.amount(ccamount)", "currency_conversations.updated"], ["AND" => ["package_gateways.locid" => JAK_MAIN_LOC, "payment_gateways.active" => 1], "ORDER" => ["payment_gateways.lastedit" => "DESC"]]);

	// Now we get the subscriptions
	$subscriptions = $jakdb1->select("subscriptions", ["[>]packages" => ["packageid" => "id"]], ["subscriptions.id", "subscriptions.packageid", "subscriptions.amount", "subscriptions.currency", "subscriptions.paidfor", "subscriptions.paidhow", "subscriptions.subscribed", "subscriptions.paidwhen", "subscriptions.paidtill", "subscriptions.active", "subscriptions.success", "packages.title"], ["AND" => ["subscriptions.locationid" => JAK_MAIN_LOC, "subscriptions.userid" => JAK_USERID], "ORDER" => ["subscriptions.paidwhen" => "DESC"]]);

	// Get all extra users
	$JAK_USER_ALL = $jakdb->select("user", "*", ["AND" => ["opid" => JAK_USERID, "validtill[!]" => "1980-05-06 00:00:00"]]);

	// Check if we have some new and unread tickets.
	$count = 0;
	$count = $jakdb1->count("support_tickets", ["AND" => ["userid" => $opmain["id"], "readtime" => 0]]);

	// We get the settings for the payment
    $sett = array();
    $settings = $jakdb1->select("settings", ["varname", "used_value"]);
    foreach ($settings as $v) {
        $sett[$v["varname"]] = $v["used_value"]; 
    }

    // Current time
	$timenow = time();

	// Current date
	$loc_date_now = new DateTime();
	$JAK_CURRENT_DATE = $loc_date_now->format('Y-m-d H:i:s');

    // Now we will need to calculate the currencies and repack the array for easier use.
    foreach ($paygate as $v) {

    	//  Now let's run some currency exchange rate calculating	
    	if ($v["pcurrency"] != $v["currency"]) {

    		if ($v["tocurrency"] != $v["pcurrency"]) {

    			$jakdb1->delete("currency_conversations", ["AND" => ["packageid" => $v["packageid"], "fromcurrency" => $v["currency"], "tocurrency" => $v["tocurrency"]]]);

    		}

    		if (isset($v["updated"]) && !empty($v["updated"]) && isset($v["ccamount"]) && !empty($v["ccamount"]) && (strtotime($v["updated"]) + 86400) < $timenow) {

    			// We have a different currency for the payment gateway so we need to have an exchange rate calculation
    			$paygate[$v["ccamount"]] = convertCurrency($sett["exchangekey"], $v["amount"], $v["currency"], $v["pcurrency"]);

    			$jakdb1->update("currency_conversations", ["amount" => $paygate[$v["ccamount"]], "updated" => $jakdb->raw("NOW()")], ["AND" => ["packageid" => $v["packageid"], "fromcurrency" => $v["currency"], "tocurrency" => $v["pcurrency"]]]);

    		} elseif (!isset($v["ccamount"]) && empty($v["ccamount"])) {
    			// We have a different currency for the payment gateway so we need to have an exchange rate calculation
    			$paygate[$v["ccamount"]] = convertCurrency($sett["exchangekey"], $v["amount"], $v["currency"], $v["pcurrency"]);

    			$jakdb1->insert("currency_conversations", ["packageid" => $v["packageid"], "fromcurrency" => $v["currency"], "tocurrency" => $v["pcurrency"], "amount" => $paygate[$v["ccamount"]], "updated" => $jakdb->raw("NOW()"), "created" => $jakdb->raw("NOW()")]);

    		}

    	}

    }

    // We have a succesful payment or not, let's do the confirmation stuff which is quick and dirty
	if (isset($page1) && $page1 == "success" && isset($page2)) {

		// Happy days Payment has been succesful and no one has cheated
		$page2d = base64_url_decode($page2);

		// userid, packageid, paygateid, coupon, amount, time, planid (title, packageid, interval, week, month, year), subscribed, different currency
		$custom = explode(":#:", $page2d);

		// Reset
		$subscription_id = $subscribe_id = $subscribeToken = 0;

		// Now let's check if we are still on track
		if (isset($custom) && $custom[0] == JAK_USERID && $jakdb->has("payment_security", ["AND" => ["payidnow" => $page2, "success" => 0]])) {

			// Update the payment security
			if (isset($_GET["session_id"]) && !empty($_GET["session_id"])) {
				$jakdb->update("payment_security", ["subscribe_id" => $_GET["session_id"]], ["payidnow" => $page2]);
			   	$subscribe_id = $_GET["session_id"];
			   	$subscribeToken = $subscribe_id;
			}

			// We have the subscription id in the URL
			if (isset($page3) && !empty($page3)) {
			    $subscription_id = $jakdb1->get("payment_plans", "id", ["planid" => $page3]);
		    }

		    // Paystack needs a emailToken
		    if (isset($page4) && !empty($page4)) {
		    	$subscribeToken = $page4;
		    	if (!isset($_GET["session_id"])) {
		    		$jakdb->update("payment_security", ["subscribe_id" => $subscribeToken], ["payidnow" => $page2]);
		    	}
		    }

			$jakdb->update("payment_security", ["success" => 1], ["payidnow" => $page2]);

			// Get the package
	        $pack = $jakdb1->get("packages", ["id", "title", "amount", "currency", "operators", "departments", "files", "copyfree", "chatwidgets", "activechats", "chathistory", "islc3", "ishd3", "validfor", "isfree"], ["AND" => ["id" => $custom[1], "active" => 1]]);

	        // Get the payment gateway
			$paga = $jakdb1->get("payment_gateways", ["id", "paygateid", "currency", "title", "secretkey_one", "secretkey_two", "emailkey", "sandbox"], ["AND" => ["id" => $custom[2], "active" => 1]]);

			// We will need to check if the user has paid
			if (isset($paga["paygateid"]) && $paga["paygateid"] == "yoomoney" && isset($_SESSION["yoomoney"])) {

				// Now we need to check if the payment has been paid.
				$yooclient = new Client();
                $yooclient->setAuth($paga["secretkey_one"], $paga["secretkey_two"]);
				$yoopayment = $yooclient->getPaymentInfo($_SESSION["yoomoney"]);

				if (isset($yoopayment->_status) && $yoopayment->_status == "succeeded" && isset($yoopayment->_paid) && $yoopayment->_paid == true) {


				} else {

					$_SESSION["errormsg"] = $jkl["g300"];
		    		jak_redirect(JAK_rewrite::jakParseurl('extend'));

				}

			}

			// We set the current currency and amount
			$amountopay = $pack['amount'];
			$currencytopay = $pack['currency'];

			// We have received a different currencies
			$differentcurrency = 0;
			if (isset($custom[8]) && is_numeric($custom[8]) && $jakdb1->has("packages", "id", ["id" => $custom[8]])) {

				$cconvert = $jakdb1->get("currency_conversations", ["id", "fromcurrency", "tocurrency", "amount"], ["id" => $custom[8]]);

				if (isset($cconvert) && is_array($cconvert) && $currencytopay == $cconvert["fromcurrency"]) {

					// We have a different currency
					$amountopay = $cconvert['amount'];
					$currencytopay = $cconvert['tocurrency'];

				}

			}

			// Add the payment to the IPN table, so we have some evidence
			$jakdb1->insert("payment_ipn", [
	                "userid" => $custom[0],
	                "status" => "success",
	                "amount" => $amountopay,
	                "currency" => $currencytopay,
	                "txn_id" => $page2,
	                "receiver_email" => $sett["emailaddress"],
	                "payer_email" => $jakuser->getVar("email"),
	                "paid_with" => $paga["paygateid"],
	                "time" => $jakdb->raw("NOW()")]);

			// check that txn_id has not been previously processed
	        $onepay = $jakdb1->count("payment_ipn", ["txn_id" => $page2]);

	        // Current time
	        $timenow = time();

	        if ($onepay == 1 && $jakdb1) {

	        	// First we need the old subscriptions
	        	$subs = $jakdb->get("subscriptions", ["id", "packageid", "operators", "departments", "files", "phpimap", "chathistory", "paygateid", "subscribeid", "subscribed", "paidtill"], ["opid" => $custom[0]]);

	        	// We get the user data from the main table
	        	$couponvalid = false;
	        	$couponprice = $amountopay;
	            if (isset($custom[3]) && !empty($custom[3]) && $jakdb1->has("coupons", ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $custom[3], "active" => 1]])) {
	                       
	                $cd = $jakdb1->get("coupons", ["id", "title", "discount", "freepackageid", "used", "total", "datestart", "dateend", "products"], ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $custom[3], "active" => 1]]);

	                // Nice, we have one let's go through and check if the coupon code is still available
	                if ($cd['used'] < $cd['total'] && $cd['freepackageid'] == 0 && ($cd['datestart'] == 0 && $cd['dateend'] == 0 || $cd['datestart'] < $timenow && $cd['dateend'] > $timenow)) {

	                    // Ok, but is it also for the right product?
	                    if ($cd['products'] == 0 || in_array($pack["id"], explode(",", $cd['products']))) {

	                        // Calculate the discount
	                        $totalD = $amountopay / 100 * $cd['discount'];
	                      	$couponprice = $amountopay - number_format(round($totalD, 2), 2, '.', '');
	                        $couponvalid = true;

	                    }

	                }
	            }

	            // We have Yoomoney we need to do some extra stuff
				if (isset($paga["paygateid"]) && $paga["paygateid"] == "yoomoney" && isset($_SESSION["yoomoney"])) {

					// We insert the subscription id for later use
					if (isset($custom[7]) && $custom[7] == 1) {

						$custom1 = explode("-", $custom[6]);

						$jakdb1->insert("payment_plans", ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $custom1[3], "interval_count" => $custom1[2], "paygateid" => $paga["id"], "planid" => $_SESSION["yoomoney"], "opid" => JAK_USERID, "created" => $jakdb->raw("NOW()")]);

						// And set the token for charging the customer again
						$subscribeToken = $_SESSION["yoomoney"];
						$jakdb->update("payment_security", ["subscribe_id" => $subscribeToken], ["payidnow" => $page2]);

					}

					// Unset the session yoomoney
					unset($_SESSION["yoomoney"]);


				}

	            // is there any open subscription
	            $jakdb1->update("subscriptions", ["subscribeid" => 0, "subscribed" => 0, "active" => 0], ["AND" => ["locationid" => JAK_MAIN_LOC, "userid" => $custom[0]]]);

	            // Get the user details.
	            $mainusr = $jakdb1->get("users", ["opid", "email", "username", "password"], ["AND" => ["opid" => $custom[0], "locationid" => JAK_MAIN_LOC]]);

	            // The unix time stamp for the subscription length
	            $paidunix = strtotime("+".$pack["validfor"]." days");

	            // get the nice time
	            $paidtill = date('Y-m-d H:i:s', $paidunix);

	            // We have an advanced payment
	            if ($pack["islc3"] || $pack["ishd3"]) {

		            // 1 stands for LC3
		            $islc3hd3 = 1;
		            if ($pack["ishd3"]) $islc3hd3 = 2;

		            if ($jakdb1->has("advaccess", ["AND" => ["userid" => $opmain["id"], "opid" => $custom[0]]])) {

			            // Update the advanced access table
			            $jakdb1->update("advaccess", [ 
			                "lastedit" => $jakdb->raw("NOW()"),
			                "paidtill" => $paidtill,
			                "lc3hd3" => $islc3hd3,
			                "paythanks" => 1], ["AND" => ["opid" => $custom[0], "id" => $opmain["id"]]]);
		            } else {
		            	$jakdb1->insert("advaccess", ["userid" => $opmain["id"], "opid" => $custom[0], "lc3hd3" => $islc3hd3, "lastedit" => $jakdb->raw("NOW()"), "paythanks" => 1, "paidtill" => $paidtill, "created" => $jakdb->raw("NOW()")]);
		            }

		            // Ok, we have removed the old stuff and now we update the user subscription table
		            $jakdb->update("subscriptions", ["packageid" => $pack["id"], "operators" => $pack["operators"], "departments" => $pack["departments"], "files" => $pack["files"], "activechats" => $pack["activechats"], "chathistory" => $pack["chathistory"], "islc3" => $pack["islc3"], "ishd3" => $pack["ishd3"], "validfor" => $pack["validfor"], "paygateid" => $subs["paygateid"], "subscribed" => $custom[7], "subscribeid" => $subscription_id, "planid" => $subscribeToken, "amount" => $couponprice, "currency" => $currencytopay, "paidhow" => $paga["id"], "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $paidtill, "trial" => 0, "active" => 1], ["opid" => $custom[0]]);

		            $mailadv = sprintf($jkl['i50'], $jakuser->getVar("name"), $mainusr["username"], $custom[0], $mainusr["email"], $mainusr["password"], $paidunix, ($islc3hd3 == 1 ? 'Live Chat 3' : 'HelpDesk 3'), SIGN_UP_URL.'/process/confirmadv.php?uid='.$custom[0]);

		            // Ok, we send the email // email address, cc email address, reply to, subject, message, attachment
		            jak_send_email(JAK_EMAIL, "", $mainusr["email"], $jkl['i49'], $mailadv, "");

	            } else {

	                // Nasty stuff starts
	               	if (isset($subs) && isset($pack)) {

	                    update_main_operator($subs, $pack, $currencytopay, $couponprice, $paga["id"], $custom[7], $subscription_id, $subscribeToken, $paga["paygateid"], $custom[0], JAK_MAIN_LOC);

	                }

	            }

	            // We insert the subscription into the main table for that user.
	            $jakdb1->insert("subscriptions", ["packageid" => $pack["id"],
	                "locationid" => JAK_MAIN_LOC,
	                "userid" => $custom[0],
	               	"amount" => $couponprice,
	                "currency" => $currencytopay,
	                "paidfor" => $pack["title"],
	                "paidhow" => $paga["paygateid"],
	                "subscribed" => $custom[7],
	                "paygateid" => $subs["paygateid"],
	                "subscribeid" => $subscription_id,
	                "subscribetoken" => $subscribeToken,
	                "paidwhen" => $jakdb->raw("NOW()"),
	                "paidtill" => $paidtill,
	                "active" => 1,
	                "success" => 1]);

	            // finally update the main database
	            $jakdb1->update("users", ["trial" => "1980-05-06 00:00:00",
	                "paidtill" => $paidtill,
	                "payreminder" => 0,
	                "paythanks" => 1,
	                "active" => 1,
	                "confirm" => 0], ["AND" => ["opid" => $custom[0], "locationid" => JAK_MAIN_LOC]]);

	            // Update the coupon counter
	            if ($couponvalid) $jakdb1->update("coupons", ["used[+]" => 1], ["id" => $cd["id"]]);

	            // Now let us delete the define cache file
	            $cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.$custom[0].'.php';
	            if (file_exists($cachewidget)) {
	                unlink($cachewidget);
	            }

	        }

	        $_SESSION["successmsg"] = $jkl["g299"];
			jak_redirect(JAK_rewrite::jakParseurl('extend'));

		}

		$_SESSION["errormsg"] = $jkl["g300"];
		jak_redirect(JAK_rewrite::jakParseurl('extend'));

	} elseif (isset($page1) && $page1 == "cancel") {
		    $_SESSION["errormsg"] = $jkl["g300"];
		    jak_redirect(JAK_rewrite::jakParseurl('extend'));
	} elseif (isset($page1) && $page1 == "withdrawal") {
		// We cancel the subscription

		if (isset($page2) && isset($page3) && $page3 == JAK_USERID) {

			// Now let's get the current subscription
			$subs = $jakdb->get("subscriptions", ["id", "paidhow", "paygateid", "subscribeid", "subscribed", "planid", "paidtill"], ["opid" => JAK_USERID]);

			// Confirm from the main admin panel
			if (isset($subs) && !empty($subs) && $jakdb1->has("subscriptions", "id", ["AND" => ["id" => $page2, "subscribed" => 1, "subscribeid" => $subs["subscribeid"]]])) {

				// Let's get the payment gateway
				$paga = $jakdb1->get("payment_gateways", ["id", "paygateid", "currency", "title", "secretkey_one", "secretkey_two", "emailkey", "sandbox"], ["AND" => ["id" => $subs['paygateid'], "active" => 1]]);

				// Get the left days
				$datenow = new DateTime();  //current date or any date
			  	$paidtill = new DateTime($subs["paidtill"]);   //Future date
			  	$diffdates = $paidtill->diff($datenow)->format("%a");  //find difference
			  	$daysleft = intval($diffdates);   //rounding days

			  	// Canceled success?
			  	$subcanceled = false;

			  	// Go trought the payment gateways
				switch ($subs['paidhow']) {
					case 'stripe':
						// code...

						$subcanceled = $JAK_payment->JAK_pay("stripe", "", "", $subs["planid"], "", "recurring", "cancel_period_end", "", "", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

						break;

					case 'paypal':
						// code...

						$subcanceled = $JAK_payment->JAK_pay("paypal", "", "", $subs["planid"], "User canceled subscription.", "recurring", "cancel", "", "", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

						break;

					case 'verifone':
						// code...

						$_SESSION["infomsg"] = $jkl["i67"];
						jak_redirect(JAK_rewrite::jakParseurl('extend'));

						break;

					case 'authorize.net':
						// code...

						$_SESSION["infomsg"] = $jkl["i67"];
						jak_redirect(JAK_rewrite::jakParseurl('extend'));

						break;

					case 'yoomoney':
						// code...

						// This is not a real cancelation as YooMoney does not charge the user automatically. We just stop charging the user by cron job.
						$subcanceled = true;

						break;

					case 'paystack':
						// code...

						$subcanceled = $JAK_payment->JAK_pay("paystack", "", "", $subs["planid"], "", "cancel_plan", "", "", "", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

						break;
				}			  	

				// All safe and sound
				if ($subcanceled) {

					// Now we need to update the subscribed table
					$jakdb->update("subscriptions", ["subscribeid" => 0, "subscribed" => 0, "planid" => ""], ["opid" => JAK_USERID]);

					$jakdb1->update("subscriptions", ["subscribed" => 0, "subscribed" => 0, "active" => 0], ["id" => $page2]);

					// Now let us delete the define cache file
					$cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.JAK_USERID.'.php';
					if (file_exists($cachewidget)) {
						unlink($cachewidget);
					}

					$_SESSION["successmsg"] = sprintf($jkl["i45"], $daysleft);
		    		jak_redirect(JAK_rewrite::jakParseurl('extend'));

		    	} else {

		    		$_SESSION["errormsg"] = $jkl["i66"];
		    		jak_redirect(JAK_rewrite::jakParseurl('extend'));

		    	}

			} else {

				$_SESSION["errormsg"] = $jkl["i66"];
		    	jak_redirect(JAK_rewrite::jakParseurl('extend'));

			}

		} else {
			$_SESSION["errormsg"] = $jkl["i66"];
		    jak_redirect(JAK_rewrite::jakParseurl('extend'));
		}

	} elseif (isset($page1) && $page1 == "opsuccess" && isset($page2)) {

		// Happy days Payment has been succesful and no one has cheated
		$page2d = base64_url_decode($page2);

		// JAK_USERID.':#:'.$amount.':#:'.$_POST['opcount'].':#:'.microtime().':#:'.$_POST['paidhowop']
		$custom = explode(":#:", $page2d);

		// Now let's check if we are still on track
		if (isset($custom) && $custom[0] == JAK_USERID && $jakdb->has("payment_security", ["AND" => ["payidnow" => $page2, "success" => 0]])) {

			// We will need to check if the user has paid
			if (isset($sett["yookassa_id"]) && $custom[4] == "yoomoney" && isset($_SESSION["yoomoney"])) {

				// Now we need to check if the payment has been paid.
				$yooclient = new Client();
                $yooclient->setAuth($sett["yookassa_id"], $sett["yookassa_secret"]);
				$yoopayment = $yooclient->getPaymentInfo($_SESSION["yoomoney"]);

				if (isset($yoopayment->_status) && $yoopayment->_status == "succeeded" && isset($yoopayment->_paid) && $yoopayment->_paid == true) {


				} else {

					$_SESSION["errormsg"] = $jkl["g300"];
		    		jak_redirect(JAK_rewrite::jakParseurl('extend'));

				}

			}

			// Ok we have a successful payment via Stripe let's add this to the extra operators
			$jakdb->update("subscriptions", ["extraoperators[+]" => $custom[2]], ["opid" => JAK_USERID]);

			$date = new DateTime();
			// Modify the date
			$date->modify('+1 month');
			$paiddate = $date->format('Y-m-d H:i:s');

            // Payment details insert
            $jakdb1->insert("subscriptions", [ 
	            "locationid" => JAK_MAIN_LOC,
	            "userid" => JAK_USERID,
	            "amount" => $custom[1],
	            "currency" => $sett["currency"],
	            "paidfor" => sprintf($jkl['hd344'], $custom[2]),
	            "paidhow" => $custom[4],
	            "paidwhen" => $jakdb->raw("NOW()"),
	            "paidtill" => $paiddate,
	            "success" => 1,
	        	"active" => 1]);

            // Add the payment to the IPN table, so we have some evidence
			$jakdb1->insert("payment_ipn", [
	                "userid" => $custom[0],
	                "status" => "success",
	                "amount" => $custom[1],
	                "currency" => $sett["currency"],
	                "txn_id" => $page2,
	                "receiver_email" => $sett["emailaddress"],
	                "payer_email" => $jakuser->getVar("email"),
	                "paid_with" => $custom[4],
	                "time" => $jakdb->raw("NOW()")]);

			// Now let us delete the define cache file
			$cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.JAK_USERID.'.php';
				if (file_exists($cachewidget)) {
				unlink($cachewidget);
			}

			$_SESSION["successmsg"] = $jkl["g299"];
			jak_redirect(JAK_rewrite::jakParseurl('extend'));

		}

		// Finally redirect the customer
		$_SESSION["errormsg"] = $jkl["g300"];
		jak_redirect(JAK_rewrite::jakParseurl('extend'));

	} elseif (isset($page1) && $page1 == "opextsuccess" && isset($page2)) {

		// Happy days Payment has been succesful and no one has cheated
		$page2d = base64_url_decode($page2);

		// JAK_USERID.':#:'.$amount.':#:'.$_POST['opidext'].':#:'.microtime().':#:'.$_POST['paidhowopext'].':#:'.$_POST['opamountext']
		$custom = explode(":#:", $page2d);

		// Now let's check if we are still on track
		if (isset($custom) && $custom[0] == JAK_USERID && $jakdb->has("payment_security", ["AND" => ["payidnow" => $page2, "success" => 0]])) {

			// We will need to check if the user has paid
			if (isset($sett["yookassa_id"]) && $custom[4] == "yoomoney" && isset($_SESSION["yoomoney"])) {

				// Now we need to check if the payment has been paid.
				$yooclient = new Client();
                $yooclient->setAuth($sett["yookassa_id"], $sett["yookassa_secret"]);
				$yoopayment = $yooclient->getPaymentInfo($_SESSION["yoomoney"]);

				if (isset($yoopayment->_status) && $yoopayment->_status == "succeeded" && isset($yoopayment->_paid) && $yoopayment->_paid == true) {


				} else {

					$_SESSION["errormsg"] = $jkl["g300"];
		    		jak_redirect(JAK_rewrite::jakParseurl('extend'));

				}

			}

			// Ok we have a successful payment via Stripe let's extend the account
			$operator = $jakdb->get("user", ["id", "validtill"], ["AND" => ["id" => $custom[2], "opid" => JAK_USERID]]);
			if ($operator['validtill'] > $JAK_CURRENT_DATE) {
				$date = new DateTime($operator['validtill']);
			} else {
				$date = new DateTime();
				
			}

			// Modify the date
			$date->modify('+'.$custom[5].' month');
			$paiddate = $date->format('Y-m-d H:i:s');

            // Payment details insert
            $jakdb1->insert("subscriptions", [ 
	            "locationid" => JAK_MAIN_LOC,
	            "userid" => JAK_USERID,
	            "amount" => $custom[1],
	            "currency" => $sett["currency"],
	            "paidfor" => sprintf($jkl['hd345'], $custom[2]),
	            "paidhow" => $custom[4],
	            "paidwhen" => $jakdb->raw("NOW()"),
	            "paidtill" => $paiddate,
	            "success" => 1,
	        	"active" => 1]);

            // Add the payment to the IPN table, so we have some evidence
			$jakdb1->insert("payment_ipn", [
	                "userid" => $custom[0],
	                "status" => "success",
	                "amount" => $custom[1],
	                "currency" => $sett["currency"],
	                "txn_id" => $page2,
	                "receiver_email" => $sett["emailaddress"],
	                "payer_email" => $jakuser->getVar("email"),
	                "paid_with" => $custom[4],
	                "time" => $jakdb->raw("NOW()")]);

            // Now finally update the user profile
            $jakdb->update("user", ["validtill" => $paiddate], ["id" => $operator["id"]]);

            // Now let us delete the define cache file
			$cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.JAK_USERID.'.php';
				if (file_exists($cachewidget)) {
				unlink($cachewidget);
			}

			// Finally redirect the customer
			$_SESSION["successmsg"] = $jkl["g299"];
			jak_redirect(JAK_rewrite::jakParseurl('extend'));

		}

		$_SESSION["errormsg"] = $jkl["g300"];
		jak_redirect(JAK_rewrite::jakParseurl('extend'));

	} else {

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['check'])) {

			if ($_POST['check'] == "coupon") {

				$coupon = filter_var($_POST['coupon'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

				if (isset($_POST['pid']) && is_numeric($_POST['pid']) && isset($coupon) && !empty($coupon) && $jakdb1->has("coupons", ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $coupon, "active" => 1]])) {

					// We get the coupon data from the main table
					$cd = $jakdb1->get("coupons", ["id", "discount", "freepackageid", "used", "total", "datestart", "dateend", "products"], ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $coupon, "active" => 1]]);

					// Nice, we have one let's go through and check if the coupon code is still available
					if ($cd['used'] < $cd['total'] && ($cd['datestart'] == 0 && $cd['dateend'] == 0 || $cd['datestart'] < $timenow && $cd['dateend'] > $timenow)) {

						// Ok, but is it also for the right product?
						if ($cd['products'] == 0 || in_array($_POST['pid'], explode(",", $cd['products']))) {

							// We have one great for the client. The last check do we have a freepackageid or a discount.
							if ($cd['freepackageid'] != 0 && $cd['freepackageid'] == $_POST['pid'] && $jakdb1->has("packages", "id", ["AND" => ["id" => $cd['freepackageid'], "islc3" => 0, "ishd3" => 0]])) {

								// We are on checkout
								if (isset($_POST['checkout']) && $_POST['checkout'] == "true") {

									// We have a free packageid and we go for it.

									// First we need the old subscriptions
									$subs = $jakdb->get("subscriptions", ["id", "packageid", "operators", "departments", "files", "phpimap", "chathistory", "paygateid", "subscribeid", "subscribed"], ["opid" => JAK_USERID]);

									// Then we need the new subscription
									$pack = $jakdb1->get("packages", ["id", "title", "amount", "currency", "operators", "departments", "files", "copyfree", "chatwidgets", "activechats", "chathistory", "islc3", "ishd3", "validfor", "isfree"], ["AND" => ["id" => $cd['freepackageid'], "active" => 1]]);

									// Nasty stuff starts
									if (isset($subs) && isset($pack)) {

										// Run the complicated stuff
										update_main_operator($subs, $pack, $sett["currency"], 0, 0, 0, 0, 0, "Free Package - Coupon", JAK_USERID, JAK_MAIN_LOC);

										$paidunix = strtotime("+".$pack["validfor"]." days");
										// get the nice time
										$paidtill = date('Y-m-d H:i:s', $paidunix);

										// Update old subscriptions to none active
			                			$jakdb1->update("subscriptions", ["active" => 0], ["AND" => ["locationid" => JAK_MAIN_LOC, "userid" => JAK_USERID]]);

										// We insert the subscription into the main table for that user.
										$jakdb1->insert("subscriptions", ["packageid" => $pack["id"],
											"locationid" => JAK_MAIN_LOC,
											"userid" => JAK_USERID,
											"amount" => 0,
											"currency" => $sett["currency"],
											"paidfor" => $pack["title"],
											"paidhow" => "Free Package - Coupon",
											"paidwhen" => $jakdb->raw("NOW()"),
											"paidtill" => $paidtill,
											"freeplan" => ($pack["isfree"] ? 1 : 0),
											"active" => 1,
											"success" => 1]);

										// finally update the main database
										$jakdb1->update("users", ["trial" => "1980-05-06 00:00:00",
											"paidtill" => $paidtill,
											"payreminder" => 0,
											"paythanks" => 0,
											"active" => 1,
											"confirm" => 0], ["AND" => ["opid" => JAK_USERID, "locationid" => JAK_MAIN_LOC]]);

										// Update the coupon counter
										$jakdb1->update("coupons", ["used[+]" => 1], ["id" => $cd["id"]]);

										// Now let us delete the define cache file
							            $cachedefinefile = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.JAK_USERID.'.php';
							            if (file_exists($cachedefinefile)) {
							                unlink($cachedefinefile);
							            }

							            $_SESSION["successmsg"] = $jkl['i43'];

							            if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
											header('Cache-Control: no-cache');
											die(json_encode(array("status" => 2, "redirect" => BASE_URL)));
										} else {
											// redirect back to home
											jak_redirect(BASE_URL);
										}

									} else {

										if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
											header('Cache-Control: no-cache');
											die(json_encode(array("status" => 0, "ctext" => $jkl['i34'])));
										} else {
											// redirect back to home
											$_SESSION["errormsg"] = $jkl['i34'];
											jak_redirect(BASE_URL);
										}

									}
									
								} else {
									$pgift = $jkl['i35'];
									$disc_price = 0;
								}
							} else {

								// Calculate the discount
								$totalD = $_POST['amount'] / 100 * $cd['discount'];
								$disc_price = $_POST['amount'] - number_format(round($totalD, 2), 2, '.', '');

								if ($_POST['checkout'] === true) {
									$pgift = $disc_price;
								} else {
									$pgift = sprintf($jkl['i33'], $cd['discount'].'%');
								}
							}

							if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
								header('Cache-Control: no-cache');
								die(json_encode(array("status" => 1, "ctext" => $pgift, "newprice" => $disc_price)));
							} else {
								// redirect back to home
								$_SESSION["successmsg"] = $jkl['g14'];
								jak_redirect(BASE_URL);
							}

						}

					}

				}

				if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
					header('Cache-Control: no-cache');
					die(json_encode(array("status" => 0, "ctext" => $jkl['i34'])));
				} else {
					// redirect back to home
					$_SESSION["errormsg"] = $jkl['i34'];
					jak_redirect(BASE_URL);
				}

			}

			if ($_POST['check'] == "paymember") {

				if (isset($_POST['pid']) && is_numeric($_POST['pid']) && isset($_POST['pgid']) && is_numeric($_POST['pgid']) && $jakdb1->has("packages", "id", ["AND" => ["id" => $_POST['pid'], "active" => 1]]) && $jakdb1->has("payment_gateways", "id", ["AND" => ["id" => $_POST['pgid'], "active" => 1]])) {

					// Get the package
					$pack = $jakdb1->get("packages", ["id", "title", "amount", "currency", "operators", "departments", "files", "copyfree", "chatwidgets", "activechats", "chathistory", "islc3", "ishd3", "validfor", "isfree"], ["AND" => ["id" => $_POST['pid'], "active" => 1]]);

					if ($pack["isfree"] == 1 && $_POST['pgid'] == 0) {
						$paga['paygateid'] = "freeaccess";
						$paga['pgid'] = 0;
						$paga['id'] = 0;
					} else {

						// Get the payment gateway
						$paga = $jakdb1->get("payment_gateways", ["id", "paygateid", "currency", "title", "secretkey_one", "secretkey_two", "emailkey", "bank_info", "sandbox"], ["AND" => ["id" => $_POST['pgid'], "active" => 1]]);

					}

					// We set the current currency and amount
					$amountopay = $pack['amount'];
					$currencytopay = $pack['currency'];

					// We have received a different currencies
					$differentcurrency = 0;
					if (isset($_POST['ccid']) && is_numeric($_POST['ccid']) && $jakdb1->has("packages", "id", ["id" => $_POST['ccid']])) {

						$cconvert = $jakdb1->get("currency_conversations", ["id", "fromcurrency", "tocurrency", "amount"], ["id" => $_POST['ccid']]);

						if (isset($cconvert) && is_array($cconvert) && $currencytopay != $paga["currency"]) {

							// We have a different currency
							$amountopay = $cconvert['amount'];
							$currencytopay = $cconvert['tocurrency'];
							$differentcurrency = $_POST['ccid'];

						}

					}

					// We get the user data from the main table
					$couponcode = $coupontitle = $cval = '';
					$createcoupon = false;
					$couponprice = $amountopay;
					if (isset($_POST["cval"]) && $jakdb1->has("coupons", ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $_POST["cval"], "active" => 1]])) {
						$cd = $jakdb1->get("coupons", ["id", "title", "discount", "freepackageid", "used", "total", "datestart", "dateend", "products"], ["AND" => ["locationid" => JAK_MAIN_LOC, "code" => $_POST["cval"], "active" => 1]]);

						// Nice, we have one let's go through and check if the coupon code is still available
						if ($cd['used'] < $cd['total'] && $cd['freepackageid'] == 0 && ($cd['datestart'] == 0 && $cd['dateend'] == 0 || $cd['datestart'] < $timenow && $cd['dateend'] > $timenow)) {

							// Ok, but is it also for the right product?
							if ($cd['products'] == 0 || in_array($_POST['pid'], explode(",", $cd['products']))) {

								// Calculate the discount
								$totalD = $amountopay / 100 * $cd['discount'];
								$couponprice = $amountopay - number_format(round($totalD, 2), 2, '.', '');

								// We create a coupon in Stripe
								$couponcode = 'cc3-coupon-'.$cd['id'];
								$createcoupon = true;
								$coupontitle = $cd['title'];
								$cval = $_POST["cval"];

							}

						}
					}

					// First we need the old subscriptions
					$subs = $jakdb->get("subscriptions", ["id", "packageid", "operators", "departments", "files", "chathistory", "amount", "currency", "paygateid", "subscribeid", "subscribed", "paidtill", "trial"], ["opid" => JAK_USERID]);

					$subscribed = 0;
					$planid = $pack["title"].'-'.$pack["id"];
					if ($_POST["subscribe"] == 1) {

						// We subscribing
						$subscribed = 1;

						// Ok we need to figure out the intervals for charging the customer
						$intervalc = 1;
						$intervalm = "month";
						if ($pack['validfor'] == 7) {
							$intervalc = 1;
							$intervalm = "week";
						} elseif ($pack['validfor'] == 14) {
							$intervalc = 2;
							$intervalm = "week";
						} elseif ($pack['validfor'] == 30) {
							$intervalc = 1;
							$intervalm = "month";
						} elseif ($pack['validfor'] == 90) {
							$intervalc = 3;
							$intervalm = "month";
						} elseif ($pack['validfor'] == 180) {
							$intervalc = 6;
							$intervalm = "month";
						} elseif ($pack['validfor'] == 365) {
							$intervalc = 1;
							$intervalm = "year";
						}

						// plan name
						$planid = $pack["title"].'-PlanID:'.$pack["id"].'-'.$intervalc.'-'.$intervalm;

					}

					// We have an advanced payment
					$islc3hd3 = 0;
		            if ($pack["islc3"] || $pack["ishd3"]) {

		            	// 1 stands for LC3
		                $islc3hd3 = 1;
		                if ($pack["ishd3"]) $islc3hd3 = 2;

		            }

					// We have now a downgrade but we are not allowed to have a downgrade or we have a payment in a different currency
					// if ((isset($subs["subscribed"]) && $subs["subscribed"] == 1 && !empty($subs["subscribeid"]) && $subs["trial"] == 0) && (($sett["subdowngrade"] == 0 && $subs["amount"] > $couponprice) || ($paga["currency"] != $subs["currency"]))) {
					if (isset($subs["subscribed"]) && $subs["subscribed"] == 1 && !empty($subs["subscribeid"])) {

						if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
							header('Cache-Control: no-cache');
							die(json_encode(array("status" => 0, "infomsg" => $jkl["i65"])));
						} else {
							// redirect back to home
							$_SESSION["errormsg"] = $jkl["i65"];
							jak_redirect(JAK_rewrite::jakParseurl('extend'));
						}

					}

					// Ok, we have no errors we need to create a payidnow for checking that the payment was legal
					$payidnow = base64_url_encode(JAK_USERID.':#:'.$pack["id"].':#:'.$paga['id'].':#:'.$cval.':#:'.$couponprice.':#:'.microtime().':#:'.$planid.':#:'.$subscribed.':#:'.$differentcurrency);
					// We will need to enter the information into a temporary database
					$jakdb->insert("payment_security", ["opid" => $opcacheid, "userid" => JAK_USERID, "payidnow" => $payidnow, "created" => $jakdb->raw("NOW()")]);

					switch ($paga['paygateid']) {
						case 'stripe':
							// code...

							if (isset($subscribed) && $subscribed == 1) {

								// Now we need to either get the plan id from the database or create one
								if (isset($planid) && $jakdb1->has("payment_plans", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]])) {

									$subscription_id = $jakdb1->get("payment_plans", "planid", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]]);

								} else {

									$subscription_id = $JAK_payment->JAK_pay("stripe", $couponprice, $currencytopay, $intervalm, $planid, "recurring", "create_plan", $intervalc, "", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

									if (isset($subscription_id) && !empty($subscription_id)) {

										// We insert the subscription id for later use
										$jakdb1->insert("payment_plans", ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"], "planid" => $subscription_id, "created" => $jakdb->raw("NOW()")]);

									} else {

										// redirect back to home
										$_SESSION["errormsg"] = $jkl["i53"];
										jak_redirect(JAK_rewrite::jakParseurl('extend'));

									}
								}

								// We have a subscription plan
								$JAK_payment->JAK_pay("stripe", "", "", $subscription_id, "", "recurring", "buy", JAK_rewrite::jakParseurl('extend', 'success', $payidnow, $subscription_id), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							} else {

								// Single payment, make sure there is no subscription
								$JAK_payment->JAK_pay("stripe", $couponprice, $currencytopay, $pack["id"], $planid, "single", "", JAK_rewrite::jakParseurl('extend', 'success', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							}
							
							break;

						case 'paypal':
							// code...

							if (isset($subscribed) && $subscribed == 1) {

								// Now we need to either get the plan id from the database or create one
								if (isset($planid) && $jakdb1->has("payment_plans", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]])) {

									$subscription_id = $jakdb1->get("payment_plans", "planid", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]]);

								} else {

									$subscription_id = $JAK_payment->JAK_pay("paypal", $couponprice, $currencytopay, "PlanID-".$pack["id"], $planid, "recurring", "create_plan", $intervalm, $intervalc, $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

									if (isset($subscription_id) && !empty($subscription_id)) {

										// We insert the subscription id for later use
										$jakdb1->insert("payment_plans", ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"], "planid" => $subscription_id, "created" => $jakdb->raw("NOW()")]);

									} else {

										// redirect back to home
										$_SESSION["errormsg"] = $jkl["i53"];
										jak_redirect(JAK_rewrite::jakParseurl('extend'));

									}
								}

								// We have a subscription plan
								$subscribeToken = $JAK_payment->JAK_pay("paypal", $couponprice, $currencytopay, $subscription_id, "", "recurring", "buy", JAK_rewrite::jakParseurl('extend', 'success', $payidnow, $subscription_id), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

								if ($subscribeToken) {
									jak_redirect(JAK_rewrite::jakParseurl('extend', 'success', $payidnow, $subscription_id, $subscribeToken));
								} else {
									jak_redirect(JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow));
								}

							} else {

								// Single payment, make sure there is no subscription
								$JAK_payment->JAK_pay("paypal", $couponprice, $currencytopay, $pack["id"], $planid, "single", "", JAK_rewrite::jakParseurl('extend', 'success', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							}

							break;

						case 'verifone':
							// code...

							$_SESSION["infomsg"] = $jkl["i67"];
							jak_redirect(JAK_rewrite::jakParseurl('extend'));

							// More infos
							/* $usrinfo = array(
						        'name'=>$jakuser->getVar("name"),
						        'email'=> $jakuser->getVar("email")
						    ); */

							break;

						case 'authorize.net':
							// code...

								$_SESSION["infomsg"] = $jkl["i67"];
								jak_redirect(JAK_rewrite::jakParseurl('extend'));

							break;

						case 'yoomoney':
							// code...

							if (isset($subscribed) && $subscribed == 1) {

								// YooKassa is strange with handling payments we just run it
								$subscription_id = $JAK_payment->JAK_pay("yoomoney", $couponprice, $currencytopay, "PlanID-".$pack["id"], $planid, "recurring", "buy", JAK_rewrite::jakParseurl('extend', 'success', $payidnow), "", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							} else {

								// Single payment, make sure there is no subscription
								$JAK_payment->JAK_pay("yoomoney", $couponprice, $currencytopay, "", $planid, "single", "", JAK_rewrite::jakParseurl('extend', 'success', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							}

							break;

						case 'paystack':
							// code...

							if (isset($subscribed) && $subscribed == 1) {

								// Now we need to either get the plan id from the database or create one
								if (isset($planid) && $jakdb1->has("payment_plans", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]])) {

									$subscription_id = $jakdb1->get("payment_plans", "planid", ["AND" => ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"]]]);

								} else {

									$subscription_id = $JAK_payment->JAK_pay("paystack", $couponprice, $currencytopay, "PlanID-".$pack["id"], $planid, "create_plan", "", $intervalm, $intervalc, $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

									if (isset($subscription_id) && !empty($subscription_id)) {

										// We insert the subscription id for later use
										$jakdb1->insert("payment_plans", ["amount" => $couponprice, "currency" => $currencytopay, "interval" => $intervalm, "interval_count" => $intervalc, "paygateid" => $paga["id"], "planid" => $subscription_id, "created" => $jakdb->raw("NOW()")]);

									} else {

										// redirect back to home
										$_SESSION["errormsg"] = $jkl["i53"];
										jak_redirect(JAK_rewrite::jakParseurl('extend'));

									}
								}

								// We have a subscription plan
								$subscribeToken = $JAK_payment->JAK_pay("paystack", $couponprice, $currencytopay, $subscription_id, "", "buy_plan", $jakuser->getVar("email"), "", 
									"", $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

								if ($subscribeToken) {
									jak_redirect(JAK_rewrite::jakParseurl('extend', 'success', $payidnow, $subscription_id, $subscribeToken));
								} else {
									jak_redirect(JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow));
								}

							} else {

								// Single payment, make sure there is no subscription
								$JAK_payment->JAK_pay("paystack", $couponprice, $currencytopay, $pack["id"], $planid, "single", $jakuser->getVar("email"), JAK_rewrite::jakParseurl('extend', 'success', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $paga["secretkey_one"], $paga["secretkey_two"], $paga["sandbox"]);

							}

							break;

							case 'bank':

								$mailadv = sprintf($jkl['i68'], $jakuser->getVar("name"), $jakuser->getVar("username"), JAK_USERID, $jakuser->getVar("email"), JAK_MAIN_LOC, $pack['id'], $pack['title']);

								// Ok, we send the email // email address, cc email address, reply to, subject, message, attachment
		            			jak_send_email(JAK_EMAIL, "", $jakuser->getVar("email"), $paga["title"], $mailadv, "");

								// redirect back to extend with the success
								$_SESSION["successmsg"] = $jkl["i69"];
								jak_redirect(JAK_rewrite::jakParseurl('extend'));

							break;

						case 'freeaccess':
							// code
							$paidunix = strtotime("+".$pack["validfor"]." days");
							// get the nice time
							$paidtill = date('Y-m-d H:i:s', $paidunix);

							// We collect the customer id from stripe
							$paygateid = $subs["paygateid"];
							$subscribeid = $subs["subscribeid"];

							// We have an advanced payment
			                if ($pack["islc3"] || $pack["ishd3"]) {

			                	// 1 stands for LC3
			                	$islc3hd3 = 1;
			                	if ($pack["ishd3"]) $islc3hd3 = 2;

			                	if ($jakdb1->has("advaccess", ["AND" => ["userid" => $opmain["id"], "opid" => JAK_USERID]])) {

			                		// Update the advanced access table
					                $jakdb1->update("advaccess", [ 
					                    "lastedit" => $jakdb->raw("NOW()"),
					                    "paidtill" => $paidtill,
					                    "lc3hd3" => $islc3hd3,
					                    "paythanks" => 1], ["AND" => ["opid" => JAK_USERID, "id" => $opmain["id"]]]);
			                	} else {
			                		$jakdb1->insert("advaccess", ["userid" => $opmain["id"], "opid" => JAK_USERID, "lc3hd3" => $islc3hd3, "lastedit" => $jakdb->raw("NOW()"), "paythanks" => 1, "paidtill" => $paidtill, "created" => $jakdb->raw("NOW()")]);
			                	}

								// Ok, we have removed the old stuff and now we update the user subscription table
								$jakdb->update("subscriptions", ["packageid" => $pack["id"], "operators" => $pack["operators"], "departments" => $pack["departments"], "files" => $pack["files"], "activechats" => $pack["activechats"], "chathistory" => $pack["chathistory"], "islc3" => $pack["islc3"], "ishd3" => $pack["ishd3"], "validfor" => $pack["validfor"], "paygateid" => $paygateid, "subscribed" => $subscribed, "amount" => $couponprice, "currency" => $currencytopay, "paidhow" => "Free Package", "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $paidtill, "trial" => 0, "active" => 1], ["opid" => JAK_USERID]);
							

			                	// The time for the advanced installation
			                	$paidunix = strtotime("+".$pack["validfor"]." days");

			                	$mailadv = sprintf($jkl['i50'], $jakuser->getVar("name"), $jakuser->getVar("username"), JAK_USERID, $jakuser->getVar("email"), $jakuser->getVar("password"), $paidunix, ($islc3hd3 == 1 ? 'Live Chat 3' : 'HelpDesk 3'), SIGN_UP_URL.'/process/confirmadv.php?uid='.JAK_USERID);

			                	// Ok, we send the email // email address, cc email address, reply to, subject, message, attachment
		            			jak_send_email(JAK_EMAIL, "", $jakuser->getVar("email"), $jkl['i49'], $mailadv, "");

								$stripemsg = $jkl['i51'];
								$stripestatus = 2;

			                } else {

			                	// Nasty stuff starts
								if (isset($subs) && isset($pack)) {

									// Update the main operator subscription
									update_main_operator($subs, $pack, $currencytopay, $couponprice, $paygateid, $subscribeid, 0, 0, "Free Plan", JAK_USERID, JAK_MAIN_LOC);

								}

			                }

			                // Update old subscriptions to none active
				            $jakdb1->update("subscriptions", ["active" => 0], ["AND" => ["locationid" => JAK_MAIN_LOC, "userid" => JAK_USERID]]);

			                // We insert the subscription into the main table for that user.
							$jakdb1->insert("subscriptions", ["packageid" => $pack["id"],
								"locationid" => JAK_MAIN_LOC,
								"userid" => JAK_USERID,
								"amount" => $couponprice,
								"currency" => $currencytopay,
								"paidfor" => $pack["title"],
								"paidhow" => "Free Plan",
								"subscribed" => 0,
								"paygateid" => $paygateid,
								"subscribeid" => $subscribeid,
								"paidwhen" => $jakdb->raw("NOW()"),
								"paidtill" => $paidtill,
								"freeplan" => 1,
								"active" => 1,
								"success" => 1]);

							// finally update the main database
							$jakdb1->update("users", ["trial" => "1980-05-06 00:00:00",
								"paidtill" => $paidtill,
								"payreminder" => 0,
								"paythanks" => 1,
								"active" => 1,
								"confirm" => 0], ["AND" => ["opid" => JAK_USERID, "locationid" => JAK_MAIN_LOC]]);

							// Now let us delete the define cache file
							$cachewidget = APP_PATH.JAK_CACHE_DIRECTORY.'/opcache'.JAK_USERID.'.php';
							if (file_exists($cachewidget)) {
								unlink($cachewidget);

								if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
									header('Cache-Control: no-cache');
									die(json_encode(array("status" => 1, "infomsg" => $jkl['i43'], "date" => JAK_base::jakTimesince($paidtill, $jakopsett['dateformat'], $jakopsett['timeformat']))));
								} else {
									// redirect back to home
									$_SESSION["successmsg"] = $jkl["i43"];
									jak_redirect(BASE_URL);
								}
							}

							break;
					}

				} else {

					// Something went wrong
					$_SESSION["errormsg"] = $jkl["i53"];
					jak_redirect(JAK_rewrite::jakParseurl('extend'));
				}

			} elseif ($_POST['check'] == "payop") {

				// Calculate the price from the months
				$amount = $_POST['opcount']*$sett["addops"];

				// Ok, we have no errors we need to create a payidnow for checking that the payment was legal
				$payidnow = base64_url_encode(JAK_USERID.':#:'.$amount.':#:'.$_POST['opcount'].':#:'.microtime().':#:'.$_POST['paidhowop']);

				// We will need to enter the information into a temporary database
				$jakdb->insert("payment_security", ["opid" => $opcacheid, "userid" => JAK_USERID, "payidnow" => $payidnow, "created" => $jakdb->raw("NOW()")]);

				// Go through the payment gateways
				switch ($_POST['paidhowop']) {
						case 'stripe':
							// code...

							// Single payment, make sure there is no subscription
							$JAK_payment->JAK_pay("stripe", $amount, $sett["currency"], $_POST['opcount'], "Additional Operator", "single", "", JAK_rewrite::jakParseurl('extend', 'opsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["stripe_secret_key"], $sett["stripe_publish_key"], "");
							
							break;

						case 'paypal':
							// code...

							// Single payment, make sure there is no subscription
							$JAK_payment->JAK_pay("paypal", $amount, $sett["currency"], $_POST['opcount'], "Additional Operator", "single", "", JAK_rewrite::jakParseurl('extend', 'opsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["paypal_client"], $sett["paypal_secret"], $sett["sandbox_mode"]);

							break;

						case 'verifone':
							// code...

							$_SESSION["infomsg"] = $jkl["i67"];
							jak_redirect(JAK_rewrite::jakParseurl('extend'));

							// More infos
							/* $usrinfo = array(
						        'name'=>$jakuser->getVar("name"),
						        'email'=> $jakuser->getVar("email")
						    ); */

							break;

						case 'authorize.net':
							// code...

								$_SESSION["infomsg"] = $jkl["i67"];
								jak_redirect(JAK_rewrite::jakParseurl('extend'));

							break;

						case 'yoomoney':
							// code...

							// Single payment, make sure there is no subscription
							$JAK_payment->JAK_pay("yoomoney", $amount, $sett["currency"], "", "Additional Operator", "single", "", JAK_rewrite::jakParseurl('extend', 'opsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["yookassa_id"], $sett["yookassa_secret"], "");

							break;

						case 'paystack':
							// code...

							// Single payment, make sure there is no subscription
							$JAK_payment->JAK_pay("paystack", $amount, $sett["currency"], $_POST['opcount'], "Additional Operator", "single", $jakuser->getVar("email"), JAK_rewrite::jakParseurl('extend', 'opsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["paystack_secret"], "", "");

						break;
					}

					


			} elseif ($_POST['check'] == "opextend") {

				// Calculate the price from the months
				$amount = $_POST['opamountext']*$sett["addops"];

				// Ok, we have no errors we need to create a payidnow for checking that the payment was legal
				$payidnow = base64_url_encode(JAK_USERID.':#:'.$amount.':#:'.$_POST['opidext'].':#:'.microtime().':#:'.$_POST['paidhowopext'].':#:'.$_POST['opamountext']);

				// We will need to enter the information into a temporary database
				$jakdb->insert("payment_security", ["opid" => $opcacheid, "userid" => JAK_USERID, "payidnow" => $payidnow, "created" => $jakdb->raw("NOW()")]);

				// Go through the payment gateways
				switch ($_POST['paidhowopext']) {
					case 'stripe':
						// code...

						// Single payment, make sure there is no subscription
						$JAK_payment->JAK_pay("stripe", $amount, $sett["currency"], $_POST['opcount'], "Operator Extend Membership", "single", "", JAK_rewrite::jakParseurl('extend', 'opextsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["stripe_secret_key"], $sett["stripe_publish_key"], "");
						
						break;

					case 'paypal':
						// code...

						// Single payment, make sure there is no subscription
						$JAK_payment->JAK_pay("paypal", $amount, $sett["currency"], $_POST['opcount'], "Operator Extend Membership", "single", "", JAK_rewrite::jakParseurl('extend', 'opextsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["paypal_client"], $sett["paypal_secret"], $sett["sandbox_mode"]);

						break;

					case 'verifone':
						// code...

						$_SESSION["infomsg"] = $jkl["i67"];
						jak_redirect(JAK_rewrite::jakParseurl('extend'));

						// More infos
						/* $usrinfo = array(
					        'name'=>$jakuser->getVar("name"),
					        'email'=> $jakuser->getVar("email")
					    ); */

						break;

					case 'authorize.net':
						// code...

							$_SESSION["infomsg"] = $jkl["i67"];
							jak_redirect(JAK_rewrite::jakParseurl('extend'));

						break;

					case 'yoomoney':
						// code...

						// Single payment, make sure there is no subscription
						$JAK_payment->JAK_pay("yoomoney", $amount, $sett["currency"], "", "Operator Extend Membership", "single", "", JAK_rewrite::jakParseurl('extend', 'opextsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["yookassa_id"], $sett["yookassa_secret"], "");

						break;

					case 'paystack':
						// code...

						// Single payment, make sure there is no subscription
						$JAK_payment->JAK_pay("paystack", $amount, $sett["currency"], $_POST['opamountext'], "Operator Extend Membership", "single", $jakuser->getVar("email"), JAK_rewrite::jakParseurl('extend', 'opextsuccess', $payidnow), JAK_rewrite::jakParseurl('extend', 'cancel', $payidnow), $sett["paystack_secret"], "", "");

					break;
				}

			}

		}

	}

}

if (isset($page1) && $page1 == "bank" && isset($page2) && is_numeric($page2)) {

	// Now get the payment gateway details.
	if (isset($paygate) && !empty($paygate)) foreach($paygate as $pg) { 

		if ($pg["id"] == $page2) {

			// Call the template
	  	    $template = 'bank.php';
	  	    break;

	  	}
  	}

} else {

	// Title and Description
	$SECTION_TITLE = $jkl['i57'];
	$SECTION_DESC = "";

	// Include the javascript file for results
	$js_file_footer = 'js_extend.php';
	// Call the template
	$template = 'extend.php';

}

?>