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
		
		if(!isset($_POST['tournament_id']) || empty($_POST['tournament_id'])){
			$response['message'] = 'Please enter tournament id';
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
			
			$conditionString = "tournament_id=$tournament_id";
			$prizePoolcolumns = array("slot_size", "slot_prize");
			$orderBy = "id ASC";
			$prizePoolQry = fetchFromTable("tournament_winner_slots", $prizePoolcolumns, $conditionString, $orderBy);
				
			if(mysqli_num_rows($prizePoolQry) > 0){
				
				while($prizePoolRow = mysqli_fetch_assoc($prizePoolQry)){
					$response['data'][] = $prizePoolRow;
				}
				$response['status'] = 1;
				$response['message'] = 'Prize Pool for the tournament!';
			}else{
				$response['message'] = 'No Prize Pool for this tournament at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>