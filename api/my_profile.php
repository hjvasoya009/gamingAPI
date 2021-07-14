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
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionsString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$userQry = fetchFromTable("users",null,$conditionsString);
		
		if(mysqli_num_rows($userQry) == 1){
			$UserRow = mysqli_fetch_assoc($userQry);
			
			if($UserRow['user_image'] != ''){
				$UserRow['user_image'] = MAIN_URL.'/images/user_images/'.$UserRow['user_image'];
			}elseif($UserRow['social_image'] != ''){
				$UserRow['user_image'] = $UserRow['social_image'];
			}else{
				$UserRow['user_image'] = '';
			}
			
			if($UserRow['kyc_image'] != ''){
				$UserRow['kyc_image'] = MAIN_URL.'/images/kyc_images/'.$UserRow['kyc_image'];
			}else{
				$UserRow['kyc_image'] = '';
			}
			
			
			$response['data'] = $UserRow;
			$response['status'] = 1;
			$response['message'] = 'User details';
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>