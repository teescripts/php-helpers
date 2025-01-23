<?php
namespace Teescripts\Helpers;

function api_path() {
    return app_path("library/php-jwt/");
}

function api_query($sql_query, $data=[], $fetchMode="", $log=0, $persist="") {
    return db_query($sql_query, $data, $fetchMode, $log, $persist);
}

function api_insert($table, $data, $log=2) {
    return db_insert($table, $data, $log);
}

function api_update($table, $data, $where, $log=1) {
    return db_update($table, $data, $where, $log);
}

function api_delete($table, $where, $log=1) {
    return db_delete($table, $where, $log);
}
    
function api_incrementKey($table, $lastId, $prefix, $station="") {
    return db_incrementKey($table, $lastId, $prefix, $station);
}

function api_run($query, $log=0) {
    return db_run($query, $log);
}

function api_exec($query) {
    return db_exec($query);
}
#---------------- 


function api_self() {
    $data	=["source"=>"self", "type"=>"auth"];
    $where	="WHERE `api_type`=:type AND `api_source`=:source";		
    $query	="SELECT `api_key` AS `key` FROM `#1_api_tokens` {$where}";
    $result =db_view($query, $data);
    return $result;
}

function api_log($input) {
    $api_key	=arrayKey("api_key", $input);
    $api_time	=arrayKey("api_time", $input);
    $api_data	=arrayKey("api_data", $input);
    $data	=arrayKey("data", $input);

    $where	="WHERE `api_key`=:key";		
    $query	="SELECT `token_id` FROM `#1_api_tokens` {$where}";
    $tokid =db_view($query, ["key"=>$api_key]);

    $data	=json_encode($input);
    $date	=date("Y-m-d H:i:s");#, $api_time
    
    $owner	=main_var("authid");
    $array	=["id"=>$tokid, "owner"=>$owner, "token"=>$api_key, "date"=>$date, "data"=>$data];
    api_tokenUsage($array);
}

function api_tokenUsage($array) {
    $keys	=["tokenid"=>"id", "ownerid"=>"owner", "api_token"=>"token", "auth_date"=>"date", "api_data"=>"data"];
    $data	=main_arrayFill($array, $keys);
    db_insert("#1_api_transact", $data, "", "trans");
}


function api_auth($public, $key="") {
    $data	=['key'=>$public];
    $colms	="`api_secret` AS `secret`, `token_id` AS `id`";
    $join	="`#1_api_tokens` AS pt";
    $query	="SELECT {$colms} FROM {$join} WHERE `api_key`=:key";
    $result =db_result($query, $data);
    $result	=arrayKey($key, $result);
    return $result;
}

function api_array($url="", $fields="", $type="", $noverify=1) {
    return api_send($url, $fields, $type, $noverify);
}

function api_text($url="", $fields="", $type="", $noverify=1) {
    $result	=api_send($url, $fields, $type, $noverify);
    return json_encode($result);
}

function api_send($url="", $data_array="", $type="", $noverify=1) {
    $link   =url($url);
    $data_array	=api_dataEncode($data_array);
    $data_text	=curl_send($link, $data_array, "json", $noverify);
    return $data_text;
}

function api_dataEncode($data_array, $api_public="") {
    if (!$api_public) $api_public=api_self();
    $api_secret	=api_auth($api_public, "secret");
    
    if (!$data_array) $data_array=[];
    $data_text	=$data_array;
    if (is_array($data_array)) $data_text=json_encode($data_array);
    $data_hash	=hash("sha256", $data_text.$api_secret);

    $data_array	=["api_time"=>time(), "api_key"=>$api_public, "api_hash"=>$data_hash, "api_data"=>$data_array];
    #$data_array	=array_merge($data_array, $api_array);
    return $data_array;
}

