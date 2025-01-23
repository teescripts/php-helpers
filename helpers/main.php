<?php
namespace Teescripts\Helpers;
use Illuminate\Support\Str;

function main_view($dataid="") {
	return db_view($dataid);
}

function main_tags($dataid="") {
	return ($dataid);
}

function main_msgView($dataid="") {
	return ($dataid);
}

function main_country($dataid="") {
	return ($dataid);
}

function main_tabNames($dataid="") {
	$query	=($dataid);
	return $query;
}

function main_etext($dataid="") {
	return ($dataid);
}

function main_userId($dataid="") {
	return ($dataid);
}

function main_userAuth($dataid="") {
	return ($dataid);
}

function main_userName($dataid="") {
	return ($dataid);
}


function main_lists($text, $link="", $attrib="") {
}

function main_builder($text, $link="", $attrib="") {
}

function main_appName() {
	return config("app.name");
}

function main_link($text="/") {
	return url($text);
}

function main_isLocal() {
	return stristr(main_link("/"), "local");
}

function main_nav($label, $link="", $array=[]) {
	$type   =constKey("app.spa_type");

	$label   =trans($label);
	$class    =arrayKey("class", $array, "text-dark");

	if ($type=="inertia") {
		$text	='<Link :href="'.$link.'" :active="route().current(\''.$link.'\')" class="'.$class.'">'.$label.'</Link>';
		#'<NavLink /> <ResponsiveNavLink />';
	}
	elseif ($type=="livewire") {
		$text	='<x-link :href="'.$link.'" :active="request()->routeIs(\''.$link.'\')" class="'.$class.'" wire:navigate>'.$label.'</x-link>';
		#'<x-nav-link /> <x-responsive-nav-link />';
	}
	else {
		$text	='<a href="'.$link.'" class="'.$class.'">'.$label.'</a>';
	}
	return $text;
}

function main_class($type="", $extra="") {
	if ($type=="select") {
		$class	="form-control";
	}
	elseif ($type=="table") {
		$class	=table_class("123", $class);
	}
	if ($extra) $class.=" ".$extra;
	return trim($class);
}

function table_class($type="", $class="") {
	if (strstr($type, 1)) $class.=" table-bordered";
	if (strstr($type, 2)) $class.=" table-striped";
	if (strstr($type, 3)) $class.=" striped-columns";
	if (strstr($type, 4)) $class.=" table-condensed table-sm";
	$text   ="table ".$class;
	return $text;
}

function main_var($dataid="") {
	$value	=varKey("var_".$dataid);
	if (!$value) $value=varKey($dataid);
	return $value;
}

function main_getId($dataid="") {
	$bits	=explode('-', $dataid);
	$get_id	=$bits[0];
	return $get_id;
}

function main_getNode() {
	$company=main_var("company");
	$query	="SELECT `stationid` FROM `#1_company` WHERE `company_id`='{$company}'";
	
	$result	=main_var("station");
	if ($company) $result=db_view($query);
	return $result;
}
	
function main_nodeSite($station) {
	if (!$station) $station=main_var("station");
	$colms	="`node_type` AS `type`, `node_name` AS `name`, `node_site` AS `site`, `country`";
	$colms	.=", `language`, `node_sales` AS `sales`, `domain`, `ext_front` AS `front`, `nodeids`, `currency` AS `curid`, `logo_image` AS `logo`, `node_sales` AS `sales`";
	
	$query	="SELECT {$colms} FROM `#1_nodes` WHERE `node_id`='{$station}'";
	return db_result($query);
}

function main_nodeBiz() {
	$nodeid	=main_var("node");
	$shopid	=arrayKey("site", $nodeid);
	if (!$shopid) $shopid=1;
	return $shopid;
}

function main_sqlColms($query, $where="", $pairs="") {
	$query	=str_replace($where, "WHERE 1", $query);
	$result	=db_result($query, $pairs, "", 2);

	$array	=[];
	if (is_array($result)) {
		foreach ($result as $colm=>$value) {
			$array[$colm]	="";
		}
	}
	return $array;
}

function main_sqlBool($text="", $sql_where="") {
	if (!$sql_where) $sql_where=varKey("sql_where");
	if (!$text) $text=$sql_where;
	$text	=trim($text);
	$exists	=stristr(".{$text}", "where");
	$join	=($exists&&!$strip)?"AND":"WHERE";
	$exists	=stristr(".{$text}", ".{$join}");

	$bool	="";
	if (!$exists) $bool=" {$join} ";
	return $bool;	
}

function main_sqlMany($field="", $value="") {
	$text	="(#='@' OR # LIKE '@,%' OR # LIKE '%,@,%' OR # LIKE '%,@')";
	$text	=str_replace("#", $field, $text);
	$text	=str_replace("@", $value, $text);
	return $text;
}


function main_prefix($text) {
	$prefix1	=constKey("app.ext.prefix1", "tee_");
	$prefix2	=constKey("app.ext.prefix2");
	$prefix3	=constKey("app.ext.prefix3", $prefix2);
	
	$text	=str_replace("#_", $prefix2, $text);
	$text	=str_replace("#t_", $prefix1, $text);
	$text	=str_replace("#1_", $prefix2, $text);
	$text	=str_replace("#2_", $prefix3, $text);
	$text	=str_replace("#3_", $prefix1, $text);
	$text	=str_replace("`#", "`{$prefix2}", $text);
	return $text;
}

