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
		
		if(!isset($_POST['old_password']) || empty($_POST['old_password'])){
			$response['message'] = 'Please enter old Password';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['new_password']) || empty($_POST['new_password'])){
			$response['message'] = 'Please enter new Password';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
	    
	    $conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
	    $columns = array("bearer_token", "password");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			
			$tokenRow = mysqli_fetch_assoc($tokenQry);
			$current_password = $tokenRow['password'];
			
			if($old_password === $current_password){
				$data = new \stdClass();
			    $data->password 	= $new_password;
			    $data->updated_at 	= $datetime;
			    
			    $insertResult = editRecordFromTable("users", $data, $user_id);
			    
				if (!$insertResult['mysqli_error']) {
			        $response['status'] = 1;
			        $response['message'] = 'Password changed successfully!';
			    } else {
			        $response['message'] = 'Password change unsuccessful!';
			    }
			}else{
				$response['message'] = 'You have entered wrong password!';
			}
			
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>