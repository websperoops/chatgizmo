<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

include_once 'class.rewrite.php';

class JAK_base
{
	private $data = array();
	private $usraccesspl = array();
	private $case;
	private $lsvar;
	private $lsvar1;
	protected $table = '', $itemid = '', $select = '', $where = '', $dseo = '';
	
	// This constructor can be used for all classes:
	
	public function __construct(array $options){
			
			foreach($options as $k=>$v){
				if(isset($this->$k)){
					$this->$k = $v;
				}
			}
	}
	
	public static function pluralize($count, $text, $plural) 
	{ 
	    return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " {$plural}" ) );
	}
	
	public static function jakTimesince($mysqlstamp, $date, $time)
	{
	
		$today = time(); /* Current unix time  */
		if (is_numeric($mysqlstamp)) {
			$unixtime = $mysqlstamp;
			$mysqlstamp = date('Y-m-d H:i:s', $mysqlstamp);
		} else {
			$unixtime = strtotime($mysqlstamp);
		}
		$since = $today - $unixtime;
		
		// Return date time
		return date(($date && $time ? $date.' ' : $date).$time, $unixtime);
	
	}
	
	public static function jakCheckSession($userid,$convid)
	{
	
		$chat_ended = time() + 600;
		
		global $jakdb;
		if ($jakdb->has("sessions",["userid" => $userid, "id" => $convid, "ended" => $chat_ended])) {
			return true;
		}
	
	}
	
	public static function jakWriteinCache($file, $content, $extra)
	{
	
		if ($file && $content) {
		
			if (isset($extra)) {
				file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
			} else {
				file_put_contents($file, $content, LOCK_EX);
			}
		}
	
	}
	
	public static function jakAvailableHours($hours,$available) {
	
		$ohours = json_decode($hours, true);
		
		// get the php str
		$dtime = new DateTime($available);
		
		// Days of the week
		$daysaweekid = array(0 => "Mon", 1 => "Tue", 2 => "Wed", 3 => "Thu", 4 => "Fri", 5 => "Sat", 6 => "Sun");
		
		// Return the correct day
		$day = array_search($dtime->format('D'), $daysaweekid);
		
		$nobh = false;
		
		// Check if the day is active and proceed
		if ($ohours[$day]["isActive"]) {
			
			// Now we need to check the time
			if (!empty($ohours[$day]["timeFrom"]) && !empty($ohours[$day]["timeTill"])) {
				
				if ($ohours[$day]["timeTill"] == "24:00") $ohours[$day]["timeTill"] = "23.59";
				
				if (($ohours[$day]["timeFrom"] <= $dtime->format('H:i')) && ($ohours[$day]["timeTill"] >= $dtime->format('H:i'))) $nobh = true;
			}
			
			if (!$nobh && !empty($ohours[$day]["timeFroma"]) && !empty($ohours[$day]["timeTilla"])) {
			
				if ($ohours[$day]["timeTilla"] == "24:00") $ohours[$day]["timeTilla"] = "23.59";
			
				if (($ohours[$day]["timeFroma"] <= $dtime->format('H:i')) && ($ohours[$day]["timeTilla"] >= $dtime->format('H:i'))) $nobh = true;
			}
			
			return $nobh;
			
		} else {
			return false;
		}
		
	}

	public static function jakCookie($cookiename, $value, $expires, $path) {

		if (version_compare(PHP_VERSION, '7.3', '>=')) {

			setcookie($cookiename, $value, [
		    'expires' => time() + $expires,
		    'path' => $path,
		    'httponly' => true,
		    'samesite' => 'None',
		    'secure' => true]);

		} else {

			setcookie($cookiename, $value, time() + $expires, $path.'; SameSite=None; Secure');
		}
	}
}
?>