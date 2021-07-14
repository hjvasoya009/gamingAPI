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
		
		if(!isset($_POST['coins_to_redeem']) || empty($_POST['coins_to_redeem'])){
			$response['message'] = 'Please enter GA Coins to Redeem';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token", "ga_coins", "referral_balance");
		$userQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
			
			$userRow 				= $userQry->fetch_assoc();
			$current_ga_coins		= $userRow['ga_coins'];
			$current_referral_balance	= $userRow['referral_balance'];
			
			if($current_ga_coins >= $coins_to_redeem){
				$conditionString 		= 'id=1';
				$settingQry 			= fetchFromTable("settings",null,$conditionString);
				$settingRow 			= $settingQry->fetch_assoc();
				$ga_coin_redeem_limit 	= $settingRow['ga_coin_redeem_limit'];
				$ga_coin_value 			= $settingRow['ga_coin_value'];
				
				if($coins_to_redeem >= $ga_coin_redeem_limit){
					$coins_left_to_redeem =  fmod($coins_to_redeem,$ga_coin_redeem_limit);
					$coins_to_be_redeemed = $coins_to_redeem - $coins_left_to_redeem;
					$converted_coin_value = ($coins_to_be_redeemed * $ga_coin_value)/$ga_coin_redeem_limit;
					
					$ga_coins_balance = $current_ga_coins - $coins_to_be_redeemed;
					
					$data = new \stdClass();
					$data->ga_coins 		= $ga_coins_balance;
					$data->referral_balance 	= $current_referral_balance + $converted_coin_value;
					$updateResult = editRecordFromTable("users", $data, $user_id);
					if (!$updateResult['mysqli_error']) {
						
						$data = new \stdClass();
					    $data->user_id 				= $user_id;
					    $data->transaction_purpose	= 'Redeem Coins';
					    $data->transaction_method 	= 'Redeem';
					    $data->transaction_datetime = $datetime;
					    $data->credit 				= $converted_coin_value;
					    $data->coins_redeemed 		= $coins_to_be_redeemed;
					    $data->ga_coin_redeem_limit = $ga_coin_redeem_limit;
					    $data->ga_coin_value 		= $ga_coin_value;
					    $data->updated_at 			= $datetime;
					    
					    $insertResult = insertToTable("transaction_history", $data);
						if (!$insertResult['mysqli_error']){
							$response['status'] = 1;
	        				$response['message'] = 'Coins redeemed successfully and credited to your referral balance!';
	        				$response['converted_money'] = $converted_coin_value;
	        				$response['redeemed_coins']  = $coins_to_be_redeemed;
	        				$response['new_referral_balance'] = $current_referral_balance + $converted_coin_value;
	        				$response['new_ga_coins_balance'] = $ga_coins_balance;
						}else{
	        				$response['message'] = 'Error while updating transaction!';
						}
				    } else {
				        $response['message'] = 'Coin redeem failed!';
				    }
				}else{
					$response['message'] = "You don't have enough GA Coins to Redeem!";
				}
			}else{
				$response['message'] = "You don't have enough GA Coins to Redeem!";
			}
			
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>