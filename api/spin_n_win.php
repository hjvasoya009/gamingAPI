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
		
		if(!isset($_POST['coins']) || empty($_POST['coins'])){
			$response['message'] = 'Please enter coins';
			echo json_encode($response);
			exit();
		}elseif(isset($_POST['coins']) &&  $_POST['coins'] > 36){
			$response['message'] = 'Something went wrong! Please contact support for more information!';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token", "ga_coins");
		$userQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
						
			$conditionString = "user_id=$user_id AND transaction_purpose='Spin and Win' AND transaction_method='Coins' AND DATE(transaction_datetime) = '".date('Y-m-d')."'";
			$transactionResult = fetchFromTable("transaction_history",null,$conditionString);
			if(mysqli_num_rows($transactionResult) > 0){
				$response['message'] = 'You have already used your spin for today!';
			}else{
				$userRow 				= $userQry->fetch_assoc();
				$current_ga_coins		= $userRow['ga_coins'];
				$ga_coins_balance 		= $current_ga_coins + $coins;
				
				$data = new \stdClass();
			    $data->user_id 				= $user_id;
			    $data->transaction_purpose	= 'Spin and Win';
			    $data->transaction_method 	= 'Coins';
			    $data->transaction_datetime = $datetime;
			    $data->credit 				= $coins;
			    $data->updated_at 			= $datetime;
			    
			    $insertResult = insertToTable("transaction_history", $data);
			    
				if (!$insertResult['mysqli_error']){
					
					$data = new \stdClass();
					$data->ga_coins = $ga_coins_balance;
					$updateResult = editRecordFromTable("users", $data, $user_id);
					
					if (!$updateResult['mysqli_error']) {
						$response['status'] = 1;
	    				$response['new_ga_coins_balance'] = $ga_coins_balance;
	    				$response['message'] = 'Coins added successfully!';
					}else{
	    				$response['message'] = 'Error while updating coins!';
					}
			    } else {
			        $response['message'] = 'Coin update failed!';
			    }
			}
			
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>