<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$response = [];
	if(isset($_POST['cmd']) && !empty($_POST['cmd'])){
		$cmd = $_POST['cmd'];
		if($cmd == "get_game_category"){
			$category_id = $_POST['category_id'];
			$conditionsString = 'id='.$category_id;
			$rs = fetchFromTable("game_category",null,$conditionsString);
			$row = $rs->fetch_assoc();
			echo json_encode($row);
		}
	}else{
		$conditionsString = 'isActive='.$_POST['isActive'];
		$rs = fetchFromTable("game_category",null,$conditionsString);
		if ($rs) {
			while ($row = mysqli_fetch_assoc($rs)) {
			    $response[] = $row;
			}
		}
		echo '{"data": ' . json_encode($response) . '}';
	}
	die;