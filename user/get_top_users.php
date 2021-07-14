<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}

## Read value
$draw            = $_POST['draw'];
$row             = $_POST['start'];
$rowperpage      = $_POST['length']; // Rows display per page
$columnIndex     = $_POST['order'][0]['column']; // Column index
$columnName      = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue     = $_POST['search']['value']; // Search value

$gameValue     = $_POST['columns'][6]['search']['value'];

## Search
$searchQuery     = " ";
if($searchValue != ''){
	$searchQuery = " AND (u.username LIKE '%".$searchValue."%' OR u.first_name LIKE '%".$searchValue."%' OR u.last_name LIKE '%".$searchValue."%' OR u.mobile_number LIKE '%".$searchValue."%') ";
}

if($gameValue != ''){
	$searchQuery .= " AND (ugp.game_id = '".$gameValue."') ";
}

$searchQuery .= " GROUP BY ugp.user_id ";

## Total number of records without filtering
//$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM user_games_played AS ugp INNER JOIN users AS u ON u.id=ugp.user_id WHERE total_winnings > 0");
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM user_games_played AS ugp INNER JOIN users AS u ON u.id=ugp.user_id WHERE 1");
$records               = mysqli_fetch_assoc($sel);
$totalRecords          = $records['allcount'];

## Total number of records with filtering
//$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM user_games_played AS ugp INNER JOIN users AS u ON u.id=ugp.user_id WHERE total_winnings > 0 ".$searchQuery);
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM user_games_played AS ugp INNER JOIN users AS u ON u.id=ugp.user_id WHERE 1 ".$searchQuery);
$records               = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

 //GROUP BY ugp.user_id


## Fetch records
/*$empQuery              = "SELECT * FROM users WHERE total_winnings > 0 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;*/
$empQuery              = "SELECT ugp.user_id, u.username, u.first_name, u.last_name, u.mobile_number, ugp.game_id, SUM(ugp.total_game_winning) AS total_game_winning, SUM(ugp.total_played) AS total_played FROM user_games_played AS ugp INNER JOIN users AS u ON u.id=ugp.user_id WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords            = mysqli_query($con, $empQuery);
$data                  = array();

while($row = mysqli_fetch_assoc($empRecords)){
	$data[] = array(
		"user_id"			=> $row['user_id'],
		"username"			=> $row['username'],
		"first_name"   		=> '<a class="pointer" id="user_'.$row['user_id'].'" title="View User" data-id="'.$row['user_id'].'" data-toggle="modal" data-target="#userModal">'.$row['first_name'].' '.$row['last_name'].'</a>',
		"mobile_number" 	=> $row['mobile_number'],
		"matches_played"    => $row['total_played'],
		"total_winnings"    => $row['total_game_winning'],
		"game_id"    		=> $gameValue
	);
}

## Response
$response = array(
	"draw"                => intval($draw),
	"iTotalRecords"       => $totalRecords,
	"iTotalDisplayRecords"=> $totalRecordwithFilter,
	"aaData"              => $data
);

echo json_encode($response);
