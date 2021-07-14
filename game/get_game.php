<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$response = [];
	if(isset($_POST['cmd']) && !empty($_POST['cmd'])){
		$cmd = $_POST['cmd'];
		if($cmd == "get_game"){
			$game_id = $_POST['game_id'];
			$sql = "SELECT g.*, gc.category_name FROM game AS g LEFT JOIN game_category AS gc ON g.category_id=gc.id WHERE g.id=$game_id";
			$rs = mysqli_query($con,$sql);
			$row = $rs->fetch_assoc();
			echo json_encode($row);
		}
	}else{
		$sql = "SELECT g.*, gc.category_name FROM game AS g LEFT JOIN game_category AS gc ON g.category_id=gc.id WHERE g.isActive=".$_POST['isActive'];
		$rs = mysqli_query($con,$sql);
		if ($rs) {
			while ($row = mysqli_fetch_assoc($rs)) {
				$row["matches"] = 0;
			    $response[] = $row;
			}
		}
		echo '{"data": ' . json_encode($response) . '}';
	}
	die;