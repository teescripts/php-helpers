<?php
namespace Teescripts\Helpers;
function search_column($key, $text="", $value="") {
	$array	=$value;
	if (!is_array($value)) $array=array($key=>$value);
	foreach ($array as $field=>$value) {
		${"var_{$field}"}	=$value;
		$text	=str_replace("{val}", $value, $text);
	}
	$text	=str_replace("{end}", $var_end, $text);
	$text	=str_replace("{start}", $var_start, $text);
	return $text;
}

function search_form($array_data, $colm=[]) {
	$name	=[];
	if ($array_data) {
		foreach ($array_data as $field=>$value) {
			$text	=$field;
			//if (arrayKey($field, $array_data)) $text=search_form($value, $field);
			//if ($colm) $text=implode("_", $text);
			$name[]	=$text;//
		}
	}
	
	return $name;
}


function search_values($fields, $phrase="") {
	$where	=[];
	$data	=array_merge($_GET, $_POST);
	if (!is_array($fields)) $fields=explode(",", $fields);
	foreach ($fields as $key=>$array) {
		if (!is_array($array)) $array=explode(":", $array);
		$column	=arrayKey(0, $array);
		$field	=arrayKey(1, $array, $column);
		$bool	=arrayKey(2, $array);
		$value	=arrayKey(3, $array);

		$ending	="";
		if (!$value) $value=arrayKey("a_{$field}", $data);
		if (!$value&&$phrase) $value=arrayKey($field, $data, $phrase);
		if (!$bool) $ending=arrayKey("to_{$field}", $data);
		if (!$bool) $bool=arrayKey("bool_{$field}", $data, "like");
		if ($value&&$ending) $value=array($value, $ending);
		if ($value) $where[]	=array($column, $value, $bool);
	}
	return $where;
}

function search_where($fields, $join="", $split="") {
	$where	=[];
	$templ	=search_sqlTemp();
	if (!is_array($fields)) $fields=explode(",", $fields);
	foreach ($fields as $key=>$array) {
		if (!is_array($array)) $array=explode(":", $array);
		$column	=arrayKey(0, $array);
		$value	=arrayKey(1, $array);
		$boolean=arrayKey(2, $array, "like");
		$temp	=arrayKey($boolean, $templ);
		
		if (in_array($boolean, ["in", "btn"])) {
			$values	=$value;
			if (!is_array($value)) {
				$values	=str_replace(";", ",", $value);
				$values	=explode(",", $values);
			}
			$value	=implode("', '", $values);
		}

		if ($split) {
			$value	=preg_replace("/\s\s+/", " ", $value);
			$values	=explode(" ", $value);
			$chip	=[];
			foreach ($values as $word) {
				$chip[]	=search_phrase($column, $word, $boolean, $temp);
			}
			$phrase	=implode(" AND ", $chip);
			$phrase	="({$phrase})";
		}
		else {
			$phrase	=search_phrase($column, $value, $boolean, $temp);
		}
		if ($phrase) $where[]=$phrase;

	}
	if (!$join) $join="AND";
	$count	=count($where);
	$where	=implode("\n {$join} ", $where);
	if ($where && $count>1) $where="({$where})";
	return $where;
}

function search_phrase($colm, $value, $boolean="", $temp="") {
	if ($colm) {
		if (!stristr($colm, "`")) $colm="`{$colm}`";
	}
	$temp	=str_replace("[col]", $colm, $temp);
	$temp	=str_replace("[val]", $value, $temp);
	if ($value && $value!="undefined"||strstr($boolean, "null")) {
		if (is_array($value)) {
			$val1	=arrayKey(0, $value);
			$val2	=arrayKey(1, $value);	
			$temp	=str_replace("[val1]", $val1, $temp);
			$temp	=str_replace("[val2]", $val2, $temp);
		}
		if (is_array($colm)) {
			$col1	=arrayKey(0, $colm);
			$col2	=arrayKey(1, $colm);	
			$temp	=str_replace("[col1]", $col1, $temp);
			$temp	=str_replace("[col2]", $col2, $temp);
		}
	}
	return $temp;
}

function search_sqlTemp() {
	$contains	="(c='v' OR c LIKE 'v,%' OR c LIKE '%,v,%' OR c LIKE '%,v')";
	$contains	=str_replace("c", "[col]", $contains);
	$contains	=str_replace("v", "[val]", $contains);
	$array	=array(
		"equal"=>array("txt"=>["=", "eql", "equals"], "temp"=>"[col]='[val]'"), 
		"not"=>array("txt"=>["!=", "<>"], "temp"=>"[col]!='[val]'"), 
		"in"=>array("txt"=>["includes"], "temp"=>"[col] IN('[val]')"), 
		"!in"=>array("txt"=>["notin"], "temp"=>"[col] NOT IN('[val]')"), 
		"less"=>array("txt"=>["<"], "temp"=>"[col]<'[val]'"), 
		"great"=>array("txt"=>[">", "grt"], "temp"=>"[col]>'[val]'"), 
		"loe"=>array("txt"=>["<="], "temp"=>"[col]<='[val]'"), 
		"goe"=>array("txt"=>[">="], "temp"=>"[col]>='[val]'"), 
		"null"=>array("txt"=>["empty"], "temp"=>"[col] IS NULL"), 
		"!null"=>array("txt"=>["not null"], "temp"=>"[col] IS NOT NULL"), 
		"int"=>array("txt"=>["ibtn"], "temp"=>"[val] BETWEEN [col1] AND [col2]"), 
		"btn"=>array("txt"=>["between"], "temp"=>"[col] BETWEEN '[val1]' AND '[val2]'"), 
		"ends"=>array("txt"=>["v%", "end"], "temp"=>"[col] LIKE CONCAT('[val]', '%')"), 
		"begins"=>array("txt"=>["%v", "bgn"], "temp"=>"[col] LIKE CONCAT('%', '[val]')"), 
		"like"=>array("txt"=>["%%", "like", "lk"], "temp"=>"[col] LIKE CONCAT('%', '[val]', '%')"), 
		"contains"=>array("txt"=>["csv"], "temp"=>$contains)
	);
	
	$item	=[];
	foreach ($array as $bool=>$array) {
		$items	=$array["txt"];
		$temp	=$array["temp"];
		$item[$bool]	=$temp;
		foreach ($items as $txt) {
			$item[$txt]	=$temp;
		}
	}
	return $item;
}
