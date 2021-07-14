<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;
	$error = true;

	if(isset($_POST) && !empty($_POST)){
		
		$conditionString 	= 'id=1';
		$settingQry 	 	= fetchFromTable("settings",null,$conditionString);
		$settingRow 	 	= $settingQry->fetch_assoc();
		$free_match_per_day = $settingRow['free_match'];
		$max_match_per_day 	= $settingRow['max_match'];
		
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
		
		if(!isset($_POST['game_id']) || empty($_POST['game_id'])){
			$response['message'] = 'Please enter game id';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['game_username']) || empty($_POST['game_username'])){
			$response['message'] = 'Please enter game username';
			echo json_encode($response);
			exit();
		}
		
		if(!isset($_POST['steam_text_required']) || $_POST['steam_text_required'] == ''){
			$response['message'] = 'Please enter steam text required or not';
			echo json_encode($response);
			exit();
		}elseif(isset($_POST['steam_text_required']) && $_POST['steam_text_required'] == 1){
			if(!isset($_POST['game_steam_username']) || empty($_POST['game_steam_username'])){
				$response['message'] = 'Please enter game steam username';
				echo json_encode($response);
				exit();
			}
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
		
		$conditionString = "id=$user_id AND bearer_token='$bearer_token' AND user_status=1";
		$userQry = fetchFromTable("users",null,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
			
			$tournamentTeamQry = mysqli_query($con, "SELECT MAX(team_id) as team_id FROM tournament_users WHERE tournament_id=$tournament_id");
			$tournamentTeamRow = mysqli_fetch_assoc($tournamentTeamQry);
			if($tournamentTeamRow['team_id'] == NULL){
				$team_id = 1;
			}else{
				$team_id = $tournamentTeamRow['team_id'] + 1;
			}
			
			$conditionString = "user_id=$user_id AND tournament_id=$tournament_id";
			$tournamentUserQry = fetchFromTable("tournament_users",null,$conditionString);
			
			if(mysqli_num_rows($tournamentUserQry) == 0){
				
				$conditionString = "id=$tournament_id AND isActive=1";
				$tournamentQry = fetchFromTable("tournament",null,$conditionString);
				
				if(mysqli_num_rows($tournamentQry) == 1){
					
					$tournamentRow = mysqli_fetch_assoc($tournamentQry);
					
					$match_datetime		= $tournamentRow['match_datetime'];
					$tournament_type_id	= $tournamentRow['tournament_type_id'];
					$entry_fee			= $tournamentRow['entry_fee'];
					$joined_users		= $tournamentRow['joined_users'];
					$player_limit		= $tournamentRow['player_limit'];
					
					$joining_date		= date('d-m-Y', strtotime($match_datetime));
					
					if($player_limit != $joined_users){
						
						$conditionString = "user_id=$user_id AND game_id=$game_id AND tournament_type_id=$tournament_type_id AND request_accepted=1";
						$teamNameQry = fetchFromTable("user_game_mode_data",null,$conditionString);
						if(mysqli_num_rows($teamNameQry) > 0){
							$teamNameRow = mysqli_fetch_assoc($teamNameQry);
							$team_name = $teamNameRow['team_name'];
						}else{
							$team_name = ''; 
						}
						
						
						$conditionString = "user_id=$user_id AND game_id=$game_id AND tournament_type_id=$tournament_type_id AND request_accepted=1";
						$membersQry = fetchFromTable("user_game_mode_data",null,$conditionString);
						$numberOfMembers = mysqli_num_rows($membersQry);
						
						if($numberOfMembers + $joined_users + 1 <= $player_limit){
							
							if($tournament_type_id == 1){
								
								$freeMatchQry = mysqli_query($con, "SELECT count(*) AS free_matches FROM tournament_users AS tu INNER JOIN tournament AS t ON t.id=tu.tournament_id WHERE t.entry_fee=0 AND DATE(t.match_datetime) = DATE('$match_datetime') AND tu.user_id=$user_id");
								$freeMatchRow = mysqli_fetch_assoc($freeMatchQry);
								$free_matches_played = $freeMatchRow['free_matches'];
								
								$maxMatchQry = mysqli_query($con, "SELECT count(*) AS max_matches FROM tournament_users AS tu INNER JOIN tournament AS t ON t.id=tu.tournament_id WHERE DATE(t.match_datetime) = DATE('$match_datetime') AND tu.user_id=$user_id");
								$maxMatchRow = mysqli_fetch_assoc($maxMatchQry);
								$max_matches_played = $maxMatchRow['max_matches'];
								
								if($max_matches_played < $max_match_per_day){
									
									if($entry_fee == 0 && $free_matches_played >= $free_match_per_day){
										$response['message'] = 'You have reached free match play limit for '.$joining_date.'.';
									}else{
										$con->autocommit(false);
										
										$userRow = mysqli_fetch_assoc($userQry);
										
										$referral_balance	= $userRow['referral_balance'];
										$wallet_balance 	= $userRow['wallet_balance'];
										$matches_played 	= $userRow['matches_played'];
										$ga_coins 			= $userRow['ga_coins'];
										
										if($tournamentRow['tournament_prize'] == 'money' || $tournamentRow['tournament_prize'] == 'other'){
											
											if(($referral_balance + $wallet_balance) >= $entry_fee){
												
												$data = new \stdClass();
											    $data->matches_played 	= $matches_played + 1;
												
												if($referral_balance < $entry_fee){
													$entry_fee_from_wallet 		= $entry_fee - $referral_balance;
													$entry_fee_from_referral	= $entry_fee - $entry_fee_from_wallet;
													
													$wallet_balance_left 	= $wallet_balance - $entry_fee_from_wallet;
													$referral_balance_left	= $referral_balance - $entry_fee_from_referral;
													
												    $data->wallet_balance 	= floatPartial($wallet_balance_left);
												    $data->referral_balance = floatPartial($referral_balance_left);
												}else{
													$referral_balance_left	= $referral_balance - $entry_fee;
													$data->referral_balance = floatPartial($referral_balance_left);
												}
												
												$error = false;
											}else{
												$response['message'] = 'You do not have enough balance to join tournament.';
											}
											
										}elseif($tournamentRow['tournament_prize'] == 'coins'){
											
											if($ga_coins >= $entry_fee){
												$data = new \stdClass();
											    $data->matches_played 	= $matches_played + 1;
											    $data->ga_coins 		= $ga_coins - $entry_fee;
												
												$error = false;
											}else{
												$response['message'] = 'You do not have enough GA Coins to join tournament.';
											}
											
										}else{
											$response['message'] = 'Tournament prize not matched! Please try again later or contact our support team!';
										}
															
										if(!$error){
											
											$updateResult = editRecordFromTable("users", $data, $user_id);
											if (!$updateResult['mysqli_error']) {
												$data = new \stdClass();
											    $data->user_id 				= $user_id;
											    $data->tournament_id 		= $tournament_id;
											    $data->transaction_purpose	= 'Tournament Join';
											    $data->transaction_method 	= ucfirst(str_replace("_", " ", $tournamentRow['tournament_prize']));
											    $data->transaction_datetime = $datetime;
											    $data->debit 				= $entry_fee;
											    $data->updated_at 			= $datetime;
											    
											    $insertResult = insertToTable("transaction_history", $data);
												if (!$insertResult['mysqli_error']) {
													$joined_users = $joined_users + 1;
													$data = new \stdClass();
											    	$data->joined_users = $joined_users;
									    			
												    $updateResult = editRecordFromTable("tournament", $data, $tournament_id);
													if (!$updateResult['mysqli_error']) {
														
														$data = new \stdClass();
											    		$data->tournament_id = $tournament_id;
											    		$data->team_id 		 = $team_id;
											    		$data->user_id 		 = $user_id;
											    		$data->entry_fee 	 = $entry_fee;
											    		$data->game_username = $game_username;
											    		if($steam_text_required == 1){
															$data->game_steam_username = $game_steam_username;
														}
											    		$data->entry_datetime 	 = $datetime;
											    		
														$insertResult = insertToTable("tournament_users", $data);
														if (!$insertResult['mysqli_error']){
															
															$condition = "game_id=$game_id AND user_id=$user_id";
															$deleteResult = removeRecordFromTable("games_username", $condition, $isFullCondition = true);
															
															if(!$deleteResult['mysqli_error']){
																
																$data = new \stdClass();
													    		$data->user_id 		 = $user_id;
													    		$data->game_id 		 = $game_id;
													    		$data->game_username = $game_username;
													    		$data->updated_at 	 = $datetime;
																if($steam_text_required == 1){
																	$data->game_steam_username = $game_steam_username;
																}
																
																$insertResult = insertToTable("games_username", $data);
																if (!$insertResult['mysqli_error']){
																
	//User Games Played
	$conditionString = "user_id=$user_id AND game_id=$game_id";
	$userGamesPlayedQry = fetchFromTable("user_games_played",null,$conditionString);

	if(mysqli_num_rows($userGamesPlayedQry) > 1){
		$response['message'] = 'Tournament join failed! Error while updating user game played data!';
	} else {
		if (mysqli_num_rows($userGamesPlayedQry) == 1){
			$userGamesPlayedRow = mysqli_fetch_assoc($userGamesPlayedQry);
			$total_played 		= $userGamesPlayedRow['total_played'];
			
			$data = new \stdClass();
			$data->total_played = $total_played + 1;
			$data->updated_at 	= $datetime;
			
			$conditionString 	= "user_id=$user_id AND game_id=$game_id";
		    $updateResult 		= editRecordFromTable("user_games_played", $data, null, $conditionString);
		    
		} else {
			$data = new \stdClass();
		    $data->user_id 		= $user_id;
		    $data->game_id 		= $game_id;
			$data->total_played = 1;
			$data->total_won 	= 0;
			$data->updated_at 	= $datetime;
		    $updateResult 		= insertToTable("user_games_played", $data);
		}
		if (!$updateResult['mysqli_error']){
			$response['status'] = 1;
			$response['message'] = 'Tournament joined successfully!';
		}else{
			$response['message'] = 'Tournament join failed! Error while updating user game played data!';
		}
	}
	//User Games Played
																}else{
											        				$response['message'] = 'Tournament join failed! Error while updating old game username!';
																}
															}else{
																$response['message'] = 'Tournament join failed! Error while updating old game username!';
															}
														}else{
									        				$response['message'] = 'Tournament join failed! Error while updating tournament user data!';
														}
												    } else {
												        $response['message'] = 'Tournament join failed! Error while updating total joined user!';
												    }
											    } else {
											        $response['message'] = 'Tournament join failed! Error while updating transaction!';
											    }
										    } else {
										        $response['message'] = 'Tournament join failed! Error while updating user data!';
										    }
										}
										
										if ($response['status'] == 0) {
									        $con->rollback();
									    } else {
									        $con->commit();
									    }
									    
									    $con->autocommit(true);
									}
										
								}else{
									$response['message'] = 'You have reached maximum match play limit for '.$joining_date.'.';
								}
								
							/*}elseif(($tournament_type_id == 2 && $numberOfMembers == 1) ||($tournament_type_id == 3 && $numberOfMembers == 3) || ($tournament_type_id == 4 && $numberOfMembers >= 4 && $numberOfMembers <= 19)){*/
							}elseif(($tournament_type_id == 2) || ($tournament_type_id == 3) || ($tournament_type_id == 4)){
								
								$con->autocommit(false);
								
								$membersArray[] = array(user_id=>$user_id, game_username=>$game_username, team_name=>$team_name);
								
								while($membersRow = mysqli_fetch_assoc($membersQry)){
									$team_name = $membersRow['team_name'];
									$membersArray[] = array(user_id=>$membersRow['member_user_id'], game_username=>$membersRow['member_game_username'], team_name=>$team_name);
								}
								
								foreach($membersArray as $userID){
									
									$response['status']  = 0;
									$error = true;
									
									$user_id 		= $userID['user_id'];
									$game_username 	= $userID['game_username'];
									$team_name 		= $userID['team_name'];
									
									$freeMatchQry = mysqli_query($con, "SELECT count(*) AS free_matches FROM tournament_users AS tu INNER JOIN tournament AS t ON t.id=tu.tournament_id WHERE t.entry_fee=0 AND DATE(t.match_datetime) = DATE('$match_datetime') AND tu.user_id=$user_id");
									$freeMatchRow = mysqli_fetch_assoc($freeMatchQry);
									$free_matches_played = $freeMatchRow['free_matches'];
									
									$maxMatchQry = mysqli_query($con, "SELECT count(*) AS max_matches FROM tournament_users AS tu INNER JOIN tournament AS t ON t.id=tu.tournament_id WHERE DATE(t.match_datetime) = DATE('$match_datetime') AND tu.user_id=$user_id");
									$maxMatchRow = mysqli_fetch_assoc($maxMatchQry);
									$max_matches_played = $maxMatchRow['max_matches'];
									
									if($max_matches_played < $max_match_per_day){
										
										if($entry_fee == 0 && $free_matches_played >= $free_match_per_day){
											$response['status'] = 0;
											$response['message'] = 'You or one of your team member have reached free match play limit for '.$joining_date.'.';
											break;
										}else{
											$conditionString = "user_id=$user_id AND tournament_id=$tournament_id";
											$tournamentUserQry = fetchFromTable("tournament_users",null,$conditionString);
											
											if(mysqli_num_rows($tournamentUserQry) == 0){
												$conditionString = "id=$user_id";
												$columns = array("id", "username", "referral_balance", "wallet_balance", "matches_played", "ga_coins", "user_status");
												$userQry = fetchFromTable("users", $columns, $conditionString);
												if(mysqli_num_rows($userQry) == 1){
													$userRow = mysqli_fetch_assoc($userQry);
													if($userRow['user_status'] == 1){
														
														$username 			= $userRow['username'];
														$referral_balance	= $userRow['referral_balance'];
														$wallet_balance 	= $userRow['wallet_balance'];
														$matches_played 	= $userRow['matches_played'];
														$ga_coins 			= $userRow['ga_coins'];
														
														if($tournamentRow['tournament_prize'] == 'money' || $tournamentRow['tournament_prize'] == 'other'){
															
															if(($referral_balance + $wallet_balance) >= $entry_fee){
																
																$data = new \stdClass();
															    $data->matches_played 	= $matches_played + 1;
																
																if($referral_balance < $entry_fee){
																	$entry_fee_from_wallet 		= $entry_fee - $referral_balance;
																	$entry_fee_from_referral	= $entry_fee - $entry_fee_from_wallet;
																	
																	$wallet_balance_left 	= $wallet_balance - $entry_fee_from_wallet;
																	$referral_balance_left	= $referral_balance - $entry_fee_from_referral;
																	
																    $data->wallet_balance 	= floatPartial($wallet_balance_left);
																    $data->referral_balance = floatPartial($referral_balance_left);
																}else{
																	$referral_balance_left	= $referral_balance - $entry_fee;
																	$data->referral_balance = floatPartial($referral_balance_left);
																}
																
																$error = false;
															}else{
																$response['message'] = $username.' does not have enough balance to join tournament.';
															}
															
														}elseif($tournamentRow['tournament_prize'] == 'coins'){
															
															if($ga_coins >= $entry_fee){
																$data = new \stdClass();
															    $data->matches_played 	= $matches_played + 1;
															    $data->ga_coins 		= $ga_coins - $entry_fee;
																
																$error = false;
															}else{
																$response['message'] = $username.' does not have enough GA Coins to join tournament.';
															}
															
														}else{
															$response['message'] = 'Tournament prize not matched! Please try again later or contact our support team!';
														}
																			
														if(!$error){
															
															$updateResult = editRecordFromTable("users", $data, $user_id);
															if (!$updateResult['mysqli_error']) {
																$data = new \stdClass();
															    $data->user_id 				= $user_id;
															    $data->tournament_id 		= $tournament_id;
															    $data->transaction_purpose	= 'Tournament Join';
															    $data->transaction_method 	= ucfirst(str_replace("_", " ", $tournamentRow['tournament_prize']));
															    $data->transaction_datetime = $datetime;
															    $data->debit 				= $entry_fee;
															    $data->updated_at 			= $datetime;
															    
															    $insertResult = insertToTable("transaction_history", $data);
																if (!$insertResult['mysqli_error']) {
																	$joined_users = $joined_users + 1;
																	$data = new \stdClass();
															    	$data->joined_users = $joined_users;
													    			
																    $updateResult = editRecordFromTable("tournament", $data, $tournament_id);
																	if (!$updateResult['mysqli_error']) {
																		
																		$data = new \stdClass();
															    		$data->tournament_id 	= $tournament_id;
															    		$data->team_id 		 	= $team_id;
															    		$data->team_name 	 	= $team_name;
															    		$data->user_id 		 	= $user_id;
															    		$data->entry_fee 	 	= $entry_fee;
															    		$data->game_username 	= $game_username;
															    		$data->entry_datetime 	= $datetime;
															    		if($steam_text_required == 1){
																			$data->game_steam_username = $game_steam_username;
																		}
															    		
																		$insertResult = insertToTable("tournament_users", $data);
																		if (!$insertResult['mysqli_error']){
																			
																			$condition = "game_id=$game_id AND user_id=$user_id";
																			$deleteResult = removeRecordFromTable("games_username", $condition, $isFullCondition = true);
																			
																			if(!$deleteResult['mysqli_error']){
																				
																				$data = new \stdClass();
																	    		$data->user_id 		 = $user_id;
																	    		$data->game_id 		 = $game_id;
																	    		$data->game_username = $game_username;
																	    		$data->updated_at 	 = $datetime;
																				if($steam_text_required == 1){
																					$data->game_steam_username = $game_steam_username;
																				}
																				
																				$insertResult = insertToTable("games_username", $data);
																				if (!$insertResult['mysqli_error']){
	//User Games Played
	$conditionString = "user_id=$user_id AND game_id=$game_id";
	$userGamesPlayedQry = fetchFromTable("user_games_played",null,$conditionString);

	if(mysqli_num_rows($userGamesPlayedQry) > 1){
		$response['message'] = 'Tournament join failed! Error while updating user game played data!';
	} else {
		if (mysqli_num_rows($userGamesPlayedQry) == 1){
			$userGamesPlayedRow = mysqli_fetch_assoc($userGamesPlayedQry);
			$total_played 		= $userGamesPlayedRow['total_played'];
			
			$data = new \stdClass();
			$data->total_played = $total_played + 1;
			$data->updated_at 	= $datetime;
			
			$conditionString 	= "user_id=$user_id AND game_id=$game_id";
		    $updateResult 		= editRecordFromTable("user_games_played", $data, null, $conditionString);
		    
		} else {
			$data = new \stdClass();
		    $data->user_id 		= $user_id;
		    $data->game_id 		= $game_id;
			$data->total_played = 1;
			$data->total_won 	= 0;
			$data->updated_at 	= $datetime;
		    $updateResult 		= insertToTable("user_games_played", $data);
		}
		if (!$updateResult['mysqli_error']){
			$response['status'] = 1;
			$response['message'] = 'Tournament joined successfully!';
		}else{
			$response['message'] = 'Tournament join failed! Error while updating user game played data!';
		}
	}
	//User Games Played
																				}else{
																					$response['status'] = 0;
															        				$response['message'] = 'Tournament join failed! Error while updating old game username!';
															        				break;
																				}
																			}else{
																				$response['status'] = 0;
																				$response['message'] = 'Tournament join failed! Error while updating old game username!';
																				break;
																			}
																		}else{
																			$response['status'] = 0;
													        				$response['message'] = 'Tournament join failed! Error while updating tournament user data!';
													        				break;
																		}
																    } else {
																    	$response['status'] = 0;
																        $response['message'] = 'Tournament join failed! Error while updating total joined user!';
																        break;
																    }
															    } else {
															    	$response['status'] = 0;
															        $response['message'] = 'Tournament join failed! Error while updating transaction!';
															        break;
															    }
														    } else {
														    	$response['status'] = 0;
														        $response['message'] = 'Tournament join failed! Error while updating user data!';
														        break;
														    }
														}
													}else{
														$response['status'] = 0;
														$response['message'] = 'One of your member\'s account is inactive or suspended!';
														break;
													}
													
												}else{
													$response['status'] = 0;
													$response['message'] = 'One of your member\'s account is inactive or suspended!';
													break;
												}
											}else{
												$response['status'] = 0;
												$response['message'] = 'One of your member already joined the tournament!';
												break;
											}
										}
									}else{
										$response['status'] = 0;
										$response['message'] = 'You or one of your team member have reached maximum match play limit for '.$joining_date.'.';
										break;
									}
								}
								
								if ($response['status'] == 0) {
							        $con->rollback();
							    } else {
							        $con->commit();
							    }
							    
							    $con->autocommit(true);
							}else{
								$response['message'] = 'Something went wrong! You do not have enough members in your team to join tournament. Please add member in user game mode data and try again.';
							}
						}else{
							$response['message'] = 'Not enough spots left to join in this tournament!';
						}
					}else{
						$response['message'] = 'Tournament full!';
					}
				}else{
					$response['message'] = 'Tournament not exist anymore!';
				}
			}else{
				$response['message'] = 'You have already joined the tournament!';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Unauthorized access detected!';
		}
	}
	echo json_encode($response);
	exit();
?>