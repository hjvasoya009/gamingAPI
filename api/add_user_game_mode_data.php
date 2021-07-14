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
		
		if(!isset($_POST['team_name']) || empty($_POST['team_name'])){
			$response['message'] = 'Please enter Team Name';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['joining_member_data']) || empty($_POST['joining_member_data'])){
			$response['message'] = 'Please enter joining members data';
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
			$tokenRow = mysqli_fetch_assoc($tokenQry);
			$username = $tokenRow['username'];
			
			$conditionString = "id=$game_id";
			$columns = array("id", "gamename");
			$gameQry = fetchFromTable("game",$columns,$conditionString);
			$gameRow = mysqli_fetch_assoc($gameQry);
			$gameName = $gameRow['gamename'];
			
			$conditionString = "id=$tournament_type_id";
			$columns = array("id", "tournamenttype");
			$ttQry = fetchFromTable("tournament_type",$columns,$conditionString);
			
			if(mysqli_num_rows($ttQry) == 1){
				
				$con->autocommit(false);
				
				$ttRow = mysqli_fetch_assoc($ttQry);
				$tournament_type = $ttRow['tournamenttype'];
				
				$joining_member_data = $_POST['joining_member_data'];
				
				/*$delCondition = "user_id=$user_id AND game_id=$game_id AND tournament_type_id=$tournament_type_id";
				$deleteResult = removeRecordFromTable("user_game_mode_data", $delCondition, $isFullCondition = true);
				if (!$deleteResult['mysqli_error']) {*/
					
					foreach($joining_member_data as $member){
						$conditionString = "username='".$member['game_akhada_user_name']."' AND user_status = 1";
						$columns = array("id", "username", "fcm_token");
						$usersQry = fetchFromTable("users",$columns,$conditionString);
						if(mysqli_num_rows($usersQry) == 1){
							$usersRow = mysqli_fetch_assoc($usersQry);
							
							$fcm_token 		= $usersRow['fcm_token'];
							$message 		= 'You are requested to join '.$gameName.' '.$tournament_type.' by '.$username.'. Do you want to join ?';
							$title 			= 'Joining Request';
							$is_requesting 	= 1;
							
							if(fcmNotificationRequest($fcm_token, $message, $title, $user_id, $game_id, $tournament_type_id, 1)){
								$data = new \stdClass();
							    $data->team_name 			= $team_name;
							    $data->user_id 				= $user_id;
							    $data->game_id 				= $game_id;
							    $data->tournament_type_id 	= $tournament_type_id;
							    $data->member_user_id 		= $usersRow['id'];
							    $data->member_username 		= $member['game_akhada_user_name'];
							    $data->updated_at 			= $datetime;
							    
							   	$insertResult = insertToTable("user_game_mode_data", $data);
							    
								if (!$insertResult['mysqli_error']) {
							        $response['status']  = 1;
							    } else {
							        $response['status']  = 0;
									$response['message'] = 'Something went wrong! Cannot add user game mode data!';
									break;
							    }
							    $response['data']['notification_sent'][] 	= $member['game_akhada_user_name'];
							}else{
								$response['data']['notification_not_sent'][] 	= $member['game_akhada_user_name'];
							}
							
						
							$response['data']['found'][] 	= $member['game_akhada_user_name'];
						}else{
							$response['data']['not_found'][] = $member['game_akhada_user_name'];
						}
					}
				/*}else{
					$response['message'] = 'Error while deleting older user game mode data!';
				}*/
				
				if ($response['status'] == 0) {
			        $con->rollback();
			        $response['message'] = 'Something went wrong! Cannot add user game mode data!';
			    } else {
			        $con->commit();
			        $response['message'] = 'User game mode data added successfully!';
			    }
			    
			    $con->autocommit(true);
			}else{
				$response['message'] = 'No data found for tournament type!';
			}			
			
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>