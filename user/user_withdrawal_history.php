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
				<table id="withdrawal_datatable" class="table table-striped table-bordered dataTable no-footer">
					<thead>
						<tr>
							<!--<th style="text-align: center; font-weight: bold; width: 0px;">ID</th>-->
							<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Method</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Status</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Amount</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$i = 1;
					$conditionString = "user_id=".$_POST['user_id'];
					$orderBy = "id DESC";
					$rLimit = "0,50";
					$fetchQry = fetchFromTable("withdrawal_history",null,$conditionString,$orderBy,$rLimit);
                	while($fetchRow = $fetchQry->fetch_assoc()){
                		if($fetchRow['withdraw_status'] == 0){
							$status = 'Rejected';
						}elseif($fetchRow['withdraw_status'] == 1){
							$status = 'Accepted';
						}elseif($fetchRow['withdraw_status'] == 2){
							$status = 'Pending';
						}elseif($fetchRow['withdraw_status'] == 3){
							$status = 'KYC Pending';
						}
				?>
						<tr>
                    		<!--<td><?= $fetchRow['id'] ?></td>-->
                    		<td><?= $fetchRow['withdraw_datetime'] ?></td>
                    		<td><?= $fetchRow['withdraw_method'] ?></td>
                    		<td><?= $status ?></td>
                    		<td class="text-right"><?= $fetchRow['amount'] ?></td>
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