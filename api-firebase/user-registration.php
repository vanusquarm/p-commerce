<?php 
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);
include '../includes/variables.php';
include_once('verify-token.php');
$db=new Database();
$db->connect();
$fn = new custom_functions();
$settings = $fn->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
include 'send-email.php';
include 'send-sms.php';

$response = array();
$accesskey = $_POST['accesskey'];
// echo $access_key;

if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'verify-user')) {
    if(!verify_token()){
        return false;
    }
	$mobile = $db->escapeString($_POST['mobile']);
// 	$country_code = $db->escapeString($_POST['country_code']);
	if (!empty($mobile)) {
		$sql = 'select id from users where mobile ='.$mobile;
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if($num_rows > 0){
			$response["error"]   = true;
			$response["id"]   = $res[0]['id'];
			$response["message"] = "This mobile is already registered. Please login!";
			echo json_encode($response);
		}else if($num_rows == 0){
		    $response["error"]   = false;
			$response["message"] = "Ready to sent firebase OTP request!";
			
			echo json_encode($response);
		}
	}
	else{
	$response['error'] = true;
	$response['message'] = "mobile is required.";
	echo json_encode($response);
	}
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'verify-user-email') {
    if(!verify_token()){
        return false;
    }
    $email  = $db->escapeString($_POST['email']);
    $otp    = rand(100000, 999999);
    
	$sql = "select `id`,`name` from `users` where `email`='".$email."'";
	$db->sql($sql);
	$result = $db->getResult();
	$num_rows = $db->numRows($result);
	if($num_rows == 0){
		//send email
		$to = $email;
		$subject = "$app_name Registration Verification";
		
		$message = "<#> Your OTP for $app_name Registration verification is : ".$otp.". Please enter this OTP to activate your profile. ";
		
		if(!send_email($to,$subject,$message)){
			$response["error"]   = true;
			$response["message"] = "Activation mail could not be sent!Try Again";
			echo json_encode($response);
			return false;
		}
		$response["error"]   = false;
		$response["message"] = "OTP for account activation is sent to your email. Please verify it to complete the registration process."/*.$smsResult*/;
		$response["OTP"] = $otp;
	}else{
		$response["error"]   = true;
		$response["message"] = "Email is already registered. Please login!";
	}
	echo json_encode($response);
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'register')) {
    if(!verify_token()){
        return false;
    }
	$name  		= (isset($_POST['name']))?$db->escapeString($_POST['name']):"";
	$mobile  	= (isset($_POST['mobile']))?$db->escapeString($_POST['mobile']):"";
	$country_code  	= (isset($_POST['country_code']))?$db->escapeString($_POST['country_code']):"";
	$fcm_id  	= (isset($_POST['fcm_id']))?$db->escapeString($_POST['fcm_id']):"";
	$dob  	= (isset($_POST['dob']))?$db->escapeString($_POST['dob']):"";
	$email  	= ( isset($_POST['email']) && !empty($_POST['email']) )?$db->escapeString($_POST['email']):"";
    $password 	= md5($_POST['password']);
    $city 		= (isset($_POST['city_id']))?$db->escapeString($_POST['city_id']):"";
    $area 		= (isset($_POST['area_id']))?$db->escapeString($_POST['area_id']):"";
	$street 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$pincode 	= (isset($_POST['pincode']))?$db->escapeString($_POST['pincode']):"";
	$api_key 	= (isset($_POST['api_key']))?$db->escapeString($_POST['api_key']):"";
	$latitude 	= (isset($_POST['latitude']))?$db->escapeString($_POST['latitude']):"0";
	$longitude 	= (isset($_POST['longitude']))?$db->escapeString($_POST['longitude']):"0";
    $status 	= 1;
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$referral_code  = "";
	for ($i = 0; $i < 10; $i++) {
	    $referral_code .= $chars[mt_rand(0, strlen($chars)-1)];
	}
	if(isset($_POST['friends_code']) && $_POST['friends_code'] != ''){
		$sql = "SELECT id FROM users WHERE referral_code='".$_POST['friends_code']."'";
		$db->sql($sql);
		$result = $db->getResult();
		$num_rows = $db->numRows($result);
		if($num_rows > 0){
			$friends_code = $_POST['friends_code'];
		}else{
			$response["error"]   = true;
			$response["message"] = "Invalid friends code!";
			echo json_encode($response);
			return false;
		}
	}else{
		$friends_code = '';
	}

	if (!empty($mobile)) {
		$sql = "select mobile from users where mobile='".$mobile."'";
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if($num_rows > 0){

			$response["error"]   = true;
			$response["message"] = "This $mobile is already registered. Please login!";
			
			echo json_encode($response);

			
		}else if($num_rows == 0){
			
			//user is not registered, insert the data to the database
			// $sql = "INSERT INTO `users`(`name`, `email`, `mobile`,`dob`, `city`,`area`, `street` , `pincode`, `apikey`, `password`,`referral_code`,`friends_code`,`fcm_id`,`latitude`,`longitude`,`status`) VALUES 
			// ('$name','$email','$mobile','$dob','$city','$area','$street','$pincode','$api_key','$password','$referral_code','$friends_code','$fcm_id','$latitude','$longitude',$status)";
			$data = array(
			    'name' => $name,
			    'email' => $email,
			    'mobile' => $mobile,
			    'country_code' => $country_code,
			    'fcm_id' => $fcm_id,
			    'dob' => $dob,
			    'city' => $city,
			    'area' => $area,
			    'street' => $street,
			    'pincode' => $pincode,
			    'apikey' => $api_key,
			    'password' => $password,
			    'referral_code' => $referral_code,
			    'friends_code' => $friends_code,
			    'latitude' => $latitude,
			    'longitude' => $longitude,
			    'status' => $status
			);
			$db->insert('users',$data);
			$res = $db->getResult();
			
			$response["error"]   = false;
			$response["message"] = "User registered successfully";
			$response["user_id"] = $res[0];
			$response['name'] = $data['name'];
			$response['email'] /*= $_SESSION['email']*/    = $data['email'];
			$response['mobile'] /*= $_SESSION['user']*/    = $data['mobile'];
			$response['country_code'] = $data['country_code'];
			$response['fcm_id'] = $data['fcm_id'];
			$response['dob'] = $data['dob'];
			$response['city_id'] = !empty($data['city'])?$data['city']:'';
			$response['city_name'] = !empty($data['city_name'])?$data['city_name']:'';
			$response['area_id'] = !empty($data['area'])?$data['area']:'';
			$response['area_name'] = !empty($data['area_name'])?$data['area_name']:'';
			$response['street']     = $data['street'];
			$response['pincode']     = $data['pincode'];
			$response['referral_code']     = $data['referral_code'];
			$response['friends_code']     = $data['friends_code'];
			$response['latitude']     = (!empty($data['latitude']))?$data['latitude']:'0';
			$response['longitude']     = (!empty($data['longitude']))?$data['longitude']:'0';
			$response['apikey']     = $data['apikey'];
			$response['status']     = $data['status'];
			$response['created_at']     = date('Y-m-d h:i:s a');
			echo json_encode($response);
		}
	}else{
		echo "Email is required.";
	}
}


