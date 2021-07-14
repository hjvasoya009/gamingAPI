<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	header('Content-Type: application/json');
	
	$arrResult['error'] = true;
/*die;*/
	if(isset($_POST) && !empty($_POST) && isset($_POST['withdraw_status'])){
		foreach ($_POST as $key => $value) {
		    $$key = mysqli_real_escape_string($con, $value);
		}
		
		if($withdraw_status == "0"){
			$withdraw_status_text = "Rejected";
		}elseif($withdraw_status == "1"){
			$withdraw_status_text = "Approved";
		}elseif($withdraw_status == "2"){
			$withdraw_status_text = "Pending";
		}

	    if (!isset($table, $keyToUpdate, $withdraw_status) || empty($table) || empty($keyToUpdate)) {
	        $arrResult['message'] = 'Body improper!';
	    } else {
	    	$con->autocommit(false);
	    	
	    	$withdrawal_id = $keyToUpdate;
	    	
	    	$conditionString = "withdraw_status != $withdraw_status AND id=$keyToUpdate";
			$columns = array("user_id", "amount", "withdraw_status", "withdraw_method");
			$withdrawHistoryQry = fetchFromTable("withdrawal_history",$columns,$conditionString);
			if(mysqli_num_rows($withdrawHistoryQry) == 1){
				$withdrawHistoryRow = $withdrawHistoryQry->fetch_assoc();
				$user_id 			= $withdrawHistoryRow['user_id'];
				$withdraw_amount 	= $withdrawHistoryRow['amount'];
				$db_withdraw_status = $withdrawHistoryRow['withdraw_status'];
				$withdraw_method 	= $withdrawHistoryRow['withdraw_method'];
				
				$conditionString = "id=$user_id AND user_status=1";
				$columns = array("wallet_balance");
				$userQry = fetchFromTable("users",$columns,$conditionString);
				if(mysqli_num_rows($userQry) == 1){
					$userRow 		= $userQry->fetch_assoc();
					$wallet_balance = $userRow['wallet_balance'];
					
					$conditionString = "user_id=$user_id AND withdrawal_id=$withdrawal_id";
					$transactionHistoryQry = fetchFromTable("transaction_history",null,$conditionString);
					if(mysqli_num_rows($transactionHistoryQry) == 1){
						$transactionHistoryRow = $transactionHistoryQry->fetch_assoc();
						$error = true;
						$delCondition = "user_id=$user_id AND withdrawal_id=$withdrawal_id";
						$deleteResult = removeRecordFromTable("transaction_history", $delCondition, $isFullCondition = true);
						if (!$deleteResult['mysqli_error']) {
							$error = false;
						}
					}else{
						$error = false;
					}
					
					if(!$error){
						$transaction_error = true;
						if($withdraw_status == "0"){
							$data = new \stdClass();
					    	$data->wallet_balance = $wallet_balance + $withdraw_amount;
			    			
						    $updateResult = editRecordFromTable("users", $data, $user_id);
							if (!$updateResult['mysqli_error']) {
						        $transaction_error = false;
						    } else {
						        $arrResult['message'] = ' Error while updating wallet balance!';
						    }
						}elseif($withdraw_status == "1"){
							$data = new \stdClass();
							$data->withdrawal_id 		= $withdrawal_id;
						    $data->user_id 				= $user_id;
						    $data->transaction_purpose	= 'Withdraw';
						    $data->transaction_method 	= $withdraw_method;
						    $data->transaction_datetime = $datetime;
						    $data->debit 				= $withdraw_amount;
						    $data->updated_at 			= $datetime;
						    
						    $insertResult = insertToTable("transaction_history", $data);
						    
							if (!$insertResult['mysqli_error']) {
								
								if($db_withdraw_status == 0){
									$data = new \stdClass();
							    	$data->wallet_balance = $wallet_balance - $withdraw_amount;
					    			
								    $updateResult = editRecordFromTable("users", $data, $user_id);
									if (!$updateResult['mysqli_error']) {
								        $transaction_error = false;
								    } else {
								        $arrResult['message'] = ' Withdraw request failed! Wallet Balance update failed!';
								    }
								}else{
									$transaction_error = false;
								}
						    } else {
						        $arrResult['message'] = ' Withdraw request failed! Transaction update failed!';
						    }
						}elseif($withdraw_status == "2"){
							if($db_withdraw_status == 0){
								$data = new \stdClass();
						    	$data->wallet_balance = $wallet_balance - $withdraw_amount;
				    			
							    $updateResult = editRecordFromTable("users", $data, $user_id);
								if (!$updateResult['mysqli_error']) {
							        $transaction_error = false;
							    } else {
							        $arrResult['message'] = ' Withdraw request failed! Wallet Balance update failed!';
							    }
							}else{
								$transaction_error = false;
							}
						}
						
						if(!$transaction_error){
							$data = new \stdClass();
					        $data->withdraw_status 	 = $withdraw_status;
					        $data->rejection_reason = '';
					        $data->updated_at 		 = $datetime;
					        
					        $insertResult = editRecordFromTable($table, $data, $keyToUpdate);
					        if (!$insertResult['mysqli_error']) {
					            $arrResult['error'] = false;
					        }else{
					            $arrResult['message'] = " Withdraw request failed! Withdrawal status update failed!";
					        }
						}else{
							$arrResult['message'] = ' Withdraw request failed! Transaction update failed!';
						}
					}
				}else{
					$arrResult['message'] = " Failed to Proceed. User account not found!";
				}
				
			}else{
				$arrResult['message'] = " Failed to Proceed. Withdraw request is already $withdraw_status_text.";
			}  	
		    
	        if ($arrResult['error']) {
		        $con->rollback();
		    } else {
		        $con->commit();
		        if($withdraw_status == 1){
					$arrResult['message'] = " Withdrawal request Approved! ";
				}elseif($withdraw_status == 2){
					$arrResult['message'] = " Withdrawal request in Pending! ";
				}else{
					$arrResult['message'] = " Withdrawal request Rejected! ";
				}
		    }
		    
		    $con->autocommit(true);
	    }
	}else{
		$arrResult['message'] = " Failed to Proceed. Something Went Wrong!";
	}

	echo json_encode($arrResult);