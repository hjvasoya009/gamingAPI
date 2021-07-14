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
		
		if(!isset($_POST['member_user_id']) || empty($_POST['member_user_id'])){
			$response['message'] = 'Please enter member user id';
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
			
			$conditionString = "id=$member_user_id";
			$membercolumns = array("first_name", "last_name", "username", "country", "user_image", "social_image", "user_level", "total_winnings", "matches_played", "matches_won");
			$memberQry = fetchFromTable("users",$membercolumns,$conditionString);
				
			if(mysqli_num_rows($memberQry) == 1){
				$memberRow = mysqli_fetch_assoc($memberQry);
				
				if($memberRow['user_image'] != ''){
					$memberRow['user_image'] = MAIN_URL.'/images/user_images/'.$memberRow['user_image'];
				}elseif($memberRow['social_image'] != ''){
					$memberRow['user_image'] = $memberRow['social_image'];
				}
				$response['data'] = $memberRow;
				
				$response['status'] = 1;
				$response['message'] = 'Member user detail!';
			}else{
				$response['message'] = 'No Member user detail available at the moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>