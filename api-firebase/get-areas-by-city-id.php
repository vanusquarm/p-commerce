<?php
    header('Access-Control-Allow-Origin: *');
	header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	
	include_once('../includes/crud.php');
	$db=new Database();
	$db->connect();
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	/* accesskey:90336
  	 city_id:24 */
  	 if(!verify_token()){
		return false;
	 }
	if(isset($_POST['accesskey']) && isset($_POST['city_id'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		$city_ID = $db->escapeString($fn->xss_clean($_POST['city_id']));
		if(isset($_POST['keyword'])){
			$keyword = $db->escapeString($fn->xss_clean($_POST['keyword']));
		}else{
			$keyword = "";
		}
		if($access_key_received == $access_key){
			if($keyword == ""){
				// find menu by category id in menu table
				$sql_query = "SELECT id, name 
					FROM area 
					WHERE city_id = ".$city_ID." 
					ORDER BY id ASC";
			}else{
				// find menu by category id and keyword in menu table
				$sql_query = "SELECT id, name 
					FROM area 
					WHERE name LIKE '%".$keyword."%' AND city_id = ".$city_ID." 
					ORDER BY id ASC";
			}
			$db->sql($sql_query);
			$res=$db->getResult();			
			// $menus = array();
			// foreach($res as $row) {
			// 	$menus[] = array('Area'=>$row);
			// }			
			// create json output
			if (!empty($res)) {
				$response['error'] = false;
				$response['data'] = $res;
			}else{
				$response['error'] = true;
				$response['message'] = "no data found!";
			}
			$output = json_encode($response);
			// print_r(json_encode($res));

		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey and city id are required.');
	}
	//Output the output.
	echo $output;
	$db->disconnect(); 
?>