<?php
namespace Teescripts\Helpers;
function lang_token() {
	$token	="phrase";
	return $token;
}

function lang_files() {
	return constKey("ROOT")."Cache/language/";
}

function lang_base() {
	$lang1	=constKey("LANGUAGE");
	$result	=lang_get($lang1, "lang_code");
	if (!$result) $result=lang_get("", "lang_code", "`lang_status`=3");
	if (!$result) $result=lang_get("", "lang_code", "`lang_code`='en'");
	if (!$result) $result=lang_get("", "lang_code", "`lang_status`=2");
	return $result;
}

function lang_set() {
	$token	="language";
	$get_language	=getKey($token);
	$language	=sess_get($token);
	if ($get_language) {
		$language	=$get_language;
		sess_set($token, $language);
	}
	if (!$language) $language=lang_base();
	return $language;
}

function lang_get($name="", $key="", $where="") {
	$query	=lang_query($name, "", $where, $key);
	$result	=db_result($query);
	$result	=arrayKey($key, $result, $result);
	return $result;
}

function lang_node() {
	$result	=lang_all("", main_var("station"));
	if (!$result) $result=lang_all("", 1);
	return $result;
}

function lang_all($name="", $node="") {
	$query	=lang_query($name, $node);
	$results=db_query($query);
	return $results;
}

function lang_query($name="", $node="", $sql_and="", $colms="") {
	$col_state	="lang_status";
	if ($node) $col_state="lan_status";

	$where	=["`{$col_state}` IN(2, 3)"];
	if ($node) $where[]="`nodeid`='{$node}'";
	if ($name) $where[]="'{$name}' IN(`lang_code`, `lang_id`)";
	if ($sql_and) $where[]=$sql_and;
	$sql_where	=implode(" AND ", $where);
	$sql_where	="WHERE {$sql_where}";

	if (!$colms) {
		$colms	="`lang_id` AS `id`, `lang_name` AS `name`, `lang_code` AS `code`, `lang_local` AS `local`";
		$colms	.=", `lang_dir` AS `direction`, `lang_status` AS `status`, LOWER(IFNULL(`lang_flag`, `code2`)) AS `flag`";
	}
	$on_ctry="cn.`language` LIKE CONCAT(`lang_code`, '-%')";
	$join	="`#1_language` AS ln";
	$join	.=" LEFT JOIN `#1_node_language` AS nl ON `lang_code`=nl.`language`";
	$join	.=" LEFT JOIN `#1_country` AS cn ON ({$on_ctry} OR `lang_code`=cn.`language`)";
	$query	="SELECT {$colms} FROM {$join} {$sql_where} GROUP BY `lang_code` ORDER BY `name` ASC";

	return $query;
}
	
function lang_pdt($text, $source="", $target="") {
	#$text	=lang($text, $source, $target, "pdt_");
	return $text;
}
	
function lang_html($text, $source="", $target="") {
	$text	=lang($text, $source, $target, "txt_");
	return $text;
}

function lang($text, $source="", $target="", $prefix="") {
	return lang_key("", $text, $source, $target, $prefix);
}
	
function lang_id($key, $text, $source="", $target="") {
	$text	=lang_trans_id("var_".$key, $text, $source, $target);
	return $text;
}
	
function lang_htmlId($key, $text, $source="", $target="") {
	$text	=lang_trans_id("txt_".$key, $text, $source, $target);
	return $text;
}
	
function lang_pdt_id($key, $text, $source="", $target="") {
	$text	=lang_trans_id("pdt_".$key, $text, $source, $target);
	return $text;
}
	
function lang_trans_id($key, $text, $source="", $target="") {
	$text	=lang_key($key, $text, $source, $target);
	return $text;
}
	
function lang_textClean($text) {
	$text	=mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
	if (strstr($text, "_")) {
		$text	=str_replace("_", " ", $text);
		$text	=ucfirst($text);
	}
	return $text;
}
	
function lang_textStrip($text, $prefix="") {
	if (!$prefix) $prefix="var_";
	if (is_array($text)) $text=implode(" ", $text);
	$text	=preg_replace("/\s\s+/", " ", $text);

	$large	=500;
	$length	=strlen($text);
	if ($length>=$large) {
		$prefix	="txt_";
		$ignore	='<p><b><strong><i><em><li><ul><ol><u><span><a>';
		$text	=strip_tags($text, $ignore);
	}
	
	$array	=["prefix"=>$prefix, "text"=>$text];
	return $array;
}
	
function lang_textSaved($keys) {
	$count	=0;
	$text	="";
	$array	=main_var("lang_keys");
	if (!is_array($array)) $array=[];
	foreach ($keys as $key) {
		$exists	=array_key_exists($key, $array);
		if ($exists && !$count) {
			$text	=arrayKey($key, $keys);
			$count++;
		}
	}
	$result	=["count"=>$count, "text"=>$text];
	return $result;
}
	
function lang_text_key($key, $text, $prefix="") {
	$array	=lang_textStrip($text, $prefix);
	$text	=$array["text"];
	$prefix	=$array["prefix"];

	if ($key) {
		$key	=lang_variable($key);
		$keys	=[$key];
	}
	else {
		$text_key	=lang_variable($text);
		if (strlen($text_key)>100) $text_key=main_abbr($text_key);
		
		$keys	=[];
		$keys[]	=$text_key;
		$keys[]	=$prefix.$text_key;
		$keys[]	=$prefix.ucfirst($text_key);
		$keys[]	=strtolower($keys[1]);
		$keys[]	=$prefix.trim($text_key, "_");
		$keys	=array_unique($keys);
		$key	=$keys[1];
	}
	$array	=["key"=>$key, "keys"=>$keys, "text"=>$text];
	return $array;
}
	
