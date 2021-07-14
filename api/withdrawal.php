<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		
		if(!isset($_POST['user_id']) || empty($_POST['user_id'])){
			$response['message'] = 'Please enter user id';
			echo json_encode($response);
			exit();
		}
		
	    if(!isset($_POST['bearer_token']) || empty($_POST['bearer_token'])){
			$response['message'] = 'Please enter bearer token';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['withdraw_method']) || empty($_POST['withdraw_method'])){
			$response['message'] = 'Please enter withdraw method';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['amount']) || empty($_POST['amount'])){
			$response['message'] = 'Please enter amount';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token", "wallet_balance", "kyc_done");
		$userQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
			
			$userRow 				= $userQry->fetch_assoc();
			$current_wallet_balance	= $userRow['wallet_balance'];
			$kyc_done				= $userRow['kyc_done'];
			
			if($kyc_done == 1){
				if($current_wallet_balance >= $amount){
					$conditionString 		= 'id=1';
					$settingQry 			= fetchFromTable("settings",null,$conditionString);
					$settingRow 			= $settingQry->fetch_assoc();
					$withdraw_min 			= $settingRow['withdraw_min'];
					$withdraw_max 			= $settingRow['withdraw_max'];
					
					if($amount >= $withdraw_min && $amount <= $withdraw_max){
						
						$updated_wallet_balance = $current_wallet_balance - $amount;
						
						$data = new \stdClass();
						$data->wallet_balance 	= $updated_wallet_balance;
						
						$updateResult = editRecordFromTable("users", $data, $user_id);
						
						if (!$updateResult['mysqli_error']) {
							
							$data = new \stdClass();
						    $data->user_id 			 = $user_id;
						    $data->withdraw_method 	 = $withdraw_method;
						    $data->withdraw_datetime = $datetime;
						    $data->amount 			 = $amount;
						    $data->withdraw_status 	 = 2;
						    $data->updated_at 		 = $datetime;
						    
						    $insertResult = insertToTable("withdrawal_history", $data);
						    
							if (!$insertResult['mysqli_error']) {
								$response['status']  = 1;
						        $response['message'] = 'Your withdrawal request is pending ! Please wait for further processing!';
						        $response['data'] 	 = $data;
						        $response['new_wallet_balance'] = $updated_wallet_balance;
						    } else {
						        $response['message'] = 'Withdraw balance unsuccessful!';
						    }
								
					    } else {
					        $response['message'] = 'Withdraw balance unsuccessful!';
					    }
					}else{
						$response['message'] = "You can withdraw minimum $withdraw_min INR and maximum $withdraw_max INR.";
						$response['withdraw_min'] = $withdraw_min;
						$response['withdraw_max'] = $withdraw_max;
					}
				}else{
					$response['message'] = "You don't have enough balance in your wallet to withdraw!";
				}
			}else{
				$response['message'] = "Please update your KYC for withdrawal request.";
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>