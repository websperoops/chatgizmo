<?php

$urlonly = parse_url(filter_var($_GET['crossurl'], FILTER_SANITIZE_URL));
$crossurl = $urlonly["scheme"].'://'.$urlonly["host"].(isset($urlonly['port']) ? ':'.$urlonly['port'] : '');

header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 6 May 1998 03:10:00 GMT");
header("Access-Control-Allow-Origin: ".$crossurl);
header('Access-Control-Allow-Credentials: true');

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// filter url inputs
function jak_valid_get_cross($value) {
    $value = html_entity_decode($value);
    $value = preg_replace('/[^\w\-.]/', '', $value);
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

// Check with callback
function is_valid_callback($input) {
    $identifier_syntax
      = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

    $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
      'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
      'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
      'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
      'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
      'private', 'public', 'yield', 'interface', 'package', 'protected', 
      'static', 'null', 'true', 'false');

    return preg_match($identifier_syntax, $input)
        && ! in_array(mb_strtolower($input, 'UTF-8'), $reserved_words);
}

// Check with callback 2
function is_valid_callback2($input) {
    return !preg_match( '/[^0-9a-zA-Z\$_]|^(abstract|boolean|break|byte|case|catch|char|class|const|continue|debugger|default|delete|do|double|else|enum|export|extends|false|final|finally|float|for|function|goto|if|implements|import|in|instanceof|int|interface|long|native|new|null|package|private|protected|public|return|short|static|super|switch|synchronized|this|throw|throws|transient|true|try|typeof|var|volatile|void|while|with|NaN|Infinity|undefined)$/', $input);
}

$callback = false;
$callback = jak_valid_get_cross($_GET['callback']);

if (!isset($callback) || !is_valid_callback($callback) || !is_valid_callback2($callback)) {
	header('status: 400 Bad Request', true, 400);
} else {
	header('content-type: application/javascript; charset=utf-8');
}

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && isset($_GET['id']) && !is_numeric($_GET['id'])) die(json_encode(array('status' => false, 'error' => "No valid ID.")));

// We set the widget id
if (isset($_GET['id']) && is_numeric($_GET['id'])) $widgetid = $_GET['id'];

if (!file_exists('../config.php')) die('include/[loadiframe_cross.php] config.php not exist');
require_once '../config.php';

// We do not load any widget code if we are on hosted and and expiring date is true.
if ($jakosub['active'] == 0) die(json_encode(array('status' => false, 'error' => "Account expired.")));

// Destroy the session linked
if (isset($_SESSION['islinked'])) unset($_SESSION['islinked']);

// Get the referrer URL
$referrer = $crossurl.(isset($urlonly['path']) ? $urlonly['path'] : '');

// Some reset
$widgethtml = $slideimg = '';

// Get the client browser
$ua = new Browser();

// Is a robot just die
if ($ua->isRobot()) die(json_encode(array('status' => false, 'error' => "Robots do not need a live chat.")));
// Is mobile
if ($ua->isMobile()) {
	$clientos = "mobile";
} else {
	$clientos = "desktop";
}

