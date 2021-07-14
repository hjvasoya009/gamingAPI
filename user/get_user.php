<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
		$conditionString = "id=".$_POST['user_id'];
		$rs = fetchFromTable("users", null, $conditionString);	
		$row = mysqli_fetch_assoc($rs);
		
		if($row['user_image'] != ''){
			$user_image = MAIN_URL.'/images/user_images/'.$row['user_image'];
		}elseif($row['social_image'] != ''){
			$user_image = $row['social_image'];
		}else{
			$user_image = MAIN_URL.'/images/user_images/img_avatar.png';
		}
		
		if($row['referred_by'] != ''){
			$referredByConditionString = "id=".$row['referred_by'];
			$referredByColumns = array("first_name", "last_name");
			$referredByQry = fetchFromTable("users", $referredByColumns, $referredByConditionString);	
			$referredByRow = mysqli_fetch_assoc($referredByQry);
			$referredByUser = $referredByRow['first_name'].' '.$referredByRow['last_name'];
		}else{
			$referredByUser = '';
		}
		
?>
		<div class="row mb-15">
			<div class="col-md-12">
				<div class="profile-head">
					<div class="profile-img text-center">
						<img src="<?= $user_image ?>" alt="Avatar">
					</div>
					<h5 class="text-center mb-15"><?= $row['username'] ?></h5>
			<?php
				if($row['user_status'] == 1){
			?>
					<form class="text-center mb-10" name="sendBonusForm" id="sendBonusForm">
						<input type="hidden" value="send_bonus" name="form_name" />
						<input type="hidden" value="<?= $row['id'] ?>" name="user_id" />
						<input type="submit" value="Bonus" id="sendBonus" name="sendBonus" class="btn btn-secondary btn-info" />
					</form>
			<?php
				}
			?>
					<ul class="nav nav-pills" id="myTab" role="tablist">
						<li class="nav-item active">
							<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true" aria-expanded="true">Profile</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="kyc-tab" data-toggle="tab" href="#kyc" role="tab" aria-controls="kyc" aria-selected="false" aria-expanded="false">KYC</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="balances-tab" data-toggle="tab" href="#balances" role="tab" aria-controls="balances" aria-selected="false" aria-expanded="false">Balances</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="referrals-tab" data-toggle="tab" href="#referrals" role="tab" aria-controls="referrals" aria-selected="false" aria-expanded="false">Referrals</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="adjustamount-tab" data-toggle="tab" href="#adjustamount" role="tab" aria-controls="adjustamount" aria-selected="false" aria-expanded="false">Adjust Amount</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="adjustcoin-tab" data-toggle="tab" href="#adjustcoin" role="tab" aria-controls="adjustcoin" aria-selected="false" aria-expanded="false">Adjust Coin</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="notification-tab" data-toggle="tab" href="#notification" role="tab" aria-controls="notification" aria-selected="false" aria-expanded="false">Send Notification</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="gameuser-tab" data-toggle="tab" href="#gameuser" role="tab" aria-controls="gameuser" aria-selected="false" aria-expanded="false">Game Username</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="gamehistory-tab" data-toggle="tab" href="#gamehistory" role="tab" aria-controls="gamehistory" aria-selected="false" aria-expanded="false">Game History</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="deposit-tab" data-id="<?= $_POST['user_id'] ?>" data-toggle="tab" href="#deposit" role="tab" aria-controls="deposit" aria-selected="false" aria-expanded="false">Deposit History</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="transaction-tab" data-id="<?= $_POST['user_id'] ?>" data-toggle="tab" href="#transaction" role="tab" aria-controls="transaction" aria-selected="false" aria-expanded="false">Transaction History</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="withdrawal-tab" data-id="<?= $_POST['user_id'] ?>" data-toggle="tab" href="#withdrawal" role="tab" aria-controls="withdrawal" aria-selected="false" aria-expanded="false">Withdrawal History</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12">
				<div class="tab-content profile-tab" id="myTabContent">
					<div class="tab-pane fade active in" id="profile" role="tabpanel" aria-labelledby="profile-tab">
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<label>UserName</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['username'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Email</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['email'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Mobile No.</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['mobile_number'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Paytm No.</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['paytm_number'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>PayUmoney No.</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['payumoney_number'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Games Played</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['matches_played'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Games Won</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['matches_won'] ?></p>
								</div>
							</div>
							
							<div class="col-md-6">
								<div class="col-md-4">
									<label>User Level</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['user_level'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>DOB</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['dob'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Gender</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['gender'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Balance</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['wallet_balance'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Referral Code</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['referral_code'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Referrals</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['referrals_count'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Referred By</label>
								</div>
								<div class="col-md-8">
									<p><?= $referredByUser ?></p>
								</div>
							</div>
						</div>
					</div>
					
			<?php
				$img_name = 'img_avatar.png';
				if($row['kyc_image'] != ''){
					$img_name = $row['kyc_image'];
				}
			?>
					
					<div class="tab-pane fade" id="kyc" role="tabpanel" aria-labelledby="kyc-tab">
						<div class="row">
							<div class="col-md-4">
								<label>Identity Proof</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['kyc_proof'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Full Name</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['kyc_name'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Card Photo</label>
							</div>
							<div class="col-md-8">
								<div class="box" style="margin: 0 0 10px;">
									<a href="#" class="kyc_img">
										<img id="kyc_img_src" src="<?= MAIN_URL ?>/images/kyc_images/<?= $img_name ?>" style="height:75px; width:75px;" height="75" width="75">
									</a>
									<!--<div class="modal fade" id="kyc_image" tabindex="-1" role="dialog" aria-labelledby="kyc_imageModalLabel" aria-hidden="true">
										<div class="modal-dialog" role="document">
											<div class="modal-content">
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">
														×
													</span>
												</button>
												<div class="modal-body">
													<img src="<?= MAIN_URL ?>/images/kyc_images/img_avatar.png" height="75" width="75">
												</div>
											</div>
										</div>
									</div>-->
								</div>
								<div>
									<button type="button" class="btn btn-secondary btn-info" id="remove_kyc" data-id="<?= $_POST['user_id'] ?>">
										Remove
									</button>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Bank Name</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['bank_name'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Account Holder Name</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['bank_ac_name'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Account Number</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['bank_ac_no'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>IFSC CODE</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['bank_ifsc'] ?></p>
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="balances" role="tabpanel" aria-labelledby="balances-tab">
						<div class="row">
							<div class="col-md-4">
								<label>Wallet</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['wallet_balance'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>GA Coins</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['ga_coins'] ?></p>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<label>Referral</label>
							</div>
							<div class="col-md-8">
								<p><?= $row['referral_balance'] ?></p>
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="referrals" role="tabpanel" aria-labelledby="referrals-tab">
						<div class="row">
							<div class="col-sm-12">
								<table id="ref_datatable" class="table table-striped table-bordered dataTable no-footer">
									<thead>
										<tr>
											<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Username</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Verified</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Amount</th>
											
										</tr>
									</thead>
									<tbody>
								<?php
									$referralQry = mysqli_query($con, "SELECT u.username, u.otp_verify, th.transaction_datetime, th.credit FROM transaction_history AS th INNER JOIN users AS u ON u.id=th.referral_user_id WHERE th.transaction_method='Referral' AND user_id=".$_POST['user_id']);	
									
                                	$i = 1;
                                	while($referralRow = mysqli_fetch_assoc($referralQry)){
								?>
										<tr>
                                    		<td><?= $i ?></td>
                                    		<td><?= $referralRow['transaction_datetime'] ?></td>
                                    		<td><?= $referralRow['username'] ?></td>
                                    		<td><?= ($referralRow['otp_verify'] == 0) ? 'NO' : 'YES' ?></td>
                                    		<td class="text-right"><?= $referralRow['credit'] ?></td>
                                    	</tr>
								<?php
										$i++;
									}
                                ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="adjustamount" role="tabpanel" aria-labelledby="adjustamount-tab">
						<div class="row">
							<form name="adjustAmountForm" id="adjustAmountForm">
								<input type="hidden" value="amountAdjust" name="form_name" />
								<input type="hidden" value="<?= $row['id'] ?>" name="user_id" />
								<div class="col-md-2 mb-5">
									<select name="adjust_type" class="form-control">
										<option value="plus">+</option>
										<option value="minus">-</option>
									</select>
								</div>
								<div class="col-md-3 mb-5">
                                    <input type="number" class="form-control" name="adjustBalance" id="adjustBalance" placeholder="Amount" />
								</div>
								<div class="col-md-4 mb-5">
									<input type="text" class="form-control" name="adjustAmountReason" id="adjustAmountReason" placeholder="Reason" />
								</div>
								<div class="col-md-3 mb-5">
									<input type="submit" name="submit" id="adjustAmountSubmit" class="btn btn-info" value="Adjust">
								</div>
							</form>
						</div>
						<div class="row mt-15">
							<div class="col-sm-12">
								<h5>Wallet Adjustment History</h5>
								<table id="walletAdjustDatatable" class="table table-striped table-bordered dataTable no-footer">
									<thead>
										<tr>
											<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Reason</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Amount</th>
										</tr>
									</thead>
									<tbody>
								<?php
									$conditionString = "transaction_method='Balance Adjust' AND user_id=".$_POST['user_id'];
								    $columns = array("id", "transaction_datetime", "credit", "debit", "transaction_purpose");
								    $orderBy = "id DESC";
									$adjustAmountQry = fetchFromTable("transaction_history", $columns, $conditionString, $orderBy);	
									
                                	while($adjustAmountRow = mysqli_fetch_assoc($adjustAmountQry)){
                                		if($adjustAmountRow['credit'] != 0){
				                			$amount = $adjustAmountRow['credit'];
											$textColor = 'text-success';
										}elseif($adjustAmountRow['debit'] != 0){
											$textColor = 'text-info';
											$amount = $adjustAmountRow['debit'];
										}else{
											$textColor = '';
											$amount = '0.00';
										}
								?>
										<tr>
                                    		<td><?= $adjustAmountRow['id'] ?></td>
                                    		<td><?= $adjustAmountRow['transaction_datetime'] ?></td>
                                    		<td><?= $adjustAmountRow['transaction_purpose'] ?></td>
                                    		<td class="text-right <?= $textColor ?>"><?= $amount ?></td>
                                    	</tr>
								<?php
										$i++;
									}
                                ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="adjustcoin" role="tabpanel" aria-labelledby="adjustcoin-tab">
						<div class="row">
							<form name="adjustCoinForm" id="adjustCoinForm">
								<input type="hidden" value="coinAdjust" name="form_name" />
								<input type="hidden" value="<?= $row['id'] ?>" name="user_id" />
								<div class="col-md-2 mb-5">
									<select name="adjust_type" class="form-control">
										<option value="plus">+</option>
										<option value="minus">-</option>
									</select>
								</div>
								<div class="col-md-3 mb-5">
                                    <input type="number" class="form-control" name="adjustCoin" id="adjustCoin" placeholder="Coins" />
								</div>
								<div class="col-md-4 mb-5">
									<input type="text" class="form-control" name="adjustCoinReason" id="adjustCoinReason" placeholder="Reason" />
								</div>
								<div class="col-md-3 mb-5">
									<input type="submit" name="submit" id="adjustCoinSubmit" class="btn btn-info" value="Adjust">
								</div>
							</form>
						</div>
						<div class="row mt-15">
							<div class="col-sm-12">
								<h5>GA Coin Adjustment History</h5>
								<table id="coinAdjustDatatable" class="table table-striped table-bordered dataTable no-footer">
									<thead>
										<tr>
											<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Reason</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Coins</th>
											
										</tr>
									</thead>
									<tbody>
								<?php
									$conditionString = "transaction_method='Coin Adjust' AND user_id=".$_POST['user_id'];
								    $columns = array("id", "transaction_datetime", "credit", "debit", "transaction_purpose");
								    $orderBy = "id DESC";
									$adjustAmountQry = fetchFromTable("transaction_history", $columns, $conditionString, $orderBy);	
									
                                	while($adjustAmountRow = mysqli_fetch_assoc($adjustAmountQry)){
                                		if($adjustAmountRow['credit'] != 0){
				                			$coins = $adjustAmountRow['credit'];
											$textColor = 'text-success';
										}elseif($adjustAmountRow['debit'] != 0){
											$textColor = 'text-info';
											$coins = $adjustAmountRow['debit'];
										}else{
											$textColor = '';
											$coins = '0.00';
										}
								?>
										<tr>
                                    		<td><?= $adjustAmountRow['id'] ?></td>
                                    		<td><?= $adjustAmountRow['transaction_datetime'] ?></td>
                                    		<td><?= $adjustAmountRow['transaction_purpose'] ?></td>
                                    		<td class="text-right <?= $textColor ?>"><?= $coins ?></td>
                                    	</tr>
								<?php
										$i++;
									}
                                ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
						<div class="row">
							<form name="sendUserNotification" id="sendUserNotification">
								<input type="hidden" value="send_notification" name="form_name" />
								<input type="hidden" value="<?= $row['id'] ?>" name="user_id" />
								<div class="form-group col-md-12 mb-5">
									<div class="col-md-9">
										<label class="control-label">
											Message
										</label>
										<textarea class="form-control" rows="4" id="text_message" name="text_message" required></textarea>
									</div>
								</div>
								<div class="form-group col-md-12 mb-5">
									<div class="col-md-3">
										<div class="form-check form-check-inline">
											<input type="radio" class="form-check-input" id="selectsmsallmyuser1" value="sms" name="send_method" checked />
											<label class="form-check-label" for="selectsmsallmyuser1">
												SMS
											</label>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-check form-check-inline">
											<input type="radio" class="form-check-input" id="selectsmsallmyuser2" value="mail" name="send_method">
											<label class="form-check-label" for="selectsmsallmyuser2">
												MAIL
											</label>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-check form-check-inline">
											<input type="radio" class="form-check-input" id="selectsmsallmyuser3" value="both" name="send_method">
											<label class="form-check-label" for="selectsmsallmyuser3">
												BOTH
											</label>
										</div>
									</div>
								</div>
								<div class="form-group col-md-12 mb-5">
									<input type="submit" name="submit" id="sendNotification" class="btn btn-info" value="Send">
								</div>
							</form>
						</div>
					</div>
					
					<div class="tab-pane fade" id="gameuser" role="tabpanel" aria-labelledby="gameuser-tab">
			<?php
		        $gameQry = fetchFromTable("game");
		        if ($gameQry){
		            while ($gameRow = $gameQry->fetch_assoc()){
						$GameUsersQry = mysqli_query($con, "SELECT game_username FROM games_username WHERE user_id=".$_POST['user_id']." AND game_id=".$gameRow['id']);
						$GameUsersRow = mysqli_fetch_assoc($GameUsersQry);
			?>
						<div class="row">
							<div class="col-md-4">
								<label><?= $gameRow['gamename'] ?></label>
							</div>
							<div class="col-md-8">
								<p><?= $GameUsersRow['game_username'] ?></p>
							</div>
						</div>
		    <?php
					}
				}
			?>
					</div>
					
					<div class="tab-pane fade" id="gamehistory" role="tabpanel" aria-labelledby="gamehistory-tab">
						<div class="row">
							<div class="col-md-3 mb-5">
								<input type="hidden" value="<?= $row['id'] ?>" name="user_id" id="user_id" />
								<select class="form-control" name="game_id" id="game_id">
									<option value="">Select Game</option>
							<?php
								$conditionString = "isActive=1";
								$rs = fetchFromTable("game", null, $conditionString);
								while($row = $rs->fetch_assoc()){
							?>
									<option value="<?= $row['id'] ?>"><?= $row['gamename'] ?></option>
							<?php
								}
							?>
								</select>
							</div>
						</div>
						<div class="row mt-15" id="gamesPlayed">
							
						</div>
					</div>
					
					<div class="tab-pane fade" id="deposit" role="tabpanel" aria-labelledby="deposit-tab">
						
					</div>
					
					<div class="tab-pane fade" id="transaction" role="tabpanel" aria-labelledby="transaction-tab">
						
					</div>
					
					<div class="tab-pane fade" id="withdrawal" role="tabpanel" aria-labelledby="withdrawal-tab">
						
					</div>
					
				</div>
			</div>
		</div>
<?php
	}
	die;