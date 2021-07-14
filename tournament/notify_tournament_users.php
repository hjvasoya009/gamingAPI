<?php
	
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$response['error'] = true;
	$response['status'] = 0;

	if(!empty($_POST['id']) && !empty($_POST['id'])){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
		
		$conditionString = "id=$id";
		$tournamentQry = fetchFromTable("tournament",null,$conditionString);
		$tournamentRow = mysqli_fetch_assoc($tournamentQry);
		$tournamentName = $tournamentRow['match_title'];
		$tournamentStartTime = $tournamentRow['match_datetime'];
		
		$datetime_now = new DateTime();
		$datetime_game = new DateTime($tournamentStartTime);
		$interval = $datetime_now->diff($datetime_game);
		$elapsed = ($interval->format('%a')*24*60)+($interval->format('%h')*60)+($interval->format('%i'))+(($interval->format('%s')/60));
		$timeLeft = ceil($interval->format('%r').$elapsed);
		
		$tournamentUserSQL = "SELECT u.id, u.username, u.fcm_token FROM tournament_users AS tu INNER JOIN users AS u ON tu.user_id=u.id WHERE tu.tournament_id = ".$id;
		$tournamentUsersQry = mysqli_query($con, $tournamentUserSQL);
		
		if(mysqli_num_rows($tournamentUsersQry) > 0){
			
			$message 		= 'Please be ready to play! '. $tournamentName.' will start in '.$timeLeft.' Minutes.';
			$title 			= 'Tournament Alert!';
			
			$data = new \stdClass();
		    $data->notification_type 	= 2;
		    $data->tournament_id 		= $id;
		    $data->notification_message	= $message;
		    $data->notification_time 	= $datetime;
		    $insertResult = insertToTable("notification_data", $data);
			
			while($tournamentUsersRow = mysqli_fetch_assoc($tournamentUsersQry)){
				$fcm_token = $tournamentUsersRow['fcm_token'];
				fcmNotificationTournamentUsers($fcm_token, $message, $title);
			}
			
			$response['error'] = false;
			$response['status'] = 1;
			$response['message'] = 'Notification sent to all users of the tournament!';
		} else {
			$response['message'] = 'No users found for the tournament!';
		}
	    
	} else {
		$response['message'] = 'Something went wwrong! Please try again later!';
	}
	
	echo json_encode($response);
?>