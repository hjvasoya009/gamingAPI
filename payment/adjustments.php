<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if (!isLoggedIn()) {
	    redirectToLoginPage();
	}
	$arrResult['error'] = true;
	$form_name = $_POST['form_name'];

	if($form_name == 'amountAdjust'){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			} 
	    }
	    
	    $conditionString = "id=".$user_id;
	    $columns = array("wallet_balance", "username");
		$rs = fetchFromTable("users", $columns, $conditionString);	
		$row = mysqli_fetch_assoc($rs);
		$current_wallet_balance = $row['wallet_balance'];
		$username = $row['username'];
	    
	    $data = new \stdClass();
	    $data->user_id 				= $user_id;
	    $data->transaction_purpose	= $adjustAmountReason;
	    $data->transaction_method 	= 'Balance Adjust';
	    $data->transaction_datetime = $datetime;
	    if($adjust_type == 'plus'){
			$data->credit 			= $adjustBalance;
		}elseif($adjust_type == 'minus'){
			$data->debit 			= $adjustBalance;
		}
	    $data->updated_at 			= $datetime;
	    
	    $insertResult = insertToTable("transaction_history", $data);
	    
		if (!$insertResult['mysqli_error']) {
			
			$data = new \stdClass();
			
			if($adjust_type == 'plus'){
				$data->wallet_balance = $current_wallet_balance + $adjustBalance;
			}elseif($adjust_type == 'minus'){
				$data->wallet_balance = $current_wallet_balance - $adjustBalance;
			}
	    	
		    $updateResult = editRecordFromTable("users", $data, $user_id);
			if (!$updateResult['mysqli_error']) {
		        $arrResult['error'] = false;
		        $arrResult['message'] = 'Wallet Balance update successful for '.$username.'!';
		        $arrResult['data'] = $data;
		    } else {
		        $arrResult['message'] = 'Adjust balance transaction successful! Wallet Balance update failed for '.$username.'!';
		    }
	    } else {
	        $arrResult['message'] = 'Adjust balance failed for '.$username.'!';
	    }
			    
	    echo json_encode($arrResult);
	    
	} elseif ($form_name == "coinAdjust"){
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			} 
	    }
	    
	    $conditionString = "id=".$user_id;
	    $columns = array("ga_coins", "username");
		$rs = fetchFromTable("users", $columns, $conditionString);	
		$row = mysqli_fetch_assoc($rs);
		$current_ga_coins_balance = $row['ga_coins'];
		$username = $row['username'];
	    
	    $data = new \stdClass();
	    $data->user_id 				= $user_id;
	    $data->transaction_purpose	= $adjustCoinReason;
	    $data->transaction_method 	= 'Coin Adjust';
	    $data->transaction_datetime = $datetime;
	    if($adjust_type == 'plus'){
			$data->credit 			= $adjustCoin;
		}elseif($adjust_type == 'minus'){
			$data->debit 			= $adjustCoin;
		}
	    $data->updated_at 			= $datetime;
	    
	    $insertResult = insertToTable("transaction_history", $data);
	    
		if (!$insertResult['mysqli_error']) {
			
			$data = new \stdClass();
			
			if($adjust_type == 'plus'){
				$data->ga_coins = $current_ga_coins_balance + $adjustCoin;
			}elseif($adjust_type == 'minus'){
				$data->ga_coins = $current_ga_coins_balance - $adjustCoin;
			}
	    	
		    $updateResult = editRecordFromTable("users", $data, $user_id);
			if (!$updateResult['mysqli_error']) {
		        $arrResult['error'] = false;
		        $arrResult['message'] = 'Coin Balance update successful for '.$username.'!';
		        $arrResult['data'] = $data;
		    } else {
		        $arrResult['message'] = 'Adjust coin balance transaction successful! Coin Balance update failed for '.$username.'!';
		    }
	    } else {
	        $arrResult['message'] = 'Adjust coin balance failed for '.$username.'!';
	    }
			    
	    echo json_encode($arrResult);
	}
?>