<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	if(!isLoggedIn()){
		redirectToLoginPage();
	}
	
	if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
?>
		<div class="row">
			<div class="col-sm-12">
				<table id="transaction_datatable" class="table table-striped table-bordered dataTable no-footer">
					<thead>
						<tr>
							<!--<th style="text-align: center; font-weight: bold; width: 0px;">#</th>-->
							<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Purpose</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Method</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Amount</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$i = 1;
					$conditionString = "user_id=".$_POST['user_id'];
					$orderBy = "id DESC";
					$rLimit = "0,50";
					$fetchQry = fetchFromTable("transaction_history",null,$conditionString,$orderBy,$rLimit);
                	while($fetchRow = $fetchQry->fetch_assoc()){
                		if($fetchRow['credit'] != 0){
                			$amount = $fetchRow['credit'];
							$textColor = 'text-success';
						}elseif($fetchRow['debit'] != 0){
							$textColor = 'text-info';
							$amount = $fetchRow['debit'];
						}else{
							$textColor = '';
							$amount = '0.00';
						}
				?>
						<tr>
                    		<!--<td><?= $fetchRow['id'] ?></td>-->
                    		<td><?= $fetchRow['transaction_datetime'] ?></td>
                    		<td><?= $fetchRow['transaction_purpose'] ?></td>
                    		<td><?= $fetchRow['transaction_method'] ?></td>
                    		<td class="text-right <?= $textColor ?>"><?= $amount ?></td>
                    	</tr>
				<?php
						$i++;
					}
                ?>
					</tbody>
				</table>
			</div>
		</div>
<?php
	}
	die;