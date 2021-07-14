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
			$conditionString = "isActive=1";
			$columns=array("id","category_name");
			$rs = fetchFromTable("game_category", $columns, $conditionString);
			if (mysqli_num_rows($rs) > 0) {
				
				while($row = mysqli_fetch_assoc($rs)){
					
					$gameSql = "SELECT g.id, g.gamename, g.game_image, gc.category_name FROM game AS g INNER JOIN game_category AS gc ON g.category_id = gc.id WHERE g.category_id=".$row['id']." AND g.isActive = 1";
					$gameRes = mysqli_query($con,$gameSql);
					while($gameRow = $gameRes->fetch_assoc()){
						$gameRow['game_image'] = MAIN_URL.'/images/game_images/'.$gameRow['game_image'];
						$row['games'][] = $gameRow;
					}
					$response['data'][] = $row;
				}
				$response['status'] = 1;
				$response['message'] = 'All Games in all category!';
			}else{
				$response['message'] = 'No Games at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>