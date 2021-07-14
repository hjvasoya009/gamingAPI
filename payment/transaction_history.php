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

## Search
$searchQuery     = " ";
if($searchValue != ''){
	$searchQuery = " AND (u.first_name LIKE '%".$searchValue."%' OR u.last_name LIKE '%".$searchValue."%' OR th.transaction_purpose LIKE'%".$searchValue."%' OR th.transaction_method LIKE'%".$searchValue."%' OR th.credit LIKE'%".$searchValue."%' OR th.debit LIKE'%".$searchValue."%' OR th.transaction_datetime = '".$searchValue."') ";
}

if($columnName == 'first_name'){
	$columnName = 'u.'.$columnName;
}else{
	$columnName = 'th.'.$columnName;
}

## Total number of records without filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM transaction_history AS th LEFT JOIN users AS u ON u.id = th.user_id");
$records               = mysqli_fetch_assoc($sel);
$totalRecords          = $records['allcount'];

## Total number of records with filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM transaction_history AS th LEFT JOIN users AS u ON u.id = th.user_id WHERE 1 ".$searchQuery);
$records               = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery              = "SELECT th.*, u.first_name, u.last_name FROM transaction_history AS th LEFT JOIN users AS u ON u.id = th.user_id WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords            = mysqli_query($con, $empQuery);
$data                  = array();

while($row = mysqli_fetch_assoc($empRecords)){
	
	if($row['credit'] != 0.00){
		$amount = '<strong class="text-right text-success">'.$row['credit'].'</strong>';
	}elseif($row['debit'] != 0.00){
		$amount = '<strong class="text-right text-info">'.$row['debit'].'</strong>';
	}else{
		$amount = 0.00;
	}
	
	$data[] = array(
		"id"					=> $row['id'],
		"transaction_datetime"	=> $row['transaction_datetime'],
		"first_name"   			=> '<a class="pointer" id="user_'.$row['user_id'].'" title="View User" data-id="'.$row['user_id'].'" data-toggle="modal" data-target="#userModal">'.$row['first_name'].' '.$row['last_name'].'</a>',
		"transaction_method" 	=> $row['transaction_method'],
		"transaction_purpose" 	=> $row['transaction_purpose'],
		"amount"  				=> $amount,
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
