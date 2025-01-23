<?php
namespace Teescripts\Helpers;

function load_lib($name) {
	#use App\Library\$name;

	include app_path("{$name}.php");
}

function getSearch() {
	$get_request=array_merge($_GET, $_POST);
	$get_phrase	=arrayKey("phrase", $get_request);
	$get_subcat	=arrayKey("category", $get_request);
	$get_search	=arrayKey("search", $get_request);
	
	if ($get_search) $_GET["phrase"]=$get_search;
	if ($get_phrase) $_GET["phrase"]=$get_phrase;
	if ($get_subcat) $_GET["category"]=$get_subcat;
}

function un_set($data="", $fields="") {
	if ($fields) {
		$array	=$fields;
		if (!is_array($array)) $array=explode(",", $fields);

		$first	=array_key_first($data);
		$value	=arrayKey($first, $data);

		$narray	=[];
		//if (!is_array($value)) $narray=array_flip($data);
		foreach ($array as $key) {
			$value	=arrayKey($key, $data);
			if ($value) {
				unset($data[$key]);
			}
			else {
				$value	=arrayKey($key, $narray);
				if ($value) unset($data[$value]);
			}
		}
	}
	return $data;
}

function myUnset($array="", $fields="") {
	if (!is_array($fields)) $fields=explode(",", $fields);
	if ($fields) {
		foreach ($fields as $key) {
			if (array_key_exists($key, $array)) unset($array[$key]);
		}
	}
	return $array;
}

function array_merger($source, $array) {
	if (is_array($array)) {
		foreach ($array as $key=>$value) {
			$option =arrayKey($key, $source);
			if ($option) {
				$keys	=is_array($option);
				if ($keys) {
					$source[$key][]  =$value;
				}
				else {
					$source[$key]  =$value;
				}
				unset($array[$key]);
			}
		}
	}
	return $source;
}

# function for checking array keys
function arrayVal($key, $array=[], $value="") {	
	# if the array is not an array
	if (!is_array($array)) $array=[];
	# if the key exists in the array
	if (in_array($key, $array)) {
		# fetch the array data
		$value    =$key;
	}
	return $value;
}

# function for checking array keys
function arrayKey($key, $array=[], $value="") {	
	# if the array is not an array
	if ($array && isset($key)) {
		# if key exists
		if (is_array($key)) $key=implode("", $key);
		
		if (is_object($array)) {
			# if the property exists in the object
			$text   =property_exists($array, $key);
			if ($text) $value=$array->$key;
		}
		elseif (is_array($array)) {
			# if the key exists in the array
			$text   =array_key_exists($key, $array);
			# fetch the array data
			if ($text) $value=$array[$key];
		}
	}
	return $value;
}

function jsonKey($text, $key="") {	
	$array	=json_decode($text, true);
	$value	=$array;
	if ($key) $value=arrayKey($key, $array);
	return $value;
}
	
function formKey($key, $default="") {
	$var	=trim($key);
	$nkey	=str_replace("_", ".", $var);
	$value	=config("forms.{$nkey}");
	if (!$value) $value=config("forms.{$var}");
	
	if (!$value) $value=arrayKey($var, $GLOBALS);
	if (!$value) {
		$value	=$default;
		config(["forms.{$nkey}", $value]);
	}
	return $value;			  
}

# if the variable is not global, set the given value
function varKey($key, $value="") {
	return formKey($key, $value);
}

function isVar($key, $value="") {
	return varKey($key, $value);
}

function propKey($key, $object="", $value="") {
	if (property_exists($object, $key)) $value=$object->$key;
	return $value;
}

# if the variable is an object, set the given value
function objKey($key, $array, $value="") {
	return arrayKey($key, $array, $value);
}

function constKey($key, $value="") {
	$nvar	=str_replace("app.", "", $key);
	$nvar	=str_replace(".", "_", $nvar);
	$nkey	=strtoupper($nvar);
	$nvalue	="";
	if (class_exists('config')) $nvalue=config($key);
	if (!$nvalue) $nvalue=env($nkey);#ROOT_BASE
	if (!$nvalue && defined($nkey)) $nvalue=constant($nkey);
	if (!$nvalue) $nvalue=$value;
	return $nvalue;
}

function isConst($key, $value="") {
	return constKey($key, $value);
}

function arrayValue($key, $array, $value="") {
	return arrayVal($key, $array, $value);
}

function inArray($key, $array, $value="") {
	return arrayVal($key, $array, $value);
}

