<?php
namespace Teescripts\Helpers;
use Illuminate\Support\Facades\DB;

function db_view($query, $data=[], $join=" ") {
		$result	=db_result($query, $data, PDO::FETCH_NUM, 2);

		$array	=[];
		if (($result)) {
			foreach ($result as $key=>$row) {
				$array[]	=$row;
			}
		}
		$text	=implode($join, $array);
		return $text;
}

function db_result($query, $data=[], $fetchMode="", $log="", $persist="") {
	if (strlen($query)>10) {
		if (!stristr($query, " limit ")) $query.=" LIMIT 0, 1";
		$results=db_query($query, $data, $fetchMode, $log, $persist);
		$result	=arrayKey(0, $results, []);
		return $result;
	}
}

function db_query($query, $data=[], $fetchMode="", $log=0, $persist="") {
	if (strlen($query)>10) {
		$sql_query	=main_prefix($query);
		$text_log	=$sql_query;
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$convert	=db_convert($value, 0);
				$log_value	=$convert["log"];

				$text_log	=str_replace("':{$key}'", $log_value, $text_log);
				$text_log	=str_replace(":{$key}", $log_value, $text_log);
			}
		}

		if ($data) {
			$results	=DB::select($sql_query, $data);
		}
		else {
			$results	=DB::select($sql_query);
		}
		
		if ($results) {
			if (!$fetchMode) $fetchMode=3;
			$results	=array_map(function($item){
				return (array) $item;
			}, $results);
		}

		db_systemLog($text_log, $log);
		//if (strstr($text_log, " AS `item_photo`")) logit('db', [$results, $sql_query]);
		return $results;
	}
}

function db_pdoConnect() {
	$db_var	=DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
	try {
		$now	=new DateTime();	
		$offset	=$now->format("P");
		$dbcon	=new PDO($db_var, DB_USER, DB_PASS, [PDO::ATTR_PERSISTENT =>false]);
		$dbcon->exec("SET time_zone='{$offset}';");
		$dbcon->exec("SET sql_mode='';");
		$dbcon->exec("SET names utf8");
	}
	catch (PDOException $error) {
		$error    =$error->getMessage();
		$text    =str_replace("SQLSTATE[HY000] [1049] ", "", $error); 
		$text    .=". Load error, please refresh after a while!";       
		die($text);
	}
	return $dbcon;
}

function db_convert($value, $null=1) {
	if (is_array($value)) {
		$value	=implode(",", $value);
	}
	$value	=htmlentities($value, ENT_HTML5, "UTF-8", false);#ENT_HTML401
	$value	=html_entity_decode($value, ENT_HTML5, "UTF-8");
	
	$text_lower		=strtolower($value);
	$text_length	=strlen($value);
	$text_slash		="'".addslashes($value)."'";

	$log_value	=str_replace('\"', '"', $text_slash);
	if ($text_lower=="now()") $log_value="NOW()";
	if (stristr($text_lower, "`")) $log_value=$value;

	if ($text_length==0 && $null) {
		$value		=NULL;
		$log_value	="NULL";
	}
	$array	=["value"=>$value, "log"=>$log_value, "slash"=>$text_slash];
	return $array;
}

function db_saveData($table, $data, $where="", $log=0) {
	if (is_array($data)) {	
		
		$array_key	=[];
		$array_log	=[];
		foreach ($data as $key => $value) {
			$convert	=db_convert($value);
			$log_value	=$convert["log"];

			$array_key[]	="`{$key}`=:{$key}";
			$array_log[]	="`{$key}`={$log_value}";
		}

		$ntable	=main_prefix($table);
		if (!strstr($table, "`")) $ntable="`{$table}`";
		$template	="INSERT INTO {$ntable} SET {field}";
		if ($where) $template="UPDATE {$ntable} SET {field} WHERE {$where}";

		$text_keys	=implode(', ', $array_key);
		$sql_query	=str_replace('{field}', $text_keys, $template);
		
		$text_log	=implode(', ', $array_log);
		$sql_log	=str_replace('{field}', $text_log, $template);
		
		$result	=DB::statement($sql_query, $data);

		$valid	=in_array($table, ["_cont"]);
		//if ($valid) logit([$data, $sql_log]);

		db_systemLog($sql_log, $log);
	}
}

