<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;

	if(isset($_POST) && !empty($_POST)){
		
		$conditionString = 'id=1';
		$settingQry 	 = fetchFromTable("settings",null,$conditionString);
		$settingRow 	 = $settingQry->fetch_assoc();
		$support_email 	 = $settingRow['support_mail'];
			
		foreach ($_POST as $key => $val) {
	        if (!is_array($val))
	            $$key = mysqli_real_escape_string($con, $val);
	    }
	    
		$data = new \stdClass();
		$data->$column_name = $value;
	    $insertResult = editRecordFromTable("tournament", $data, $id);
	    if (!$insertResult['mysqli_error']) {
	    	if($column_name == 'isActive' && $value == '2'){
	    		$conditionString = "id=".$id;
		        $tournamentQry = fetchFromTable("tournament", null, $conditionString);
		        if(mysqli_num_rows($tournamentQry) == 1){		        	
		        	$tournamentRow = mysqli_fetch_assoc($tournamentQry);
		        	$tournament_prize = $tournamentRow['tournament_prize'];
		        	
		        	if($tournament_prize == 'coins'){
						$prizeSymbol = '<i class="fa fa-empire"></i>';
					}else{
						$prizeSymbol = '₹';
					}
		        	
		        	$totalWinnersQry = mysqli_query($con,"SELECT count(*) AS number_of_winners FROM tournament_users WHERE tournament_id=$id AND is_winner=1");
		        	$totalWinnersRow = mysqli_fetch_assoc($totalWinnersQry);
		        	$totalWinners = $totalWinnersRow['number_of_winners'];
		        	
					$tournamentUsersQry = mysqli_query($con, "SELECT u.first_name, u.last_name, u.username, u.email, u.mobile_number, u.user_status, tu.* FROM tournament_users AS tu INNER JOIN users AS u ON u.id=tu.user_id WHERE tu.tournament_id=$id");
					while($tournamentUsersRow = mysqli_fetch_assoc($tournamentUsersQry)){
						if($tournamentUsersRow['is_winner'] == 1){
							
							$to      = $tournamentUsersRow['email'];
							$subject = "Congratulations! You are winner in ".$tournamentRow['match_title'];
$winner_mail = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>
	<body>
		<div style="padding:0px 0 0px 0">
			<div class="movableContent">
				<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" class="container">
					<tr>
						<td valign="top" align="center">
							<div class="contentEditableContainer contentImageEditable">
								<div class="contentEditable" align="center" >
									<img src="http://demo.axoneinfotech.com/gamingakhada/dist/img/big_logo.png" height="200px"  alt="Logo"  data-default="placeholder" style="border-radius: 1000px;" />
								</div>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div style="padding:20px 5px 0 5px">
				<div style="margin:0 auto;max-width:600px;padding:0px;border-radius:4px">
					<table width="100%" style="border-spacing:0;background-color:#fff;border:0px">
						<tbody>


							<tr>
								<td style="padding:32px 30px 0px 30px" align="center">
									<div style="font-family:\'Montserrat\',Arial,Helvetica;font-size:24px;line-height:27px;font-weight:700;color:#404040">
										'.$tournamentUsersRow['first_name'].' '.$tournamentUsersRow['last_name'].' ('.$tournamentUsersRow['username'].')
										<br />
										Congratulations!
									</div>
								</td>
							</tr>

							<tr>
								<td style="padding:20px 30px 0px 30px" align="center">
									<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;font-weight:normal;color:#404040">
										You’ve won
									</div>
								</td>
							</tr>

							<tr>
								<td style="padding:8px 30px 0px 30px" align="center">
									<div style="font-family:\'Montserrat\',Arial,Helvetica;font-size:32px;line-height:1;color:#46ae49">
										'.$prizeSymbol.'
										<span style="font-weight:700">
											'.$tournamentUsersRow['tournament_winning'].'
										</span>
									</div>
								</td>

							</tr>
							<tr>
								<td style="padding:14px 30px 0px 30px" align="center">
									<table style="width:100%;max-width:334px">
										<tbody>
											<tr>
												<td align="center">
													<div style="font-family:\'Helvetica\',Arial;font-size:14px;font-weight:normal;color:#555555;line-height:1">
														'.$tournamentRow['match_title'].'
													</div>
													<div style="font-family:\'Helvetica\',Arial;font-size:14px;font-weight:normal;color:#b0b0b0;line-height:1;margin-top:9px">
														'.date('d/m/Y', strtotime($tournamentRow['match_datetime'])).'
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>

							<tr>
								<td style="padding:33px 30px 12px 30px" align="center">
									<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:12px;line-height:1;font-weight:300;color:#b6b6b6">
										WINNING BREAKUP
									</div>
								</td>
							</tr>

							<tr>
								<td>
									<table id="m_3227339266221464490m_3559302004156779863table_content" width="100%">
										<tbody>
											<tr>
												<td style="padding-top:12px">
													<div style="border-radius:4px;overflow:hidden;border:1px solid #ebebeb;max-width:428px;margin:0 auto;width:90%;padding-top:10px">
														<table width="100%">
															<tbody>
																<tr>
																	<td align="left" style="padding-left:12px">
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:10px;line-height:1;font-weight:normal;color:#979797">
																			TOTAL WINNINGS
																		</div>
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;font-weight:normal;color:#555555;margin-top:5px">
																			'.$prizeSymbol.' '.$tournamentRow['win_prize'].'
																		</div>
																	</td>
																	<td align="right" style="padding-right:12px">
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:10px;line-height:1;font-weight:normal;color:#979797">
																			ENTRY FEE
																		</div>
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;font-weight:normal;color:#555555;margin-top:5px">
																			'.$prizeSymbol.' '.$tournamentRow['entry_fee'].'
																		</div>
																	</td>
																</tr>
																<tr>
																	<td align="left" style="padding-left:12px;padding-top:15px;padding-bottom:15px">
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:10px;line-height:1;font-weight:normal;color:#979797">
																			NO. OF WINNERS
																		</div>
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;font-weight:normal;color:#555555;margin-top:5px">
																			'.$totalWinners.'
																		</div>
																	</td>
																</tr>
															</tbody>
														</table>
														<table width="100%;" style="background:rgba(70,174,73,0.05)">
															<tbody>
																<tr>
																	<td align="left" style="padding-left:12px;padding-top:15px;padding-bottom:10px">
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:10px;line-height:1;font-weight:normal;color:#979797">
																			YOUR RANK
																		</div>
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;font-weight:normal;color:#555555;margin-top:5px">
																			#'.$tournamentUsersRow['winner_rank'].'
																		</div>
																	</td>
																	<td align="right" style="padding-right:12px;padding-top:15px;padding-bottom:10px">
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:10px;line-height:1;font-weight:normal;color:#979797">
																			YOUR WINNINGS
																		</div>
																		<div style="font-family:\'Helvetica\',Arial,Helvetica;font-size:16px;line-height:1;color:#46ae49;margin-top:5px">
																			'.$prizeSymbol.'
																			<span style="font-weight:bold">
																				'.$tournamentUsersRow['tournament_winning'].'
																			</span>
																		</div>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</body>
</html>';
							$message = $winner_mail;
							$headers  = 'MIME-Version: 1.0' . "\r\n";
                            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                            $headers .= 'X-Mailer: PHP' . "\r\n";
                            $headers .= 'From: '.$support_email.'<' . $support_email . ">\r\n";
							mail($to,$subject,$message,$headers);
						}
						/*print_r($tournamentUsersRow);*/
					}
					
				}
				
				$arrResult['error'] = false;
	        	$arrResult['message'] = 'Tournament ended and updated Successfully';
			}else{
				$arrResult['error'] = false;
	        	$arrResult['message'] = 'Record Updated Successfully';
			}
	        
	    } else {
	        $arrResult['message'] = 'Record Updation Failed';
	    }
	}else{
		$arrResult['message'] = 'Something went wrong!';
	}
	echo json_encode($arrResult);
?>