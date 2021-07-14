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
		
		if(!isset($_POST['email']) || empty($_POST['email'])){
			$response['message'] = 'Please enter email';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
	    
	    $conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
	    $columns = array("bearer_token", "email");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			
			$tokenRow = mysqli_fetch_assoc($tokenQry);
			$currentEmail = $tokenRow['email'];
			
			if($email != $currentEmail){
				$conditionString = "email='".$email."'";
				$rs = fetchFromTable("users", null, $conditionString);		
				if (mysqli_num_rows($rs) > 0) {
					$response['message'] = 'Email already exist! Please try another email!';
					echo json_encode($response);
					exit();
				}
			}
			
			$data = new \stdClass();
		    $data->country 			= $country;
		    $data->salutation 		= $salutation;
		    $data->first_name 		= $first_name;
		    $data->last_name 		= $last_name;
		    $data->dob 				= $dob;
		    $data->email 			= $email;
		    $data->paytm_number 	= $paytm_number;
		    $data->payumoney_number = $payumoney_number;
		    $data->bank_name 		= $bank_name;
		    $data->bank_ac_no 		= $bank_ac_no;
		    $data->bank_ac_name 	= $bank_ac_name;
		    $data->bank_branch 		= $bank_branch;
		    $data->bank_ifsc 		= $bank_ifsc;
		    $data->kyc_proof 		= $kyc_proof;
		    $data->kyc_image 		= $kyc_image;
		    if($kyc_image != ''){
				$data->kyc_done 	= 1;
			}
		    $data->kyc_address 		= $kyc_address;
		    $data->user_image 		= $user_image;
		    $data->updated_at 		= $datetime;
		    
		    $insertResult = editRecordFromTable("users", $data, $user_id);
		    
			if (!$insertResult['mysqli_error']) {
				$data->kyc_image = MAIN_URL.'/images/kyc_images/'.$kyc_image;
		        $data->user_image = MAIN_URL.'/images/user_images/'.$user_image;
		        $response['status'] = 1;
		        $response['message'] = 'Your data updated successfully';
		        $response['data'] = $data;
		    } else {
		        $response['message'] = 'Your data updation failed!';
		    }
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>