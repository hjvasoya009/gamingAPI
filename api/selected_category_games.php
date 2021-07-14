<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		
		if(!isset($_POST['category_id']) || empty($_POST['category_id'])){
			$response['message'] = 'Please enter category id';
			echo json_encode($response);
			exit();
		}
		
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
			$gameSql = "SELECT g.*, gc.category_name FROM game AS g INNER JOIN game_category AS gc ON g.category_id = gc.id WHERE g.category_id=".$_POST['category_id']." AND g.isActive = 1";
			$gameRes = mysqli_query($con,$gameSql);
			if(mysqli_num_rows($gameRes) > 0){
				while($gameRow = $gameRes->fetch_assoc()){
					$gameRow['game_image'] = MAIN_URL.'/images/game_images/'.$gameRow['game_image'];
					$response['data'][] = $gameRow;
				}
				$response['status'] = 1;
				$response['message'] = 'All Games in selected category!';
			}else{
				$response['message'] = 'No Games in this category at this moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>