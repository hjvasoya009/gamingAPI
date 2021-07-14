<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['tournament_id']) && !empty($_POST['tournament_id'])){
		
		$conditionString = 'id=1';
		$settingQry 	 = fetchFromTable("settings",null,$conditionString);
		$settingRow 	 = $settingQry->fetch_assoc();
		$support_email 	 = $settingRow['support_mail'];
		
		$tournament_id = $_POST['tournament_id'];
		$tournamentSQL = "SELECT t.*, g.gamename, gp.gameplatform, tt.tournamenttype FROM tournament AS t INNER JOIN game AS g ON t.game_id=g.id INNER JOIN game_platform AS gp ON t.game_platform_id=gp.id INNER JOIN tournament_type AS tt ON t.tournament_type_id=tt.id WHERE t.id = ".$tournament_id;
		$tournamentQry = mysqli_query($con, $tournamentSQL);
		$tournamentRow = mysqli_fetch_assoc($tournamentQry);
    	$tournament_prize = $tournamentRow['tournament_prize'];
		
		$title = "New tournament!";
		$message = "A new tournament (".$tournamentRow['match_title'].") is up! Hurry up ... Register now for ".$tournamentRow['gamename']." tournament before slots get full.";
		
		$data = new \stdClass();
	    $data->notification_type 	= 3;
	    $data->notification_message	= $message;
	    $data->notification_time 	= $datetime;
	    $insertResult = insertToTable("notification_data", $data);
		
		/*$conditionString = "user_status=1";
		$columns = array("username", "fcm_token");
		$usersQry = fetchFromTable("users", $columns, $conditionString);
		$count = 1;	
		while($usersRow = mysqli_fetch_assoc($usersQry)){
			fcmNotificationNewTournament($usersRow['fcm_token'], $message, $title, $tournamentRow['game_id']);
		}*/
		fcmNotificationNewTournament($message, $title, $tournamentRow['game_id']);
	}
	die;