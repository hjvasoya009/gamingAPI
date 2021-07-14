<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		
		$conditionString = 'id=1';
		$settingQry 	 = fetchFromTable("settings",null,$conditionString);
		$settingRow 	 = $settingQry->fetch_assoc();
		$deposit_bonus 	 = $settingRow['deposit_bonus'];
		$referral_amount = $settingRow['referral_amount'];
		
		if(!isset($_POST['user_id']) || empty($_POST['user_id'])){
			$response['message'] = 'Please enter user id.';
			echo json_encode($response);
			exit();
		}
		
	    if(!isset($_POST['bearer_token']) || empty($_POST['bearer_token'])){
			$response['message'] = 'Please enter bearer token.';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['transaction_id']) || empty($_POST['transaction_id'])){
			$response['message'] = 'Please enter transaction id.';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['payment_gateway']) || empty($_POST['payment_gateway'])){
			$response['message'] = 'Please enter payment gateway.';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['payment_datetime']) || empty($_POST['payment_datetime'])){
			$response['message'] = 'Please enter payment date and time.';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['amount']) || empty($_POST['amount'])){
			$response['message'] = 'Please enter payment amount.';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token", "wallet_balance", "referral_balance", "referred_by");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			$tokenRow = mysqli_fetch_assoc($tokenQry);
			$walletBalance 			= $tokenRow['wallet_balance'];
			$updatedWalletBalance 	= $walletBalance + $amount;
			
			$data = new \stdClass();
		    $data->user_id 			= $user_id;
		    $data->transaction_id 	= $transaction_id;
		    $data->payment_gateway 	= $payment_gateway;
		    $data->payment_datetime = $payment_datetime;
		    $data->amount 			= $amount;
		    $data->updated_at 		= $datetime;
		    
		    $insertResult = insertToTable("deposit_history", $data);
		    
			if (!$insertResult['mysqli_error']) {
				
				$deposit_id = $insertResult['mysqli_insert_id'];
				
				$data = new \stdClass();
				$data->deposit_id 			= $deposit_id;
			    $data->user_id 				= $user_id;
			    $data->transaction_purpose	= 'Deposit';
			    $data->transaction_method 	= $payment_gateway;
			    $data->transaction_datetime = $payment_datetime;
			    $data->credit 				= $amount;
			    $data->updated_at 			= $datetime;
			    
			    $insertResult = insertToTable("transaction_history", $data);
			    
				if (!$insertResult['mysqli_error']) {
					
					$conditionString = "user_id=$user_id";
					$userDepositQry = fetchFromTable("deposit_history",null,$conditionString);
					$depositRows = mysqli_num_rows($userDepositQry);
					
					if($depositRows == 1){
						$referralBalance 		= $tokenRow['referral_balance'];
						$depositBonus 			= (($amount*$deposit_bonus)/100);
						$updateReferralBalance 	= $referralBalance + $depositBonus;
						
						$data = new \stdClass();
					    $data->user_id 				= $user_id;
					    $data->transaction_purpose	= 'Deposit Bonus';
					    $data->transaction_method 	= 'Deposit Bonus';
					    $data->transaction_datetime = $payment_datetime;
					    $data->credit 				= $depositBonus;
					    $data->updated_at 			= $datetime;
					    
					    $insertResult = insertToTable("transaction_history", $data);
						if (!$insertResult['mysqli_error']){
							$data = new \stdClass();
					    	$data->wallet_balance 	= $updatedWalletBalance;
					    	$data->referral_balance = $updateReferralBalance;
			    			
						    $updateResult = editRecordFromTable("users", $data, $user_id);
							if (!$updateResult['mysqli_error']) {
						        $response['status'] = 1;
						        $response['message'] = 'Deposit successful!';
						        $response['data'] = $data;
						    } else {
						        $response['message'] = 'Deposit successful! Wallet Balance update failed!';
						    }
						}else{
							$response['message'] = 'Deposit successful! Deposit Bonus transaction update failed!';
						}
					}else{
						if (!$insertResult['mysqli_error']){
							$data = new \stdClass();
					    	$data->wallet_balance 	= $updatedWalletBalance;
			    			
						    $updateResult = editRecordFromTable("users", $data, $user_id);
							if (!$updateResult['mysqli_error']) {
						        $response['status'] = 1;
						        $response['message'] = 'Deposit successful!';
						        $response['data'] = $data;
						    } else {
						        $response['message'] = 'Deposit successful! Wallet Balance update failed!';
						    }
						}else{
							$response['message'] = 'Deposit successful! Deposit Bonus transaction update failed!';
						}
					}
			    } else {
			        $response['message'] = 'Deposit successful! Deposit transaction update unsuccessful!';
			    }
		    } else {
		        $response['message'] = 'Deposit unsuccessful!';
		    }
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>