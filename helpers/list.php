<?php
namespace Teescripts\Helpers;
function load_lists($text) {
	$array	=explode("/", $text);
	$var_list	=arrayKey(0, $array);
	$var_type	=arrayKey(1, $array);
	$var_fxn	=arrayKey(2, $array);
	$var_name	="{$var_list}_{$var_type}";
	$results	=call_user_func_array($var_name, [$var_fxn]);
	return $results;
}

function list_quotes($text="") {
	$text	=str_replace('[', '"', $text);
	$text	=str_replace(']', '"', $text);
	return $text;
}

function list_search() {
	$search	=arrayKey("q", $_GET);
	$search	=addslashes($search);
	$search	=strip_tags($search);
	return $search;
}

function list_load($function="", $args=[]) {
	if (function_exists($function)) {
		if (!is_array($args)) $args=[$args];
		$result	=call_user_func_array($function, $args);
		return $result;
	}
}

function list_loader($module="", $args="") {
	$module	=$module.'List';
	$query	=list_load($module, $args);
	return $query;
}

function list_loadView($module="", $args="") {
	$module	=$module.'View';
	$query	=list_load($module, $args);
	$result	=db_view($query);
	if (!$result && $args) $result=$args;
	return $result;
}

function list_loadQuery($module="", $args="") {
	$query	=list_loader($module, $args);
	return $query;
}

function list_loadArray($module="", $args="") {
	$query	=list_loader($module, $args);
	$data	=$query;
	if (stristr($query, "select ")) $data=db_query($query);
	return $data;
}

function list_loadGrid($module="", $args="") {
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");

	$search	=list_search();
	#if (!$args&&$search) $args=$search;

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);

	$get_page	=arrayKey("page", $_GET, 1);
	$get_rows	=arrayKey("rows", $_GET, 20);

	$total	=page_count($query);
	$start	=($get_rows * $get_page)- $get_rows;
	if ($query) $query.=" LIMIT {$start}, {$get_rows}";
	$data	=db_query($query);

	$array	=["total"=>$total, "rows"=>$data];
	$text	=($array);
	return $text;
}

function list_loadJson($module="", $args="") {
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");
	
	$search	=list_search();

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);

	$get_page	=arrayKey("page", $_GET, 1);
	$get_rows	=arrayKey("rows", $_GET, 20);

	$total	=page_count($query);
	$start	=($get_rows * $get_page) - $get_rows;
	if ($query) $query.=" LIMIT {$start}, {$get_rows}";
	$data	=db_query($query);

	$text	=json_encode($data);
	return $text;
}

function list_loadSuggest($module="", $args="") {
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");
	
	$search	=list_search();

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);

	$query	=str_replace(" AS `id`", " AS `value`", $query);
	$query	=str_replace(" AS `name`", " AS `data`", $query);
	$query	=str_replace(" AS `title`", " AS `html`", $query);
	$query	=str_replace(":phrase", $search, $query);

	if ($query) $query.=" LIMIT 0, 20";
	$data	=db_query($query);#, $bind

	$array	=["suggestions"=>$data];
	$text	=json_encode($array);
	return $text;
}

function list_loadSelect($module="", $args="") {
	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: *");
	
	$search	=list_search();

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);

	$get_rows	=20;
	$get_page	=arrayKey("page", $_GET, 1);
	
	$total	=page_count($query);
	$start	=($get_rows*$get_page)-$get_rows;
	if ($query) $query.=" LIMIT {$start}, {$get_rows}";
	$data	=db_query($query);

	$array	=array(
		"results"=>$data, 
		"pagination"=>["more"=>true]
	);
	$text	=json_encode($array);
	$text	=str_replace('"name"', '"text"', $text);
	return $text;
}

function list_loadText($module="", $args="") {
	$text	="";
	$data	=list_loadArray($module, $args);
	$row	=arrayKey(0, $data);
	if ($row) {
		$cols	=array_keys($row);
		$key	=inArray("id", $cols, $cols[0]);
		$name	=inArray("name", $cols, $cols[1]);
		$data	=array_column($data, $name, $key);
		$text	=json_encode($data);
		$text	=list_flatten($text);
	}
	return $text;
}

