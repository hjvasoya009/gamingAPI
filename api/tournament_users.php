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
		
		if(!isset($_POST['tournament_id']) || empty($_POST['tournament_id'])){
			$response['message'] = 'Please enter tournament id';
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
			
			$conditionString = "id=$tournament_id";
			$tournamentQry = fetchFromTable("tournament",null,$conditionString);
			
			if(mysqli_num_rows($tournamentQry) == 1){
				
				$tournamentRow = mysqli_fetch_assoc($tournamentQry);
				$tournament_type_id = $tournamentRow['tournament_type_id'];
				if($tournament_type_id == 1){
					
					$tournamentUsersSQL = "SELECT tu.*, u.username FROM tournament_users AS tu INNER JOIN users AS u ON u.id=tu.user_id WHERE tu.tournament_id = ".$_POST['tournament_id']." ORDER BY winner_rank=0, winner_rank, user_kills DESC, tu.id";
					$tournamentUsersResult = mysqli_query($con, $tournamentUsersSQL);
					
					while($tournamentUsersRow = mysqli_fetch_assoc($tournamentUsersResult)){
						$tournamentUsersRow['team_data'] = (array) null;
						$response['data'][] = $tournamentUsersRow;
					}
					
					$response['status'] = 1;
					$response['message'] = 'Tournament users!';
					
				}else{
					
					$conditionString = "tournament_id=$tournament_id";
					$columns = array("team_id", "team_name");
					$groupBy = "team_id";
					$orderBy = "winner_rank=0, winner_rank, team_id, user_kills DESC, id";
					$tournamentTeamQry = fetchFromTable("tournament_users", $columns, $conditionString, $orderBy, null, $groupBy);
					
					while($tournamentTeamRow = mysqli_fetch_assoc($tournamentTeamQry)){
						
						$team_id = $tournamentTeamRow['team_id'];
						$team_name = $tournamentTeamRow['team_name'];
						
						$tournamentUsersSQL = "SELECT tu.*, u.username FROM tournament_users AS tu INNER JOIN users AS u ON u.id=tu.user_id WHERE tu.team_id = $team_id AND tu.tournament_id = ".$_POST['tournament_id'];
						$tournamentUsersResult = mysqli_query($con, $tournamentUsersSQL);
						while($tournamentUsersRow = mysqli_fetch_assoc($tournamentUsersResult)){
							$tournamentTeamRow['team_data'][] = $tournamentUsersRow;
						}
						
						$response['data'][] = $tournamentTeamRow;
					}
					
					$response['status'] = 1;
					$response['message'] = 'Tournament users!';
				}
			}else{
				$response['message'] = 'Tournament not found!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>