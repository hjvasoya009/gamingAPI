<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;
	$form_name = $_POST['form_name'];

	if($form_name == 'add_game_category'){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
	    $data = new \stdClass();
	    $data->category_name 	= $category_name;
	    $data->updated_at 		= $datetime;
    	$data->updated_by 		= $_SESSION['id'];
	    $insertResult = insertToTable("game_category", $data);
	    
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
	    
	} elseif ($form_name == "edit_game_category"){
			
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
		$data = new \stdClass();
		$data->category_name	= $category_name;
		$data->updated_at 		= $datetime;
    	$data->updated_by 		= $_SESSION['id'];
	    $insertResult = editRecordFromTable("game_category", $data, $key_for_update);
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
	}elseif($form_name == "del_game_category"){
		$category_id = mysqli_real_escape_string($con,$_POST['category_id']);
		$insertResult = removeRecordFromTable('game_category', $category_id);
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