// Now check the button id
if (isset($_GET['id']) && is_numeric($_GET['id']) && $jakwidget[$_GET['id']]['id'] == $_GET['id']) {

	// Language file
	$lang = $jakwidget[$widgetid]['lang'];
	if (isset($_POST['lang']) && !empty($_POST['lang'])) $lang = $_POST['lang'];

	// Import the language file
	if ($lang && file_exists(APP_PATH.'lang/'.strtolower($lang).'.php')) {
	    include_once(APP_PATH.'lang/'.strtolower($lang).'.php');
	} else {
	    include_once(APP_PATH.'lang/'.JAK_LANG.'.php');
	    $lang = JAK_LANG;
	}

	// Set first time visited so we can fire the pro active at the right time
	if (isset($_POST['firstvisit']) && !empty($_POST['firstvisit'])) {
		
		$firstvisit = $_POST['firstvisit'];
			
	} else {

		$firstvisit = time();
	}

	// Get the unique session for this customer
	if (isset($_POST['rlbid']) && !empty($_POST['rlbid'])) {
		
		$rlbid = $_POST['rlbid'];
			
	} else {

		$salt = rand(100, 99999);
		$rlbid = $salt.time();
	}

	// Set the session anyway
	$_SESSION['rlbid'] = $rlbid;

	// We will update the button stat table
	$btstat = $jakdb->update("buttonstats", ["depid" => $jakwidget[$widgetid]['depid'], "opid" => $jakwidget[$widgetid]['opid'], "singleopid" => $jakwidget[$widgetid]['singleopid'], "hits[+]" => 1, "referrer" => $referrer, "crossurl" => $crossurl, "ip" => $ipa, "lasttime" => $jakdb->raw("NOW()")], ["session" => $rlbid]);
	
	// Update database first to see who is online!
	$geodata = "";
	if (!$btstat->rowCount()) {

		// get client information
		$clientsystem = $ua->getPlatform().' - '.$ua->getBrowser(). " " . $ua->getVersion();

		// Country Stuff
		$country_name = 'Disabled';
		$country_code = 'xx';
		$city = 'Disabled';
		$country_lng = $country_lat = '';

		// we will use the local storage for geo
		$removeloc = true;
		if (isset($_POST['geo']) && !empty($_POST['geo'])) {

			// Always escape any user input, including cookies:
			list($city, $country_name, $country_code, $country_lat, $country_lng, $storedtime) = explode('|', strip_tags(jak_string_encrypt_decrypt($_POST['geo'], false)));

			// We check if the geo data is older th3n
			if (isset($storedtime) && !empty($storedtime) && strtotime('+3 day', $storedtime) < time() || (isset($country_code) && !empty($country_code))) $removeloc = false;

		}

		if ($removeloc) {

			// Now let's check if the ip is ipv4
			if ($ipa && !$ua->isRobot()) {

				$ipc = curl_init();
				curl_setopt($ipc, CURLOPT_URL, "https://ipgeo.jakweb.ch/api/".$ipa);
				curl_setopt($ipc, CURLOPT_HEADER, false);
				curl_setopt($ipc, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ipc);
				curl_close($ipc);

				$getinfo = json_decode($response, true);

				if (isset($getinfo) && !empty($getinfo)) {

					$country_name = ucwords(strtolower(filter_var($getinfo["country"]["name"], FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
					$country_code = strtolower(filter_var($getinfo["country"]["code"], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
					$city = filter_var($getinfo["city"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					$country_lng = filter_var($getinfo["location"]["longitude"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); 
					$country_lat = filter_var($getinfo["location"]["latitude"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

					// Setting a cookie with the data, which is set to expire in a week:
					$geodata = jak_string_encrypt_decrypt($city.'|'.$country_name.'|'.$country_code.'|'.$country_lat.'|'.$country_lng.'|'.time());

				}

			}

		}

		$jakdb->insert("buttonstats", ["depid" => $jakwidget[$widgetid]['depid'], "opid" => $jakwidget[$widgetid]['opid'], "singleopid" => $jakwidget[$widgetid]['singleopid'], "referrer" => $referrer, "firstreferrer" => $referrer, "crossurl" => $crossurl, "agent" => $clientsystem, "hits" => 1, "ip" => $ipa, "country" => $country_name, "countrycode" => $country_code, "latitude" => $country_lat, "longitude" => $country_lng, "session" => $rlbid, "time" => $jakdb->raw("NOW()"), "lasttime" => $jakdb->raw("NOW()")]);
		
	}

	if (isset($_POST['customer']) && !empty($_POST['customer'])) {

		// Let's safely encrypt the chat data from the customer
		$cudetails = jak_string_encrypt_decrypt($_POST['customer'], false);

		// Let's explode the string (0 = convid, 1 = uniqueid, 2 = userid, 3 = name, 4 = email, 5 = phone, 6 = avatar)
		$cudetails = explode(":#:", $cudetails);

		// insert new referrer
		$jakdb->insert("transcript", ["name" => $cudetails[3], "message" => sprintf($jkl['g55'], $referrer), "user" => $cudetails[2], "convid" => $cudetails[0], "time" => $jakdb->raw("NOW()"), "class" => "notice", "plevel" => 2]);

		$lastrefid = $jakdb->id();

		$jakdb->update("checkstatus", ["newo" => $lastrefid, "typec" => 0], ["convid" => $cudetails[0]]);

		$pageload = JAK_rewrite::jakParseurl('lc', $_POST['cstatus'], $_GET['id'], $lang, $cudetails[0], $cudetails[1]);

		// customer is chatting
		$ischatting = true;

	} else {

		// Now let's check if we are on a page where we do not want to show the chat aka Black List
		if (isset($LC_BLACKLIST) && !empty($LC_BLACKLIST)) if (filter_var($referrer, FILTER_VALIDATE_URL) && in_array($referrer, $LC_BLACKLIST)) die(json_encode(array('status' => false, 'error' => "Do not show chat on this page.")));

		// No one chatting at the moment
		$ischatting = false;

	}

	// We have a holiday mode and hide chat or no one is online and the chat widget is set to hide
	if (!isset($_POST['customer']) && JAK_HOLIDAY_MODE == 2) {
		die(json_encode(array('status' => false, 'error' => "No operator online and chat settings are set to hide.")));
	}

	// We have custom vars
	$customvars = "";
	if (!empty($_POST['name']) || !empty($_POST['email']) || !empty($_POST['msg'])) $customvars = jak_string_encrypt_decrypt(filter_var(jak_input_filter($_POST['name']), FILTER_SANITIZE_FULL_SPECIAL_CHARS).':#:'.filter_var($_POST['email'], FILTER_SANITIZE_EMAIL).':#:'.filter_var(jak_input_filter($_POST['msg']), FILTER_SANITIZE_FULL_SPECIAL_CHARS));

	// We have a members only setting
	if ($jakwidget[$widgetid]['onlymembers'] == 1 && !$ischatting && empty($customvars)) die(json_encode(array('status' => false, 'error' => "Only for members...")));

	// page to load
	if (!isset($pageload) && empty($pageload)) {
		if (isset($_POST['cstatus']) && $_POST['cstatus'] == "open") {
			$pageload = JAK_rewrite::jakParseurl('lc', 'open', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		} elseif (isset($_POST['cstatus']) && $_POST['cstatus'] == "big") {
			$pageload = JAK_rewrite::jakParseurl('lc', 'big', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		} elseif (isset($_POST['cstatus']) && $_POST['cstatus'] == "profile") {
			$pageload = JAK_rewrite::jakParseurl('lc', 'big', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		} elseif (isset($_POST['cstatus']) && $_POST['cstatus'] == "feedback") {
			$pageload = JAK_rewrite::jakParseurl('lc', 'big', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		} elseif (isset($_POST['cstatus']) && $_POST['cstatus'] == "contactform") {
			$pageload = JAK_rewrite::jakParseurl('lc', 'contactform', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		} else {
			$pageload = JAK_rewrite::jakParseurl('lc', 'closed', $_GET['id'], $lang, $jakwidget[$widgetid]['depid'], $jakwidget[$widgetid]['opid']);
		}
	}

	// We load the chat window
	die(json_encode(array('status' => true, 'widgethtml' => '<iframe id="livesupportchat'.$_GET['id'].'" seamless="seamless" allowtransparency="true" style="background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; border: 0px none; bottom: 0px; float: none; height: 100%; width: 100%; left: 0px; margin: 0px; padding: 0px; position: absolute; right: 0px; top: 0px;" scrolling="no" src="'.str_replace('include/', '', $pageload).'"></iframe>', 'url' => str_replace('include/', '', BASE_URL), 'customvars' => $customvars, 'clientos' => $clientos, 'firstvisit' => $firstvisit, 'lastvisit' => time(), 'geodata' => $geodata, 'rlbid' => $rlbid)));

} else {
	die(json_encode(array('status' => false, 'error' => "No Widget available with this ID.")));
}
?>