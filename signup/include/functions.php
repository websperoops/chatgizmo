<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.0.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Redirect to something...
function jak_redirect($url, $code = 302) {
    header('Location: '.html_entity_decode($url), true, $code);
    exit;
}

// Filter inputs
function jak_input_filter($value) {
  $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  return preg_replace("/[^0-9 _,.@\-\p{L}]/u", '', $value);
}

// filter url inputs
function jak_url_input_filter($value) {
	$value = html_entity_decode($value);
    $value = preg_replace('/[^\w\-.]/', '', $value);
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

// Check if userid can have access to the pages.
function jak_get_access($page, $array, $superoperator) {
	$roles = explode(',', $array);
	if ((is_array($roles) && in_array($page, $roles)) || $superoperator) {
		return true;
	}
}

function validateDate($date, $format = 'Y-m-d H:i:s') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

// Get the data only per ID (e.g. edit single user, edit category)
function jak_get_data($table, $id, $opid) {	
	global $jakdb;
	$datasett = $jakdb->get($table, "*", ["AND" => ["id" => $id, "opid" => $opid]]);
    return $datasett;
}
function jak_get_data_admin($table, $id) {	
	global $jakdb;
	$datasett = $jakdb->get($table, "*", ["id" => $id]);
    return $datasett;
}

// Check if row exist
function jak_row_exist($id, $table) {
	global $jakdb;
    if ($jakdb->has($table, ["id" => $id])) {
        return true;
	}
}

// Verify paramaters
function verifyparam($name, $regexp, $default = null) {

	if (isset($_GET[$name])) {
		$val = $_GET[$name];
		if (preg_match($regexp, $val))
			return $val;

	} else if (isset($_POST[$name])) {
		$val = $_POST[$name];
		if (preg_match($regexp, $val))
			return $val;

	} else {
		if (isset($default))
			return $default;
	}
	die("<html><head></head><body>Wrong parameter used or absent: " . $name . "</body></html>");
}

// Check if row exist with custom field
function jak_field_not_exist($check, $table, $field) {
    global $jakdb;
    if ($jakdb->has($table, [$field => $check])) {
        return true;
    }
}

// Check if row exist with id
function jak_field_not_exist_id($lsvar,$id,$table,$lsvar3) {
	global $jakdb;
    if ($jakdb->has($table, ["AND" => ["id[!]" => $id, $lsvar3 => $lsvar]])) {
     return true;
	}
}

// Verfiy if there is a online operator
function online_operators($opid) {
	
	$timeout = time() - 300;
	$timerunout = 1;
	
	global $jakdb;
	
	// Update database first to see who is online!
	$jakdb->update("user", ["available" => 0], ["AND" => ["id" => $opid, "lastactivity[<]" => $timeout]]);

	// First we have no operator
	$oponline = false;
	
	$operator = $jakdb->get("user", ["hours_array", "available", "emailnot", "pusho_tok", "push_notifications"], ["AND" => ["id" => $opid, "access" => 1]]);

	if (isset($operator) && !empty($operator)) {
			
		// Operator is available
		if ($operator["available"] == 1) $oponline = true;
			
		// Now let's check if we have a time available
		if (!$oponline && JAK_base::jakAvailableHours($operator["hours_array"], date('Y-m-d H:i:s')) && ($operator["emailnot"] || ($operator["pusho_tok"] && $operator["push_notifications"]))) $oponline = true;
		
	}
	
	return $oponline;
}

// Search for lang files in the admin folder, only choose .ini files.
function jak_get_lang_files($lang = "") {

    // Get the language folder
    $langdir = APP_PATH.'lang/';
    
    if ($handle = opendir($langdir)) {
        
        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            $showlang = substr($file, strrpos($file, '.'));
            if ($file != '.' && $file != '..' && $showlang == '.php' && $lang != substr($file, 0, -4)) {
                
                $getlang[] = substr($file, 0, -4);
                
            }
        }
        return $getlang;
        closedir($handle);
    }
}

// Check if the lang folder for buttons exist
function folder_lang_button($lang) {
	return file_exists('./img/buttons/'.$lang.'/');
}

// Get the real IP Address
function get_ip_address() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
                    return $ip;
                } else {
                	return 0;
                }
            }
        }
    }
}

// Replace urls
function replace_urls($string) {
	$string = preg_replace('/(https?|ftp)([\:\/\/])([^\\s]+)/', '<a href="$1$2$3" target="_blank">$1$2$3</a>', $string);
	return $string;
}

// only full words
function ls_cut_text($jakvar,$jakvar1,$jakvar2) {
	if (empty($jakvar1)) {
		$jakvar1 = 160;
	}
	$crepl = array('<?','<?php','"',"'","?>");
	$cfin = array('','','','','');
	$jakvar = str_replace($crepl, $cfin, $jakvar);
    $jakvar = trim($jakvar);
    $jakvar = strip_tags($jakvar);
    $txtl = strlen($jakvar);
    if($txtl > $jakvar1) {
        for($i=1;$jakvar[$jakvar1-$i]!=" ";$i++) {
            if($i == $jakvar1) {
                return substr($jakvar,0,$jakvar1).$jakvar2;
            }
        }
        $jakdata = substr($jakvar,0,$jakvar1-$i+1).$jakvar2;
    } else {
    	$jakdata = $jakvar;
    }
    return $jakdata;
}

