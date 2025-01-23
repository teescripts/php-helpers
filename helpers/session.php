<?php
namespace Teescripts\Helpers;
$var_stored	=getKey();

sess_init();

function sess_fields($key="") {
	$array	=["field"=>"tokid", "sess1"=>"last", "sess2"=>"token"];
	if ($key) $array=arrayKey($key, $array, $array);
	return $array;
}

function sess_token() {
	$sess_md5	=md5(main_appName());
	$sess_sub	="b".substr($sess_md5, 4, 7);
	$sess_sub	=strtolower($sess_sub);
	return $sess_sub;
}

function sess_init() {
	if (!isset($_SESSION)) session_start();
}

function sess_empty($key) {
	sess_null($key);
}

function sess_remove($key) {
	sess_null($key);
}

function sess_null($key="") {	
	if ($key) {
		$sess_token	=sess_token();
		$sess_data	=arrayKey($sess_token, $_SESSION, []);
		if (array_key_exists($key, $sess_data)) {
			unset($_SESSION[$sess_token][$key]);
		}
	}
	else {
		sess_destroy();
	}
}

function sess_destroy() {
	$token	=sess_token();
	unset($_SESSION[$token]);
}

function sess_set($key="", $value="") {		
	if ($key!="") {
		$token	=sess_token();
		$_SESSION[$token][$key]	=$value;
	}
}

function sess_get($key="", $value="") {
	$token	=sess_token();
	$data	=arrayKey($token, $_SESSION);
	$result	=[];
	if ($data) {
		if ($key) {
			$result	=arrayKey($key, $data);
			if (!$result) $result=$value;
		}
		else {
			$result=$data;
		}
		
	}	
	return $result;
}

function sess_signOut() {
	sess_destroy();
	$result	=[2, "Successfully logged out"];
	return $result;
}

function sess_check($load=1) {

	//If you are logged in
	$account=sess_get("user");
	$text 	="";
	if (!isset($account)) $text='Logged Out';

	if ($load==1) {
		echo $text;
		redirect("dashboard/");
	}
	return $text;
}

function sess_tokenValue() {
	$array	=sess_fields();

	$var_sess1	=$array["sess1"];
	$var_sess2	=$array["sess2"];

	$timeout=20 * 60;
	$time	=time();
	$last	=sess_get($var_sess1, 0);//io($last);
	if (is_array($last)) $last=0;
	$lapse	=$time - $last;
	
	$rand	=sess_get($var_sess2);
	if($lapse > $timeout) {
		$rand	="site_form_".md5($time);
		sess_set($var_sess1, $time);
		sess_set($var_sess2, $rand);		
	}
	$result	=$rand.substr($time, 0, 4);
	return $result;
}

function sess_tokenField($return="") {
	$field	=sess_fields("field");
	$value	=sess_tokenValue();
	$text	='
	<input type="hidden" name="'.$field.'" value="'.$value.'" />';

	if ($return) {
		return $text;
	}
	else {
		echo $text;
	}
}

function sess_tokenDecode() {
	$array	=sess_fields();

	$rand	=sess_get($array["sess2"]);
	$token	=arrayKey($array["field"], $_POST);
	$string	=substr($token, 0, strlen($token)-4);
	$result	=(($string==$rand) && $token);

	//echo "$result	=(($string==$rand) && $token);";
	return $result;
}

function sess_tokenAuth() {
	$max_time 	=2000 * 60;
	$var_field	=sess_fields("sess1");
	$get_time	=sess_get($var_field, 0);
	$get_time	=intval($get_time);
	$elapsed	=(time() - $get_time);
	$in_time	=($elapsed<=$max_time);

	$decoded	=sess_tokenDecode();
	$is_valid	=1;//($in_time && $decoded);
	return $is_valid;
}

function sess_vars() {
	$list	=["id"=>"authid", "user"=>"username", "name"=>"names", "refid"=>"refid", "username"=>"username", "names"=>"names", "role"=>"role", "authid"=>"authid", "roleid"=>"roleid", "email"=>"email", "company"=>"company", "arole"=>"arole", "group"=>"group", "agent"=>"agent", "client"=>"client", "outlet"=>"outlet", "till"=>"till", "location"=>"location", "zoom"=>"zoom"];
	return $list;
}

function sess_varsInit() {
	$session	=sess_get();
	$variables	=sess_vars();
	$array	=[];
	foreach ($variables as $field=>$name) {
		$value	=arrayKey($field, $session);
		$array[$field]	=$value;
	}
	$array	=(object) $array;
	return $array;
}

function sess_start($data, $goto="", $redirect=1) {
	$status	=arrayKey("status", $data);
	if ($status==1) {
		$sess_vars	=sess_vars();
		foreach ($sess_vars as $key=>$name) {
			$value	=arrayKey($name, $data);
			sess_set($key, $value);
			//io([$key, $value]);
		}
		sess_set("time", time());
		
		$prev_url	=sess_get("prev_url");
		if (!$prev_url && $goto) $prev_url=$goto;
		if (!$prev_url) $prev_url="admin/dashboard";

		//sess_null("prev_url");
		$path_url=$prev_url;
		if ($redirect==1) redirect($path_url);
		return $path_url;
	}
}

function sess_end($icon="") {
	$checks	=sess_checks($icon);
	$msg	=sess_stop($checks);
	return $msg;	
}

function sess_checks($icon="") {
	$set_timeout 	=100*60;
	$username		=sess_get("user");
	$get_time		=sess_get("time", 0);
	$get_time		=intval($get_time);
	$time_elapsed	=time()-$get_time;
	
	$timeout	=(($time_elapsed>$set_timeout) && $username);
	$signout	=($icon=="signout"||$icon=="logout");
	$array	=["timeout"=>$timeout, "signout"=>$signout];
	return $array;
}

function sess_stop($checks, $load=1) {
	$timeout	=arrayKey("timeout", $checks);
	$signout	=arrayKey("signout", $checks);
	
	if ($timeout||$signout) {
		$msg	=sess_signOut();
		if ($signout) {
			sess_null("prev_url");
			if ($load==1) redirect("/");//content/welcome
		}
		else {	
			# ------ return path
			$msg	=[3, 'Your session has expired'];
			if ($load==1) sess_prevUrl("accounts/signin");
		}
	}
	else {
		sess_set("time", time());
	}
	return $msg;
}

function sess_lastUrl($path_url="") {	
	#----------- save prev path
	$prev_url	=getKey("prev_url");
	$referer	=arrayKey("HTTP_REFERER", $_SERVER);
	if (!$path_url && !$prev_url) $path_url=$referer;
	
	if (stristr($referer, "accounts/") || stristr($referer, "http")) $path_url="";
		
	$path_prev	=$prev_url;
	if ($path_url && $path_url!=$prev_url) {
		$path_prev	=str_replace(main_link(), "", $path_url);
		$path_prev	=str_replace("url=", "", $path_prev);
		sess_set("prev_url", $path_prev);
	}
	return $path_prev;
}

function sess_prevUrl($path_prev="", $path_url="") {
	sess_lastUrl($path_url);
	$prev_url	=sess_get("prev_url");
	#----------- save prev path
	if ($path_prev==1 && $prev_url) $path_prev=$prev_url;
	$path_prev	=str_replace(main_link(), "", $path_prev);
	if (stristr($path_prev, "accounts/") || stristr($path_prev, "http")) $path_prev=constKey("DEFAULT");
	
	if ($path_prev) redirect($path_prev);
}