function main_sqlJoin($data) {
	if (is_array($data)) $data=implode("\n LEFT JOIN ", $data);
	$data	=str_replace("`#", "`#1_", $data);
	return $data;
}

function main_limit($array, $max=20) {
	$limit	=50;
	$max	=arrayKey("max", $array, 20);
	if ($max>$limit) $max=$limit;
	return $max;
}

function main_image($file="", $folder="", $array=[]) {
	$class	=arrayKey("class", $array);
	$dummy	=arrayKey("alt", $array);
	$strict	=arrayKey("strict", $array, true);
	$file	=main_photo($file, $folder, $strict);
	if (!$class) $class="img-polaroid wd-100";
	if (!$file && $dummy) $file=$dummy;

	$text	="";
	if ($file) $text='<img src="'.$file.'" alt="photo" class="'.$class.'" />';
	return $text;
}

function main_file($folder="", $file="", $strip="") {
	$link	=constKey("app.link.cdn");
	$base	=constKey("app.root.base");
	$docs	=constKey("app.root.cdn");
	
	$nbase	=base_path($base);
	$nbase	=realpath($nbase);

	$name	=Str::after($folder, "path_");
	$path	="{$docs}/{$name}";
	if (strstr($folder, $docs)) $path=str_replace('{base}', $nbase, $folder);

	$npath	=base_path($path);
	$npath	=realpath($npath);
	
	if ($file) {
		$source	="{$npath}/{$file}";
		$http	=$link."{$name}/{$file}";
		if (!is_file($source)) {
			$http	="";
			$source	="";
		}
	}
	else {
		if ($strip) {
			$http	="";
			$source	="";
		}
		else {
			$source	=$npath;
			$http	=$link."{$name}/";
		}
	}
	
	$plain	=str_replace("{$nbase}/", "", $source);
	$result	=["file"=>$source, "http"=>$http, "plain"=>$plain];
	return $result;
}

function main_docs($folder="", $file="", $strip="", $http="") {
	$array	=main_file($folder, $file, $strip);
	if ($http) {
		$path	=arrayKey("http", $array);
	}
	else {
		$path	=arrayKey("file", $array);
	}
	return $path;
}

function main_photo($file, $folder="thumbs", $strict=0, $alt="") {
	$array	=main_file($folder, $file, 1);
	$path	=arrayKey("file", $array);
	
	$dummy	="";
	if (!$strict) {
		$array_alt	=[1=>"male", 2=>"female", 4=>"product", 5=>"article", 6=>"video", 7=>"food", 8=>"cart", 9=>"dummy"];
		
		$dummy	=arrayKey($alt, $array_alt);
		if (!$dummy) $dummy=inArray($alt, $array_alt, "other");
		
		$dummy	=main_docs("stock/no_photo", $dummy.".png", "", 1);
		if (strstr($alt, ".")) $dummy=$alt;
		//$dummy="holder.js/31x50?bg=fff&fg=ccc&text=M";
	}

	if (is_file($path)) {
		$source	=arrayKey("http", $array);
	}
	elseif ($strict==2) {
		$source	=arrayKey("plain", $array);
	}
	else {
		$source	=$dummy;
	}
	return $source;
}

function main_head($text, $type="h3", $link="", $class="", $attrib="", $span="") {
	if ($text) {
		$types	=array("h1"=>"title", "h2"=>"dotted", "h3"=>"headings");
		if ($class) $types[$type]=$class;
		$text	=trim($text);
		$tags	=explode(" ", $text);
		$chip	=[];
		foreach ($tags as $key=>$tag) {
			if ($key==0&&$span) {
				$chip[]	='<span class="theme-secondary-color">'.$tag.'</span>';
			}
			else {
				$chip[]	=$tag;
			}		
		}
		$nText	=implode(" ", $chip);

		$nClass	="ex-title-format";
		if ($class) $nClass.=" {$class}";
		$attrib1	=' class="'.$nClass.'" '.trim($attrib);
		$attrib2	=' title="'.$text.'" '.trim($attrib);
		if ($type=="a") $attrib2.=' class="'.$nClass.'"';
		if ($link) $nText='<a href="'.$link.'"'.$attrib2.'>'.$nText.'</a>';
		if ($type!="a"||!$link) $nText='<'.$type.''.$attrib1.'>'.$nText.'</'.$type.'>';
		$text	=$nText;
	}
	return $text;			
}
	
function main_headPlain($text, $type="", $link="", $class="", $attrib="", $span="") {
	return main_head($text, $type, $link, $class, $attrib, $span);
}

function main_headId($key, $text, $type="", $link="", $class="", $attrib="", $span="") {
	$text	=lang_htmlId($key, $text);
	return main_head($text, $type, $link, $class, $attrib, $span);
}

function main_headHtml($text, $type="", $link="", $class="", $attrib="", $span="") {
	//$text	=lang_html($text);
	return main_head($text, $type, $link, $class, $attrib, $span);
}