// Is search bot
function is_bot() {	
	$botlist = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi",
	"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
	"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
	"crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp",
	"msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
	"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
	"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
	"Butterfly","Twitturls","Me.dium","Twiceler");

	foreach($botlist as $bot)
	{
		if(strpos($_SERVER['HTTP_USER_AGENT'],$bot)!==false)
		return true;	// Is a bot
	}

	return false;	// Not a bot
}

// Detect Mobile Browser in a simple way to display videos in html5 or video/template not available message
function jak_find_browser($useragent, $wap) {

	$ifmobile = preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile|o2|opera m(ob|in)i|palm( os)?|p(ixi|re)\/|plucker|pocket|psp|smartphone|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce; (iemobile|ppc)|xiino/i', $useragent);
	
	$ifmobileM = preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent,0,4));
	
	if ($ifmobile || $ifmobileM || isset($wap)) {
		return true;
	} else {
		return false;
	}
}

function selfURL() {

	$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF'];
	$referrer = filter_var($referrer, FILTER_VALIDATE_URL);
    
    return $referrer;  
}

// Password generator
function jak_password_creator($length = 8) {
	return substr(md5(rand().rand()), 0, $length);
}

// Update main operator from admin panel
function update_main_operator($subs, $pack, $currency, $amount, $paygateid, $subscribeid, $subscribed, $paidhow, $opid, $location) {

	global $jakdb;
	global $jakdb1;
	// Ok we have less operators therefore we remove the oldest ones.
	if ($subs['operators'] > $pack['operators']) {

		$opremove = $subs['operators'] - $pack['operators'];

		$oldops = $jakdb->select("user", "id", ["opid" => $opid, "ORDER" => ["lastactivty" => "ASC"], "LIMIT" => $opremove]);
		if (isset($oldops) && !empty($oldops)) foreach ($oldops as $o) {

			// Delete the stuff from this user
			$jakdb->delete("user", ["id" => $o]);
			$jakdb->delete("push_notification_devices", ["userid" => $o]);
			$jakdb->delete("user_stats", ["userid" => $o]);
		}

	}

	// Ok we have less department therefore we remove the newest ones.
	if ($subs['departments'] > $pack['departments']) {

		$dpremove = $subs['departments'] - $pack['departments'];

		$olddep = $jakdb1->select("departments", "id", ["opid" => $opid, "ORDER" => ["id" => "DESC"], "LIMIT" => $dpremove]);
		if (isset($olddep) && !empty($olddep)) foreach ($olddep as $d) {

			// Delete the stuff from this user
			$jakdb->delete("departments", ["id" => $d]);
		}

	}

	$paidunix = strtotime("+".$pack["validfor"]." days");
	// get the nice time
	$paidtill = date('Y-m-d H:i:s', $paidunix);

	// Ok, we have removed the old stuff and now we update the user subscription table
	$jakdb1->update("subscriptions", ["packageid" => $pack["id"], "chatwidgets" => $pack["chatwidgets"], "groupchats" => $pack["groupchats"], "operatorchat" => $pack["operatorchat"], "operators" => $pack["operators"], "departments" => $pack["departments"], "files" => $pack["files"], "activechats" => $pack["activechats"], "chathistory" => $pack["chathistory"], "islc3" => $pack["islc3"], "ishd3" => $pack["ishd3"], "validfor" => $pack["validfor"], "paygateid" => $paygateid, "subscribeid" => $subscribeid, "subscribed" => $subscribed, "amount" => $amount, "currency" => $pack["currency"], "paidhow" => $paidhow, "paidwhen" => $jakdb->raw("NOW()"), "paidtill" => $paidtill, "trial" => 0, "active" => 1], ["opid" => $opid]);

	return true;

}

// Update Operator CC
function updatePaygate($locid, $packageid, $paygateids) {

	global $jakdb;

	if (empty($paygateids)) {

		// Delete all entries if we have none
    	$jakdb->delete("package_gateways", ["AND" => ["locid" => $locid, "packageid" => $packageid]]);
    } else {

        // Get all operators in cc
        $currentpg = $jakdb->select("package_gateways", "paygateid", ["AND" => ["locid" => $locid, "packageid" => $packageid]]);

        // We check the difference
        $paygateremove = array_diff($currentpg, $paygateids);
        $paygateadd = array_diff($paygateids, $currentpg);

        // We run the foreach to remove
        if (!empty($paygateremove)) foreach ($paygateremove as $or) {
            $jakdb->delete("package_gateways", ["AND" => ["locid" => $locid, "packageid" => $packageid, "paygateid" => $or]]);
        }

        if (!empty($paygateadd)) foreach ($paygateadd as $oa) {
        	$jakdb->insert("package_gateways", ["locid" => $locid, "packageid" => $packageid, "paygateid" => $oa, "created" => $jakdb->raw("NOW()")]);
        }

    }

    return true;

}
?>