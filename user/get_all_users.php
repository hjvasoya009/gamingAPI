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

$searchValue = strtolower($searchValue);

if($searchValue == 'inactive'){
	$statusValue = 0;
}elseif($searchValue == 'active'){
	$statusValue = 1;
}elseif($searchValue == 'suspended'){
	$statusValue = 2;
}else{
	$statusValue = '';
}

## Search
$searchQuery     = " ";
if($searchValue != ''){
	$searchQuery = " AND (username LIKE '%".$searchValue."%' OR first_name LIKE '%".$searchValue."%' OR last_name LIKE '%".$searchValue."%' OR mobile_number LIKE '%".$searchValue."%' OR wallet_balance+referral_balance LIKE '%".$searchValue."%' OR ga_coins LIKE '%".$searchValue."%' OR matches_played LIKE '%".$searchValue."%' OR referrals_count LIKE '%".$searchValue."%' OR user_status = '$statusValue') ";
}

## Total number of records without filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM users");
$records               = mysqli_fetch_assoc($sel);
$totalRecords          = $records['allcount'];

## Total number of records with filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM users WHERE 1 ".$searchQuery);
$records               = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery              = "SELECT * FROM users WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords            = mysqli_query($con, $empQuery);
$data                  = array();

while($row = mysqli_fetch_assoc($empRecords)){
	if($row['user_status'] == 0){
		$status = 'Inactive';
	}elseif($row['user_status'] == 1){
		$status = 'Active';
	}elseif($row['user_status'] == 2){
		$status = 'Suspended';
	}
	$data[] = array(
		"id"				=> $row['id'],
		"username"			=> $row['username'],
		/*"first_name"   		=> $row['first_name'].' '.$row['last_name'],*/
		"first_name"   		=> '<a class="pointer" id="user_'.$row['id'].'" title="View User" data-id="'.$row['id'].'" data-toggle="modal" data-target="#userModal">'.$row['first_name'].' '.$row['last_name'].'</a>',
		"mobile_number" 	=> $row['mobile_number'],
		"balance"  			=> $row['wallet_balance'] + $row['referral_balance'],
		"coins"    			=> $row['ga_coins'],
		"matches_played"    => $row['matches_played'],
		"referrals_count"  	=> $row['referrals_count'],
		"user_status"    	=> $status,
		"status"    		=> $row['user_status'],
		"isActive"    		=> $row['isActive']
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