function main_headPdt($text, $type="", $link="", $class="", $attrib="", $span="") {
	//$text	=lang_pdt($text);
	return main_head($text, $type, $link, $class, $attrib, $span);
}

function main_dropCap($text, $light="") {
	$text	=htmlspecialchars_decode($text);
	$text	=trim($text);
	$text	=str_replace('_<p>', '<p class="dropcap">', "_$text");
	$text	=str_replace('<ul>', '<ul class="style bullet-2">', $text);
	$text	=str_replace('<ol>', '<ol class="style special-2">', $text);
	$text	=str_replace('<li>', '<li class="special"><i class="fad fa-caret-right"></i> ', $text);
	$text	=trim($text, "_");
	#$text	=nl2br($text);
	return $text;
}

function main_concat($space=", ", $data=[]) {
	$text	=$data;
	if (is_array($data)) {
		$piece	=[];
		foreach ($data as $key=>$value) {
			$text	=$value;
			if (!strstr($value, "#")) $text="{$value}#";
			$text	=str_replace("#", $key, $text);
			if ($key) $piece[]=$text;
		}
		$text	=implode($space, $piece);	
	}
	return $text;
}

function main_json($text, $key="") {
	return jsonKey($text, $key);
}

function main_map($array_list, $value) {
	if (!is_array($array_list)) {
		if (stristr($array_list, 'list_')) $array_list=varKey($array_list);

		if (stristr($array_list, 'form_store(')) {		
			$array_list	=@eval('return '.$array_list.';');
		}
		elseif (stristr($array_list, '[f]')) {
			$result	=main_evalue($array_list, $value);
			$array_list	=array($value=>$result);
		}
		else {
			$array_list	=main_toArray($array_list, "all");
		}
		
		if (is_array($array_list)) $array_list=array_column($array_list, "name", "id");
	}
	
	$result	=[];
	$values	=explode(",", $value);
	foreach ($values as $value) {
		$text	=arrayKey($value, $array_list, $value);
		if ($text) $result[]=($text);#lang
	}
	$result	=implode(", ", $result);
	$result	=str_replace('[cm]', ",", $result);
	return $result; 
}

function main_value($value, $default="") {
	if (!$value) $value="";
	if (!$value && $default) $value=$default;
	if (is_array($value)) $value=implode(",", $value);
	if ($value) $value=htmlentities($value, ENT_COMPAT);
	return $value;
}

function main_evalue($text, $name="", $row="") {
	$sepr	=':';
	$text	=str_replace('":', '"[s]', $text);
	$text	=str_replace(':"', '[s]"', $text);
	$split	=explode($sepr, $text);
	
	$fxEnd	='[/f]';
	$sepr	=$fxEnd;
	if (strstr($text, "{$fxEnd}:")) $sepr="{$fxEnd}:";
	if (strstr($text, $sepr)) {
		$text	=str_replace(':', $fxEnd, $text);
		$text	=str_replace('[s]', ':', $text);
	}
	$expr	=arrayKey(0, $split);
	$link	=arrayKey(1, $split);
	$extra	=arrayKey(2, $split);
	# ------ custom
	$value	=arrayKey($name, $row, $name);
	if ($name&&!$row) $value=$name;
	$expr	=main_getRow($expr, $name, $value, $row);
	$field	=$expr;
	if ($link) {
		$link	=rtrim($link, '/').'/'.$value;
		$link	=str_replace('=/', '=', $link);
		$link	=main_getRow($link, $name, $value, $row);
		$field	='<a href="'.$link.'" '.$extra.'>'.$expr.'</a>';
	}
	$field	=str_replace('[s]', ':', $field);
	return $field; 
}
	
function main_getRow($text, $name, $value="", $row="") {
	$main	="";

	if (strstr($text, '[f]')) {
		$text	=str_replace('[f]', 'return ', $text);
		$text	=str_replace('[/f]', ';', $text);
	}
	else {
		$text	=str_replace('"', '\"', $text);
		$text	='return "'.$text.'";';
	}
	$text	=str_replace('[c]', ':', $text);
	$text	=str_replace('[s]', ':', $text);
	$text	=str_replace('[', '$row_', $text);
	$text	=str_replace(']', '', $text);
	$text	=str_replace('$row_v', '[v]', $text);
	$text	=str_replace('#', $value, $text);
	$text	=str_replace('{row}', '$row', $text);
	$text	=str_replace('$this->main', '$main', $text);
	$text	=str_replace('$this->load', '$load', $text);
	$text	=str_replace('$this->form', '$form', $text);

	if (is_array($row) && strstr($text, '$row_')) extract($row, EXTR_PREFIX_ALL, "row");
	$text	=eval($text);

	$text	=str_replace('(h)', '#', $text);
	return $text; 
}


function main_redirect($path, $timeout=0) {
	return '<script type="text/javascript">
	setTimeout(function() {
		document.location.href="'.$path.'";
	}, '.$timeout.'000);
	</script>';
}
	
