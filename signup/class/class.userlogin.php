<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.5.2                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2020 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

class JAK_userlogin
{

	protected $name = '', $email = '', $pass = '', $time = '';
	var $username;     //Username given on sign-up
	
	public function __construct() {
	        $this->username = '';
	    }
	   
	function jakChecklogged(){
	
	      /* Check if user has been remembered */
	      if (isset($_COOKIE['jak_lcpa_cookname']) && isset($_COOKIE['jak_lcpa_cookid'])) {
	         $_SESSION['jak_lcpa_username'] = $_COOKIE['jak_lcpa_cookname'];
	         $_SESSION['jak_lcpa_idhash'] = $_COOKIE['jak_lcpa_cookid'];
	      }
	
	      /* Username and idhash have been set */
	      if (isset($_SESSION['jak_lcpa_username']) && isset($_SESSION['jak_lcpa_idhash']) && $_SESSION['jak_lcpa_username'] != $this->username) {
	         /* Confirm that username and userid are valid */
	         if (!JAK_userlogin::jakConfirmidhash($_SESSION['jak_lcpa_username'], $_SESSION['jak_lcpa_idhash'])) {
	            /* Variables are incorrect, user not logged in */
	            unset($_SESSION['jak_lcpa_username']);
	            unset($_SESSION['jak_lcpa_idhash']);
	            
	            return false;
	         }
	         
	         // Return the user data
	         return JAK_userlogin::jakUserinfo($_SESSION['jak_lcpa_username']);
	      }
	      /* User not logged in */
	      else{
	         return false;
	      }
	   }
	
	public static function jakCheckuserdata($username, $pass)
	{
	
		// The new password encrypt with hash_hmac
		$passcrypt = hash_hmac('sha256', $pass, DB_PASS_HASH);
		
		if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
		
			if (!preg_match('/^([a-zA-Z0-9\-_])+$/', $username)) {
				return false;
			}
			
		}
	
