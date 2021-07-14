<?php
	
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;

	if(!empty($_POST['tournament_id']) && !empty($_POST['user_id']) && !empty($_POST['tournament_users_id'])){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
	    
	    $tournament_winning = (int)$tournament_winning;
	    
	    $con->autocommit(false);
		
		$conditionString = "id=$tournament_id";
		$tournamentQry = fetchFromTable("tournament",null,$conditionString);
		
		if(mysqli_num_rows($tournamentQry) == 1){
			
			$tournamentRow 		= mysqli_fetch_assoc($tournamentQry);
			$tournament_prize 	= $tournamentRow['tournament_prize'];
			$game_id 			= $tournamentRow['game_id'];
			
			$conditionString = "id=$tournament_users_id";
			$tournamentUsersQry = fetchFromTable("tournament_users",null,$conditionString);
			
			if(mysqli_num_rows($tournamentUsersQry) == 1){
				
				$tournamentUsersRow 		= mysqli_fetch_assoc($tournamentUsersQry);
				$current_tournament_winning	= $tournamentUsersRow['tournament_winning'];
				$current_is_winner			= $tournamentUsersRow['is_winner'];
				$is_game_counted			= $tournamentUsersRow['is_game_counted'];
				
				$data = new \stdClass();
			    $data->user_kills 			= $user_kills;
			    $data->kills_prize 			= $kills_prize;
		    	$data->win_prize 			= $win_prize;
		    	$data->tournament_winning 	= $tournament_winning;
		    	$data->winner_rank 			= $winner_rank;
		    	$data->is_winner 			= $is_winner;
		    	/*$data->is_game_counted 		= 1;*/
		    	
			    $updateResult = editRecordFromTable("tournament_users", $data, $tournament_users_id);
			    
				if (!$updateResult['mysqli_error']) {
					
					$conditionString = "id=$user_id AND user_status=1";
					$userQry = fetchFromTable("users",null,$conditionString);
					
					if(mysqli_num_rows($userQry) == 1){
						
						$userRow = mysqli_fetch_assoc($userQry);
						
						$matches_won 	= $userRow['matches_won'];
						$total_winnings = $userRow['total_winnings'];
						$wallet_balance = $userRow['wallet_balance'];
						$ga_coins 		= $userRow['ga_coins'];
						
						$error = true;
						
						if($tournament_prize == 'money' || $tournament_prize == 'other'){
							
							$data = new \stdClass();
							$data->total_winnings 	= $total_winnings + $tournament_winning - $current_tournament_winning;
							$data->wallet_balance 	= $wallet_balance + $tournament_winning - $current_tournament_winning;
							if($current_is_winner == 0 && $is_winner == 1){
								$data->matches_won 	= $matches_won + 1;
							}elseif($current_is_winner == 1 && $is_winner == 0){
								$data->matches_won 	= $matches_won - 1;
							}else{
								$data->matches_won 	= $matches_won;
							}
							
							$error = false;
					    	
						}elseif($tournament_prize == 'coins'){
							
							$data = new \stdClass();
							$data->ga_coins 	= $ga_coins + $tournament_winning - $current_tournament_winning;
							if($current_is_winner == 0 && $is_winner == 1){
								$data->matches_won 	= $matches_won + 1;
							}elseif($current_is_winner == 1 && $is_winner == 0){
								$data->matches_won 	= $matches_won - 1;
							}else{
								$data->matches_won 	= $matches_won;
							}
							
							$error = false;
							
						}else{
							$arrResult['message'] = 'No data found for the tournament prize!';
						}
						
						if(!$error){
										
							$updateResult = editRecordFromTable("users", $data, $user_id);
							
							if (!$updateResult['mysqli_error']) {
								
								$conditionString = "user_id=$user_id AND game_id=$game_id";
								$userGamesPlayedQry = fetchFromTable("user_games_played",null,$conditionString);
								
								if(mysqli_num_rows($userGamesPlayedQry) > 1){
									$arrResult['message'] = 'Something went wrong with user games played data!';
								} else {
									if(mysqli_num_rows($userGamesPlayedQry) == 1){
										$userGamesPlayedRow = mysqli_fetch_assoc($userGamesPlayedQry);
										$total_played 		= $userGamesPlayedRow['total_played'];
										$total_won 			= $userGamesPlayedRow['total_won'];
										$total_game_winning = $userGamesPlayedRow['total_game_winning'] + $tournament_winning - $current_tournament_winning;
										$data = new \stdClass();
										if($current_is_winner == 0 && $is_winner == 1){
											$data->total_won 	= $total_won + 1;
										}elseif($current_is_winner == 1 && $is_winner == 0){
											$data->total_won 	= $total_won - 1;
										}else{
											$data->total_won 	= $total_won;
										}
								    	$data->total_game_winning 	= $total_game_winning;
								    	$data->updated_at 			= $datetime;
								    	$conditionString = "user_id=$user_id AND game_id=$game_id";
									    $updateResult = editRecordFromTable("user_games_played", $data, null, $conditionString);
									} else {
										$data = new \stdClass();
									    $data->user_id 			  = $user_id;
									    $data->game_id 			  = $game_id;
								    	$data->total_played 	  = 1;
								    	$data->total_game_winning = $tournament_winning;
								    	if($is_winner == 1){
											$data->total_won 	= 1;
										}else{
											$data->total_won 	= 0;	
										}
								    	$data->updated_at 	= $datetime;
									    $updateResult = insertToTable("user_games_played", $data);
									}
									
									if (!$updateResult['mysqli_error']) {
										
										$delCondition = "user_id=$user_id AND tournament_id=$tournament_id AND transaction_purpose='Tournament Win'";
										$deleteResult = removeRecordFromTable("transaction_history", $delCondition, $isFullCondition = true);
										if (!$deleteResult['mysqli_error']) {
											if($tournament_winning != ''){
												$data = new \stdClass();
											    $data->user_id 				= $user_id;
											    $data->tournament_id 		= $tournament_id;
											    $data->transaction_purpose	= 'Tournament Win';
											    $data->transaction_method 	= ucfirst(str_replace("_", " ", $tournamentRow['tournament_prize']));
											    $data->transaction_datetime = $datetime;
											    $data->credit 				= $tournament_winning;
											    $data->updated_at 			= $datetime;
											    
											    $insertResult = insertToTable("transaction_history", $data);
												if (!$insertResult['mysqli_error']) {
													$arrResult['error'] = false;
											        $arrResult['message'] = 'User match result update successfull!';
											    } else {
											        $arrResult['message'] = 'User match result update failed! Error while updating transaction!';
											    }
											}else{
												$arrResult['error'] = false;
											    $arrResult['message'] = 'User match result update successfull!';
											}
										}else{
											$arrResult['message'] = 'User match result update failed! Error while deleting transaction detail!';
										}											
								    } else {
								        $arrResult['message'] = 'User match result update failed! Error while updating user games played data!';
								    }
								}
						    } else {
						        $arrResult['message'] = 'User match result update failed! Error while updating user data!';
						    }
						}
					}else{
						$arrResult['message'] = 'User not found! Match result update failed!';
					}
			    } else {
			        $arrResult['message'] = 'User match result update failed! Error while updating tournament user details!';
			    }
			}else{
				$arrResult['message'] = 'User match result update failed! Tournament user not found!';
			}
			
		} else {
			$arrResult['message'] = 'User match result update failed! No data found for the tournament!';
		}
		
		if ($arrResult['error']) {
	        $con->rollback();
	    } else {
	        $con->commit();
	    }
	    
	    $con->autocommit(true);
	    
	} else {
		$arrResult['message'] = 'Result update fail!';
	}
	
	echo json_encode($arrResult);
?>