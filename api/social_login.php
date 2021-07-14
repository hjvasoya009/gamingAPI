<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
	
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;

	if(isset($_POST) && !empty($_POST)){
		
		$conditionString = 'id=1';
		$settingQry 	 = fetchFromTable("settings",null,$conditionString);
		$settingRow 	 = $settingQry->fetch_assoc();
		$signup_bonus 	 = $settingRow['signup_bonus'];
		$max_referral 	 = $settingRow['max_referral'];
		$referral_amount = $settingRow['referral_amount'];
		$support_email 	 = $settingRow['support_mail'];
		
		if(!isset($_POST['social_id']) || empty($_POST['social_id'])){
			$response['message'] = 'Please enter Social ID';
		}else{
			if(!isset($_POST['fcm_token']) || empty($_POST['fcm_token'])){
				$response['message'] = 'Please enter FCM Token';
				echo json_encode($response);
				exit();
			}
			
			foreach ($_POST as $key => $val) {
		        if (!is_array($val)){
					$$key = mysqli_real_escape_string($con, $val);
				}
		    }
			
			$conditionsString = "social_id='".$social_id."'";
			$rs = fetchFromTable("users", null, $conditionsString);		
			if (mysqli_num_rows($rs) == 1) {
				$row = mysqli_fetch_assoc($rs);
			    if($row['user_status'] == 1){
			    	$user_id = $row['id'];
			    	$bearer_token = generateToken(75);
			    	
			    	$data = new \stdClass();
			    	$data->bearer_token = $bearer_token;
			    	$data->fcm_token 	= $fcm_token;
			    	if($social_image != ''){
						$data->social_image = $social_image;
					}
	    			
				    $updateResult = editRecordFromTable("users", $data, $user_id);
					if (!$updateResult['mysqli_error']) {
				    	$data = new \stdClass();
				    	$row['user_id'] = $user_id;
				    	$row['bearer_token'] = $bearer_token;
				    	if($row['user_image'] != ''){
							$row['user_image'] = MAIN_URL.'/images/user_images/'.$row['user_image'];
						}elseif($row['social_image'] != ''){
							$row['user_image'] = $social_image;
						}else{
							$row['user_image'] = '';
						}
				    	$data = $row;
				        $response['status'] = 1;
				        $response['message'] = 'Login successful!';
				        $response['data'] = $data;
				    } else {
				        $response['message'] = 'Token updation failed!';
				    }
				}elseif($row['user_status'] == 2){
					$response['message'] = 'Unable to login! Your account has been suspended.';
				}else{
					$response['message'] = 'Unable to login! Your account is inactive.';
				}
			}elseif (mysqli_num_rows($rs) == 0){
				if(!isset($_POST['gender']) || empty($_POST['gender'])){
					$response['message'] = 'Please enter gender';
					echo json_encode($response);
					exit();
				}
				
			    if(!isset($_POST['first_name']) || empty($_POST['first_name'])){
					$response['message'] = 'Please enter first name';
					echo json_encode($response);
					exit();
				}
				
				if(!isset($_POST['last_name']) || empty($_POST['last_name'])){
					$response['message'] = 'Please enter last name';
					echo json_encode($response);
					exit();
				}
				
				if(!isset($_POST['email']) || empty($_POST['email'])){
					$response['message'] = 'Please enter email';
					echo json_encode($response);
					exit();
				}else{
					$conditionString = "email='".$_POST['email']."'";
					$rs = fetchFromTable("users", null, $conditionString);		
					if (mysqli_num_rows($rs) > 0) {
						$response['message'] = 'Email already exist! Please try another email!';
						echo json_encode($response);
						exit();
					}
				}
				
				if(!isset($_POST['username']) || empty($_POST['username'])){
					$response['message'] = 'Please enter username';
					echo json_encode($response);
					exit();
				}else{
					if(ctype_alnum($_POST['username'])){
						$conditionString = "username='".removeSpecialCharacters($_POST['username'])."'";
						$rs = fetchFromTable("users", null, $conditionString);		
						if (mysqli_num_rows($rs) > 0) {
							$response['message'] = 'Username already exist! Please try another username!';
							echo json_encode($response);
							exit();
						}
					}else{
						$response['message'] = 'Invalid username! Please use only alphabets and numbers to generate username!';
						echo json_encode($response);
						exit();
					}
				}
				
				if(!isset($_POST['password']) || empty($_POST['password'])){
					$response['message'] = 'Please enter password';
					echo json_encode($response);
					exit();
				}
				
				if(isset($_POST['promo_code']) && !empty(trim($_POST['promo_code']))){
					$referredByUserID = '';
					$conditionString = "referral_code='".$_POST['promo_code']."' AND user_status=1";
					$rs = fetchFromTable("users", null, $conditionString);
					if (mysqli_num_rows($rs) == 1){
						$referredByUserRow = mysqli_fetch_assoc($rs);
						$referredByUserID  = $referredByUserRow['id'];
						$referredByUserReferralBalance  = $referredByUserRow['referral_balance'];
						$referredByUserReferralsCount = $referredByUserRow['referrals_count'];
						if($referredByUserReferralsCount >= $max_referral){
							$referredByUserID = '';
							$response['message'] = 'This promocode is expired!';
							echo json_encode($response);
							exit();
						}
					}else{
						$referredByUserID = '';
						$response['message'] = 'You have entered wrong promo code or cannot be applicable anymore.';
						echo json_encode($response);
						exit();
					}
				}else{
					$referredByUserID = '';
				}
				
				foreach ($_POST as $key => $val) {
			        if (!is_array($val)){
						$$key = mysqli_real_escape_string($con, $val);
					}
			    }
			    
			    if($gender = 'M'){
					$gender = 'Male';
				}elseif($gender = 'F'){
					$gender = 'Female';
				}
			    
			    $data = new \stdClass();
			    $data->gender 			= $gender;
			    $data->first_name 		= $first_name;
			    $data->last_name 		= $last_name;
			    $data->email 			= $email;
			    $data->username 		= removeSpecialCharacters($username);
	    		$data->password			= $password;
			    $data->social_id 		= $social_id;
			    $data->social_image 	= $social_image;
	    		$data->referral_balance = $signup_bonus;
	    		$data->referred_by 		= $referredByUserID;
			    $data->fcm_token 		= $fcm_token;
			    $data->bearer_token 	= generateToken(75);
			    $data->updated_at 		= $datetime;
			    $data->created_at 		= $datetime;
			    
			    do {
					$referral_code = generateReferralCode();
					$referralCondition = "referral_code='".$referral_code."'";
					$referralQry = fetchFromTable("users",null,$referralCondition);
					$referralCount = $referralQry->num_rows;
				} while ($referralCount > 0);
				
			    $data->referral_code 	= $referral_code;
			    
			    $insertResult = insertToTable("users", $data);
			    
				if (!$insertResult['mysqli_error']) {
					$user_id = $insertResult['mysqli_insert_id'];
					$data->user_id = $user_id;
			        
			        $conditionsString = "id=$user_id";
					$rs = fetchFromTable("users", null, $conditionsString);		
					if (mysqli_num_rows($rs) == 1) {
						$row = mysqli_fetch_assoc($rs);
						$response['data'] = $row;
						
						if($signup_bonus > 0){
							$data = new \stdClass();
						    $data->user_id 				= $user_id;
						    $data->transaction_purpose	= 'Signup Bonus';
						    $data->transaction_method 	= 'Signup Bonus';
						    $data->transaction_datetime = $datetime;
						    $data->credit 				= $signup_bonus;
						    $data->updated_at 			= $datetime;
						    
						    $insertResult = insertToTable("transaction_history", $data);
							if (!$insertResult['mysqli_error']){
								$response['status'] = 1;
				        		$response['message'] = 'User registration successful!';
							}else{
								$response['status'] = 1;
								$response['message'] = 'User registration successful!! Error while updating signup bonus transaction!';
							}
						}else{
							$response['status'] = 1;
				        	$response['message'] = 'User registration successful!';
						}
						
						if($referredByUserID != ''){
							$data = new \stdClass();
							$data->referrals_count 	= $referredByUserReferralsCount+1;
							$updateResult = editRecordFromTable("users", $data, $referredByUserID);
							
							if (!$updateResult['mysqli_error']) {
								$response['status'] = 1;
			        			$response['message'] = 'User registration successful!';
							}else{
								$response['status'] = 1;
								$response['message'] = 'User registration successful! Error while updating Referral count addition to referred by user!';
							}
						}
						
					}else{
						$response['status'] = 1;
						$response['message'] = 'User registration successful! Error while fetching user data!';
					}
			        
			    } else {
			        $response['message'] = 'User registration unsuccessful!';
			    }
			}else{
				$response['message'] = 'Multiple user found with this Social ID';
			}
		}
		
		if($response['status'] == 1){
			$to      = $_POST['email'];
			$subject = "Welcome to Gaming Akhada";
										
	$email_html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
			<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
			<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
			<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
			<title>'.$subject.'</title>
			<style type="text/css">
				@media screen and (max-width: 600px){
					table[class="container"]{
						width: 95% !important;
					}
				}
				#outlook a{
					padding: 0;
				}
				body{
					width: 100% !important;
					-webkit-text-size-adjust: 100%;
					-ms-text-size-adjust: 100%;
					margin: 0;
					padding: 0;
				}
				.ExternalClass{
					width: 100%;
				}
				.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div{
					line-height: 100%;
				}
				#backgroundTable{
					margin: 0;
					padding: 0;
					width: 100% !important;
					line-height: 100% !important;
				}
				img{
					outline: none;
					text-decoration: none;
					-ms-interpolation-mode: bicubic;
				}
				a img{
					border: none;
				}
				.image_fix{
					display: block;
				}
				p{
					margin: 1em 0;
				}
				h1, h2, h3, h4, h5, h6{
					color: black !important;
				}

				h1 a, h2 a, h3 a, h4 a, h5 a, h6 a{
					color: blue !important;
				}

				h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active{
					color: red !important;
				}

				h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited{
					color: purple !important;
				}

				table td{
					border-collapse: collapse;
				}

				table{
					border-collapse: collapse;
					mso-table-lspace: 0pt;
					mso-table-rspace: 0pt;
				}

				a{
					color: #000;
				}

				@media only screen and (max-device-width: 480px){

					a[href^="tel"], a[href^="sms"]{
						text-decoration: none;
						color: black;
						pointer-events: none;
						cursor: default;
					}

					.mobile_link a[href^="tel"], .mobile_link a[href^="sms"]{
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}
				}


				@media only screen and (min-device-width: 768px) and (max-device-width: 1024px){
					a[href^="tel"], a[href^="sms"]{
						text-decoration: none;
						color: blue;
						pointer-events: none;
						cursor: default;
					}

					.mobile_link a[href^="tel"], .mobile_link a[href^="sms"]{
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}
				}
				h2{
					color: #181818;
					font-family: Helvetica, Arial, sans-serif;
					font-size: 22px;
					line-height: 22px;
					font-weight: normal;
				}
				a.link1{

				}
				a.link2{
					color: #fff;
					text-decoration: none;
					font-family: Helvetica, Arial, sans-serif;
					font-size: 16px;
					color: #fff;
					border-radius: 4px;
				}
				p{
					color: #555;
					font-family: Helvetica, Arial, sans-serif;
					font-size: 16px;
					line-height: 160%;
				}
			</style>

			<script type="colorScheme" class="swatch active">
				{
					"name":"Default",
					"bgBody":"ffffff",
					"link":"fff",
					"color":"555555",
					"bgItem":"ffffff",
					"title":"181818"
				}
			</script>

		</head>
		<body>
			<table cellpadding="0" width="100%" cellspacing="0" border="0" id="backgroundTable" class="bgBody">
				<tr>
					<td>
						<table cellpadding="0" width="100%" class="container" align="center" cellspacing="0" border="0">
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" class="container">
										<tr>
											<td class="movableContentContainer bgItem">

												<div class="movableContent">
													<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" class="container">
														<tr>
															<td valign="top" align="center">
																<div class="contentEditableContainer contentImageEditable">
																	<div class="contentEditable" align="center" >
																		<img src="'.MAIN_URL.'/dist/img/big_logo.png" height="200px"  alt="Logo"  data-default="placeholder" style="border-radius: 1000px;" />
																	</div>
																</div>
															</td>
														</tr>
													</table>
												</div>

												<div class="movableContent">
													<table cellpadding="0" cellspacing="0" border="0" align="center" width="100%" class="container">
														<tr>
															<td width="100%" colspan="3" align="center" style="padding-bottom:10px;padding-top:25px;">
																<div class="contentEditableContainer contentTextEditable">
																	<div class="contentEditable" align="center" >
																		<h2 >
																			Gaming Akhada Team
																		</h2>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td align="center">
																<div class="contentEditableContainer contentTextEditable">
																	<div class="contentEditable" align="left" >
																		<p >
																			'.$_POST['username'].',
																			<br/>
																			<br/>
																			<p>
																				Welcome to Gamingakhada - Best Virtual Gaming Place to Earn Money Online
																			</p>
																			<p>
																				Have Fun to Earn
																			</p>
																			<p>
																				<br />Looking for the fun-filled options to make money? Gaming Akhada is the competitive gaming platform that let you enjoy having a pocket full of money. With Limitless money making game, we are introducing an exciting and fun way to earn money.
																			</p>
																			<p>
																				<br />Play games for real money in India and earn exciting surprises. We are passionate gaming platform where you can play online games for cash on any device. We have a collection of popular games that let you earn a huge amount.
																			</p>
																			<p>
																				<br />We welcome every individual to play online games for money and claim to be the rewards. With us, earning money in a short period is now possible for all. If you want to earn money with Pubg, Gaming Akhada is the place you should choose.
																			</p>
																			<p>
																				Now, access your favorite game and let us bring you unlimited fun. Earn money playing Pubg most conveniently.
																			</p>
																			<p>
																				<br />Regards From,
																				Gaming Akhada
																			</p>
																		</p>
																	</div>
																</div>
															</td>
														</tr>
													</table>
													<table cellpadding="0" cellspacing="0" border="0" align="center" width="600" class="container">
														<tr>
															<td align="center" style="padding-top:25px;">
																<table cellpadding="0" cellspacing="0" border="0" align="center" width="200" height="50">
																	<tr>
																		<td bgcolor="#46ae49" align="center" style="border-radius:4px;" width="200" height="50">
																			<div class="contentEditableContainer contentTextEditable">
																				<div class="contentEditable" align="center" >
																					<a target="_blank" href="https://gamingakhada.com" class="link2">
																						Visit Website
																					</a>
																				</div>
																			</div>
																		</td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
	</html>';
			$message = $email_html;
			$headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'X-Mailer: PHP' . "\r\n";
            $headers .= 'From: '.$support_email.'<' . $support_email . ">\r\n";
			mail($to,$subject,$email_html,$headers);
		}
		
	    echo json_encode($response);
	    exit();
	}
?>