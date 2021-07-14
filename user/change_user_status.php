<?php
include_once "../../connection.php";
include_once "../../utilities.php";
header('Content-Type: application/json');
$arrResult['error'] = true;
foreach ($_POST as $key => $value) {
    $$key = mysqli_real_escape_string($con, $value);
}
try {
    if (!isset($table, $keyToUpdate, $user_status) || empty($table) || empty($keyToUpdate)) {
        $arrResult['message'] = 'Body improper !';
    } else {
    	
        $data = new \stdClass();
        $data->user_status 		 = $user_status;
        $data->suspension_reason = '';
        $data->updated_at 		 = $datetime;
        
        $insertResult = editRecordFromTable($table, $data, $keyToUpdate);
        if (!$insertResult['mysqli_error']) {
            $arrResult['error'] = false;
            $arrResult['message'] =($user_status == "1")? " Successfully Reactivated": " Successfully Deactivated ";
        }else{
            $arrResult['message'] = "Failed to Proceed, Something Went Wrong";

        }
        if (TEST_MODE) {
            $arrResult["test_data"] = $insertResult;
        }

    }
} catch (Exception $e) {
    $arrResult['message'] = TEST_MODE ? $e: " Something Went Wrong";
}
echo json_encode($arrResult);