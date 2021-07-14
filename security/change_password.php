<?php
include_once "../../connection.php";
include_once "../../utilities.php";

$arrResult = array();
$arrResult['error'] = true;
$id = $_SESSION['id'];
foreach ($_POST as $key => $value) {
    $$key = mysqli_real_escape_string($con, $value);
}

if (isset($current_password, $new_password, $confirm_password)) {
	if($new_password == $confirm_password){
		$rs = fetchFromTable("admin", null, " `id`=$id");
	    if ($rs && $rs->num_rows == 1) {
	        $row = $rs->fetch_assoc();
	        if($current_password == $row['password']){
	        	$data = new \stdClass();
			    $data->password = $new_password;
			    $insertResult = editRecordFromTable("admin", $data, $id);
			    if(!$insertResult['mysqli_error']){
					$arrResult['error'] = false;
	        		$arrResult['message']='Password changed successfully!';
				}else{
					$arrResult['message'] = 'Password change unsuccessful';
				}
			}else{
				$arrResult['message'] = 'You have entered wrong current password! ';
			}
	    } else {
	        $arrResult['message'] = ' Opps! No data found! '.mysqli_error($con);

	    }
	}else{
		$arrResult['message'] = 'New Password and Confirm Password not matched';
	}
} else {
    $arrResult['message'] = 'Something went Wrong! Please try again latter!';
}
echo json_encode($arrResult);
die;