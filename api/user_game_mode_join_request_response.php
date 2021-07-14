<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;
	
	if(isset($_POST) && !empty($_POST)){
		
		if(!isset($_POST['responder_user_id']) || empty($_POST['responder_user_id'])){
			$response['message'] = 'Please enter Responder user id';
			echo json_encode($response);
			exit();
		}
		
	    if(!isset($_POST['responder_username']) || empty($_POST['responder_username'])){
			$response['message'] = 'Please enter responder username';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['responder_game_user_name']) || empty($_POST['responder_game_user_name'])){
			$response['message'] = 'Please enter responder game user name';
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
		
		if(!isset($_POST['requested_user_id']) || empty($_POST['requested_user_id'])){
			$response['message'] = 'Please enter requested user id';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['is_accepted'])){
			$response['message'] = 'Please enter is accepted or not';
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
		
		$conditionString = "id=$responder_user_id AND username='$responder_username' AND user_status=1";
		$columns = array("fcm_token", "username");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			
			$conditionString = "user_status = 1 AND id=$requested_user_id";
			$columns = array("fcm_token", "username");
			$userQry = fetchFromTable("users",$columns,$conditionString);
			if(mysqli_num_rows($userQry) == 1){
				
				$usersRow = mysqli_fetch_assoc($userQry);
				$fcm_token = $usersRow['fcm_token'];
				
				$conditionString = "id=$game_id";
				$columns = array("id", "gamename");
				$gameQry = fetchFromTable("game",$columns,$conditionString);
				$gameRow = mysqli_fetch_assoc($gameQry);
				$gameName = $gameRow['gamename'];
				
				$conditionString = "id=$tournament_type_id";
				$columns = array("id", "tournamenttype");
				$ttQry = fetchFromTable("tournament_type",$columns,$conditionString);
				$ttRow = mysqli_fetch_assoc($ttQry);
				$tournament_type = $ttRow['tournamenttype'];
							
				if($is_accepted == 1){
					
					$message 		= $responder_username.' has accepted to join your '.$gameName.' '.$tournament_type.'.';
					$title 			= 'Joining confirmation';
					$is_requesting 	= 0;
					
					$editCondition = "user_id = $requested_user_id AND game_id = $game_id AND tournament_type_id = $tournament_type_id AND member_user_id = $responder_user_id";
					$data = new \stdClass();
				    $data->member_game_username	= $responder_game_user_name;
				    $data->request_accepted 	= $is_accepted;
				    $data->updated_at 			= $datetime;
				    $updateUserGameData = editRecordFromTable("user_game_mode_data", $data, null, $editCondition);
					
					if(fcmNotificationResponse($fcm_token, $message, $title, 0)){
						if (!$updateUserGameData['mysqli_error']) {
					        $response['status'] = 1;
					        $response['message'] = 'Joining Successfull!';
					    } else {
					        $response['message'] = 'Joining failed!';
					    }
					}else{
						if (!$updateUserGameData['mysqli_error']) {
					        $response['status'] = 1;
					        $response['message'] = 'Notification sending failed! Joining Successfull!';
					    } else {
					        $response['message'] = 'Notification sending failed! Joining failed!';
					    }
					}
					
				}elseif($is_accepted == 0){
					
					$message 		= $responder_username.' has rejected to join your '.$gameName.' '.$tournament_type.'.';
					$title 			= 'Joining confirmation';
					$is_requesting 	= 0;
					
					$delCondition = "user_id = $requested_user_id AND game_id = $game_id AND tournament_type_id = $tournament_type_id AND member_user_id = $responder_user_id";
					$deleteResult = removeRecordFromTable("user_game_mode_data", $delCondition, $isFullCondition = true);
					
					if(fcmNotificationResponse($fcm_token, $message, $title, 0)){
						if (!$deleteResult['mysqli_error']) {
					        $response['status'] = 1;
					        $response['message'] = 'Joining request Rejected and updated successfully in user game mode data!';
					    } else {
					        $response['message'] = 'Joining request Rejected and updation failed in user game mode data!';
					    }
					}else{
						if (!$deleteResult['mysqli_error']) {
					        $response['status'] = 1;
					        $response['message'] = 'Notification sending failed! Joining request Rejected and updated successfully in user game mode data!';
					    } else {
					        $response['message'] = 'Notification sending failed! Joining request Rejected and updated successfully in user game mode data!';
					    }
					}
					
				}else{
					$response['message'] = 'Acceptance response not found!';
				}
			}else{
				$response['message'] = 'Requested user account not found!';
			}
				
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>