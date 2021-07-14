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
			$conditionString = "user_id=$user_id";
			$notificationDataQry = mysqli_query($con, "SELECT nd.notification_message, nd.notification_time FROM notification_data AS nd LEFT JOIN tournament_users AS tu ON tu.tournament_id = nd.tournament_id WHERE nd.tournament_id = 0 OR (nd.tournament_id!=0 AND tu.user_id=$user_id) ORDER BY nd.id DESC");
			if (mysqli_num_rows($notificationDataQry) > 0) {
				while($notificationDataRow = mysqli_fetch_assoc($notificationDataQry)){
					$response['data'][] = $notificationDataRow;
				}
				$response['status'] = 1;
			}else{
				$response['message'] = 'No Notifications at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>