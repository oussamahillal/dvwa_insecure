<?php
define( 'DVWA_WEB_PAGE_TO_ROOT', '../../' );
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

dvwaDatabaseConnect();

/*
On impossible only the admin is allowed to retrieve the data.
*/

if (dvwaCurrentUserId() != $data->id && dvwaCurrentUser() != "admin") {
    echo json_encode(["result" => "fail", "error" => "Unauthorized modification attempt."]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] != "POST") {
    $result = array (
                        "result" => "fail",
                        "error" => "Only POST requests are accepted"
                    );
    echo json_encode($result);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    if (is_null ($data)) {
        $result = array (
                            "result" => "fail",
                            "error" => 'Invalid format, expecting "{id: {user ID}, first_name: "{first name}", surname: "{surname}"}'

                        );
        echo json_encode($result);
        exit;
    }
} catch (Exception $e) {
    $result = array (
                        "result" => "fail",
                        "error" => 'Invalid format, expecting \"{id: {user ID}, first_name: "{first name}", surname: "{surname}\"}'

                    );
    echo json_encode($result);
    exit;
}

$stmt = $mysqli->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE user_id = ?");
$stmt->bind_param("ssi", $data->first_name, $data->surname, $data->id);
$stmt->execute();

$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

print json_encode (array ("result" => "ok"));
exit;
?>