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
				<table id="deposit_datatable" class="table table-striped table-bordered dataTable no-footer">
					<thead>
						<tr>
							<th style="text-align: center; font-weight: bold; width: 0px;">Date</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Transaction ID</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Gateway</th>
							<th style="text-align: center; font-weight: bold; width: 0px;">Amount</th>
						</tr>
					</thead>
					<tbody>
				<?php
					$i = 1;
					$conditionsString = "user_id=".$_POST['user_id'];
					$orderBy = "id DESC";
					$rLimit = "0,50";
					$fetchQry = fetchFromTable("deposit_history",null,$conditionsString,$orderBy,$rLimit);
                	while($fetchRow = $fetchQry->fetch_assoc()){
				?>
						<tr>
                    		<td><?= $fetchRow['payment_datetime'] ?></td>
                    		<td><?= $fetchRow['transaction_id'] ?></td>
                    		<td><?= $fetchRow['payment_gateway'] ?></td>
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