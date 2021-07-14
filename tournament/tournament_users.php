<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['tournament_id']) && !empty($_POST['tournament_id'])){
		$tournament_id = $_POST['tournament_id'];
		$tournamentUserSQL = "SELECT u.id, u.username FROM tournament_users AS tu INNER JOIN users AS u ON tu.user_id=u.id WHERE tu.tournament_id = ".$tournament_id;
		$tournamentUserResult = mysqli_query($con, $tournamentUserSQL);
		while($tournamentUserRow = mysqli_fetch_assoc($tournamentUserResult)){
?>
			<option value="<?= $tournamentUserRow['id'] ?>"><?= $tournamentUserRow['username'] ?></option>
<?php			
		}
	}
	
	die;