if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'edit-profile') {
    if(!verify_token()){
        return false;
    }
    $id   	= $db->escapeString($_POST['id']);
    $name   = $db->escapeString($_POST['name']);
    $email  = $db->escapeString($_POST['email']);
    $city   = $db->escapeString($_POST['city_id']);
    $area   = $db->escapeString($_POST['area_id']);
    $street = $db->escapeString($_POST['street']);
    $pincode = $db->escapeString($_POST['pincode']);
    $dob = $db->escapeString($_POST['dob']);
	$latitude 	= (isset($_POST['latitude']) && !empty($_POST['latitude']))?$db->escapeString($_POST['latitude']):"0";
	$longitude 	= (isset($_POST['longitude']) && !empty($_POST['longitude']))?$db->escapeString($_POST['longitude']):"0";

    $sql = 'select * from users where id ='.$id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
    
		$sql = 'UPDATE `users` SET `name`="'.$name.'",`email`="'.$email.'",`dob`="'.$dob.'",`city`="'.$city.'",`area`="'.$area.'",`street`="'.$street.'",`pincode`="'.$pincode.'",`latitude`="'.$latitude.'",`longitude`="'.$longitude.'" WHERE `id`='.$id;
		$db->sql($sql);

		$response["error"]   = false;
		$response["message"] = "Profile has been updated successfully.";

	}else{
		$response["error"]   = true;
		$response["message"] = "valid id is required!!";
	}
	echo json_encode($response);
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'change-password') {
    if(!verify_token()){
        return false;
    }
    
    $id   	= $db->escapeString($_POST['id']);
    $password = $db->escapeString($_POST['password']);
    // $password = 'test1234';
    $password = md5($password);
    
    // if(!empty($password)) {
    	$sql = 'UPDATE `users` SET `password`="'.$password.'" WHERE `id`='.$id;
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Profile updated successfully";
		}else{
			$response["error"]   = true;
			$response["message"] = "Something went wrong! Try Again!";
		}
	echo json_encode($response);
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-email') {
    if(!verify_token()){
        return false;
    }
    $email  = $db->escapeString($_POST['email']);
    $password = rand(10000,99999);
	$encrypted_password = md5($password);
	
	$sql = "select `id`,`name` from `users` where `email`='".$email."'";
	$db->sql($sql);
	$result = $db->getResult();
	if($db->numRows($result)){
		//send email
		$to = $email;
		$subject = "Password Recovery Mail - Password is reset ( $email )";
		$message = "Hi, <b>".$row['name']." - ".$email."</b>, \t\r\n Your Password has been reset. Please Login with the new password. \r\nYour new Password is : ".$password."\r\n Thank you";
		
		if(!send_email($to,$subject,$message)){
			$response["error"]   = true;
			$response["message"] = "Password could not be reset!Try Again";
			echo json_encode($response);
			return false;
		}
		$sql = 'UPDATE `users` SET `password`="'.$encrypted_password.'" WHERE `email`="'.$email.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Password updated successfully! Please check the mail!";
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Email ID does not exist!";
	}
	echo json_encode($response);
}


