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
		$columns = array("bearer_token");
		$tokenQry = fetchFromTable("users",$columns,$conditionsString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			$conditionString = "user_status=2";
			$fetchQry = fetchFromTable("users",null,$conditionString);
			if (mysqli_num_rows($fetchQry) > 0) {
				
				while($fetchRow = mysqli_fetch_assoc($fetchQry)){
					$response['data'][] = $fetchRow;
				}
				$response['status'] = 1;
				$response['message'] = 'Hall of Shame Users.';
			}else{
				$response['message'] = 'No Hall of Shame Users at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>