		global $jakdb;
		$datausr = $jakdb->get("admins", "username", ["AND" => ["OR" => ["username" => strtolower($username), "email" => strtolower($username)], "password" => $passcrypt, "access" => 1]]);
		if ($datausr) {
			return $datausr;
		} else {
			return false;
		}
			
	}
	
	public static function jakLogin($name, $pass, $remember)
	{
		
		// The new password encrypt with hash_hmac
		$passcrypt = hash_hmac('sha256', $pass, DB_PASS_HASH);
	
		global $jakdb;
		
		// Get the stuff out the database
		$datausr = $jakdb->get("admins",["idhash", "logins"], ["AND" => ["username" => $name, "password" => $passcrypt]]);
		
		if ($datausr['logins'] % 10 == 0) {
		
			// Generate new idhash
			$nidhash = JAK_userlogin::generateRandID();
			
		} else {
		
			if (isset($datausr['idhash']) && !empty($datausr['idhash']) && $datausr['idhash'] != "NULL") {
		
				// Take old idhash
				$nidhash = $datausr['idhash'];
			
			} else {
			
				// Generate new idhash
				$nidhash = JAK_userlogin::generateRandID();
			
			}
		
		}
		
		// Set session in database
		$jakdb->update("admins", ["session" => session_id(), "idhash" => $nidhash, "logins[+]" => 1, "forgot" => 0, "lastactivity" => time()], ["AND" => ["username" => $name, "password" => $passcrypt]]);
		
		$_SESSION['jak_lcpa_username'] = $name;
		$_SESSION['jak_lcpa_idhash'] = $nidhash;

		// Check if cookies are set previous (wrongly) and delete
		if (isset($_COOKIE['jak_lcpa_cookname']) || isset($_COOKIE['jak_lcpa_cookid'])) {

			JAK_base::jakCookie('jak_lcpa_cookname', $name, JAK_COOKIE_TIME, JAK_COOKIE_PATH);
			JAK_base::jakCookie('jak_lcpa_cookid', $nidhash, JAK_COOKIE_TIME, JAK_COOKIE_PATH);

		}
		
		// Now check if remember is selected and set cookies new...
		if ($remember) {

			JAK_base::jakCookie('jak_lcpa_cookname', $name, JAK_COOKIE_TIME, JAK_COOKIE_PATH);
			JAK_base::jakCookie('jak_lcpa_cookid', $nidhash, JAK_COOKIE_TIME, JAK_COOKIE_PATH);
		}
		
	}
	
	public static function jakConfirmidhash($username, $idhash)
	{
	
		global $jakdb;
		
		if (isset($username) && !empty($username)) {
		
		    $datausr = $jakdb->get("admins","idhash",["AND" => ["username" => $username, "access" => 1]]);
		    
		    if ($datausr) {
		    
		    	$datausr = stripslashes($datausr);
		    	$idhash = stripslashes($idhash);
		    			    	
		    	/* Validate that userid is correct */
		    	if(!is_null($datausr) && $idhash == $datausr) {
		    		return true; //Success! Username and idhash confirmed
		    	}

		    }
		        
		}
	
		return false;
			
	}
	
	public static function jakUserinfo($username)
	{
	
			global $jakdb;
			$datauinfo = $jakdb->get("admins", "*", ["AND" => ["username" => $username, "access" => 1]]);
			if ($datauinfo) {
			   return $datauinfo;
			} else {
				return false;
			}
			
	}
	
	public static function jakUpdatelastactivity($userid) {
	
			global $jakdb;
			if (is_numeric($userid)) $jakdb->update("admins", ["lastactivity" => time()], ["id" => $userid]);
			
	}
	
	public static function jakForgotpassword($email, $time) {
	
			global $jakdb;
			if ($jakdb->has("admins", ["AND" => ["email" => $email, "access" => 1]])) {
				if ($time != 0) $jakdb->update("admins", ["forgot" => $time], ["email" => $email]);
			    return true;
			} else {
			    return false;
			}
			
	}
	
	public static function jakForgotactive($forgotid)
	{
	
			global $jakdb;
			if ($jakdb->has("admins", ["AND" => ["forgot" => $forgotid, "access" => 1]])) {
			    return true;
			} else
			    return false;
			
	}
	
	public static function jakForgotcheckuser($email, $forgotid)
	{
	
			global $jakdb;
			if ($jakdb->has("admins", ["AND" => ["email" => $email, "forgot" => $forgotid, "access" => 1]])) {
			    return true;
			} else
			    return false;
			
	}
	
	public static function jakWriteloginlog($username, $url, $ip, $agent, $success)
	{
	
			global $jakdb;
			if ($success == 1) {
				$jakdb->update("loginlog", ["access" => 1], ["AND" => ["ip" => $ip, "time" => $jakdb->raw("NOW()")]]);
			} else {
				$jakdb->insert("loginlog", ["name" => $username, "fromwhere" => $url, "usragent" => $agent, "ip" => $ip, "time" => $jakdb->raw("NOW()"), "access" => 0]);
			}
			
	}
	
	public static function jakLogout($userid)
	{
	
			global $jakdb;
			
			// Delete cookies from this page
			JAK_base::jakCookie('jak_lcpa_cookname', '', -JAK_COOKIE_TIME, JAK_COOKIE_PATH);
			JAK_base::jakCookie('jak_lcpa_cookid', '', - JAK_COOKIE_TIME, JAK_COOKIE_PATH);
			
			// Update Database to session NULL
			$jakdb->update("admins", ["session" => $jakdb->raw("NULL"), "idhash" => $jakdb->raw("NULL")], ["id" => $userid]);
			
			// Unset the main sessions
			unset($_SESSION['jak_lcpa_username']);
			unset($_SESSION['jak_lcpa_idhash']);
			unset($_SESSION['jak_lcpa_lang']);
			
			// Destroy session and generate new one for that user
			session_destroy();

			// Start session
			if (version_compare(PHP_VERSION, '7.3', '<=')) session_set_cookie_params(JAK_COOKIE_TIME, JAK_COOKIE_PATH.'; samesite=None', $_SERVER['HTTP_HOST'], true, false);
			session_start();
			session_regenerate_id();
			
	}
	
	public static function generateRandStr($length){
	   $randstr = "";
	   for($i=0; $i<$length; $i++){
	      $randnum = mt_rand(0,61);
	      if($randnum < 10){
	         $randstr .= chr($randnum+48);
	      }else if($randnum < 36){
	         $randstr .= chr($randnum+55);
	      }else{
	         $randstr .= chr($randnum+61);
	      }
	   }
	   return $randstr;
	}
	
	private static function generateRandID(){
	   return md5(JAK_userlogin::generateRandStr(16));
	}
}
?>