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
		
		if(!isset($_POST['game_id']) || empty($_POST['game_id'])){
			$response['message'] = 'Please enter game id';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['tournament_type_id']) || empty($_POST['tournament_type_id'])){
			$response['message'] = 'Please enter tournament type id';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['member_user_id']) || empty($_POST['member_user_id'])){
			$response['message'] = 'Please enter Team member user id';
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
		$columns = array("bearer_token", "fcm_token", "username");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){	
			$delCondition = "user_id=$user_id AND game_id=$game_id AND tournament_type_id=$tournament_type_id AND member_user_id=$member_user_id";
			$deleteResult = removeRecordFromTable("user_game_mode_data", $delCondition, $isFullCondition = true);
			if (!$deleteResult['mysqli_error']) {
				$response['status']  = 1;
				$response['message'] = 'Member removed successfully from user game mode data!';
			}else{
				$response['message'] = 'Error while deleting member user game mode data!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>