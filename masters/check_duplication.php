<?php
include_once "../../connection.php";
include_once "../../utilities.php";
header('Content-Type: application/json');
$arrResult['error'] = true;
try {
    if (!isset($_POST['table']) || empty($_POST['table']) || !isset($_POST['field']) || empty($_POST['field']) || !isset($_POST['value']) )
    {
        $arrResult['message'] = 'Body improper !';

    } else {
        foreach ($_POST as $key => $value) {
            $$key = mysqli_real_escape_string($con, $value);
        }
        $rs = fetchFromTable($table, [$field], "$field = '".trim($value)."'");
        if (!$rs) {
            $arrResult['message'] = 'Something Went Wrong !';
            if (TEST_MODE) {
                $arrResult['error_reason'] = mysqli_error($con);
            }
        } else {
            $arrResult['error'] = false;
            $arrResult['message'] = 'Result retrieved successfully';
            $data = [];
            if (mysqli_num_rows($rs)) {
                $data['isDuplicate'] = true;
                $data['message'] = 'ENTRY already exists with given data';

            } else {
                $data['isDuplicate'] = false;
                $data['message'] = 'Alright! new Value';
            }
            $arrResult['data'] = $data;
        }
    }
} catch (Exception $e) {
    echo $e;
}
echo json_encode($arrResult);
