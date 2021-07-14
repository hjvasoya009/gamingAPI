<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$response = [];
	if(isset($_POST['cmd']) && !empty($_POST['cmd'])){
		$cmd = $_POST['cmd'];
		if($cmd == "get_admin"){
			$u_id = $_POST['u_id'];
			$conditionsString = 'id='.$u_id;
			$rs = fetchFromTable("admin",null,$conditionsString);
			$row = $rs->fetch_assoc();
			echo json_encode($row);
		}
	}else{
		$conditionsString = ' id!=1 AND isActive='.$_POST['isActive'];
		$rs = fetchFromTable("admin",null,$conditionsString);
		if ($rs) {
			while ($row = mysqli_fetch_assoc($rs)) {
				$row["role"] = preg_replace(["/1/","/2/"] ,["ALL","Limited"],$row["role"]);
			    $response[] = $row;
			}
		}
		echo '{"data": ' . json_encode($response) . '}';
	}
	die;