<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;
	$form_name = $_POST['form_name'];

	if($form_name == 'send_bonus'){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			} 
	    }
	    
	    $conditionString = 'id=1';
		$rs = fetchFromTable("settings",null,$conditionString);
		if(mysqli_num_rows($rs) == 1){
			$row = $rs->fetch_assoc();
			$bonus_value = $row['bonus_value'];
			
		    $conditionString = "id=".$user_id;
		    $columns = array("referral_balance", "username");
			$rs = fetchFromTable("users", $columns, $conditionString);	
			$row = mysqli_fetch_assoc($rs);
			$current_referral_balance = $row['referral_balance'];
			$username = $row['username'];
		    
		    $data = new \stdClass();
		    $data->user_id 				= $user_id;
		    $data->transaction_purpose	= 'Bonus';
		    $data->transaction_method 	= 'Bonus';
		    $data->transaction_datetime = $datetime;
		    $data->credit 				= $bonus_value;
		    $data->updated_at 			= $datetime;
		    
		    $insertResult = insertToTable("transaction_history", $data);
		    
			if (!$insertResult['mysqli_error']) {
				
				$data = new \stdClass();
				$data->referral_balance = $current_referral_balance + $bonus_value;
		    	
			    $updateResult = editRecordFromTable("users", $data, $user_id);
				if (!$updateResult['mysqli_error']) {
			        $arrResult['error'] = false;
			        $arrResult['message'] = 'Bonus given successful to '.$username.'!';
			        $arrResult['data'] = $data;
			    } else {
			        $arrResult['message'] = 'Transaction successful! Balance update failed for '.$username.'!';
			    }
		    } else {
		        $arrResult['message'] = 'Bonus release failed for '.$username.'!';
		    }
		}
	}
	
	echo json_encode($arrResult);
?>