function db_error($handle, $query="") {
	$text	="";
	if ($handle) {
		$array	=$handle->errorInfo();
		$code	=arrayKey(0, $array);
		$type	=arrayKey(1, $array);
		$error	=arrayKey(2, $array);
		
		if ($error) {
			$icon	='<i class="fad fa-grin-beam-sweat fa-1x"></i> ';#meh, frown, grimace, grin-beam-sweat
			$text	=$error.'<hr>'.$query;
			$text	='<div class="alert alert-danger m5">'.$icon.$text.'</div>';
			if (!main_isLocal()) $text='Whew! We failed to fetch this data!';

			logger("db", [$query]);
			trigger_error("{$error} \n\n{$query}", E_USER_ERROR);
		}
	}
	return $text;
}

function db_insert($table, $data, $log=2, $prefix="") {
	if (is_array($data)) {
		$connect	=db_saveData($table, $data, "", $log);

		$lastId		=$connect->lastInsertId();
		if ($prefix && $lastId) $lastId=db_incrementKey($table, $lastId, $prefix);
		
		$connect	=NULL;
		return $lastId;
	}
}

function db_update($table, $data, $where, $log=1) {
	if (is_array($data)) {
		db_recreate($table, $where, $log);
		$connect	=db_saveData($table, $data, $where, $log);
		$connect	=NULL;
	}
}

function db_delete($table, $where, $log=1) {
	if (!strstr($table, "`")) $table="`{$table}`";
	$query	="DELETE FROM {$table} WHERE {$where}";
	
	db_recreate($table, $where, $log);
	$flag	=db_flagField($table, $where, $log);

	$query	="SET FOREIGN_KEY_CHECKS=0;{$query};SET FOREIGN_KEY_CHECKS=1;";
	if (!$flag) db_run($query, $log);
}

function db_run($query, $log=0) {
	return db_exec($query, $log);
}

function db_exec($query, $log=0) {
	$connect	=db_pdoConnect();
	$result		=$connect->exec($query);
	
	db_systemLog($query, $log);
	$connect	=NULL;
	return $result;
}
	
function db_incrementKey($table, $lastId, $prefix, $station="") {
	if ($lastId && $table && $prefix) {
		if (!$station) $station=main_var("station", 1);
		$pkey	="{$prefix}_id";
		$newId	=$station.$lastId;
		$data	=[$pkey=>$newId, "stationid"=>$station];
		$where	="`{$prefix}_key`='{$lastId}' AND (IFNULL(`{$pkey}`, 0)<=0)";
		db_update($table, $data, $where, 0);
		$lastId	=$newId;
	}
	return $lastId;
}

function db_systemLog($sql_query, $log=0) {
	if ($log==1) {
		$text_user	=main_var("user");
		$text_link	=$_SERVER['REQUEST_URI'];
		$text_ip	=$_SERVER['REMOTE_ADDR'];
		$nsql_query	=$sql_query;

		$pk_field	="log_id";
		$log_table	=TEE."logs";
		$log_where	="`log_query`=:sql AND `username`=:user AND `log_url`=:url AND `log_ip`=:ip";
		$bind_array	=array("sql"=>$nsql_query, "user"=>$text_user, "url"=>$text_link, "ip"=>$text_ip);
		$log_query	="SELECT `{$pk_field}` AS `id` FROM `{$log_table}` WHERE {$log_where} LIMIT 0, 1";
		$log_result	=db_result($log_query, $bind_array, "", 2);
		$dataid	=arrayKey("id", $log_result);
		if ($dataid) {
			$data 	=array(				
				"username"=>$text_user,
				"log_url"=>$text_link,
				"log_ip"=>$text_ip,
				'log_time'=>date('Y-m-d H:i:s')
			);
			db_update($log_table, $data, "`{$pk_field}`='{$dataid}'", 2);
		}
		else {
			$nsql_query=str_replace("'", "\'", $nsql_query);
			$data =array(			
				"username"=>$text_user,
				"log_url"=>$text_link,
				"log_query"=>"'{$nsql_query}'",
				"log_ip"=>$text_ip,
				"log_time"=>"NOW()"
			);
			db_insert($log_table, $data, 2);
		}
		#if (stristr($sql_query, "logs")) echo $sql_query;#file_put_contents("log.sql", $sql_query);
	}
	
}

