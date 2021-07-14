<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		
		if(!isset($_POST['social_id']) || empty($_POST['social_id'])){
			$response['message'] = 'Please enter Social ID';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionsString = "social_id='".$social_id."'";
		$rs = fetchFromTable("users", null, $conditionsString);		
		if (mysqli_num_rows($rs) == 1) {
			$row = mysqli_fetch_assoc($rs);
		    if($row['email'] == '' || $row['username'] == '' || $row['password'] == ''){
				$response['message'] = 'Email, Username or Password is empty!';
			}else{
				$response['status'] = 1;
			}
		}else{
			$response['message'] = 'User not found with this Social ID';
		}
		
	    echo json_encode($response);
	    exit();
	}
?>