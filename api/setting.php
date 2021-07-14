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
			
			$conditionString = "user_id=$user_id AND transaction_purpose='Spin and Win' AND transaction_method='Coins' AND DATE(transaction_datetime) = '".date('Y-m-d')."'";
			$transactionResult = fetchFromTable("transaction_history",null,$conditionString);
			if(mysqli_num_rows($transactionResult) > 0){
				$spin_win_count = 1;
			}else{
				$spin_win_count = 0;
			}
			
			$conditionString = 'id=1';
			$rs = fetchFromTable("settings",null,$conditionString);
			if(mysqli_num_rows($rs) == 1){
				$row = $rs->fetch_assoc();
				$row['spin_win_count'] = $spin_win_count;
				$response['data'] = $row;
				$response['status'] = 1;
				$response['message'] = 'All Games in selected category!';
			}else{
				$response['message'] = 'No Settings exist at this moment!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>