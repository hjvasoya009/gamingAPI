<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;
	$form_name = $_POST['form_name'];

	if($form_name == 'add_tournament_type'){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
	    $data = new \stdClass();
	    $data->tournamenttype = $tournamenttype;
	    $data->updated_at 		= $datetime;
    	$data->updated_by 		= $_SESSION['id'];
	    $insertResult = insertToTable("tournament_type", $data);
	    
		if (!$insertResult['mysqli_error']) {
	        $arrResult['error'] = false;
	        $arrResult['message'] = 'Data Inserted Successfully';
	    } else {
	        $arrResult['error'] = true;
	        $arrResult['message'] = 'Data Insertion Failed';
	        if(isset($insertResult["error_code"] ) && $insertResult["error_code"] ==1062)
                $arrResult['message'] = "Duplicate Entry Found for this Record" ;
	    }
	    if (TEST_MODE) {
	        $arrResult['error_data_test'] = $insertResult;
	    }
	    echo json_encode($arrResult);
	}elseif($form_name == "edit_tournament_type"){	
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
		$data = new \stdClass();
		$data->tournamenttype = $tournamenttype;
		$data->updated_at 		= $datetime;
    	$data->updated_by 		= $_SESSION['id'];
	    $insertResult = editRecordFromTable("tournament_type", $data, $key_for_update);
	    if (!$insertResult['mysqli_error']) {
	        $arrResult['error'] = false;
	        $arrResult['message'] = 'Data Updated Successfully';
	    } else {
	        $arrResult['error'] = true;
	        $arrResult['message'] = 'Data Updation Failed';
	        if(isset($insertResult["error_code"] ) && $insertResult["error_code"] ==1062)
                $arrResult['message'] = "Duplicate Entry Found for this Record" ;
	    }
	    if (TEST_MODE) {
	        $arrResult['error_data_test'] = $insertResult;
	    }
	    echo json_encode($arrResult);
	}elseif($form_name == "del_tournament_type"){
		$tournament_id = mysqli_real_escape_string($con,$_POST['tournament_id']);
		$insertResult = removeRecordFromTable('tournament_type', $tournament_id);
		if (!$insertResult['mysqli_error']) {
	        $arrResult['error'] = false;
	        $arrResult['message'] = 'Data Deleted Successfully';
	    } else {
	        $arrResult['error'] = true;
	        $arrResult['message'] = 'Data Deletion Unsuccessful';
	    }
	    if (TEST_MODE) {
	        $arrResult['error_data_test'] = $insertResult;
	    }
	    echo json_encode($arrResult);
	}
?>