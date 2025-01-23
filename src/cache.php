<?php
namespace Teescripts\Helpers;
function cache_path() {
	return constKey("ROOT")."Cache/web/";
} 

function cache_name($name="", $type=1, $temp=1) {
	$var_init	=main_var("init");
	$homepage	=constKey("HOME");
	$template	=constKey("TEMP");
	$nodeid		=main_var("station");
	$currency	=main_var("currency");
	$language	=main_var("language");
	$url_string	=$var_init->urlString;
	$url_string	=str_replace("url=", "", $url_string);
	if (strlen($url_string)>200) $url_string=main_abbr($url_string);

	$part	=[];
	$part[]	=$nodeid;
	if ($template) $part[]=$template;
	if ($homepage) $part[]=$homepage;
	if ($type<3) {
		if ($language) $part[]=$language["code"];	
		if ($currency) $part[]=$currency["code"];
		if ($name) $part[]=$name;
		if ($url_string && $type==1&&$temp!=2) $part[]=$url_string;
	}
	
	$file	=implode("_", $part);
	$file	=str_replace("/", "_", $file);
	$file	=str_replace("mobile-", "", $file);
	$file	=main_textNormal($file, 2);
	$file	=strtolower($file);
	$file	=str_replace("?", "-", $file);
	$file	=trim($file, "_");
	if ($type==1) $file	=cache_path().$file.".html";
	return $file;
} 

function cache_file($name="", $temp="") {
	$file	=cache_name($name, 1, $temp);
	return $file;
}

function cache_get($time="", $file="") {
	if (!$time) $time=15;
	$timestamp	=($time * 60);
	$elapsed	=time() - $timestamp;

	$text	="";
	$cache	=0;
	if (!$file) $file=cache_file();
	if (is_file($file)) {
		$cache	=1;
		$created=filemtime($file);
		$expired=($elapsed > $created);
		if ($expired) $cache=0;
		$text	=file_get_contents($file);
	}
	$result	=["file"=>$file, "text"=>$text, "cache"=>$cache];
	return $result;
}

function cache_save($text, $file="") {
	if (strlen($text)>200) {
		$path	=cache_path();
		if (!$file) $file=cache_file();
		if (!is_dir($path)) mkdir($path, 0777, 1);
		file_put_contents($file, $text);
	}
}

function cache_do($time="", $name="", $temp="") {
	$file	=cache_file($name, $temp);
	$result	=cache_get($time, $file);
	return $result;
}

function cache_remove($name="", $type=2) {
	$file	=cache_name($name, $type);
	$path	=cache_path();
	$handle	=scandir($path);
	foreach ($handle as $filename) {
		if (stristr($filename, $file)) {
			unlink($path.$filename);
		}
	}
}

function cache_start() {
	return ob_start();
}

function cache_html() {
	return ob_get_contents();
}

function cache_end() {
	return ob_end_flush();
}