function main_multi($values, $query, $url="", $link_class="") {
	$tip_class	="tip-top";
	$btn_group	="d-inline";
	$btn_link	="badge mr5 mb5";
	$txt	=[];
	$array	=explode(",", $values);
	$many	=(count($array)>1);
	$page	=arrayKey(1, main_var("init_url"), "index");
	$view	=stristr("view,update,insert,print", $page);
	$grouped=(($link_class==$btn_group)&&$link_class);
	$class	=($grouped)?$btn_link:$link_class;
	$class	=($class&&$tip_class)?'class="'.$tip_class.' '.$class.'"':"";
	foreach ($array as $value) {
		$nquery	=str_replace('[v]', $value, $query);
		$nquery	=str_replace('{v}', $value, $nquery);
		$nquery	=str_replace('(v)', $value, $nquery);
		$name	=db_view($nquery);
		if (!$name) $name=$value;
		$name	=trans($name);
		$title	=$name;
		//if (!$view && $many) $title=main_abbr($name);
		$link	=$url.$value;
		$text	='<span title="'.$name.'" '.$class.'>'.$title.'</span>';
		if ($url&&$name) $text='<a href="'.$link.'" title="'.$name.'" '.$class.'>'.$title.'</a>';
		$txt[]	=$text;
	}
	$text	=($grouped)?'<div class="'.$btn_group.'">'.implode("", $txt).'</div>':implode(", ", $txt);
	return $text;
}


function main_tagExtract($tag, $text) {
	$tag_array	=main_arrayFormat($text);
	$new_value	=arrayKey($tag, $tag_array);
	return $new_value;
}

function main_arrayFormat($list) {
	$array	=trim($list);
	$array	=str_replace('""', '[]', $array);
	$array	=trim($array, '"');
	if (strstr($array, "='")) {
		$array	=trim($array, "'");
		$array	=str_replace("='", '=>', $array);
	}
	$array	=str_replace('[]', '"', $array);
	$array	=str_replace('="', '=>', $array);
	$array	=str_replace(",", '[cm]', $array);
	$array	=str_replace('" ', ';', $array);
	$array	=main_arrayConvert($array, "list");
	return $array;
}	

function main_arrayTree($list) {
	return main_arrayConvert($list, "tree");
}

function main_arrayChild($list) {
	return main_arrayConvert($list, "children");
}

function main_toArray($list, $multi="") {
	if ($multi) $multi="all";
	$array	=main_arrayConvert($list, $multi);
	#if (!$multi && is_array($array)) $array=array_column($array["all"], "name", "id");
	return $array;
}


function main_arrayFill($data="", $list="", $except="") {
	if (!is_array($list)) $list	=explode(",", $list);
	
	$count	=0;
	$array	=[];
	$keys1	=array_keys($list);
	$keys2	=array_keys($keys1);
	foreach ($list as $colm=>$field) {
		$value	=arrayKey($field, $data, null);
		$valid	=array_key_exists($field, $data);
		if ($valid && ($value||strstr($except, $field))) {
			$key1	=arrayKey($count, $keys1);
			$key2	=arrayKey($count, $keys2);
			if ($key1==$key2 && is_int($colm)) $colm=$field;
			$array[$colm]	=$value;
		}
		$count++;
	}
	return $array;
}

function main_arrayMerge($data="", $values="") {
	$keys	=array_keys($data + $values);
	$array	=[];
	foreach ($keys as $colm) {
		$value	=arrayKey($colm, $data);
		$value	=arrayKey($colm, $values, $value);
		$array[$colm]	=$value;#if ($value) 
	}
	return $array;
}

function main_arrayConvert($list, $type="") {
	if (strstr($list, ";") || strstr($list, "=>")) {
		$nlist	=str_replace('=>', '":"', $list);
		$nlist	=str_replace(';', '", "', $nlist);
		if (strstr($list, "=>")) {
			$nlist	='{"'.$nlist.'"}';
		}
		else {
			$nlist	='["'.$nlist.'"]';
		}
		
		$array	=json_decode($nlist, true);
		if (in_array($type, ["tree", "all"])) {
			if (is_array($array)) {
				$item	=[];
				$keys	=array_values($array);
				foreach ($array as $key=>$value) {
					$colm	=arrayKey($key, $keys);
					if ($colm==$value) $key=$value;
					$item[]	=["id"=>$key, "name"=>$value];
				}
				$array	=$item;
			}
		}
		$list	=$array;
	}
	return  $list;
	//return main_arrayConvert($list, $type);
}	

function main_splitUrl($exclude="") {
	$get_url	=$_GET;
	if (!is_array($exclude)) $exclude=explode(",", $exclude);
	$exclude[]	="currency";
	$exclude[]	="language";
	foreach ($exclude as $word) {
		if (array_key_exists($word, $get_url)) unset($get_url[$word]);
	}
	$link	=http_build_query($get_url);
	$link	=str_replace(".url=", "", ".{$link}");
	$link	=str_replace("%2F", "/", $link);
	return $link;
}

function main_hash($open_password, $hash="") {	
	$password	=hash("sha256", $open_password.$hash);
	return $password;
}
 
