<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;

	if(isset($_POST) && !empty($_POST)){	
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
	    $key_for_update = 1;
		$data = new \stdClass();
		$data->$column_name = stripcslashes($value);
	    $insertResult = editRecordFromTable("settings", $data, $key_for_update);
	    if (!$insertResult['mysqli_error']) {
	        $arrResult['error'] = false;
	        $arrResult['message'] = 'Record Updated Successfully';
	    } else {
	        $arrResult['message'] = 'Record Updation Failed';
	    }
	}else{
		$arrResult['message'] = 'Something went wrong!';
	}
	echo json_encode($arrResult);
?>