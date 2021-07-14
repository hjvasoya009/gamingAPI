<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
/*print_r($_POST);
die;*/
## Read value
$draw            = $_POST['draw'];
$row             = $_POST['start'];
$rowperpage      = $_POST['length']; // Rows display per page
$columnIndex     = $_POST['order'][0]['column']; // Column index
$columnName      = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue     = $_POST['search']['value']; // Search value
$statusValue     = $_POST['columns'][6]['search']['value'];

if($statusValue == ''){
	$statusValue = 2;
}

/*$searchValue = strtolower($searchValue);*/

/*if($searchValue == 'rejected'){
	$statusValue = 0;
}elseif($searchValue == 'accepted'){
	$statusValue = 1;
}elseif($searchValue == 'pending'){
	$statusValue = 2;
}elseif($searchValue == 'kyc pending'){
	$statusValue = 3;
}*/

## Search
$searchQuery     = " ";
if($searchValue != ''){
	$searchQuery = " AND (u.username LIKE '%".$searchValue."%' OR u.first_name LIKE '%".$searchValue."%' OR u.last_name LIKE '%".$searchValue."%' OR wh.withdraw_method LIKE'%".$searchValue."%' OR wh.amount LIKE'%".$searchValue."%') ";
}

if($statusValue != 'all' && $statusValue != ''){
	$searchQuery .= " AND (wh.withdraw_status = '".$statusValue."') ";
}

if($columnName == 'first_name'){
	$columnName = 'u.'.$columnName;
}else{
	$columnName = 'wh.'.$columnName;
}

## Total number of records without filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM withdrawal_history AS wh LEFT JOIN users AS u ON u.id = wh.user_id");
$records               = mysqli_fetch_assoc($sel);
$totalRecords          = $records['allcount'];

## Total number of records with filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM withdrawal_history AS wh LEFT JOIN users AS u ON u.id = wh.user_id WHERE 1 ".$searchQuery);
$records               = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery              = "SELECT wh.*, u.username, u.first_name, u.last_name FROM withdrawal_history AS wh LEFT JOIN users AS u ON u.id = wh.user_id WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords            = mysqli_query($con, $empQuery);
$data                  = array();

while($row = mysqli_fetch_assoc($empRecords)){
	if($row['withdraw_status'] == 0){
		$status = 'Rejected';
	}elseif($row['withdraw_status'] == 1){
		$status = 'Approved';
	}elseif($row['withdraw_status'] == 2){
		$status = 'Pending';
	}elseif($row['withdraw_status'] == 3){
		$status = 'KYC Pending';
	}
	
	$data[] = array(
		"id"				=> $row['id'],
		"withdraw_datetime"	=> $row['withdraw_datetime'],
		"username"   		=> '<a class="pointer" id="user_'.$row['user_id'].'" title="View User" data-id="'.$row['user_id'].'" data-toggle="modal" data-target="#userModal">'.$row['username'].'</a>',
		"first_name"   		=> '<a class="pointer" id="user_'.$row['user_id'].'" title="View User" data-id="'.$row['user_id'].'" data-toggle="modal" data-target="#userModal">'.$row['first_name'].' '.$row['last_name'].'</a>',
		"withdraw_method" 	=> $row['withdraw_method'],
		"amount"  			=> $row['amount'],
		"withdraw_status"   => $status,
		"rejection_reason"  => $row['rejection_reason'],
		"status"    		=> $row['withdraw_status']
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