function main_randomize($length="", $type="", $upper="") {
	$number	=1;
	if (!$length) $length =24;
	if (function_exists('openssl_random_pseudo_bytes')) {
		$salt	=openssl_random_pseudo_bytes($length, $number);
		$text	=base64_encode($salt); //base64 is about 33% longer, so we need to truncate the result
	}
	else {
		$text	=str_pad(uniqid(1, 1), $length, "B", STR_PAD_LEFT);
		$text	=md5($text);
	}

	if ($upper) $text=strtoupper($text);
	$string	=substr($text, 0, $length);

	return $string;
}

function main_inWords($number, $type="") {
	$places		=array("", "thousand", "million", "billion", "trillion", "quadrillion");
	$part		=explode(".", $number);
	$number		=arrayKey(0, $part);
	$decimal	=arrayKey(1, $part);
	
	$csv_number	=number_format((int)$number);
	$csv_blocks	=explode(",", $csv_number);
	$count_csv	=count($csv_blocks);
	
	$defined	=array();
	foreach ($csv_blocks as $position=>$csv_block) {
		$text	=main_inPart($csv_block);
		$block_key	=($count_csv - ($position+1));
		$csv_place	=arrayKey($block_key, $places);
		if ($csv_place) $csv_place=" $csv_place";
		
		if ($text) $defined[]=$text.$csv_place;
	}
	$words		=[];	
	$words[]	=implode(", ", $defined);
	
	if ($type&&$decimal>0) {
		$words[]="point";
		$words[]=main_inPart($decimal, $type);
	}
	$words	=implode(" ", $words);
	return 	$words;
}

function main_inPart($number, $type="") {		
	$digits		=["zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"];
	$array_tens	=[10=>"ten", 11=>"eleven", 12=>"twelve", 13=>"thirteen", 15=>"fifteen", 2=>"twenty", 3=>"thirty", 5=>"fifty"];
	
	$array_text	=[];
	$array_no	=str_split($number, 1);
	if ($type=="") {
		$array_rev	=array_reverse($array_no);
		$one	=arrayKey(0, $array_rev);
		$ten	=arrayKey(1, $array_rev);
		$hundred=arrayKey(2, $array_rev);
		
		$teens	="";
		$text_one	=arrayKey($one, $digits);
		if ($one=="0") $text_one="";
		if ($ten!="") {
			$tens	=$ten.$one;
			$text_tens	=arrayKey($tens, $array_tens);
			$text_ten	=arrayKey($ten, $array_tens);
			$text_dten	=arrayKey($ten, $digits);
			if ($ten=="0") $text_dten="";
			$text_ones	=" ".$text_one;
			if ($text_dten) $teens=$text_dten."ty".$text_ones;
			if ($text_ten) $teens=$text_ten.$text_ones;
			if ($ten==1) $teens=$text_one."teen";
			if ($text_tens) $teens=$text_tens;
			if ($teens=="") $teens=$text_one;
		}
		if ($hundred>0) $array_text[]=arrayKey($hundred, $digits)." hundred";
		if ($teens) $array_text[]=$teens;
		if ($one!="0"&&!$teens) $array_text[]=$text_one;
	}
	else {
		foreach ($array_no as $x=>$digit) {
			$array_text[]	=arrayKey($digit, $digits);
		}					
	}
	$text	=implode(" ", $array_text);
	$text	=str_replace("tt", "t", $text);
	return $text;
}

function main_frontEnd($details) {
	$details	=str_replace("..//","../",$details);
	$details	=str_replace("../","",$details);
	$details	=htmlspecialchars_decode($details);
	#$details	=main_dropCap($details);
	#$details	=nl2br($details);
	
	$stack	=array("", "rotated", "twisted", "rotated-left");//rand(0, 3)
	$details	=str_replace('<blockquote','<div class="img_file"><blockquote class="news-ad pull-right" ',$details);
	$details	=str_replace('</blockquote>','</blockquote></div>',$details);
	$details	=str_replace('<iframe','<div class="video-wrapper"><div class="nt-video"><iframe',$details);
	$details	=str_replace('</iframe>','</iframe><!-- iframe --></div></div>',$details);
	$details	=str_replace('<img ','<img class="img-rounded" alt="..." ',$details);#stack '.$stack[0].'
	$details	=str_replace(' />', '></span>', $details);
	$details	=str_replace('alt=""', 'alt="photo" class="img-circle thumb2"', $details);
	$details	=str_replace('float: left;','float:left; margin-right:10px;',$details);
	$details	=str_replace('float: right;','float:right; margin-left:10px;',$details);
	$details	=str_replace('"pos-image" style="text-align: left;','"pos-image left" style="',$details);
	$details	=str_replace('"pos-image" style="text-align: right;','"pos-image right" style="',$details);	
	$details	=str_replace('<ul>', '<ul class="list-style2">', $details);
	$details	=str_replace('<ol>', '<ul class="list-style1">', $details);
	$details	=str_replace('<li>', '<li><i class="fad fa-check"></i> ', $details);

	return $details;
}

function main_menu($link="", $type="", $id="", $title="") {			
	$link	=main_urlExtra($link);
	if (!$link) $link=main_url($type, $id, $title);
	return $link;
}