function list_loadNestJson($module="", $args="") {
	header("Content-Type: application/json");
	header('Access-Control-Allow-Origin: *');

	$array	=[];
	$result	=list_nestJsonRow($module, $args, $array);
	$text	=json_encode($result);
	return $text;
}

function list_loadNestGrid($module="", $args="") {
	return list_loadNestJson($module, $args);
}

function list_loadNestSelect($module="", $args="") {
	header("Content-Type: application/json");
	header('Access-Control-Allow-Origin: *');

	$array	=[];
	$result	=list_nestJsonRow($module, $args, $array);
	$array	=array(
		"results"=>$result, 
		"pagination"=>array("more"=>true)
	);
	$text	=json_encode($array);
	$text	=str_replace('"name"', '"text"', $text);
	return $text;
}

function list_loadNest($module="", $args="") {
	$array	=[];
	$result	=list_nestRow($module, $args, $array);
	$text	=implode(";", $result);
	return $text;
}

function list_nestRow($module="", $args="", $tree=[]) {

	$search	=list_search();

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);
	$results=db_query($query);

	$tree	=list_nestData($module, $args, $results, $tree);
	return $tree;
}

function list_nestData($module="", $args="", $results="", $tree=[]) {
	if ($results) {
		foreach ($results as $key=>$row) {
			$value	=$row["id"];
			$text	=$row["name"];
			$args[1]=$value;
			
			$array	=array($value=>"{$value}=>{$text}");//array($value=>$text)
			$tree	=array_merge($tree, $array);
			$tree	=list_nestRow($module, $args, $tree);
		}
	}
	return $tree;
}

function list_nestJsonRow($module="", $args="", $tree=[]) {
	$search	=list_search();

	$query	=list_loadQuery($module, $args);
	$query	=str_replace(":phrase", $search, $query);
	$results=db_query($query);
	$tree	=list_nestJsonData($module, $args, $results, $tree);
	return $tree;
}

function list_nestJsonData($module="", $args="", $results="", $tree=[]) {
	if ($results) {
		foreach ($results as $key=>$row) {
			$value	=$row["id"];
			$args[1]=$value;
			
			$array	=[];
			$result	=list_nestJsonRow($module, $args, $array);
			$array	=$row;
			if ($result) {
				//unset($array["id"]);
				//$array["disabled"]	=true;
				$count	=count($result);
				$array["count"]	=$count;
				$array["children"]	=$result;
			}
			$tree[]	=$array;
		}
	}
	return $tree;
}

function list_treeView($module, $args, $tree=[]) {
	$results	=list_loadArray($module, $args);
	foreach ($results as $row) {
		$value	=$row["id"];
		$array	=$row;
		$result	=list_treeView($module, $value, $array);
		if ($result) $tree[]=$result;
	}
	return $tree;
}


function list_flatten($text) {
	$text	=str_replace('":null', '":"N/A"', $text);
	$text	=str_replace('":"', '=>', $text);
	$text	=str_replace('}{', ';', $text);
	$text	=str_replace('"]["', ';', $text);
	$text	=str_replace('[{"', '', $text);
	$text	=str_replace('"}]', '', $text);
	$text	=str_replace('"]', '', $text);
	$text	=str_replace('["', '', $text);
	$text	=str_replace('{"', '', $text);
	$text	=str_replace('"}', '', $text);
	$text	=str_replace('","', ';', $text);
	$text	=str_replace('\/', '/', $text);
	$text	=str_replace('":', '=>', $text);
	$text	=str_replace(':"', '=>', $text);
	$text	=str_replace(',"', ';', $text);
	$text	=str_replace('",', ';', $text);
	$text	=str_replace('{', '', $text);
	$text	=str_replace('}', '', $text);
	return $text; 
}



# ---- references
function lists_index($name="", $args="") {
	return lists_array($module, $args);
}

function lists_query($name="", $args="") {
  $text	="lists/query/".$name;
  return list_loadQuery($name, $args);
}

function lists_item($name="", $args="") {
  return list_loader($name, $args);
}

function lists_get($name="", $dataid="") {
  return list_loadView($name, $dataid);
}

function lists_view($name="", $dataid="") {
  return list_loadView($name, $dataid);
}

function lists_array($name="", $args="") {
  return list_loadArray($name, $args);
}

function lists_text($name="", $args="") {
  return list_loadText($name, $args);
}

