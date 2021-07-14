<?php
include_once "../../connection.php";
include_once "../../utilities.php";

$arrResult = array();
$arrResult['error'] = true;

foreach ($_POST as $key => $value) {
    $$key = mysqli_real_escape_string($con, $value);
}
if (isset($login_id, $login_password)) {
    $rs = fetchFromTable("admin",null, " `username` = '$login_id' AND `password` = '$login_password' AND isActive=1");

    if ($rs && $rs->num_rows == 1) {
        $row = $rs->fetch_assoc();
        $_SESSION["id"] = $row['id'];
        $_SESSION["username"] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $arrResult['error'] = false;
        $arrResult['message']='login succeeded, wait redirecting...';
    } else {
        $arrResult['message'] = ' Opps ! invalid username or password! '.mysqli_error($con);

    }
} else {
    $arrResult['message'] = 'can not login.Please try again latter';
}
echo json_encode($arrResult);
die;