if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-mobile') {
    if(!verify_token()){
        return false;
    }
    $mobile  = $db->escapeString($_POST['mobile']);
    // $password = rand(10000,99999);
    $password = 'test1234';
	$encrypted_password = md5($password);
	$sql = "select `id`,`name`,`country_code` from `users` where `mobile`='".$mobile."'";
// 	echo $sql;
	$db->sql($sql);
	$result = $db->getResult();
	
	if($db->numRows($result) > 0){
	    $country_code = $result[0]['country_code'];
		//send sms
		$message = 'Your Password for '.$app_name.' is Reset. Please login using new Password : '.$password.'.';
		$sql = 'UPDATE `users` SET `password`="'.$encrypted_password.'" WHERE `mobile`="'.$mobile.'"';
		if($db->sql($sql)){
		        // sendSms($mobile,$message,$country_code);    
		        $response["error"]   = false;
			    $response["message"] = "Password is sent successfully! Please login via the OTP sent to your mobile number!";
			
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number does not exist! Please Register";
	}
	echo json_encode($response);
}


if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'delete-notification') {
	if($permissions['notifications']['delete']==0){
		echo 2;
		return false;
	}
    $id		= $_POST['id'];
    $image 	= $_POST['image'];
	
	if(!empty($image))
		unlink('../'.$image);
	
	$sql = 'DELETE FROM `notifications` WHERE `id`='.$id;
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'register-device') {
    if(!verify_token()){
        return false;
    }
    $user_id  = $db->escapeString($_POST['user_id']);
    $token  = $db->escapeString($_POST['token']);
    
    $sql = "select `id` from `users` where `id`='".$user_id."'";
	$db->sql($sql);
	$result=$db->getResult();
	if($db->numRows($result) > 0){
		// Update the Device ID
		$sql = 'UPDATE `users` SET `fcm_id`="'.$token.'" WHERE `id`="'.$user_id.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Device updated successfully";
		}
	}else{
	    // $sql = "INSERT INTO devices (user_id, token) VALUES ('$user_id','$token')";
	    // $db->sql($sql);
		$response["error"]   = true;
		$response["message"] = "User does't exists.";
	}
	echo json_encode($response);
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'send-invitation') {
    if(!verify_token()){
        return false;
    }
	$referral_code = $db->escapeString($_POST['referral_code']);
    $friend_id  = $db->escapeString($_POST['friend_id']);
    $sql = "select * from `users` where `referral_code`='".$referral_code."'";
	$db->sql($sql);
	$result=$db->getResult();
	if($db->numRows($result) > 0){
		// Update the Device ID
		$sql = 'UPDATE `users` SET `friends_code`="'.$referral_code.'" WHERE `id`="'.$friend_id.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Invitation sent successfully";
			$response['data'] = $result;
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Invalid referral code.";
	}
	echo json_encode($response);
}


?>