function lists_nest($name="", $args="", $type="") {
  return list_loadNest($name, [$args, $type]);
}

function lists_json($name="", $args="") {
  echo list_loadJson($name, $args);
}

function lists_grid($name="", $args="") {
  echo list_loadGrid($name, $args);
}

function lists_select($name="", $args="") {
  echo list_loadSelect($name, $args);
}

function lists_suggest($name="", $args="") {
  echo list_loadSuggest($name, $args);
}

function lists_nestJson($name="", $args="", $type="") {
  echo list_loadNestJson($name, [$args, $type]);
}

function lists_nestGrid($name="", $args="", $type="") {
  echo list_loadNestGrid($name, [$args, $type]);
}

function lists_nestSelect($name="", $args="", $type="") {
  echo list_loadNestSelect($name, [$args, $type]);
}

# ---- static lists
function static_load($function="", $args=[]) {
	return list_load($function, $args);
}

function static_get($module="", $args="") {
	return static_view($module, $args);
}

function static_nestArray($module="", $args="") {
	return static_nest($module, $args);
}

function static_nest($module="", $args="") {
	$list	=static_list($module, $args);
	$array	=main_arrayConvert($list, "tree");//print_r($array);
	return $array;
}

function static_tree($module="", $args="") {
	$list	=static_query($module, $args);
	$array	=$list;
	if (!is_array($array)) $array=main_arrayTree($list);
	return $array;
}

function static_item($module="", $args="") {
	$query	=static_load($module, $args);
	return $query;
}

function static_view($module="", $args="") {
	$query	=static_list($module, $args);
	if (stristr($module, "status")) $query.=";20=>Flagged";
	$result	=main_map($query, $args);
	return $result;
}

function static_list($module="", $args="") {
	$module	=$module.'List';
	$query	=static_load($module, $args);
	return $query;
}

function static_query($module="", $args="") {
	$list	=static_list($module, $args);
	return $list;
}

function static_array($module="", $args="") {
	$list	=static_list($module, $args);
	$array	=$list;
	if (!is_array($array)) $array=main_arrayConvert($list, "tree");
	return $array;
}

function static_lists($module="", $args="") {
	$list	=static_list($module, $args);
	$array	=$list;
	if (!is_array($array)) $array=main_arrayConvert($list, "list");
	return $array;
}

function static_grid($module="", $args="") {
	header("Content-Type: application/json");
	$array	=static_array($module, $args);

	$total	=count($array);
	$data	=["total"=>$total, "rows"=>$array];
	$text	=json_encode($data);
	return $text;
}

function static_suggest($module="", $args="") {
	header("Content-Type: application/json");
	$array	=static_array($module, $args);
	$data	=["suggestions"=>$array];
	$text	=json_encode($data);
	return $text;
}

function static_json($module="", $args="") {
	header("Content-Type: application/json");
	$array	=static_array($module, $args);
	$text	=json_encode($array);
	return $text;
}

function static_select($module="", $args="") {
	header("Content-Type: application/json");
	$array	=static_array($module, $args);
	$data	=["results"=>$array];
	$text	=json_encode($data);
	$text	=str_replace('"name"', '"text"', $text);
	return $text;
}

function static_text($module="", $args="") {
	$text	=static_query($module, $args);
	if (is_array($text)) {
		$row	=arrayKey(0, $text);
		$cols	=array_keys($row);
		$key	=inArray("id", $cols, $cols[0]);
		$name	=inArray("name", $cols, $cols[1]);
		$data	=array_column($text, $name, $key);
		$text	=json_encode($data);
		$text	=list_flatten($text);
	}
	return $text;
}

function static_nestJson($module="", $args="") {
	$text	=static_nestGrid($module, $args);
	$text	=str_replace('"name"', '"text"', $text);
	return $text;
}

function static_nestGrid($module="", $args="") {
	header("Content-Type: application/json");
	$data	=static_tree($module, $args);
	$text	=json_encode($data);
	return $text;
}

function static_nestSelect($module="", $args="") {
	header("Content-Type: application/json");
	$array	=static_tree($module, $args);
	$data	=["results"=>$array];
	$text	=json_encode($data);
	$text	=str_replace('"name"', '"text"', $text);
	return $text;
}
