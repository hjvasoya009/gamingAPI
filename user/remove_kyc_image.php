<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	$arrResult['error'] = true;
	if(isset($_POST) && !empty($_POST)){
		$user_id = $_POST['user_id'];
		$conditionsString = "id=$user_id";
		$columns = array("kyc_image");
		$tokenQry = fetchFromTable("users",$columns,$conditionsString);
        if(mysqli_num_rows($tokenQry) == 1){
        	
        	$data = new \stdClass();
	    	$data->kyc_image = '';
		    $updateResult = editRecordFromTable("users", $data, $user_id);
			if (!$updateResult['mysqli_error']){
				$path = '../../images/kyc_images/';
	        	$files = glob($path.$user_id.".*");
				foreach ($files as $file) {
					unlink($file);
				}
				$arrResult['error'] = false;
			}
		}
	}
	echo json_encode($arrResult);
	exit();
?>