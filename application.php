<?php

function socket_display_error($error_code, $error_message)
{
    $list = array("error_code" => $error_code, "error_message" => $error_message);
    echo(json_encode($list));
}

header('Content-Type: application/json; charset=UTF-8');

if (!in_array('application/json', explode(";", $_SERVER['CONTENT_TYPE']))) {
    socket_display_error("07", "JSON_ERROR");
    exit;
}

function isValidJSON($str)
{
    json_decode($str);
    return json_last_error() == JSON_ERROR_NONE;
}

$json_params = file_get_contents("php://input");
if (strlen($json_params) > 0 && isValidJSON($json_params)) {
    $data = json_decode($json_params, true);
} else {
    socket_display_error("07", "JSON_ERROR");
    exit;
}

if (!isset($data["cid"]) && !isset($data["u_cmd"])) {
    socket_display_error("09", "There is a parameter that was not passed.");
    exit;
}

require_once "Database.php";
$dbconn = mysqli_connect(
    $GLOBALS['db_host'],
    $GLOBALS['db_user'],
    $GLOBALS['db_password'],
    $GLOBALS['db_database']
);

$ip = $_SERVER['REMOTE_ADDR'];
$port = $_SERVER['SERVER_PORT'];
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $agent = $_SERVER['HTTP_USER_AGENT'];
} else {
    $agent = "NULL";
}

$cid = $data["cid"];
$u_cmd = $data["u_cmd"];

$stat = 0;
$c_cnt = 0;
$c_park_ty = 0;
$car_num = "";

$result = $dbconn->query("INSERT * FROM h_application");

$list = array(
  "stat" => $stat, "c_cnt" => $c_cnt, "c_park_ty" => $c_park_ty, "car_num" => $c_park_ty
);

die(json_encode($list));
