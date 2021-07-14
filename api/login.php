<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		$username = '';
		$condString = '';
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		/*if(isset($_POST['mobile_number']) && !empty($_POST['mobile_number'])){
			$condString = "mobile_number='".$mobile_number."'";
		}elseif(isset($_POST['username']) && !empty($_POST['username'])){
			$condString = "username='".$username."'";
		}else{
			$response['message'] = 'Please enter username or mobile number';
			echo json_encode($response);
			exit();
		}*/
		
		if(isset($_POST['username']) && !empty($_POST['username'])){
			$condString = "(username='".$username."' OR mobile_number='".$username."')";
		}else{
			$response['message'] = 'Please enter username or mobile number';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['password']) || empty($_POST['password'])){
			$response['message'] = 'Please enter password';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['fcm_token']) || empty($_POST['fcm_token'])){
			$response['message'] = 'Please enter FCM Token';
			echo json_encode($response);
			exit();
		}
		
		if($condString != ''){
			$conditionString = "$condString AND password='".$password."'";
			$rs = fetchFromTable("users", null, $conditionString);
			if(mysqli_num_rows($rs) == 1){
				$row = mysqli_fetch_assoc($rs);
			    if($row['user_status'] == 1){
			    	$user_id = $row['id'];
			    	$bearer_token = generateToken(75);
			    	$data = new \stdClass();
			    	$data->bearer_token = $bearer_token;
			    	$data->fcm_token 	= $fcm_token;
	    			
				    $updateResult = editRecordFromTable("users", $data, $user_id);
					if (!$updateResult['mysqli_error']) {
				    	$data = new \stdClass();
				    	$row['user_id'] = $user_id;
				    	$row['bearer_token'] = $bearer_token;
				    	
						if($row['user_image'] != ''){
							$row['user_image'] = MAIN_URL.'/images/user_images/'.$row['user_image'];
						}elseif($row['social_image'] != ''){
							$row['user_image'] = $row['social_image'];
						}else{
							$row['user_image'] = '';
						}
						
						if($row['kyc_image'] != ''){
							$row['kyc_image'] = MAIN_URL.'/images/kyc_images/'.$row['kyc_image'];
						}else{
							$row['kyc_image'] = '';
						}
						
				    	$data = $row;
				        $response['status'] = 1;
				        $response['message'] = 'Login successful!';
				        $response['data'] = $data;
				    } else {
				        $response['message'] = 'Token updation failed!';
				        exit();
				    }
				    
				}elseif($row['user_status'] == 2){
					$response['message'] = 'Unable to login! Your account has been suspended.';
				}else{
					$response['message'] = 'Unable to login! Your account is inactive.';
				}
			}else{
				$response['message'] = 'Unable to login! Wrong username or mobile number or Password!';
			}
		}else{
			$response['message'] = 'Unable to login! Something went wrong! Please try again later.';
		}
	}
	echo json_encode($response);
	exit();
?>