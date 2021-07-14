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
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("bearer_token");
		$tokenQry = fetchFromTable("users",$columns,$conditionString);
		
		if(mysqli_num_rows($tokenQry) == 1){
			$sql = "SELECT id, tournamenttype FROM tournament_type";
			$rs = mysqli_query($con,$sql);
			if (mysqli_num_rows($rs) > 0) {
				
				while($row = mysqli_fetch_assoc($rs)){
					
					$gameModeSql = "SELECT gm.*, g.gamename, tt.tournamenttype FROM user_game_mode_data AS gm INNER JOIN game AS g ON g.id=gm.game_id INNER JOIN tournament_type AS tt ON tt.id=gm.tournament_type_id WHERE gm.game_id=$game_id AND user_id=$user_id AND tournament_type_id=".$row['id'];
					$gameModeRes = mysqli_query($con,$gameModeSql);
					while($gameModeRow = $gameModeRes->fetch_assoc()){
						$row['user_games_mode_data'][] = $gameModeRow;
					}
					
					$response['data'][] = $row;
				}
				$response['status'] = 1;
				$response['message'] = 'User Game mode data.';
			}else{
				$response['message'] = 'No game mode data of user at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>