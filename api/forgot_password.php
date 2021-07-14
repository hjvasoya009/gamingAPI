<?php
	include_once "../../connection.php";
	include_once "../../utilities.php";
		
	$_POST = json_decode(file_get_contents('php://input'),TRUE);
	$response['status'] = 0;
	
	if(isset($_POST) && !empty($_POST)){
		
		if(!isset($_POST['email']) || empty($_POST['email'])){
			$response['message'] = 'Please enter email id';
			echo json_encode($response);
			exit();
		}
		
		foreach ($_POST as $key => $val) {
	        if (!is_array($val)){
				$$key = mysqli_real_escape_string($con, $val);
			}
	    }
	    
	    $conditionString = "email='$email' AND user_status=1";
		$userQry = fetchFromTable("users",null,$conditionString);
		
		if(mysqli_num_rows($userQry) == 1){
			
			$userRow = mysqli_fetch_assoc($userQry);
			$user_id = $userRow['id'];
			
			$temperory_password		= generateReferralCode();
			
			$data = new \stdClass();
		    $data->password 		= $temperory_password;
		    $data->updated_at 		= $datetime;
		    
		    $insertResult = editRecordFromTable("users", $data, $user_id);
		    
			if (!$insertResult['mysqli_error']) {
				
				$conditionString = 'id=1';
				$rs = fetchFromTable("settings",null,$conditionString);
				if(mysqli_num_rows($rs) == 1){
					$row = $rs->fetch_assoc();
					$support_email = $row['support_mail'];
					
					$to      = $email;
					$subject = "Gaming Akhada Password Reset for ".$userRow['username'];
									
$email_html = '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		@media screen and (max-width: 720px)
		{
			body .c-v84rpm
			{
				width: 100% !important;
				max-width: 720px !important;
			}
			body .c-v84rpm .c-7bgiy1 .c-1c86scm
			{
				display: none !important;
			}
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-pekv9n .c-1qv5bbj,
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-1c9o9ex .c-1qv5bbj,
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-90qmnj .c-1qv5bbj
			{
				border-width: 1px 0 0 !important;
			}
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-183lp8j .c-1qv5bbj
			{
				border-width: 1px 0 !important;
			}
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-pekv9n .c-1qv5bbj
			{
				padding-left: 12px !important;
				padding-right: 12px !important;
			}
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-1c9o9ex .c-1qv5bbj,
			body .c-v84rpm .c-7bgiy1 .c-f1bud4 .c-90qmnj .c-1qv5bbj
			{
				padding-left: 8px !important;
				padding-right: 8px !important;
			}
			body .c-v84rpm .c-ry4gth .c-1dhsbqv
			{
				display: none !important;
			}
		}


		@media screen and (max-width: 720px)
		{
			body .c-v84rpm .c-ry4gth .c-1vld4cz
			{
				padding-bottom: 10px !important;
			}
		}
	</style>
	<title>
		Recover your Crisp password
	</title>
</head>

<body style="margin: 0; padding: 0; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; font-stretch: normal; font-size: 14px; letter-spacing: .35px; background: #EFF3F6; color: #333333;">
	<table border="1" cellpadding="0" cellspacing="0" align="center" class="c-v84rpm" style="border: 0 none; border-collapse: separate; width: 720px;" width="720">
		<tbody>
			<tr class="c-1syf3pb" style="border: 0 none; border-collapse: separate; height: 114px;">
				<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
					<table align="center" border="1" cellpadding="0" cellspacing="0" class="c-f1bud4" style="border: 0 none; border-collapse: separate;">
						<tbody>
							<tr align="center" class="c-1p7a68j" style="border: 0 none; border-collapse: separate; padding: 16px 0 15px;">
								<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
									<img alt="" src="'.MAIN_URL.'/dist/img/big_logo.png" class="c-1shuxio" style="border: 0 none; line-height: 100%; outline: none; text-decoration: none;" height="200">
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr class="c-7bgiy1" style="border: 0 none; border-collapse: separate; -webkit-box-shadow: 0 3px 5px rgba(0,0,0,0.04); -moz-box-shadow: 0 3px 5px rgba(0,0,0,0.04); box-shadow: 0 3px 5px rgba(0,0,0,0.04);">
				<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
					<table align="center" border="1" cellpadding="0" cellspacing="0" class="c-f1bud4" style="border: 0 none; border-collapse: separate; width: 100%;" width="100%">
						<tbody>
							<tr class="c-pekv9n" style="border: 0 none; border-collapse: separate; text-align: center;" align="center">
								<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
									<table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-1qv5bbj" style="border: 0 none; border-collapse: separate; border-color: #E3E3E3; border-style: solid; width: 100%; border-width: 1px 1px 0; background: #FBFCFC; padding: 40px 54px 42px;">
										<tbody>
											<tr style="border: 0 none; border-collapse: separate;">
												<td class="c-1m9emfx c-zjwfhk" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; color: #1D2531; font-size: 25.45455px;"
                          valign="middle">
													'.$userRow['first_name'].' '.$userRow['last_name'].' ('.$userRow['username'].'), recover your password.
												</td>
											</tr>
											<tr style="border: 0 none; border-collapse: separate;">
												<td class="c-46vhq4 c-4w6eli" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeueRoman&quot;,&quot;HelveticaNeue-Roman&quot;,&quot;HelveticaNeueRoman&quot;,&quot;HelveticaNeue-Regular&quot;,&quot;HelveticaNeueRegular&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 400; color: #7F8FA4; font-size: 15.45455px; padding-top: 20px;"
                          valign="middle">
													Looks like you lost your password?
												</td>
											</tr>
											<tr style="border: 0 none; border-collapse: separate;">
												<td class="c-eitm3s c-16v5f34" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueMedium&quot;,&quot;HelveticaNeue-Medium&quot;,&quot;HelveticaNeueMedium&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,sans-serif;font-weight: 500; font-size: 13.63636px; padding-top: 12px;"
                          valign="middle">
													We’re here to help. Use below code to login and reset your password.
												</td>
											</tr>
											<tr style="border: 0 none; border-collapse: separate;">
												<td class="c-rdekwa" style="border: 0 none; border-collapse: separate; vertical-align: middle; padding-top: 38px;" valign="middle">
													<a href="" target="_blank"
                            class="c-1eb43lc c-1sypu9p c-16v5f34" style="color: #000000; -webkit-border-radius: 4px; font-family: &quot; HelveticaNeueMedium&quot;,&quot;HelveticaNeue-Medium&quot;,&quot;HelveticaNeueMedium&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,sans-serif;font-weight: 500; font-size: 15px; line-height: 15px; display: inline-block; letter-spacing: .7px; text-decoration: none; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; border-radius: 4px; background-color: #288BD5; background-image: url(&quot;https://mail.crisp.chat/images/linear-gradient(-1deg,#137ECE2%,#288BD598%)&quot; );color: #ffffff; padding: 12px 24px;">
														'.$temperory_password.'
													</a>
												</td>
											</tr>
											<tr style="border: 0 none; border-collapse: separate;">
												<td class="c-ryskht c-zjwfhk" style="border: 0 none; border-collapse: separate; vertical-align: middle; font-family: &quot; HelveticaNeueLight&quot;,&quot;HelveticaNeue-Light&quot;,&quot;HelveticaNeueLight&quot;,&quot;HelveticaNeue&quot;,&quot;HelveticaNeue&quot;,Helvetica,Arial,&quot;LucidaGrande&quot;,sans-serif;font-weight: 300; font-size: 12.72727px; font-style: italic; padding-top: 52px;"
                          valign="middle">
													If you didn’t ask to recover your password, please ignore this email.
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							
							<tr class="c-183lp8j" style="border: 0 none; border-collapse: separate;">
								<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
									<table border="1" cellpadding="0" cellspacing="0" width="100%" class="c-1qv5bbj" style="border: 0 none; border-collapse: separate; border-color: #E3E3E3; border-style: solid; width: 100%; background: #FFFFFF; border-width: 1px; font-size: 11.81818px; text-align: center; padding: 18px 40px 20px;"
              align="center">
										<tbody>
											<tr style="border: 0 none; border-collapse: separate;">
												<td style="border: 0 none; border-collapse: separate; vertical-align: middle;" valign="middle">
													<span class="c-1w4lcwx">
														You receive this email because you or someone initiated a password recovery operation on your Gaming Akhada account.
													</span>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</body>
</html>';
					$message = $email_html;
				    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers .= 'X-Mailer: PHP' . "\r\n";
                    $headers .= 'From: '.$support_email.'<' . $support_email . ">\r\n";
                    
					mail($to,$subject,$message,$headers);
					
			        $response['status'] = 1;
			        $response['message'] = 'Password has been sent to your email address';
				}else{
					$response['message'] = 'No Support exist at this moment!';
				}
		    } else {
		        $response['message'] = 'Your password reset request failed!';
		    }
		}else{
			$response['message'] = 'Email not found on Gaming Akhada!';
		}
	}
	echo json_encode($response);
	exit();
?>