function db_recreate($table, $where, $log=0) {
	if ($log==1 && strlen($table)<=20) {
		$result		=db_query("DESCRIBE {$table}", "", "", 2);
		$columns	=array_column($result, "Field", "Field");
		#------- record data query
		$query	="SELECT * FROM {$table} WHERE {$where}";
		$result	=db_query($query);
		$sql	=[];
		foreach ($result as $key=>$row) {
			$array_line	=[];
			foreach ($columns as $column) {
				$column_value	=($row[$column]);
				$array_line[]	="`{$column}`='{$column_value}'";			
			}
			$line_text	=implode(", ", $array_line);
			$query	="REPLACE INTO {$table} SET ".$line_text;
			$sql[]	=$query;
		}
		$query	=implode(";\n", $sql);;
		db_systemLog($query, 1);
		return $query;
	}
	
}

function db_flagField($table, $where, $log=0) {
	$status	=[];
	if (strlen($table)<=20) {
		$query	="DESCRIBE {$table}";
		$result	=db_query($query, "", "", 2);
		foreach ($result as $row) {
			$column	=$row["Field"];
			if (stristr($column, "status")) $status[]=$column;
		}
		#------- update status field to flagged
		if ($status) {
			$column	=$status[0];
			$query	="UPDATE {$table} SET `{$column}`='20' WHERE {$where}";
			db_exec($query, $log);
			return $column;
		}	
	}
}


function db_errorHandler($type, $message, $file, $line, $context) {
	$contents	="";
	$filename	=storage_path("logs/db.json");
	if (is_file($filename)) $contents=file_get_contents($filename);
	if (!$contents) $contents='[]';
	$array_text	=json_decode($contents, true);
	
	$array_types	=array(1064=>"Syntax error", 1175=>"Safe Update", 1215=>"Cannot add foreign key constraint", 1045=>"Access denied", 1236=>"impossible position in Replication", 2002=>"Cannot connect, ", 1067=>"Bad Value for number, date, default", 1366=>"Column type vs value type is not aligned.", 1292=>"Check the allowed values for the VARIABLE you are trying to SET", 1411=>"Incorrectly formatted date", 126=>"Index file is crashed", 127=>"Record-file is crashed", 134=>"Record was already deleted (or record file crashed)", 144=>"Table is crashed and last repair failed", 145=>"Table was marked as crashed and should be repaired", 139=>"Number and size of the fields exceeds some limit.", 1366=>"character set handling was not consistent between client and server", 24=>"Cant open file (Too many open files)", 1062=>"Duplicate Entry");

	$date	=date("Y-m-d");
	$ndate	="date_{$date}";
	$nfile	="file_{$file}";
	$nline	="line_{$line}";
	$ntype	=arrayKey($type, $array_types, "E_UNKNOWN");

	$array_date	=arrayKey($date, $array_text);
	$array_type	=arrayKey($type, $array_date);
	$array_file	=arrayKey($file, $array_type);
	$array_line	=arrayKey($line, $array_file);
	$count_line	=arrayKey("count", $array_line, 0);
	$date_last	=date("D d M H:i:s");#l dS F H:i:s
	$array_line	=array("message"=>$message, "context"=>$context, "last"=>$date_last);
	$array_text[$ntype][$nfile][$nline]	=$array_line;# [$date]

	$contents	=json_encode($array_text, JSON_PRETTY_PRINT);
	$contents	=str_replace('\/', '/', $contents);
	$contents	=str_replace('","', '", "', $contents);
	file_put_contents($filename, $contents);
}

function db_sim($table, $array) {
	$keys	=array_keys($array);
	$values	=array_values($array);
	$colms	=implode("`, `", $keys);
	$data	=implode("', '", $values);
	$data	="insert into `{$table}` (`{$colms}`) values ('{$data}');";
	return $data;
}
