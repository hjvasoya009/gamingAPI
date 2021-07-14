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

if($columnName == 'first_name'){
	$columnName = 'u.first_name';
}


## Search
$searchQuery     = " ";
if($searchValue != ''){
	$searchQuery = " AND (dh.transaction_id LIKE '%".$searchValue."%' OR dh.payment_gateway LIKE '%".$searchValue."%' OR dh.amount LIKE'%".$searchValue."%' OR u.first_name LIKE '%".$searchValue."%' OR u.last_name LIKE '%".$searchValue."%') ";
}

## Total number of records without filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM deposit_history AS dh INNER JOIN users AS u ON u.id = dh.user_id");
$records               = mysqli_fetch_assoc($sel);
$totalRecords          = $records['allcount'];

## Total number of records with filtering
$sel                   = mysqli_query($con,"SELECT COUNT(*) AS allcount FROM deposit_history AS dh INNER JOIN users AS u ON u.id = dh.user_id WHERE 1 ".$searchQuery);
$records               = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery              = "SELECT dh.*, u.first_name, u.last_name FROM deposit_history AS dh INNER JOIN users AS u ON u.id = dh.user_id WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords            = mysqli_query($con, $empQuery);
$data                  = array();
$count=0;
while($row = mysqli_fetch_assoc($empRecords)){
	$count++;
	$data[] = array(
		"id"				=> $row['id'],
		"user_id"			=> $row['user_id'],
		"first_name"   		=> $row['first_name'].' '.$row['last_name'],
		"transaction_id" 	=> $row['transaction_id'],
		"payment_gateway"  	=> $row['payment_gateway'],
		"payment_datetime"	=> $row['payment_datetime'],
		"amount"    		=> $row['amount']
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