function post($field="", $array="") {
	$post_data	=$_POST;
	if ($field) {
		$post_data	=arrayKey($field, $post_data);
	}
	if (is_array($post_data)&&!$array) $post_data=implode(",", $post_data);		
	return $post_data;
}

function get($field="", $array="") {
	$get_data	=$_GET;
	if ($field) {
		$get_data	=arrayKey($field, $get_data);
	}
	if (is_array($get_data)&&!$array) $get_data=implode(",", $get_data);		
	return $get_data;
}

function getKey($key="", $array="") {
	return get($key, $array);
}

function postKey($key="", $array="") {
	return post($key, $array);
}

function io($array, $type="") {
	$text	=json_encode($array, JSON_PRETTY_PRINT);
	if ($type=="x") $text=var_export($array, true);
	$text	=str_replace("\/", "/", $text);
	echo '<pre>'.$text.'</pre>';
}

function ix($array) {
	io($array, "x");
}

function build_url($data) {
	$link	=http_build_query($data);
	return str_replace("%2C", ",", $link);
}

function curl_send($url, $data="", $type="", $noverify=1, $method="", $options=[], $header="") {
	$post	=false;
	$text	=$data;
	if ($type=="post") $post=true;
	if (!in_array($method, ["GET", "POST", "PUT", "DELETE"])) $method="POST";
	if ($type=="json") {
		if (is_array($data)) $text=($data);
		$headers	=["HTTP/1.1 200 OK", "Content-Type: application/json"];#, "Content-Length: " . strlen($text)
	}
	else {
		if (is_array($data)) $text=http_build_query($data);
		$headers	=["HTTP/1.1 200 OK", "Content-Type: application/x-www-form-urlencoded"];//text/plain, multipart/form-data			
	}
	if ($header) $headers=$header;

	$array	=array(
		CURLOPT_URL=>$url, 
		CURLOPT_HEADER=>false, 
		CURLOPT_TIMEOUT=>0, 
		CURLOPT_HTTPHEADER=>$headers, 
		CURLOPT_CUSTOMREQUEST=>$method, 
		CURLOPT_RETURNTRANSFER=>true
	);
	
	if ($text) {
		if ($post) $array[CURLOPT_POST]=$post;
		$array[CURLOPT_POSTFIELDS]=$text;
	}
	if ($noverify) {
		$array[CURLOPT_SSL_VERIFYHOST]=false;
		$array[CURLOPT_SSL_VERIFYPEER]=false;
	}
	
	if ($options) $array=array_merge($array, $options);
	
	$curl	=curl_init();
	curl_setopt_array($curl, $array);
	$response	=curl_exec($curl);
	$curl_error	=curl_errno($curl);
	
	if ($curl_error) {
		$response	=["response"=>curl_error($curl), "status"=>$curl_error];
	}
	curl_close($curl);
	
	$result	=$response;
	if (!is_array($response)) $result=json_decode($response, 1);
	
	$log_data   =["url"=>$url, "response"=>$result, "options"=>$array];#, "data"=>$data
	logger("curl", $log_data);

	return $result;
}

function logit($method="", $array="") {
	$file	=strtolower($method);
	$file	=str_replace("::", "_", $file);
	$file	=storage_path('logs/'.$file.'.json');
	$text1	="";
	if (is_file($file)) $text1=file_get_contents($file);

	$data=["post"=>$_POST, "get"=>$_GET, "date"=>date("Y-m-d H:i:s")];
	if ($array) $data["return"]=$array;
	$text2	=json_encode($data, JSON_PRETTY_PRINT);
	$text2	=str_replace('\n', "\n", $text2);
	$text2	=str_replace('\r', "\r", $text2);
	$text2	=str_replace('\t', "\t", $text2);
	$text2	=str_replace('\/', "/", $text2);
	$text2	=str_replace('\"', '"', $text2);
	$text2	=str_replace('"{', '{', $text2);
	$text2	=str_replace('}"', '}', $text2);
	$text3  =implode(", \n\t", [$text2, $text1]);
	file_put_contents($file, $text3);
}


function browser() {
	$text_keys  ="browser,kernel,op_sys,rv,version";
	$browser  =arrayKey("HTTP_USER_AGENT", $_SERVER);

	$browser  =str_replace("(", ";", $browser);
	$browser  =str_replace(")", ";", $browser);
	$browser  =str_replace("; ", ";", $browser);
	$array_keys  =explode(",", $text_keys);
	$array_value =explode(";", $browser);
	$result  =array_combine($array_keys, $array_value);
	return $result;
}