function lang_translate($textKey, $textBlock, $source="en", $target="fr", $prefix="") {
	$array	=lang_text_key($textKey, $textBlock, $prefix);
	$textKey	=$array["key"];
	$arrayKeys	=$array["keys"];
	$textBlock	=$array["text"];

	$array	=lang_textSaved($arrayKeys);
	$exists		=$array["count"];
	$text_saved	=$array["text"];
	
	$textNew	=lang_textClean($textBlock);
	
	$textTrans	="";
	if (!$text_saved) {
		#$textTrans	=lang_translateBlocks($textNew, $source, $target);
	}

	$transText	=$textNew;
	if ($exists) $transText=$text_saved;
	if ($textTrans) $transText=$textTrans;
	if (!$transText) $transText=arrayKey($textKey, main_var("lang_keys"));

	$translate	=(!$exists||($textTrans && !$exists));
	$result	=["key"=>$textKey, "text"=>$transText, "save"=>$translate];
	return $result;
}

function lang_key($textKey, $text_block, $source="", $target="", $prefix="") {
	
	if (!$target) {
		$target	=main_var("lang");
	}

	if (!$source) $source="en";
	if (is_array($text_block)) {
		$text_block	=implode(" ", array_values($text_block));
	}
	
	$text_trans	="";
	if ($source && $target) {
		# translate text
		$result	=lang_translate($textKey, $text_block, $source, $target, $prefix);
		# save result
		$text_trans	=lang_textSave($target, $result);
	}
	if ($text_trans=="") $text_trans=$text_block;
	return $text_trans;
}
	
function lang_textSave($target, $array) {
	$translate	=$array["save"];
	$transText	=$array["text"];
	$transKey	=$array["key"];

	$token	=lang_token();
	if ($translate && $transText) {
		$words	=sess_get($token, []);
		$words[$target][$transKey]	=$transText;
		sess_set($token, $words);
	}
	return $transText;
}

function lang_translateBlocks($textBlock, $source, $target) {
	$limit	=1600;
	$length	=strlen($textBlock);
	# if string is beyond 2048 size limit
	if ($length>$limit) {
		$words	=explode(" ", $textBlock);
		$count	=count($words)-1;
		$block_full	=($limit/7);
		$text	="";
		$textBlock	="";
		foreach ($words as $key=>$word) {
			$full	=false;
			if (($key%$block_full==0)||($key==$count)) $full=true;
			
			$text	.="{$word} ";
			if ($key>0&&$full) {
				$text	=trim($text);
				# translate each part individually
				$textTrans	=lang_googleTranslate($text, $source, $target);

				$textBlock	.="{$textTrans} ";
				$text	="";
			}
		}
	}
	return $textBlock;
}
	
function lang_googleTranslate($textBlock, $source, $target) {
	$encodeText	=urlencode($textBlock);
	
	#"https://translation.googleapis.com/language/translate/v2?key=YOUR_API_KEY&source=".$source."&target=".$target."&q=".$encodeText;
	#"http://translate.google.com/translate_a/t?client=gtx&sl=".$source."&tl=".$target."&text=".$encodeText;		
	$transUrl	="https://translate.googleapis.com/translate_a/single?client=gtx&sl=".$source."&tl=".$target."&dt=t&q=".$encodeText;			
	$returnText	=@file_get_contents($transUrl);
	$translated	=lang_extractTrans($returnText);
	$textBlock	=$translated['translated'];
	return $textBlock;
}

function lang_extractTrans($text="") {
	$array	=json_decode($text, 1);
	$trans	=array('translated'=>arrayKey(0, $array), 'original'=>arrayKey(1, $array));
	return $trans;
}
	
function lang_savePhrase() {
	$folder	=lang_files();
	$token	=lang_token();
	$array_phrase	=sess_get($token);
	if (!is_dir($folder)) mkdir($folder, 0777, 1);
	if ($array_phrase) {
		foreach ($array_phrase as $language=>$words) {
			if ($words) {
				$json_file	=$folder.$language.".json";
				$json_array	=lang_fileJson($json_file);
				$json_array	=array_merge($words, $json_array);
				array_unique($json_array);
				$json_text	=json_encode($json_array);
				
				$error	=json_last_error();
				if ($error==0) {
					$json_text	=str_replace('","var_', "\", \n\"var_", $json_text);
					#$json_text	=str_replace('":"', '": "', $json_text);
					file_put_contents($json_file, $json_text);
				}
			}
		}
		sess_null($token);
	}
}
	
function lang_fileJson($file) {
	$result	=[];
	if (is_file($file)) {
		$text	=file_get_contents($file);
		if ($text) $array=json_decode($text, 1);
		if (is_array($array)) $result=$array;
	}
	return $result;
}

function lang_fileArray($lang_file) {
	$c	=[];
	if (is_file($lang_file)) include $lang_file;
	return $c;
}

function lang_keys() {
	$folder	=lang_files();
	$language	=main_var("var_lang");
	$jsonLang	=lang_fileJson($folder.$language.".json");		
	$arrayLang	=lang_fileArray($folder.$language.".php");
	
	$array	=[];
	if ($arrayLang) $array=$arrayLang;
	if ($jsonLang) $array=array_merge($array, $jsonLang);
	ksort($array);
	array_unique($array);
	return $array;
}

function lang_variable($text) {
	$text	=main_textDash($text, 2);
	$text	=str_replace("-", "_", $text); 
	$text	=preg_replace("/__+/", "_", $text); 
	$text	=strtolower($text);
	if ($text=='\\') $text="_backslash_";
	return $text;
}
