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
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}else{
				$$key = $val;
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token", "fcm_token", "username", "mobile_number", "otp_verify");
		$userQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
			$userRow = mysqli_fetch_assoc($userQry);
			if($userRow['otp_verify'] == 0){
				
				$conditionString = "mobile_number='$mobile_number'";
				$columns = array("id", "username", "mobile_number", "country_code");
				$mobileQry = fetchFromTable("users",$columns,$conditionString);
				if(mysqli_num_rows($mobileQry) == 0){
					$data = new \stdClass();
				    $data->mobile_number = $mobile_number;
				    $data->country_code	 = $country_code;
				    
				    $insertResult = editRecordFromTable("users", $data, $user_id);
				    
					if (!$insertResult['mysqli_error']) {
				        $otp = rand(1000,9999);
						$message = 'Your Gaming Akhada OTP is: '.$otp;
						
						$sendOTPResponse = sendOTP($country_code.$mobile_number, $message, $otp);
						$sendOTPResponse = json_decode($sendOTPResponse, true);
						if($sendOTPResponse['type'] == 'success'){
							$response['status'] = 1;
							$response['message'] = 'OTP has been sent to your registered mobile number.';
						}else{
							$response['message'] = 'OTP has been failed! Please try again!';
						}
				    } else {
				        $response['message'] = 'Error while updating your mobile number!';
				    }
				}elseif(mysqli_num_rows($mobileQry) == 1){
					
					$mobileRow = mysqli_fetch_assoc($mobileQry);
					$mobileUserID = $mobileRow['id'];
					if($mobileUserID == $user_id){
						$otp = rand(1000,9999);
						$message = 'Your Gaming Akhada OTP is: '.$otp;
						
						$sendOTPResponse = sendOTP($country_code.$mobile_number, $message, $otp);
						$sendOTPResponse = json_decode($sendOTPResponse, true);
						if($sendOTPResponse['type'] == 'success'){
							$response['status'] = 1;
							$response['message'] = 'OTP has been sent to your registered mobile number.';
						}else{
							$response['message'] = 'OTP has been failed! Please try again!';
						}
					}else{
						$response['message'] = 'This mobile number already registred with other user.';
					}						
				}else{
					$response['message'] = 'This mobile number already registred with other user.';
				}
			}else{
				$response['status'] = 1;
				$response['message'] = 'This account has already been verified.';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>