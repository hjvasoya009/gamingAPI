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
			$sql = "SELECT t.*, g.gamename, gp.gameplatform, tt.tournamenttype FROM tournament AS t INNER JOIN game AS g ON g.id=t.game_id INNER JOIN game_platform AS gp ON gp.id=t.game_platform_id INNER JOIN tournament_type AS tt ON tt.id=t.tournament_type_id WHERE t.match_datetime < NOW() AND t.isActive=1 ORDER BY t.match_datetime DESC";
			$rs = mysqli_query($con,$sql);
			if (mysqli_num_rows($rs) > 0) {
				
				while($row = mysqli_fetch_assoc($rs)){
					$checkQry = "SELECT * FROM tournament_users WHERE user_id=$user_id AND tournament_id=".$row['id'];
					$checkRes = mysqli_query($con,$checkQry);
					if (mysqli_num_rows($checkRes) == 1){
						$row['is_joined'] = 1;
					}else{
						$row['is_joined'] = 0;
					}
					$row['match_image'] = MAIN_URL.'/images/tournament_images/'.$row['match_image'];
					$row['match_datetime'] = date('d-m-Y h:i:s A', strtotime($row['match_datetime']));
					$response['data'][] = $row;
				}
				$response['status'] = 1;
				$response['message'] = 'Ongoing tournaments!';
			}else{
				$response['message'] = 'Oops! No Ongoing games right now!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>