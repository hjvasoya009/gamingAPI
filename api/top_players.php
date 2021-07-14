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
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			
			$conditionString = "user_status = 1 AND total_winnings > 0";
			$columns = array("first_name", "last_name", "total_winnings");
			$orderBy = 'total_winnings DESC';
			$rLimit  = '0, 50';
			$topPlayersQry = fetchFromTable("users", $columns, $conditionString, $orderBy, $rLimit);
			if(mysqli_num_rows($topPlayersQry) > 0){
				while($row = $topPlayersQry->fetch_assoc()){
					$response['data'][] = $row;
				}
				$response['status'] = 1;
				$response['message'] = 'Top players of the app!';
			}else{
				$response['message'] = 'No top players at this moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>