function api_dataDecode($api_array) {
    $api_key	=arrayKey("api_key", $api_array);
    $api_hash	=arrayKey("api_hash", $api_array);
    $data_array	=arrayKey("api_data", $api_array, $api_array);
    
    unset($api_array["api_key"], $api_array["api_hash"]);

    $result	="";
    $valid	=false;
    if ($data_array) {
        $api_secret	=api_auth($api_key, "secret");
        $data_text	=json_encode($data_array);
        $data_hash	=hash("sha256", $data_text.$api_secret);
        logger("api", [$data_hash, $api_hash]);#$data_array, $data_text
            
        $valid	=($data_hash==$api_hash);
        if ($valid) $result=$data_array;
    }
    $result	=["valid"=>$valid, "result"=>$result];
    return $result;
}

function api_data() {
    $result		="";
    $valid		=false;
    $api_data	=file_get_contents("php://input");
    if ($api_data) {
        $api_array	=json_decode($api_data, true);
    }
    else {
        $api_array	=$_GET;
    }
    
    if ($api_array) {
        if (is_array($api_array)) {
            api_log($api_array);

            $decode	=api_dataDecode($api_array);
            $valid	=arrayKey("valid", $decode);
            $result	=arrayKey("result", $decode);
        }
    }
    
    //$result	=($api_array);
    $result	=api_forbidden($valid, $result);
    //$result	=["data"=>$result, "status"=>"status", "message"=>"message"];
    //$result	=json_encode($result);
    //echo $result;
    //exit;
    return $result;
}

function api_forbidden($valid, $text="") {
    $data	="";
    #header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $result	=[];
    if (isset($text)||$valid) {
        if ($valid) {
            $code	=200;
            $message="Access granted.";
            $data	=$text;
        }
        else {
            $code	=401;
            $message="Access denied.";
        }
    }
    else {
        $code	=204;
        $message="No credentials included.";
    }
    $result	=api_message($code, $message, $data);
    return $result;
}

function api_message($code, $message="", $data="") {
    $result	=[];
    $result["data"]		=$data;
    $result["status"]	=$code;
    $result["message"]	=$message;
    # encode
    if (!$data) {
        $result	=json_encode($result);
        echo $result;
        exit;
    }
    # set response code
    http_response_code($code);
    return $result;
}

# ------- JWT
function api_decodeToken($path="") {

    // required headers
    if ($path) header("Access-Control-Allow-Origin: {$path}");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $path_files	=api_path();
    # required to decode jwt
    require_once $path_files.'BeforeValidException.php';
    require_once $path_files.'ExpiredException.php';
    require_once $path_files.'SignatureInvalidException.php';
    require_once $path_files.'JWT.php';
    
    //use \Firebase\JWT\JWT;

    # get posted data
    $post	=file_get_contents("php://input");

    $code	=401;
    $message="Request empty";
    $result	=[];
    // if data is not empty
    if ($post) {
        // get jwt
        $array	=json_decode($post, true);
        $jwt	=arrayKey("jwt", $array);
        if ($jwt) {
            try {
                # decode jwt
                $decode	=JWT::decode($jwt, $this->key, ['HS256']);

                $code	=200;
                $message="Access granted.";
                $data	=$decode->data;
            }
            catch (Exception $e){
                # if decode fails, jwt is invalid
                $code	=401;
                $message=$e->getMessage();
            }
        }
        else {
            $message	="Access denied.";
        }
    }
    $result	=api_message($code, $message, $data);
    return $result;
}

function api_encodeToken($data="") {
    // variables used for jwt
    $var_key	=uniqid(); # unique secret key
    $var_iss	=main_url(); # issuer
    $var_aud	=main_url(); # audience/recipients
    $var_iat	=time(); # issued at
    $var_nbf	=($var_iat + 476); # not before
    $var_exp	=$var_nbf; # expiration time

    $array  =[];

    $path_files	=api_path();
    # required to decode jwt
    require_once $path_files.'BeforeValidException.php';
    require_once $path_files.'ExpiredException.php';
    require_once $path_files.'SignatureInvalidException.php';
    require_once $path_files.'JWT.php';
    //use \Firebase\JWT\JWT;

    $result	=[];
    if ($data) {
        # generate jwt
        $token	=["iss"=>$var_iss, "aud"=>$var_aud, "iat"=>$var_iat, "nbf"=>$var_nbf, "data"=>$data];
        $result	=JWT::encode($token, $var_key);
    }
    return $result;
}