function main_urlExtra($link="") {
	$get_biz	=getKey("biz");		
	$get_shop	=getKey("shop");		
	if ($link && !strstr($link, "http")) {
		$link	=url($link);
		$is_biz	=($get_biz && $get_biz==main_var("company"));
		if ($get_shop) $link.="&shop=".$get_shop;
		if ($is_biz) $link.="&biz=".$get_biz;
	}
	return $link;
}

function main_url($parent, $record="", $title="") {
	$url_text	=[];
	if ($parent==1) {
		$link	="";
		$query	="SELECT `link`, `title` FROM `#1_cont_articles` WHERE `article_id`=:articleid";
		$result	=db_query($query, ["articleid"=>$record]);
		if ($result) {
			$link	=$result[0]["link"];			
			$title	=$result[0]["title"];
		}
		if (!$link) {
			$title	=str_replace("&" ,"and", str_replace(" ", "-", strtolower(trim($title))));#str_replace("?","",)
			$link	="content";
			if ($record) $link.="/read/{$record}-{$title}";	
		}
		$url_text[]	=$link;
	}
	elseif($parent==2) {
		$url_text[]	="shop/";
		if ($record) $url_text[]="product/{$record}";
	}
	elseif($parent==3) {
		$url_text[]	="shop/";
		if ($record) $url_text[]="subcat/{$record}";
	}
	elseif($parent==4) {
		$url_text[]	="shop/";
		if ($record) $url_text[]="category/{$record}";
	}
	elseif ($title) {
		if (is_array($title)) $title=implode(" ", $title);
		$title	=trim($title);
		$title	=strtolower($title);
		$title	=str_replace("&", "and", $title);
		$title	=str_replace(" ", "-", $title);
		$title	=main_textNormal($title, 2);
		$title	=str_replace("_", "-", $title);
		$title	=preg_replace("/-+/", "-", $title);
		
		$url_text[]	="{$parent}/";
		if ($record) $url_text[]="{$record}-{$title}";
	}
	else {
		if ($parent) $url_text[]="{$parent}";
		if ($record) $url_text[]="/{$record}";
	}
	$url_text	=implode("", $url_text);
	if ($url_text) {
		$url_text	=main_urlExtra($url_text);
		return $url_text;
	}
	else {
		//$url_text	="javascript:;";
	}
}


function main_textNormal($text, $encode=2) {
	if (is_array($text)) $text=implode(" ", $text);
	$text	=strip_tags($text);
	$text	=preg_replace("/\s\s+/", " ", $text);
	if ($encode==1||$encode=="url") $text=urlencode($text);
	if ($encode==2||$encode=="chars") {
		$text	=htmlspecialchars($text, ENT_HTML5, "UTF-8", false);
	}
	if ($encode==3||$encode=="ent") $text=htmlentities($text, ENT_HTML5, "UTF-8", false);
	$text	=str_replace("\r", "", $text);
	$text	=str_replace("\n", "", $text);
	$text	=str_replace("\t", "", $text);
	$text	=str_replace('.', "", $text);
	$text	=str_replace(' ', "-", $text);
	$text	=str_replace('+', "-", $text);
	$text	=str_replace('?', "_", $text);
	$text	=str_replace('=', "_", $text);
	$text	=str_replace('%', "_", $text);
	$text	=str_replace('&', "_", $text);
	$text	=str_replace('#', "_", $text);
	$text	=str_replace(';', "_", $text);
	$text	=str_replace("'", "_", $text);
	$text	=str_replace(",", "_", $text);
	$text	=str_replace("/", "_", $text);
	$text	=preg_replace("/__+/", "_", $text);
	return $text;
}

function main_textDash($text, $encode=2) {
	$text	=main_textNormal($text, $encode);
	$text	=str_replace("-", "_", $text);
	$text	=strtolower($text);
	return $text;
}

function main_textName($text, $encode=2) {
	$text	=main_textNormal($text, $encode);
	$text	=strtolower($text);
	return $text;
}

function main_textOnly($text, $encode=2) {
	$text	=main_textDash($text, $encode);
	$text	=str_replace("_", "", $text);
	return $text;
}
	
function main_intVal($value, $default="") {
	$value	=str_replace(",", "", $value);
	$value	=strip_tags($value);
	$value	=doubleval($value);
	if (!$value && $default) $value=$default;
	return $value;
}

function main_postInt($fields, $post="") {
	if (!$post) $post=$_POST;
	$array	=explode(",", $fields);
	foreach ($array as $field) {
		$amount	=arrayKey($field, $post);
		if ($amount) {
			$amount	=main_intVal($amount);
			$post[$field]	=$amount;
		}
	}
	$_POST	=$post;
	return $post;
}

function main_abbr($text, $space=" ", $case=1) {
	$abbr	="";
	$text=preg_replace("/[()\[\]\-%!'`_.=:;,]+/i", " ", $text);
	$text=preg_replace("/\s\s+/i", " ", $text);
	$words	=explode($space, $text);
	foreach ($words as $word) {
		$txt	=trim($word);
		$txt	=substr($txt, 0, 1);
		if ($case==1) $txt=strtoupper($txt);
		if ($case==2) $txt=strtolower($txt);
		$abbr	.=$txt;
	}
	return $abbr;
}

