<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
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
		
		if(!isset($_POST['image_for']) || empty($_POST['image_for'])){
			$response['message'] = 'Please pass image_for value';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionsString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$columns = array("$image_for","bearer_token");
		$tokenQry = fetchFromTable("users",$columns,$conditionsString);
        
        if(mysqli_num_rows($tokenQry) == 1){
				
			foreach ($_FILES as $key => $file) {
				$path = '../../images/'.$image_for.'s/';
		        $fileName = $user_id. "." . pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION);
		    	
		        if (move_uploaded_file($_FILES[$key]['tmp_name'], $path.$fileName)) {
		        	
		        	$data = new \stdClass();
			    	$data->$image_for = $fileName;
	    			
				    $updateResult = editRecordFromTable("users", $data, $user_id);
					if (!$updateResult['mysqli_error']){
						$rFiles = array_diff(glob($path.$user_id.".*"), glob($path.$fileName));
					    foreach($rFiles as $rFile) {
					      if(is_file($rFile))
					        unlink($rFile); // delete file
					    }
					}
		            $response[$image_for] = $fileName;
		        }
			}
			
			if (!empty($response[$image_for])) {
			    $response['status'] = "1";
			    $response['message'] = "Image upload successful!";
			} else {
				$response['status'] = "0";
			    $response['message'] = "Image upload failed.";
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}	
	}else{
		$response['message'] = 'No input found!';
	}
	echo json_encode($response);
	exit();
?>
