<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['game_id']) && !empty($_POST['game_id'])){
		
		$gameQry = fetchFromTable("game",null,$_POST['game_id']);
        if ($gameQry){
            $gameRow = $gameQry->fetch_assoc();
			$gameName = $gameRow['gamename'];
		}
		
		$gameUsersQry = mysqli_query($con,"SELECT u.username, ugp.total_played FROM users AS u INNER JOIN user_games_played AS ugp ON u.id=ugp.user_id WHERE u.user_status=1 AND ugp.game_id=".$_POST['game_id']." ORDER BY u.id");
?>
		<div class="row">
			<div class="col-sm-12">
				<h5><?= $gameName ?> Users</h5>
				<table id="gameUsers_datatable" class="table table-striped table-bordered dataTable no-footer">
					<thead>
						<tr>
							<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Username</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Games Played</th>
						</tr>
					</thead>
					<tbody>
			<?php
            	$i = 1;
            	if (mysqli_num_rows($gameUsersQry) > 0){
            		while($gameUsersRow = mysqli_fetch_assoc($gameUsersQry)){
			?>
						<tr>
                    		<td><?= $i ?></td>
                    		<td><?= $gameUsersRow['username'] ?></td>
                    		<td><?= $gameUsersRow['total_played'] ?></td>
                    	</tr>
			<?php
						$i++;
					}
				}
            ?>
					</tbody>
				</table>
			</div>
		</div>
<?php
	}
	die;