function main_letters($text, $length="") {
	$length	=($length)?$length:45;
	$strip	=strip_tags($text);
	$ntext	=preg_replace("/\s\s+/", " ", $strip);
	$ntext	=html_entity_decode($ntext);
	$ntext	=trim($ntext);
	$ntext	=strlen($ntext);
	$ntext	=substr($ntext, 0, $length);
	
	$text	=($strlen>=$length)?$ntext.'&hellip;':$strip;
	
	return $text;
}

function main_words($text, $count="", $tags="") {
	$count	=($count)?$count:10;
	$ntext	=str_replace("\n", ", ", $text);
	$ntext	=str_replace("<br>", ", ", $text);
	$ntext	=preg_replace("/\s\s+/", " ", $ntext);
	$ntext	=trim($ntext);
	$ntext	=strip_tags($ntext, $tags);
	
	$words	=explode(" ", $ntext);
	$chunk	=array_chunk($words, $count);
	$text	=implode(" ", $chunk[0]);
	$text	.=(count($words)>$count)?"...":"";
	return $text;
}
	
function main_txt($exploder=".", $text="", $key="array") {
	if ($text) {
		$bits	=$text;
		if (!is_array($text)) $bits=explode($exploder, $text);
		$count	=count($bits);	
		$lkey	=$count - 1;	
		$first	=arrayKey(0, $bits);	
		$last	=arrayKey($lkey, $bits);	
		$name	=str_replace($exploder.$last, "", $text);
		$array	=["name"=>$name, "first"=>$first, "ext"=>$last, "count"=>$count, "last"=>$last];
		$array	=array_merge($array, $bits);
		$array["array"]	=$array;
		$result	=arrayKey($key, $array, $array);
		return $result;
	}
}

function main_msg($array, $text="", $script="") {
	return msg($array, $text, $script);
}

