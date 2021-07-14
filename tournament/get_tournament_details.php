<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['tournament_id']) && !empty($_POST['tournament_id'])){
		$tournament_id = $_POST['tournament_id'];
		$tournamentSQL = "SELECT t.*, g.gamename, gp.gameplatform, tt.tournamenttype FROM tournament AS t INNER JOIN game AS g ON t.game_id=g.id INNER JOIN game_platform AS gp ON t.game_platform_id=gp.id INNER JOIN tournament_type AS tt ON t.tournament_type_id=tt.id WHERE t.id = ".$tournament_id;
		$getResult = mysqli_query($con, $tournamentSQL);
		$row = mysqli_fetch_assoc($getResult);
		if($row['isActive'] == 1){
			$activeTxt = 'End Tournament';
			$alterStatus = 2;
			$current_status = '<p class="text-success">Active</p>';
		}elseif($row['isActive'] == 2){
			$activeTxt = 'Active Tournament';
			$alterStatus = 1;
			$current_status = '<p class="text-info">Completed</p>';
		}elseif($row['isActive'] == 0){
			$activeTxt = 'Inactive Tournament';
			$alterStatus = 1;
			$current_status = '<p class="text-danger">Inactive</p>';
		}
?>
	<div class="modal-body">
		<div class="row mb-15">
			<div class="col-md-12">
				<div class="profile-head">
					<div class="float-right">
						<a href="add-match-result.php?tournament_id=<?= $tournament_id ?>" id="tournamentResult" class="btn btn-secondary btn-success mr-10" target="_blank">Match Result</a>
				<?php
					$datetime_now = new DateTime();
					$datetime_game = new DateTime($row['match_datetime']);
					$interval = $datetime_now->diff($datetime_game);
					$elapsed = ($interval->format('%a')*24*60*60)+($interval->format('%h')*60*60)+($interval->format('%i')*60)+($interval->format('%s'));
					$timeLeft = (int)($interval->format('%r').$elapsed);
					/*if($timeLeft > 0 ){*/
					if($row['isActive'] != 2 ){
				?>
						<a href="edit-tournament.php?id=<?= $tournament_id ?>" target="_blank" class="btn btn-secondary btn-primary mr-10">
							Edit
						</a>
				<?php
					}
				?>
					<a id="tournamentStatus" data-tstatus-id="<?= $alterStatus ?>" onclick="tournamentUpdate('1','isActive',<?= $tournament_id ?>)" class="btn btn-secondary btn-info mr-10"><?= $activeTxt ?></a>
				
					<a id="tournamentStatusDel" data-tstatus-id="0" onclick="tournamentUpdate('','isActive',<?= $tournament_id ?>)" class="btn btn-secondary btn-danger">Delete</a>
					</div>
					<div class="profile-img tournament-img">
						<img src="<?= MAIN_URL ?>/images/tournament_images/<?= $row['match_image'] ?>" alt="Avatar">
					</div>
					<h5 class="text-center mb-15"><?= $row['match_title'] ?></h5>
					<ul class="nav nav-pills" id="myTab" role="tablist">
						<li class="nav-item active">
							<a class="nav-link" id="tournament-tab" data-toggle="tab" href="#tournament" role="tab" aria-controls="tournament" aria-selected="true" aria-expanded="true">Tournament</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="winnerSlot-tab" data-toggle="tab" href="#winnerSlot" role="tab" aria-controls="winnerSlot" aria-selected="false" aria-expanded="false">Winner Slot</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="roomDetail-tab" data-toggle="tab" href="#roomDetail" role="tab" aria-controls="roomDetail" aria-selected="false" aria-expanded="false">Room Detail</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="streamingLink-tab" data-toggle="tab" href="#streamingLink" role="tab" aria-controls="streamingLink" aria-selected="false" aria-expanded="false">Streaming Link</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="tournamentUsers-tab" data-toggle="tab" href="#tournamentUsers" role="tab" aria-controls="tournamentUsers" aria-selected="false" aria-expanded="false">Joined Users</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="tournamentUsersNotification-tab" data-toggle="tab" href="#tournamentUsersNotification" role="tab" aria-controls="tournamentUsersNotification" aria-selected="false" aria-expanded="false">Notification Joined Users</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="tab-content profile-tab" id="myTabContent">
					<div class="tab-pane fade active in" id="tournament" role="tabpanel" aria-labelledby="tournament-tab">
						<div class="row">
							<div class="col-md-6">
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Match Date: </label>
								</div>
								<div class="col-md-8">
									<p><?= $row['match_datetime'] ?></p>
								</div>
							</div>
							<div class="col-md-6">
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Match Status: </label>
								</div>
								<div class="col-md-8">
									<?= $current_status ?>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<label>Game</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['gamename'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Type</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['tournamenttype'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Entry Fee</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['entry_fee'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Per Kill</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['per_kill'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Player Limit</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['player_limit'] ?></p>
								</div>
							</div>
							
							<div class="col-md-6">
								<div class="col-md-4">
									<label>Game Platform</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['gameplatform'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Map</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['map'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Win Prize</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['win_prize'] ?></p>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-4">
									<label>Version</label>
								</div>
								<div class="col-md-8">
									<p><?= $row['version'] ?></p>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-3">
									<label>Match Description</label>
								</div>
								<div class="col-md-9">
									<p><?= nl2br($row['description']) ?></p>
								</div>
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="winnerSlot" role="tabpanel" aria-labelledby="winnerSlot-tab">
						<div class="row">
							<div class="col-sm-12">
								<table id="tournamentUsersDatatable" class="table table-striped table-bordered dataTable no-footer">
									<thead>
										<tr>
											<th style="text-align: center; font-weight: bold; width: 0px;">#</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Slot Size</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Prize</th>
										</tr>
									</thead>
									<tbody>
								<?php
									$columns = array("slot_size", "slot_prize");
									$conditionString = "tournament_id=".$tournament_id;
							        $slotQry = fetchFromTable("tournament_winner_slots", $columns, $conditionString);
							        $count = 1;
                                	while($slotRow = $slotQry->fetch_assoc()){
								?>
										<tr>
                                    		<td><?= $count ?></td>
                                    		<td><?= $slotRow['slot_size'] ?></td>
                                    		<td><?= $slotRow['slot_prize'] ?></td>
                                    	</tr>
								<?php
										$count++;
									}
                                ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="roomDetail" role="tabpanel" aria-labelledby="roomDetail-tab">
						<div class="row">
							<div class="form-group col-md-6 mb-5">
								<label class="control-label mb-10" for="room_id">Room ID</label>
								<input type="text" class="form-control" name="room_id" value="<?= $row['room_id'] ?>" placeholder="Room ID" onchange="tournamentUpdate(this.value,'room_id',<?= $tournament_id ?>)" />
							</div>
							<div class="form-group col-md-6 mb-5">
								<label class="control-label mb-10" for="password">Password</label>
								<input type="text" class="form-control" name="password" value="<?= $row['password'] ?>" placeholder="Password" onchange="tournamentUpdate(this.value,'password',<?= $tournament_id ?>)" />
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="streamingLink" role="tabpanel" aria-labelledby="streamingLink-tab">
						<div class="row">
							<div class="form-group col-md-6 mb-5">
								<label class="control-label mb-10" for="streaming_link">Streaming Link</label>
								<input type="text" class="form-control" name="streaming_link" value="<?= $row['streaming_link'] ?>" placeholder="Streaming Link" onchange="tournamentUpdate(this.value,'streaming_link',<?= $tournament_id ?>)" />
							</div>
						</div>
					</div>
					
					<div class="tab-pane fade" id="tournamentUsers" role="tabpanel" aria-labelledby="tournamentUsers-tab">
						<div class="row">
							<div class="col-sm-12">
								<table id="tournamentUsersDatatable" class="table table-striped table-bordered dataTable no-footer">
									<thead>
										<tr>
											<th style="text-align: center; font-weight: bold; width: 0px;">#</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Username</th>
											<th style="text-align: center; font-weight: bold; width: 0px;">Game Username</th>
										</tr>
									</thead>
									<tbody>
								<?php
                                	$i = 1;
                                	$tournamentUsersSQL = "SELECT tu.game_username, u.id AS userid, u.username FROM tournament_users AS tu INNER JOIN users AS u ON u.id=tu.user_id WHERE tu.tournament_id = ".$tournament_id." ORDER BY tu.id";
									$tournamentUsersResult = mysqli_query($con, $tournamentUsersSQL);
									
                                	while($tournamentUsersRow = mysqli_fetch_assoc($tournamentUsersResult)){
								?>
										<tr>
                                    		<td><?= $i ?></td>
                                    		<td><a class="pointer" id="user_<?= $tournamentUsersRow['userid'] ?>" title="View User" data-id="<?= $tournamentUsersRow['userid'] ?>" data-toggle="modal" data-target="#userModal"><?= $tournamentUsersRow['username'] ?></a></td>
                                    		<td><?= $tournamentUsersRow['game_username'] ?></td>
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
					
					<div class="tab-pane fade" id="tournamentUsersNotification" role="tabpanel" aria-labelledby="tournamentUsersNotification-tab">
						<div class="row">
							<div class="col-sm-12">
								<a id="tournamentNotification" onclick="notifyTournamentUSers(<?= $tournament_id ?>)" class="btn btn-secondary btn-info mr-10">Notify Joined Users</a>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary btn-info float-lg-right" data-dismiss="modal">
			Close
		</button>
	</div>
	<script>
		function tournamentUpdate(value,column_name,id) {
			
			if(column_name == 'isActive'){
				if(value == 1){
					value = $('#tournamentStatus').data('tstatus-id');
				}else{
					value = $('#tournamentStatusDel').data('tstatus-id');
				}
				if(confirm('Are you sure? Do you want to proceed?')){
					$.ajax({
				        type: 'POST',
				        url: "services/tournament/update_tournament_detail.php",
						data: {value:value, column_name:column_name, id:id},
						success: function(data) {
							data = JSON.parse(data);
							if(data.error) {
				                showAlert('error', 'Opps! ',data.message);
				            }else {
				                showAlert('success', 'Successful',data.message);
				            }
				            if(column_name == 'isActive'){
								if(value == 1){
									$('#tournamentStatus').html('End Tournament');
									$('#tournamentStatus').data('tstatus-id', 2);
								}else if(value == 2){
									$('#tournamentStatus').html('Active Tournament');
									$('#tournamentStatus').data('tstatus-id', 1);
								}else if(value == 0){
									$('#tournamentStatus').html('Inactive Tournament');
									$('#tournamentStatus').data('tstatus-id', 1);
								}
							}
						}
				    });
				}
			}else{
				$.ajax({
			        type: 'POST',
			        url: "services/tournament/update_tournament_detail.php",
					data: {value:value, column_name:column_name, id:id},
					success: function(data) {
						data = JSON.parse(data);
						if(data.error) {
			                showAlert('error', 'Opps! ',data.message);
			            }else {
			                showAlert('success', 'Successful',data.message);
			            }
			            if(column_name == 'isActive'){
							if(value == 1){
								$('#tournamentStatus').html('End Tournament');
								$('#tournamentStatus').data('tstatus-id', 2);
							}else if(value == 2){
								$('#tournamentStatus').html('Active Tournament');
								$('#tournamentStatus').data('tstatus-id', 1);
							}else if(value == 0){
								$('#tournamentStatus').html('Inactive Tournament');
								$('#tournamentStatus').data('tstatus-id', 1);
							}
						}
					}
			    });
			}
		}
		
		
		function notifyTournamentUSers(id) {
			$.ajax({
		        type: 'POST',
		        url: "services/tournament/notify_tournament_users.php",
				data: {id:id},
				success: function(data) {
					data = JSON.parse(data);
					if(data.error) {
		                showAlert('error', 'Opps! ',data.message);
		            }else {
		                showAlert('success', 'Successful',data.message);
		            }
				}
		    });
		}
		
	</script>
<?php
	}
	die;