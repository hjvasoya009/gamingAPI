<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;
	$form_name = $_POST['form_name'];
	
	if($form_name == 'send_notification'){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			} 
	    }
	    
	    $conditionString = 'id=1';
		$rs = fetchFromTable("settings",null,$conditionString);
		if(mysqli_num_rows($rs) == 1){
			$row = $rs->fetch_assoc();
			$support_email = $row['support_mail'];
			
			$conditionString = "id=$user_id";
			$columns = array("username", "mobile_number", "email");
			$userQry = fetchFromTable("users", $columns, $conditionString);
			
			if(mysqli_num_rows($userQry) == 1){
				$userRow = mysqli_fetch_assoc($userQry);
				$username = $userRow['username'];
				if($send_method == 'sms'){
					if($userRow['mobile_number'] != ''){
						sendSMS($userRow['mobile_number'], $text_message);
					}
					$arrResult['error'] = false;
			        $arrResult['message'] = 'SMS sent to '.$username.'!';
				}elseif($send_method == 'mail'){
					if($userRow['email'] != ''){
						$to      = $userRow['email'];
						$subject = "Notification for ".$userRow['username'];
						$message = "<html><head><title></title></head><body><div>".$text_message."</div></body></html>";
						$headers = "MIME-Version: 1.0" . "\r\n";
						$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
						$headers .= 'From: <'.$support_email.'>' . "\r\n";
						mail($to,$subject,$message,$headers);
					}
					$arrResult['error'] = false;
			        $arrResult['message'] = 'Mail sent to '.$username.'!';
				}elseif($send_method == 'both'){
					if($userRow['mobile_number'] != ''){
						sendSMS($userRow['mobile_number'], $text_message);
					}
					if($userRow['email'] != ''){
						$to      = $userRow['email'];
						$subject = "Notification for ".$userRow['username'];
						$message = "<html><head><title></title></head><body><div>".$text_message."</div></body></html>";
						$headers = "MIME-Version: 1.0" . "\r\n";
						$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
						$headers .= 'From: <'.$support_email.'>' . "\r\n";
						mail($to,$subject,$message,$headers);
					}
					$arrResult['error'] = false;
			        $arrResult['message'] = 'SMS and Mail sent to '.$username.'!';
				}
			}else{
				$arrResult['message'] = 'Notification sent failed!';
			}
		}else{
			$arrResult['message'] = 'Notification sent failed!';
		}
	    
	    echo json_encode($arrResult);
	}
?>