function msg($array, $text="Be notified", $script=true) {
	$base	=arrayKey(0, main_var("init_url"));
	if ($script!=2) $script=stristr($base, "admin");

	$class	=$array;
	if (is_array($array)) {
		$class	=arrayKey(0, $array);
		$text	=arrayKey(1, $array, $text);
	}
	$icons	=[1=>"check-circle", 2=>"info-circle", 3=>"exclamation-circle", 4=>"times-circle", 0=>"info"];
	$color1	=[1=>"success", 2=>"info", 3=>"warning", 4=>"danger", 0=>"secondary"];#primary
	
	#teal, cyan, amber, red
	$color2	=[1=>"green darken-1", 2=>"light-blue darken-1", 3=>"amber lighten-2", 4=>"deep-orange lighten-2", 0=>"notice blue-grey lighten-3"];
	$colors	=[1=>$color1, 2=>$color2];

	$alert	=constKey("ALERT");
	$icon	=arrayKey($class, $icons);
	$type	=arrayKey($class, $color1);

	$colors	=arrayKey($alert, $colors);
	$color	=arrayKey($class, $colors);
	if ($alert==1) $color="alert alert-{$color} shadow-sm d-block";
	if ($alert==2) $color="{$color} z-depth-3 pd-10";

	$type2	=$type;
	if ($class==4) $type2="error";

	$reason ='
	<div class="'.$color.'"><i class="fad fa-1x fa-'.$icon.'"></i> '.$text.'</div>';// <a href="#" class="close-btn">&times;</a>

	$notice	='
		toastr.options	=toastrOptions;
		toastr["'.$type2.'"](\''.($text).'\', "'.ucfirst($type).'");';

	if ($class==1) $notice='
		Swal.fire({
			icon: "'.$type.'",
			title: "'.ucfirst($type).'",
			html: \''.$text.'\', 
			timer: 5000,
			timerProgressBar: true
		});';#title, icon, text, html, footer, position: 'top-end'

	if ($script==2) $script=false;
	if ($script) {
		$reason .='<script>
	$(function() {
		'.$notice.'
	});
	</script>';
	}
	return $reason;
}
	
function msgPlain($class, $message="", $script="") {
	return msg($class, $message, $script);//general->
}

function msgLang($class, $text="") {
	$text	=trans($text);
	return msgPlain($class, $text);
}

function msgId($key, $class, $text="", $script="") {
	$base	=arrayKey(0, main_var("init_url"));
	if (!$key) $key="info_".$base;
	$text	=lang_htmlId($key, $text);
	return msg($class, $text, $script);
}

function main_dateFormat($date, $format="") {
	if (!$format) $format="l dS F, Y";
	$new_date	=$date;
	if (strlen($date)>=5) {
		$new_date=date_create($new_date);
		$new_date=date_format($new_date, $format);
	}
	return $new_date;
}

function main_dateAdd($start, $period="P0D") {
	return main_datePlusMin($start, $period, "+");
}

function main_dateSub($start, $period="P0D") {
	return main_datePlusMin($start, $period, "-");
}

function main_datePlusMin($start, $period="P0D", $dir="+") {
	#'P7Y5M4DT4H3M2S'=Period/Date part: 7years 5 months 4 days, Time part: 4 hrs 3 min 2 seconds
	$format	="Y-m-d";
	if (strlen($start)>10) $format.=" H:m:s";
	$plus	=($dir=="+");
	$basic	=strstr($period, " ");
	if ($basic) {
		$created	=date_create($start);
		$interval	=date_interval_create_from_date_string($period);
		if ($plus) {
			date_add($created, $interval);
		}
		else {
			date_sub($created, $interval);
		}
		$text	=date_format($created, $format);
	}
	else {
		$created	=new DateTime($start);
		$interval	=new DateInterval($period);
		if ($plus) {
			$created->add($interval);
		}
		else {
			$created->sub($interval);
		}
		$text	=$created->format($format);
	}
	return $text;
}

function main_dateArray($start, $end, $type="array") {
	$start	=date_create($start);
	$end	=date_create($end);
	$interval=date_diff($end, $start);//y,m,d,h,i,s,days
	
	$seconds	=$interval->s;
	$minutes	=$interval->i;
	$hours		=$interval->h;
	$days		=$interval->days;#d
	$months		=$interval->m;
	$years		=$interval->y;

	$day	=$days;
	if (!$months && $years) $months=($years%12);
	if ($days>=30) $day=($days%30);
	$weeks	=floor($day/7);
	if ($day>=7) $day=($day%7);
	
	$interval	=["year"=>$years, "month"=>$months, "week"=>$weeks, "day"=>$day, "days"=>$days, "hour"=>$hours, "minute"=>$minutes, "second"=>$seconds];
	
	$result	=arrayKey($type, $interval, $interval);
	return $result;
}

function main_dateDiff($start, $end, $type="", $decimal="") {
	$array	=main_dateArray($start, $end, "array");
	$result	=arrayKey($type, $array);
	if (!$result) $result=arrayKey("hour", $array);

	$result	=($decimal)?number_format((float)$result, 2):round($result);
	return $result;
}

function main_days($date, $from="now") {
	return main_dateArray($date, $from, "days");
}

function main_age($date, $labels="days,hour,minute,second", $max="") {
	#century,decade,quarter,fortnight,week
	$interval	=main_dateArray($date, 'now', "array");
	
	$labels	=explode(",", $labels);
	$array	=array();
	foreach ($interval as $label=>$value) {
		$title	=$label;
		if ($label=="day") $title="day";
		if ($value>1) $title.="s";
		$text	="{$value} {$title}";
		if ($value && in_array($label, $labels)) $array[]=$text;
		if ($max && $array) return "Over {$text}";
	}
	$text	=implode(", ", $array);
	if (substr($date, 0, 4)=="0000") $text="";
	return $text;
}

function main_ageText($date, $labels="", $max="") {
	$text	=main_age($date, $labels, $max);
	#century,decade,quarter,fortnight,week
	$text	='<i class="fad fa-clock"></i> '.$text.'';
	$text	='<span class="timeago" title="'.$date.'" datetime="'.$date.'">'.$text.'</span>';
	return $text;
}

function main_gpsKms($coords="") {
	if (!$coords) $coords=arrayKey("location", main_var("stored"));
	if (!is_array($coords) && $coords) {
		if (stristr($coords, '"lat":')) {
			$coords	=json_decode($coords);
		}
		else {
			$coords	=explode(",", $coords);
			$lat	=arrayKey(0, $coords);
			$long	=arrayKey(1, $coords);
			$coords	=["lat"=>$lat, "lng"=>$lng];
		}
	}
	$lat	=arrayKey("lat", $coords);
	$long	=arrayKey("lng", $coords);
	if (!$lat) $lat="''";
	if (!$long) $long="''";
	
	$colm_lat	="`gps_lat`";
	$colm_long	="`gps_lng`";
	$formula	="(((acos(sin(({$colm_lat}*pi()/180)) * sin(({$lat}*pi()/180))+cos(({$colm_lat}*pi()/180)) * cos(({$lat}*pi()/180)) * cos((({$colm_long}-{$long})*pi()/180))))*180/pi())*60*1.1515*1.609344)";#distance in KMs
	return $formula;
}

function main_rgb($number) {
	$color	=($number % 255);
	return $color;
}

function main_color($text) {
	$string	=preg_replace("/[a-z\=]+/i", "", base64_encode($text));
	$number	=intval($string);
	$number	=str_repeat($number, 3);
	$number	=substr($number, 0, 9);
	$chip	=str_split($number, 3);
	
	$array	=[main_rgb($chip[0]), main_rgb($chip[1]), main_rgb($chip[2])];
	$text	=implode(", ", $array);
	#$text	="rgba({$text}, .5)";
	return $text;
}

function main_permission($table, $page="") {
    $table2   =main_prefix($table);
    $table1   =main_prefix("#t_permissions");
    $actions =DB::table($table1)->where("roleid", 1)->where("table", $table2)->value("actions");
    $ar_tabs =explode(",", $actions);

    $module =menu_modClean($mod_tbl);

    $tabs =[];
    foreach ($ar_tabs as $tab) {
		$file   =resource_path("views/mods/{$module}/{$tab}.blade.php");
		if (is_file($file)) {
			$link   =main_link("{$module}/{$tab}");
			$tabs[$tab] =$link;
		}
    }
    $result =$tabs;
    if ($page) $result=arrayKey($page, $tabs);
    return $result;
}

