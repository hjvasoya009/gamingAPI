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
			$columns=array("id","category_name");
			$rs = fetchFromTable("game_category", $columns);
			if (mysqli_num_rows($rs) > 0) {
				
				while($row = mysqli_fetch_assoc($rs)){
					
					$conditionString = "category_id=".$row['id'];
					$columns=array("id", "gamename", "category_id");
					$gameRs = fetchFromTable("game", $columns, $conditionString);
					
					while($gameRow = mysqli_fetch_assoc($gameRs)){
						
						$statisticsSql = "SELECT tu.tournament_id, tu.user_id, u.username, tu.is_winner, t.match_title, t.match_datetime, t.game_id, g.gamename, gc.category_name FROM tournament_users AS tu INNER JOIN users AS u ON u.id = tu.user_id INNER JOIN tournament AS t ON t.id = tu.tournament_id INNER JOIN game AS g ON g.id = t.game_id INNER JOIN game_category AS gc ON g.category_id = gc.id WHERE tu.user_id=$user_id AND g.category_id=".$row['id']." AND g.id=".$gameRow['id']." ORDER BY t.match_datetime DESC";
						$statisticsRes = mysqli_query($con,$statisticsSql);
						while($statisticsRow = $statisticsRes->fetch_assoc()){
							$gameRow['tournaments'][] = $statisticsRow;
						}
						$row['games'][] = $gameRow;
					}
					$response['data'][] = $row;
				}
				$response['status'] = 1;
				$response['message'] = 'All Games Statistics!';
			}else{
				$response['message'] = 'No Statistics at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>