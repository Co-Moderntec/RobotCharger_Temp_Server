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

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
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

$result = $dbconn->query("INSERT INTO h_application (u_cmd) VALUES ('$u_cmd')");
if (!$result) die(socket_display_error("06", "Internal error X4"));
$result = $dbconn->query("SELECT * FROM h_kiosk ORDER BY `No` DESC LIMIT 1");
if (!$result) die(socket_display_error("06", "Internal error X5"));
$result_object = $result->fetch_object();

$list = array(
  "cid" => $cid, "s_stat" => $result_object->c_stat, "c_cnt" => $result_object->c_cnt, "c_park_ty" => $result_object->c_park_ty, "car_num" => $result_object->car_num,
  "c_voltage" => $result_object->c_voltage, "c_ampare" => $result_object->c_ampare, "c_kwh" => $result_object->c_kwh, "c_soc" => $result_object->c_soc,
  "c_stt" => $result_object->c_stt, "c_ret" => $result_object->c_ret
);

die(json_encode($list));
