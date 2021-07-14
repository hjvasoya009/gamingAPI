<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
		$user_id = $_POST['user_id'];
		$game_id = $_POST['game_id'];
		$statisticsSql = "SELECT tu.is_winner, tu.is_game_counted, tu.win_prize, tu.tournament_winning, t.match_title, t.match_datetime, t.isActive FROM tournament_users AS tu INNER JOIN tournament AS t ON t.id = tu.tournament_id WHERE tu.user_id=$user_id AND t.game_id=$game_id ORDER BY t.match_datetime";
		$statisticsRes = mysqli_query($con,$statisticsSql);
		
?>
		<div class="col-sm-12">
			<h5>Game History</h5>
			<table id="gamesPlayedDatatable" class="table table-striped table-bordered dataTable no-footer">
				<thead>
					<tr>
						<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>
						<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
						<th style="text-align: center; font-weight: bold; width: 0px;">Tournament</th>
						<th style="text-align: center; font-weight: bold; width: 0px;">Won Prize</th>
						<th style="text-align: center; font-weight: bold; width: 0px;">W/L</th>
					</tr>
				</thead>
				<tbody>
			<?php
            	$i = 1;
            	while($statisticsRow = $statisticsRes->fetch_assoc()){
            		if(is_numeric($statisticsRow['win_prize'])){
						$won_prize = $statisticsRow['tournament_winning'];
					}else{
						$won_prize = $statisticsRow['win_prize'];
					}
					
					if($statisticsRow['isActive'] == 2 && $statisticsRow['is_winner'] == 1){
						$won_lost = 'Won';
					}elseif($statisticsRow['isActive'] == 2 && $statisticsRow['is_winner'] == 0){
						$won_lost = 'Lost';
					}else{
						$won_lost = 'No Result';
					}
			?>
					<tr>
                		<td><?= $i ?></td>
                		<td><?= $statisticsRow['match_datetime'] ?></td>
                		<td><?= $statisticsRow['match_title'] ?></td>
                		<td class="text-center"><?= $won_prize ?></td>
                		<td><?= $won_lost ?></td>
                	</tr>
			<?php
					$i++;
				}
            ?>
				</tbody>
			</table>
		</div>
<?php
	}
	die;