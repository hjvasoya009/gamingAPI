<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;
	
	if(isset($_POST) && !empty($_POST)){
		
		$conditionString = 'id=1';
		$settingQry 	 = fetchFromTable("settings",null,$conditionString);
		$settingRow 	 = $settingQry->fetch_assoc();
		$referral_amount = $settingRow['referral_amount'];
		
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
		
		if(!isset($_POST['mobile_number']) || empty($_POST['mobile_number'])){
			$response['message'] = 'Please enter mobile number';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['country_code']) || empty($_POST['country_code'])){
			$response['message'] = 'Please enter country code';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['otp']) || empty($_POST['otp'])){
			$response['message'] = 'Please enter mobile number';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}else{
				$$key = $val;
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND mobile_number='$mobile_number' AND user_status=1";
		$columns = array("bearer_token", "fcm_token", "username", "mobile_number", "country_code", "otp_verify", "referred_by");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			
			$userRow = mysqli_fetch_assoc($tokenQry);
			$referredByUserID = $userRow['referred_by'];
			
			$verifyOTPResponse = verifyOTP($country_code.$mobile_number, $otp);
			$verifyOTPResponse = json_decode($verifyOTPResponse, true);
			
			if($verifyOTPResponse['type'] == 'success' && $verifyOTPResponse['message'] == 'otp_verified'){
				
				$data = new \stdClass();
				$data->otp_verify = 1;
				$updateResult = editRecordFromTable("users", $data, $user_id);
				
				if (!$updateResult['mysqli_error']) {
					
					if($referredByUserID != ''){
						
						$conditionString = "id=$referredByUserID AND user_status=1";
						$referredByUserQry = fetchFromTable("users", null, $conditionString);
						
						if (mysqli_num_rows($referredByUserQry) == 1){
							$referredByUserRow = mysqli_fetch_assoc($referredByUserQry);
							$referredByUserReferralBalance = $referredByUserRow['referral_balance'];
							
							$data = new \stdClass();
							$data->referral_user_id 	= $user_id;
						    $data->user_id 				= $referredByUserID;
						    $data->transaction_purpose	= 'Referral Bonus';
						    $data->transaction_method 	= 'Referral';
						    $data->transaction_datetime = $datetime;
						    $data->credit 				= $referral_amount;
						    $data->updated_at 			= $datetime;
						    
						    $insertResult = insertToTable("transaction_history", $data);
						    
							if (!$insertResult['mysqli_error']){
								$data = new \stdClass();
								$data->referral_balance = $referredByUserReferralBalance+$referral_amount;
								$updateResult = editRecordFromTable("users", $data, $referredByUserID);
								
								if (!$updateResult['mysqli_error']) {
									$response['status'] = 1;
									$response['message'] = 'Your OTP is verified successfully!';
								}else{
									$response['status'] = 1;
									$response['message'] = 'Your OTP is verified successfully! Error while updating Referral Amount to referred by user!';
								}
							}else{
								$response['status'] = 1;
								$response['message'] = 'Your OTP is verified successfully! Error while updating transaction for referred by user!';
							}
						}else{
							$response['status'] = 1;
							$response['message'] = 'Your OTP is verified successfully!';
						}
					}else{
						$response['status'] = 1;
						$response['message'] = 'Your OTP is verified successfully!';
					}
				}else{
					$response['message'] = 'Your OTP is verified but update unsuccessful for user.';
				}
				
			}elseif($verifyOTPResponse['message'] == 'already_verified'){
				$response['status'] = 1;
				$response['message'] = 'Your mobile number is aleady verified.';
			}elseif($verifyOTPResponse['message'] == 'otp_not_verified'){
				$response['message'] = 'OTP verification failed! Please try again!';
			}else{
				$response['message'] = 'OTP verification failed! Please try again!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>